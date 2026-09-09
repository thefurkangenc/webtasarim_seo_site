/** Neden Biz ekranı. */

import { confirm } from '../../core/confirm.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { escapeHtml, http, HttpError, ValidationError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const modal = new AjaxModal();

const truncate = (text, length = 100) =>
    text.length > length ? `${text.slice(0, length)}…` : text;

const table = new DataTable({
    endpoint: '/admin/why-choose-us/datatable',
    body: document.getElementById('why-choose-us-table-body'),
    search: document.getElementById('why-choose-us-search'),
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Henüz kart eklenmedi.',
    reorder: {
        button: document.getElementById('why-choose-us-reorder'),
        endpoint: '/admin/why-choose-us/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(`<span class="font-medium">${escapeHtml(item.title)}</span>`)}
        ${cell(escapeHtml(truncate(item.description)))}
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
    await modal.open(`/admin/why-choose-us/form/${id ?? ''}`, {
        title: id ? 'Kartı Düzenle' : 'Yeni Kart',
    });
}

document.getElementById('why-choose-us-create')?.addEventListener('click', () => open());

document.getElementById('why-choose-us-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu kart silinsin mi?', {
        title: 'Kartı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/why-choose-us/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/why-choose-us/${id}`, body)
        : await http.post('/admin/why-choose-us', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();

/*
 * Bölüm başlığı/açıklaması — ayrı, küçük bir AJAX form. Kart listesi
 * datatable'ından bağımsız, kendi submit'ini yönetir.
 */
const headingForm = document.getElementById('why-choose-us-heading-form');

headingForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = headingForm.querySelector('[type=submit]');

    clearErrors(headingForm);
    setLoading(button, true);

    try {
        const { message } = await http.put(headingForm.action, new FormData(headingForm));
        toast.success(message);
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(headingForm, error.errors);
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        }
    } finally {
        setLoading(button, false);
    }
});
