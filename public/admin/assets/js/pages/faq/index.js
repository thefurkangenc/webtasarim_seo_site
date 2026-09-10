/** Sıkça sorulan sorular ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

const truncate = (text, length = 100) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

const table = new DataTable({
    endpoint: '/admin/faq/datatable',
    body: document.getElementById('faq-table-body'),
    search: document.getElementById('faq-search'),
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz soru eklenmedi.',
    reorder: {
        button: document.getElementById('faq-reorder'),
        endpoint: '/admin/faq/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(`<span class="font-medium">${escapeHtml(item.question)}</span>`)}
        ${cell(escapeHtml(truncate(item.answer)))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Faq\\Faq', item.id)}
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
    await modal.open(`/admin/faq/form/${id ?? ''}`, {
        title: id ? 'Soruyu Düzenle' : 'Yeni Soru',
    });
}

document.getElementById('faq-create')?.addEventListener('click', () => open());

document.getElementById('faq-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu soru silinsin mi?', {
        title: 'Soruyu sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/faq/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/faq/${id}`, body)
        : await http.post('/admin/faq', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
