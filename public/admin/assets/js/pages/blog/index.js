/** Blog yazıları listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const BADGES = {
    published: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    draft: 'bg-warning-100 dark:bg-[#15203c] text-warning-600 dark:text-warning-500',
};

const thumb = (item) => item.thumb
    ? `<img src="${escapeHtml(item.thumb)}" alt="" class="w-[44px] h-[34px] rounded-md object-cover shrink-0">`
    : '<span class="w-[44px] h-[34px] rounded-md bg-gray-50 dark:bg-[#15203c] flex items-center justify-center shrink-0"><i class="material-symbols-outlined !text-[18px] text-gray-400">image</i></span>';

const table = new DataTable({
    endpoint: '/admin/blog/datatable',
    body: document.getElementById('blog-table-body'),
    search: document.getElementById('blog-search'),
    filters: {
        status: document.getElementById('blog-status'),
        blog_category_id: document.getElementById('blog-category'),
    },
    sort: 'created_at',
    empty: 'Henüz yazı eklenmedi.',
    row: (item) => `<tr>
        ${cell(`<div class="flex items-center gap-[10px]">
            ${thumb(item)}
            <div class="min-w-0">
                <span class="font-medium block truncate max-w-[320px]">${escapeHtml(item.title)}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.slug)}</span>
            </div>
        </div>`)}
        ${cell(escapeHtml(item.category ?? '—'))}
        ${cell(escapeHtml(item.author ?? '—'))}
        ${cell(`<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[item.status]}">${escapeHtml(item.status_label)}</span>
            ${item.is_featured ? ' <i class="material-symbols-outlined !text-[16px] text-warning-500 align-middle" title="Öne çıkan">star</i>' : ''}`)}
        ${cell(escapeHtml(item.published_at ?? '—'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Blog\\Blog', item.id)}
            <a href="/admin/blog/${item.id}/edit" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('blog-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu yazı silinsin mi? Kapak görseli kütüphanede kalır.', {
        title: 'Yazıyı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/blog/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
