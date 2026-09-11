/** Hizmetler listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import { bindBulk, bulkCell } from '../../core/bulk.js';
import { revisionButton } from '../../core/revisions.js';
import { pageViews } from '../analytics/views.js';
import { scoreBadge } from '../seo/badge.js';

const views = pageViews('service');

const BADGES = {
    published: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    draft: 'bg-warning-100 dark:bg-[#15203c] text-warning-600 dark:text-warning-500',
};

const thumb = (item) => item.thumb
    ? `<img src="${escapeHtml(item.thumb)}" alt="" class="w-[44px] h-[34px] rounded-md object-cover shrink-0">`
    : '<span class="w-[44px] h-[34px] rounded-md bg-gray-50 dark:bg-[#15203c] flex items-center justify-center shrink-0"><i class="material-symbols-outlined !text-[18px] text-gray-400">image</i></span>';

const selection = bindBulk({
    module: 'service',
    body: document.getElementById('service-table-body'),
    onDone: () => table.reload(),
});

const table = new DataTable({
    endpoint: '/admin/service/datatable',
    body: document.getElementById('service-table-body'),
    search: document.getElementById('service-search'),
    filters: {
        status: document.getElementById('service-status'),
        service_region_id: document.getElementById('service-region'),
    },
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz hizmet eklenmedi.',
    onLoaded: (items) => views.fill(items),
    reorder: {
        button: document.getElementById('service-reorder'),
        endpoint: '/admin/service/reorder',
    },
    onLoaded: () => selection.sync(),
    row: (item) => `<tr data-id="${item.id}">
        ${bulkCell(item)}
        ${reorderHandle()}
        ${cell(thumb(item))}
        ${cell(`<div class="min-w-0">
            <span class="font-medium block truncate max-w-[320px]">${escapeHtml(item.title)}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.slug)}</span>
        </div>`)}
        ${cell(item.regions_count)}
        ${cell(`<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[item.status]}">${escapeHtml(item.status_label)}</span>`)}
        ${cell(scoreBadge(item.seo_score, item.seo_grade))}
        ${views.cell(item)}
        ${cell(escapeHtml(item.created_at ?? '—'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Service\\Service', item.id)}
            ${revisionButton('App\\Models\\Service\\Service', item.id)}
            <a href="/admin/service/${item.id}/edit" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('service-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu hizmet silinsin mi? Bölge bağlantıları kaldırılır, kapak görseli kütüphanede kalır.', {
        title: 'Hizmeti sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/service/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
