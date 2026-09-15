/** Foto Galeri listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError, adminUrl } from '../../core/http.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import { bindBulk, bulkCell } from '../../core/bulk.js';
import { revisionButton } from '../../core/revisions.js';
import { scoreBadge } from '../seo/badge.js';

const MODEL = 'App\\Models\\Gallery\\Gallery';

const BADGES = {
    published: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    draft: 'bg-warning-100 dark:bg-[#15203c] text-warning-600 dark:text-warning-500',
};

const thumb = (item) => item.thumb
    ? `<img src="${escapeHtml(item.thumb)}" alt="" class="w-[44px] h-[34px] rounded-md object-cover shrink-0">`
    : '<span class="w-[44px] h-[34px] rounded-md bg-gray-50 dark:bg-[#15203c] flex items-center justify-center shrink-0"><i class="material-symbols-outlined !text-[18px] text-gray-400">photo_library</i></span>';

const selection = bindBulk({
    module: 'gallery',
    body: document.getElementById('gallery-table-body'),
    onDone: () => table.reload(),
});

const table = new DataTable({
    endpoint: adminUrl('/gallery/datatable'),
    body: document.getElementById('gallery-table-body'),
    search: document.getElementById('gallery-search'),
    filters: {
        status: document.getElementById('gallery-status'),
    },
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz galeri eklenmedi.',
    onLoaded: () => selection.sync(),
    reorder: {
        button: document.getElementById('gallery-reorder'),
        endpoint: adminUrl('/gallery/reorder'),
    },
    row: (item) => `<tr data-id="${item.id}">
        ${bulkCell(item)}
        ${reorderHandle()}
        ${cell(thumb(item))}
        ${cell(`<div class="min-w-0">
            <span class="font-medium truncate max-w-[320px] block">${escapeHtml(item.title)}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.slug)}</span>
        </div>`)}
        ${cell(item.photos_count > 0
            ? `<span class="inline-flex items-center gap-[3px] text-[11px] text-gray-500 dark:text-gray-400">
                <i class="material-symbols-outlined !text-[14px]">photo_library</i>${item.photos_count} fotoğraf
               </span>`
            : '<span class="text-xs text-gray-400">—</span>')}
        ${cell(`<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[item.status]}">${escapeHtml(item.status_label)}</span>`)}
        ${cell(scoreBadge(item.seo_score, item.seo_grade))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton(MODEL, item.id)}
            ${revisionButton(MODEL, item.id)}
            <a href="${adminUrl(`/gallery/${item.id}/edit`)}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('gallery-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm(
        'Bu galeri silinsin mi? Fotoğraflar medya kütüphanesinde kalır, yalnızca albüm bağlantısı kalkar.',
        { title: 'Galeriyi sil', accept: 'Evet, sil' },
    );

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(adminUrl(`/gallery/${remove.dataset.delete}`));
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
