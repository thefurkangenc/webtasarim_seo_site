/** Slaytlar ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError, adminUrl } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';
import { bindBulk, bulkCell } from '../../core/bulk.js';
import { revisionButton } from '../../core/revisions.js';

const modal = new AjaxModal();

const thumb = (item) => item.desktop
    ? `<img src="${item.desktop.thumb}" alt="${escapeHtml(item.title)}" class="w-[64px] h-[36px] rounded object-cover">`
    : `<span class="w-[64px] h-[36px] rounded bg-gray-100 dark:bg-[#15203c] flex items-center justify-center text-gray-500 dark:text-gray-400">
        <i class="material-symbols-outlined !text-[19px]">image</i>
    </span>`;

const truncate = (text, length = 60) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

const selection = bindBulk({
    module: 'slider',
    body: document.getElementById('slider-table-body'),
    onDone: () => table.reload(),
});

const table = new DataTable({
    endpoint: adminUrl('/slider/datatable'),
    body: document.getElementById('slider-table-body'),
    search: document.getElementById('slider-search'),
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz slayt eklenmedi.',
    reorder: {
        button: document.getElementById('slider-reorder'),
        endpoint: adminUrl('/slider/reorder'),
    },
    onLoaded: () => selection.sync(),
    row: (item) => `<tr data-id="${item.id}">
        ${bulkCell(item)}
        ${reorderHandle()}
        ${cell(thumb(item))}
        ${cell(`<span class="font-medium">${escapeHtml(item.title)}</span>`)}
        ${cell(item.slogan ? escapeHtml(item.slogan) : '<span class="text-gray-500 dark:text-gray-400">—</span>')}
        ${cell(item.button_text ? escapeHtml(truncate(item.button_text)) : '<span class="text-gray-500 dark:text-gray-400">—</span>')}
        ${cell(item.is_active
            ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500">Yayında</span>'
            : '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-400">Gizli</span>')}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Slider\\Slider', item.id)}
            ${revisionButton('App\\Models\\Slider\\Slider', item.id)}
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
    await modal.open(adminUrl(`/slider/form/${id ?? ''}`), {
        title: id ? 'Slaytı Düzenle' : 'Yeni Slayt',
        width: 'max-w-[1040px]',
    });
}

document.getElementById('slider-create')?.addEventListener('click', () => open());

document.getElementById('slider-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu slayt silinsin mi?', {
        title: 'Slaytı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(adminUrl(`/slider/${remove.dataset.delete}`));
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
        ? await http.put(adminUrl(`/slider/${id}`), body)
        : await http.post(adminUrl('/slider'), body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
