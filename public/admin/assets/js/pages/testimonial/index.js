/** Müşteri yorumları ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

const stars = (rating) => `<span class="text-orange-500">${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</span> <span class="text-gray-500 dark:text-gray-400">(${rating})</span>`;

const avatar = (item) => item.photo
    ? `<img src="${item.photo.thumb}" alt="${escapeHtml(item.name)}" class="w-[36px] h-[36px] rounded-full object-cover">`
    : `<span class="w-[36px] h-[36px] rounded-full bg-gray-100 dark:bg-[#15203c] flex items-center justify-center text-gray-500 dark:text-gray-400">
        <i class="material-symbols-outlined !text-[19px]">person</i>
    </span>`;

const truncate = (text, length = 80) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

const table = new DataTable({
    endpoint: '/admin/testimonial/datatable',
    body: document.getElementById('testimonial-table-body'),
    search: document.getElementById('testimonial-search'),
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz müşteri yorumu eklenmedi.',
    reorder: {
        button: document.getElementById('testimonial-reorder'),
        endpoint: '/admin/testimonial/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(avatar(item))}
        ${cell(`<span class="font-medium">${escapeHtml(item.name)}</span>`)}
        ${cell(item.title ? escapeHtml(item.title) : '<span class="text-gray-500 dark:text-gray-400">—</span>')}
        ${cell(stars(item.rating))}
        ${cell(escapeHtml(truncate(item.content)))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Testimonial\\Testimonial', item.id)}
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
    await modal.open(`/admin/testimonial/form/${id ?? ''}`, {
        title: id ? 'Yorumu Düzenle' : 'Yeni Yorum',
    });
}

document.getElementById('testimonial-create')?.addEventListener('click', () => open());

document.getElementById('testimonial-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu yorum silinsin mi?', {
        title: 'Yorumu sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/testimonial/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/testimonial/${id}`, body)
        : await http.post('/admin/testimonial', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
