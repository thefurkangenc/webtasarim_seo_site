/** Yapay zeka sağlayıcıları ekranı. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { setLoading } from '../../core/form.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

// Tailwind derleyicisi kaynak dosyaları tarar; class adları şablon değişkeniyle
// kurulursa bulunamaz, bu yüzden tam metin olarak yazılıyorlar.
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
    ? `<img src="/admin/assets/images/icons/ai/${DRIVER_ICONS[driver]}.svg" alt="" class="w-[18px] h-[18px] shrink-0">`
    : '';

const table = new DataTable({
    endpoint: '/admin/ai-provider/datatable',
    body: document.getElementById('provider-table-body'),
    search: document.getElementById('provider-search'),
    filters: { driver: document.getElementById('provider-driver') },
    sort: 'created_at',
    empty: 'Henüz sağlayıcı eklenmedi.',
    row: (item) => `<tr>
        ${cell(`<span class="flex items-center gap-[8px]">
            ${driverIcon(item.driver)}
            <span>
                <span class="font-medium">${escapeHtml(item.name)}</span>
                ${item.is_default ? ` ${badge('varsayılan', 'primary')}` : ''}
                ${item.has_key ? '' : ' <span class="text-xs text-gray-500 dark:text-gray-400">(anahtarsız)</span>'}
            </span>
        </span>`)}
        ${cell(escapeHtml(item.driver_label))}
        ${cell(`<code class="text-xs">${escapeHtml(item.model)}</code>`)}
        ${cell(item.is_active ? badge('aktif', 'success') : badge('pasif', 'danger'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Ai\\AiProvider', item.id)}
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
    modal.open(`/admin/ai-provider/form/${id ?? ''}`, {
        title: id ? 'Sağlayıcıyı Düzenle' : 'Yeni Sağlayıcı',
        width: 'max-w-[720px]',
    });
}

document.getElementById('provider-create')?.addEventListener('click', () => open());

document.getElementById('provider-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm(
        'Bu sağlayıcı silinsin mi? Onu kullanan şablonlar varsayılan sağlayıcıya düşer.',
        { title: 'Sağlayıcıyı sil', accept: 'Evet, sil' },
    );

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/ai-provider/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

/* Modal içi davranışlar: sürücü seçimine göre varsayılanları doldurma ve
   bağlantı testi. Modal gövdesi her açılışta yeniden basıldığı için
   dinleyiciler kökte duruyor. */
document.addEventListener('change', (event) => {
    const select = event.target.closest('[data-provider-driver]');

    if (! select) {
        return;
    }

    const defaults = JSON.parse(document.getElementById('provider-driver-defaults')?.textContent ?? '{}');
    const driver = defaults[select.value];
    const form = select.closest('form');

    if (! driver || ! form) {
        return;
    }

    // Yalnızca boş alanlar doldurulur; kullanıcının yazdığı ezilmez.
    ['base_url', 'model'].forEach((field) => {
        const input = form.querySelector(`[name="${field}"]`);

        if (input && input.value.trim() === '') {
            input.value = driver[field];
        }
    });

    const key = form.querySelector('[data-provider-key]');

    if (key) {
        key.closest('div').classList.toggle('hidden', ! driver.requires_key);
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-provider-test]');

    if (! button) {
        return;
    }

    const form = button.closest('form');
    const output = form.querySelector('[data-provider-test-result]');

    setLoading(button, true);
    output.textContent = 'İstek gönderiliyor...';
    output.className = '!mb-0 text-xs text-gray-500 dark:text-gray-400';

    try {
        const { message, data } = await http.post(`/admin/ai-provider/${form.dataset.id}/test`);
        output.textContent = `${message} Yanıt: "${data.content}"`;
        output.className = '!mb-0 text-xs text-success-500';
    } catch (error) {
        output.textContent = error instanceof HttpError ? error.message : 'Bağlantı kurulamadı.';
        output.className = '!mb-0 text-xs text-danger-500';
    } finally {
        setLoading(button, false);
    }
});

modal.onSubmit(async (form) => {
    const id = form.dataset.id;
    const body = new FormData(form);

    const { message } = id
        ? await http.put(`/admin/ai-provider/${id}`, body)
        : await http.post('/admin/ai-provider', body);

    toast.success(message);
    modal.close();
    table.reload();
});

table.load();
