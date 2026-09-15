/** Sıkça sorulan sorular ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import { bindBulk, bulkCell } from '../../core/bulk.js';
import { revisionButton } from '../../core/revisions.js';

const modal = new AjaxModal();

const truncate = (text, length = 100) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

const selection = bindBulk({
    module: 'faq',
    body: document.getElementById('faq-table-body'),
    onDone: () => table.reload(),
});

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
    onLoaded: () => selection.sync(),
    row: (item) => `<tr data-id="${item.id}">
        ${bulkCell(item)}
        ${reorderHandle()}
        ${cell(`<span class="font-medium">${escapeHtml(item.question)}</span>`)}
        ${cell(escapeHtml(truncate(item.answer)))}
        ${cell(item.is_active
            ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500">Yayında</span>'
            : '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-400">Gizli</span>')}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Faq\\Faq', item.id)}
            ${revisionButton('App\\Models\\Faq\\Faq', item.id)}
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
