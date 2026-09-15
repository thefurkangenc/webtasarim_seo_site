/** Duyuru şeridi listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import '../../core/audience-form.js';

const modal = new AjaxModal();

const statusBadge = (active) => active
    ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500">Yayında</span>'
    : '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-400">Kapalı</span>';

const table = new DataTable({
    endpoint: '/admin/announcement/datatable',
    body: document.getElementById('announcement-table-body'),
    search: document.getElementById('announcement-search'),
    sort: 'created_at',
    direction: 'desc',
    empty: 'Henüz duyuru eklenmedi.',
    row: (item) => `<tr data-id="${item.id}">
        ${cell(`<span class="font-medium">${escapeHtml(item.title)}</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[2px] max-w-[320px] truncate">${escapeHtml(item.message)}</span>`)}
        ${cell(escapeHtml(item.audience_label))}
        ${cell(`<span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.schedule_label)}</span>`)}
        ${cell(statusBadge(item.is_active))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Announcement\\Announcement', item.id)}
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
    await modal.open(`/admin/announcement/form/${id ?? ''}`, {
        title: id ? 'Duyuruyu Düzenle' : 'Yeni Duyuru',
        width: 'max-w-[720px]',
    });
}

document.getElementById('announcement-create')?.addEventListener('click', () => open());

document.getElementById('announcement-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    if (! await confirm('Bu duyuru silinsin mi?', { title: 'Duyuruyu sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/announcement/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/announcement/${id}`, body)
        : await http.post('/admin/announcement', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
