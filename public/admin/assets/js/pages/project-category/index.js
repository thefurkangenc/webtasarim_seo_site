/** Proje kategorileri ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { initSeoFields } from '../../core/seo-field.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

const BADGES = {
    success: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    danger: 'bg-danger-100 dark:bg-[#15203c] text-danger-600 dark:text-danger-500',
};

const badge = (label, variant) =>
    `<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[variant]}">${label}</span>`;

const table = new DataTable({
    endpoint: '/admin/project-category/datatable',
    body: document.getElementById('category-table-body'),
    search: document.getElementById('category-search'),
    filters: { is_active: document.getElementById('category-active') },
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz kategori eklenmedi.',
    reorder: {
        button: document.getElementById('category-reorder'),
        endpoint: '/admin/project-category/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(`<div class="min-w-0">
            <span class="font-medium block">${escapeHtml(item.name)}</span>
            ${item.description ? `<span class="text-xs text-gray-500 dark:text-gray-400 block truncate max-w-[320px]">${escapeHtml(item.description)}</span>` : ''}
        </div>`)}
        ${cell(`<code class="text-xs">${escapeHtml(item.slug)}</code>`)}
        ${cell(item.projects_count)}
        ${cell(item.is_active ? badge('aktif', 'success') : badge('pasif', 'danger'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\ProjectCategory\\ProjectCategory', item.id)}
            <button type="button" data-edit="${item.id}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </button>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

async function open(id = null) {
    await modal.open(`/admin/project-category/form/${id ?? ''}`, {
        title: id ? 'Kategoriyi Düzenle' : 'Yeni Kategori',
        width: 'max-w-[1200px]',
    });

    // SEO bileşeni modal gövdesiyle birlikte geldi; sayaç ve önizlemeyi kur.
    initSeoFields(modal.body);
}

document.getElementById('category-create')?.addEventListener('click', () => open());

document.getElementById('category-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu kategori silinsin mi?', {
        title: 'Kategoriyi sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/project-category/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

modal.onSubmit(async (form) => {
    const id = form.dataset.id;
    const body = new FormData(form);

    const { message } = id
        ? await http.put(`/admin/project-category/${id}`, body)
        : await http.post('/admin/project-category', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
