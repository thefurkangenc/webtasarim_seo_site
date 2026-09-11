/**
 * Kırık link denetimi — sonuç tablosu, tarama tetikleme ve kırık bir iç
 * adresten tek tuşla 301 yönlendirme oluşturma (Yönlendirme modülünün
 * kayıt modalı burada da kullanılır).
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-broken-link]');
const modal = new AjaxModal();

const KIND_BADGES = {
    link: 'bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-300',
    image: 'bg-secondary-100 dark:bg-[#15203c] text-secondary-600 dark:text-secondary-400',
};

const badge = (text, classes) =>
    `<span class="text-[10px] font-medium py-[2px] px-[8px] rounded-sm inline-block ${classes}">${escapeHtml(text)}</span>`;

const urlCell = (item) => `<div class="min-w-0">
    <span class="font-medium block truncate max-w-[340px]" title="${escapeHtml(item.url)}">${escapeHtml(item.url)}</span>
    <span class="inline-flex items-center gap-[6px] mt-[3px]">
        ${badge(item.kind_label, KIND_BADGES[item.kind] ?? KIND_BADGES.link)}
        ${item.scope === 'external' ? badge('dış site', 'bg-warning-100 text-warning-700') : badge('kendi sitemiz', 'bg-primary-100 text-primary-600')}
        ${item.ignored ? badge('yok sayıldı', 'bg-gray-100 dark:bg-[#15203c] text-gray-500') : ''}
    </span>
</div>`;

const sourceCell = (item) => `<div class="min-w-0">
    <span class="block truncate max-w-[260px]" title="${escapeHtml(item.source_label ?? '')}">${escapeHtml(item.source_label ?? '—')}</span>
    <span class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.source_type_label)}</span>
</div>`;

const reasonCell = (item) => `<div class="min-w-0">
    ${badge(item.reason_label, 'bg-danger-100 text-danger-500')}
    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px] max-w-[260px]">
        ${escapeHtml(item.message ?? '')}${item.status_code ? ` (HTTP ${item.status_code})` : ''}
    </span>
</div>`;

const actionsCell = (item) => `<div class="flex items-center gap-[9px]">
    ${item.redirect_path ? `<button type="button" data-redirect="${escapeHtml(item.redirect_path)}" title="301 yönlendirme oluştur"
        class="inline-flex items-center gap-[4px] text-xs text-primary-500 py-[5px] px-[10px] rounded-md border border-primary-200 hover:bg-primary-50 transition-all">
        <i class="material-symbols-outlined !text-[15px]">add_link</i> Yönlendir
    </button>` : ''}
    ${item.edit_url ? `<a href="${escapeHtml(item.edit_url)}" title="Kaynağı düzenle"
        class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
        <i class="material-symbols-outlined !text-md">edit_document</i>
    </a>` : ''}
    <button type="button" data-ignore="${item.id}" title="${item.ignored ? 'Yeniden denetle' : 'Yok say'}"
        class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-warning-500">
        <i class="material-symbols-outlined !text-md">${item.ignored ? 'visibility' : 'visibility_off'}</i>
    </button>
    <button type="button" data-delete="${item.id}" title="Kaydı sil"
        class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
        <i class="material-symbols-outlined !text-md">delete</i>
    </button>
</div>`;

const table = new DataTable({
    endpoint: '/admin/broken-link/datatable',
    body: document.getElementById('broken-link-table-body'),
    search: document.getElementById('broken-link-search'),
    filters: {
        scope: document.getElementById('broken-link-scope'),
        kind: document.getElementById('broken-link-kind'),
        reason: document.getElementById('broken-link-reason'),
        source_type: document.getElementById('broken-link-source'),
        include_ignored: document.getElementById('broken-link-ignored'),
    },
    sort: 'last_checked_at',
    direction: 'desc',
    empty: 'Kırık link bulunamadı.',
    row: (item) => `<tr data-id="${item.id}" class="${item.ignored ? 'opacity-60' : ''}">
        ${cell(urlCell(item))}
        ${cell(sourceCell(item))}
        ${cell(reasonCell(item))}
        ${cell(escapeHtml(item.last_checked_at ?? '—'))}
        ${cell(actionsCell(item))}
    </tr>`,
});

document.getElementById('broken-link-table-body').addEventListener('click', async (event) => {
    const redirect = event.target.closest('[data-redirect]');
    const ignore = event.target.closest('[data-ignore]');
    const remove = event.target.closest('[data-delete]');

    if (redirect) {
        return modal.open(`/admin/redirect/form?from=/${encodeURIComponent(redirect.dataset.redirect)}`, {
            title: 'Yeni Yönlendirme',
        });
    }

    if (ignore) {
        try {
            const { message } = await http.put(`/admin/broken-link/${ignore.dataset.ignore}/ignore`);
            toast.success(message);
            refresh();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Güncellenemedi.');
        }

        return;
    }

    if (! remove) {
        return;
    }

    if (! await confirm('Bu kayıt listeden silinsin mi? Bağlantı hâlâ kırıksa sonraki taramada geri gelir.', {
        title: 'Kaydı sil',
        accept: 'Evet, sil',
    })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/broken-link/${remove.dataset.delete}`);
        toast.success(message);
        refresh();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

/* Yönlendirme modalının gönderimi — kayıt Yönlendirme modülüne gider. */
modal.onSubmit(async (form) => {
    const { message } = await http.post('/admin/redirect', new FormData(form));

    toast.success(message);
    modal.close();
});

/* ---------------------------------------------------------------------- *
 | Tarama
 * ---------------------------------------------------------------------- */

document.getElementById('broken-link-scan')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;

    try {
        const { message } = await http.post(root.dataset.scanEndpoint);
        toast.success(message);
        button.disabled = true;
        pollWhileScanning();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Tarama başlatılamadı.');
    }
});

/*
 * Tarama kuyrukta çalışıyor; ne zaman biteceği belli değil. Bir süre boyunca
 * listeyi tazeleyip bırakıyoruz — kullanıcı isterse sayfayı yeniler.
 */
function pollWhileScanning(remaining = 10) {
    if (remaining === 0) {
        document.getElementById('broken-link-scan').disabled = false;

        return;
    }

    setTimeout(() => {
        refresh();
        pollWhileScanning(remaining - 1);
    }, 10000);
}

async function refresh() {
    table.reload();

    try {
        const { data } = await http.get('/admin/broken-link/stats');

        Object.entries(data).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);

            if (element) {
                element.textContent = new Intl.NumberFormat('tr-TR').format(value);
            }
        });
    } catch {
        // Özet güncellenemezse tablo yine tazelenmiş olur; sessiz geç.
    }
}

table.load();
