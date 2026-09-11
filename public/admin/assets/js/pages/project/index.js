/** Neler Yaptık (projeler) listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import { bindBulk, bulkCell } from '../../core/bulk.js';
import { revisionButton } from '../../core/revisions.js';
import { scoreBadge } from '../seo/badge.js';

const MODEL = 'App\\Models\\Project\\Project';

const BADGES = {
    published: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    draft: 'bg-warning-100 dark:bg-[#15203c] text-warning-600 dark:text-warning-500',
};

const thumb = (item) => item.thumb
    ? `<img src="${escapeHtml(item.thumb)}" alt="" class="w-[44px] h-[34px] rounded-md object-cover shrink-0">`
    : '<span class="w-[44px] h-[34px] rounded-md bg-gray-50 dark:bg-[#15203c] flex items-center justify-center shrink-0"><i class="material-symbols-outlined !text-[18px] text-gray-400">workspaces</i></span>';

/** Galeri / video / sonuç sayıları — sıfır olanlar basılmaz, kolon kalabalıklaşmasın. */
function contentChips(item) {
    const chips = [];

    if (item.gallery_count > 0) {
        chips.push(['photo_library', `${item.gallery_count} görsel`]);
    }

    if (item.has_video) {
        chips.push(['movie', 'video']);
    }

    if (item.results_count > 0) {
        chips.push(['trending_up', `${item.results_count} sonuç`]);
    }

    if (item.services_count > 0) {
        chips.push(['design_services', `${item.services_count} hizmet`]);
    }

    if (chips.length === 0) {
        return '<span class="text-xs text-gray-400">—</span>';
    }

    return `<div class="flex items-center gap-[8px] flex-wrap">${chips.map(([icon, label]) => `
        <span class="inline-flex items-center gap-[3px] text-[11px] text-gray-500 dark:text-gray-400" title="${escapeHtml(label)}">
            <i class="material-symbols-outlined !text-[14px]">${icon}</i>${escapeHtml(label)}
        </span>`).join('')}</div>`;
}

const selection = bindBulk({
    module: 'project',
    body: document.getElementById('project-table-body'),
    onDone: () => table.reload(),
});

const table = new DataTable({
    endpoint: '/admin/project/datatable',
    body: document.getElementById('project-table-body'),
    search: document.getElementById('project-search'),
    filters: {
        status: document.getElementById('project-status'),
        project_category_id: document.getElementById('project-category'),
        featured: document.getElementById('project-featured'),
    },
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz proje eklenmedi.',
    onLoaded: () => selection.sync(),
    reorder: {
        button: document.getElementById('project-reorder'),
        endpoint: '/admin/project/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${bulkCell(item)}
        ${reorderHandle()}
        ${cell(thumb(item))}
        ${cell(`<div class="min-w-0">
            <span class="font-medium flex items-center gap-[6px]">
                ${item.is_featured ? '<i class="material-symbols-outlined !text-[16px] text-orange-500" title="Öne çıkan">star</i>' : ''}
                <span class="truncate max-w-[280px]">${escapeHtml(item.title)}</span>
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                ${item.category ? escapeHtml(item.category) + ' · ' : ''}${escapeHtml(item.slug)}
            </span>
        </div>`)}
        ${cell(`<div class="min-w-0">
            <span class="block truncate max-w-[160px]">${escapeHtml(item.client_name ?? '—')}</span>
            ${item.sector ? `<span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.sector)}</span>` : ''}
        </div>`)}
        ${cell(escapeHtml(item.completed_label ?? '—'))}
        ${cell(contentChips(item))}
        ${cell(`<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[item.status]}">${escapeHtml(item.status_label)}</span>`)}
        ${cell(scoreBadge(item.seo_score, item.seo_grade))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton(MODEL, item.id)}
            ${revisionButton(MODEL, item.id)}
            <a href="/admin/project/${item.id}/edit" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('project-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm(
        'Bu proje silinsin mi? Hizmet ve SSS bağlantıları kaldırılır; görseller medya kütüphanesinde kalır.',
        { title: 'Projeyi sil', accept: 'Evet, sil' },
    );

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/project/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
