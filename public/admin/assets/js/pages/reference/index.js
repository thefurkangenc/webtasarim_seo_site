/** Referanslar ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const modal = new AjaxModal();

const dash = '<span class="text-gray-500 dark:text-gray-400">—</span>';

// Logo oranı korunmalı: 'medium' oranı bozmadan küçültür, 'thumb' 400x400
// cover-crop'tur ve geniş logoların kenarlarını keser.
const logo = (item) => item.logo
    ? `<img src="${item.logo.medium}" alt="${escapeHtml(item.name)}" class="w-[72px] h-[36px] object-contain">`
    : `<span class="w-[72px] h-[36px] rounded-md bg-gray-100 dark:bg-[#15203c] flex items-center justify-center text-gray-500 dark:text-gray-400">
        <i class="material-symbols-outlined !text-[19px]">image</i>
    </span>`;

const shorten = (value, length = 40) =>
    value.length > length ? `${value.slice(0, length)}…` : value;

// Sadece http/https bağlantı olarak basılır; başka bir şema (javascript: gibi)
// düz metin kalır — href'e ham veri koymanın tek güvenli yolu bu.
const isWebUrl = (value) => /^https?:\/\//i.test(value);

const link = (item) => {
    if (! item.url) {
        return dash;
    }

    const label = escapeHtml(shorten(item.url));

    return isWebUrl(item.url)
        ? `<a href="${escapeHtml(item.url)}" target="_blank" rel="noopener noreferrer" class="transition-all hover:text-primary-500">${label}</a>`
        : label;
};

const table = new DataTable({
    endpoint: '/admin/reference/datatable',
    body: document.getElementById('reference-table-body'),
    search: document.getElementById('reference-search'),
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz referans eklenmedi.',
    reorder: {
        button: document.getElementById('reference-reorder'),
        endpoint: '/admin/reference/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(logo(item))}
        ${cell(`<span class="font-medium">${escapeHtml(item.name)}</span>`)}
        ${cell(link(item))}
        ${cell(`<div class="flex items-center gap-[9px]">
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
    await modal.open(`/admin/reference/form/${id ?? ''}`, {
        title: id ? 'Referansı Düzenle' : 'Yeni Referans',
    });
}

document.getElementById('reference-create')?.addEventListener('click', () => open());

document.getElementById('reference-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu referans silinsin mi?', {
        title: 'Referansı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/reference/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/reference/${id}`, body)
        : await http.post('/admin/reference', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
