/** Prompt şablonları ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

const BADGES = {
    success: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    danger: 'bg-danger-100 dark:bg-[#15203c] text-danger-600 dark:text-danger-500',
    primary: 'bg-primary-100 dark:bg-[#15203c] text-primary-600 dark:text-primary-500',
};

const badge = (label, variant) =>
    `<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[variant]}">${label}</span>`;

// Sürücü adı ile dosya adı bire bir eşleşmiyor (openai -> chatgpt.svg).
const DRIVER_ICONS = { openai: 'chatgpt', deepseek: 'deepseek', ollama: 'ollama' };

const driverIcon = (driver) => DRIVER_ICONS[driver]
    ? `<img src="/admin/assets/images/icons/ai/${DRIVER_ICONS[driver]}.svg" alt="" class="w-[16px] h-[16px] shrink-0">`
    : '';

const table = new DataTable({
    endpoint: '/admin/ai-prompt/datatable',
    body: document.getElementById('prompt-table-body'),
    search: document.getElementById('prompt-search'),
    sort: 'created_at',
    empty: 'Henüz şablon eklenmedi.',
    row: (item) => `<tr>
        ${cell(`<span class="font-medium">${escapeHtml(item.name)}</span>${item.is_default ? ` ${badge('varsayılan', 'primary')}` : ''}`)}
        ${cell(`<code class="text-xs">${escapeHtml(item.key)}</code>`)}
        ${cell(`<span class="flex items-center gap-[6px]">${driverIcon(item.driver)}${escapeHtml(item.provider)}</span>`)}
        ${cell(item.is_active ? badge('aktif', 'success') : badge('pasif', 'danger'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Ai\\AiPrompt', item.id)}
            <button type="button" data-edit="${item.id}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </button>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

function open(id = null) {
    modal.open(`/admin/ai-prompt/form/${id ?? ''}`, {
        title: id ? 'Şablonu Düzenle' : 'Yeni Şablon',
        width: 'max-w-[820px]',
    });
}

document.getElementById('prompt-create')?.addEventListener('click', () => open());

document.getElementById('prompt-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu şablon silinsin mi?', {
        title: 'Şablonu sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/ai-prompt/${remove.dataset.delete}`);
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
        ? await http.put(`/admin/ai-prompt/${id}`, body)
        : await http.post('/admin/ai-prompt', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
