/** Açılır pencere listesi. */

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
    endpoint: '/admin/popup/datatable',
    body: document.getElementById('popup-table-body'),
    search: document.getElementById('popup-search'),
    sort: 'created_at',
    direction: 'desc',
    empty: 'Henüz açılır pencere eklenmedi.',
    row: (item) => `<tr data-id="${item.id}">
        ${cell(`<div class="flex items-center gap-[10px]">
            ${item.image ? `<img src="${escapeHtml(item.image)}" alt="" class="w-[36px] h-[36px] rounded-md object-cover shrink-0">` : ''}
            <span class="font-medium">${escapeHtml(item.title)}</span>
        </div>`)}
        ${cell(escapeHtml(item.audience_label))}
        ${cell(`<span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.schedule_label)}</span>`)}
        ${cell(`${statusBadge(item.is_active)}${item.collect_email ? '<span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-[3px]">bülten formu</span>' : ''}`)}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Popup\\Popup', item.id)}
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
    await modal.open(`/admin/popup/form/${id ?? ''}`, {
        title: id ? 'Pencereyi Düzenle' : 'Yeni Açılır Pencere',
        width: 'max-w-[720px]',
    });
}

document.getElementById('popup-create')?.addEventListener('click', () => open());

document.getElementById('popup-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    if (! await confirm('Bu açılır pencere silinsin mi?', { title: 'Pencereyi sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/popup/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/popup/${id}`, body)
        : await http.post('/admin/popup', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
