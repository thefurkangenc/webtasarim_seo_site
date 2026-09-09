/** Roller listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const table = new DataTable({
    endpoint: '/admin/role/datatable',
    body: document.getElementById('role-table-body'),
    search: document.getElementById('role-search'),
    sort: 'name',
    direction: 'asc',
    empty: 'Henüz rol eklenmedi.',
    row: (item) => `<tr>
        ${cell(`<span class="font-medium">${escapeHtml(item.label)}</span>`)}
        ${cell(`<code class="text-xs">${escapeHtml(item.name)}</code>`)}
        ${cell(escapeHtml(item.guard_name))}
        ${cell(item.permissions_count)}
        ${cell(item.users_count)}
        ${cell(`<div class="flex items-center gap-[9px]">
            <a href="/admin/role/${item.id}/edit" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('role-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu rol silinsin mi?', {
        title: 'Rolü sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/role/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
