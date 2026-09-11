/**
 * Sistem sağlığı paneli — kontrol kartları ve başarısız iş listesi.
 *
 * Sayfa boş bir iskeletle gelir, rapor AJAX ile dolar: kontroller SMTP ve
 * sertifika gibi ağ işleri yaptığı için sayfa açılışını bekletmemeleri
 * gerekiyor. "Yeniden Tara" cache'i atlayıp hepsini baştan çalıştırır.
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-health]');
const checksBox = root.querySelector('[data-checks]');
const failedCard = root.querySelector('[data-failed-card]');
const failedBody = root.querySelector('[data-failed-body]');
const refreshButton = root.querySelector('[data-refresh]');

/* Durum -> renk sınıfları ve ikon. Sınıf adları tam yazılı (Tailwind taraması statik). */
const STATUS = {
    critical: {
        label: 'Kritik',
        icon: 'error',
        chip: 'bg-danger-100 dark:bg-[#15203c] text-danger-500',
        text: 'text-danger-500',
        border: 'border-danger-500',
    },
    warning: {
        label: 'Dikkat',
        icon: 'warning',
        chip: 'bg-warning-100 dark:bg-[#15203c] text-warning-600',
        text: 'text-warning-600',
        border: 'border-warning-500',
    },
    ok: {
        label: 'Sorun yok',
        icon: 'check_circle',
        chip: 'bg-success-100 dark:bg-[#15203c] text-success-600',
        text: 'text-success-600',
        border: 'border-transparent',
    },
    skipped: {
        label: 'Kurulu değil',
        icon: 'remove',
        chip: 'bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400',
        text: 'text-gray-500 dark:text-gray-400',
        border: 'border-transparent',
    },
};

const style = (status) => STATUS[status] ?? STATUS.skipped;

const checkCard = (check) => {
    const tone = style(check.status);

    return `<div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md border ${tone.border}">
        <div class="flex items-start gap-[14px]">
            <span class="shrink-0 w-[40px] h-[40px] rounded-[12px] flex items-center justify-center ${tone.chip}">
                <i class="material-symbols-outlined !text-[21px]">${tone.icon}</i>
            </span>
            <div class="min-w-0">
                <span class="block font-medium text-black dark:text-white">
                    ${escapeHtml(check.label)}
                    <span class="text-xs font-normal ${tone.text}">· ${tone.label}</span>
                </span>
                <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[3px]">${escapeHtml(check.message)}</span>
                ${check.hint ? `<span class="block text-xs text-gray-500 dark:text-gray-400 mt-[8px] pt-[8px] border-t border-gray-100 dark:border-[#172036]">
                    ${escapeHtml(check.hint)}
                </span>` : ''}
            </div>
        </div>
    </div>`;
};

const failedRow = (job) => `<tr data-uuid="${escapeHtml(job.uuid)}">
    <td class="ltr:text-left rtl:text-right whitespace-nowrap px-[20px] py-[15px] border-b border-gray-100 dark:border-[#172036]">
        <span class="block font-medium">${escapeHtml(job.name)}</span>
        <span class="block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(job.queue)} kuyruğu</span>
    </td>
    <td class="ltr:text-left rtl:text-right px-[20px] py-[15px] border-b border-gray-100 dark:border-[#172036]">
        <span class="block text-xs text-gray-500 dark:text-gray-400 max-w-[420px]">${escapeHtml(job.exception)}</span>
    </td>
    <td class="ltr:text-left rtl:text-right whitespace-nowrap px-[20px] py-[15px] border-b border-gray-100 dark:border-[#172036]">
        ${escapeHtml(job.failed_at_label)}
    </td>
    <td class="ltr:text-left rtl:text-right whitespace-nowrap px-[20px] py-[15px] border-b border-gray-100 dark:border-[#172036]">
        <div class="flex items-center gap-[9px]">
            <button type="button" data-retry title="Yeniden dene"
                class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">restart_alt</i>
            </button>
            <button type="button" data-forget title="Kaydı sil"
                class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>
    </td>
</tr>`;

function renderSummary(report) {
    const tone = style(report.status);
    const { critical, warning } = report.counts;
    const chip = root.querySelector('[data-summary-chip]');

    chip.className = `shrink-0 w-[46px] h-[46px] rounded-full flex items-center justify-center ${tone.chip}`;
    root.querySelector('[data-summary-icon]').textContent = tone.icon;

    root.querySelector('[data-summary-title]').textContent = critical > 0
        ? `${critical} kritik sorun var`
        : (warning > 0 ? `${warning} uyarı var, kritik sorun yok` : 'Her şey yolunda');

    root.querySelector('[data-summary-note]').textContent =
        `${report.checks.length} kontrol · son tarama ${report.checked_ago}`;
}

function render(report) {
    renderSummary(report);

    checksBox.innerHTML = report.checks.map(checkCard).join('');

    failedCard.classList.toggle('hidden', report.failed_jobs.length === 0);
    failedBody.innerHTML = report.failed_jobs.map(failedRow).join('');
}

async function load(fresh = false) {
    if (refreshButton) {
        refreshButton.disabled = true;
    }

    try {
        const { data } = await http.get(`/admin/health/data${fresh ? '?fresh=1' : ''}`);
        render(data);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Sağlık raporu alınamadı.');
    } finally {
        if (refreshButton) {
            refreshButton.disabled = false;
        }
    }
}

refreshButton?.addEventListener('click', () => load(true));

failedBody.addEventListener('click', async (event) => {
    const retry = event.target.closest('[data-retry]');
    const forget = event.target.closest('[data-forget]');
    const uuid = event.target.closest('tr')?.dataset.uuid;

    if (! uuid || (! retry && ! forget)) {
        return;
    }

    if (forget && ! await confirm('Bu başarısız iş kaydı silinsin mi? İş bir daha çalıştırılmaz.', {
        title: 'Kaydı sil',
        accept: 'Evet, sil',
    })) {
        return;
    }

    try {
        const { message } = retry
            ? await http.post(`/admin/health/${uuid}/retry`)
            : await http.delete(`/admin/health/${uuid}`);

        toast.success(message);
        load();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'İşlem tamamlanamadı.');
    }
});

root.querySelector('[data-retry-all]')?.addEventListener('click', async () => {
    if (! await confirm('Tüm başarısız işler yeniden kuyruğa alınsın mı?', {
        title: 'Tümünü yeniden dene',
        accept: 'Evet, dene',
    })) {
        return;
    }

    try {
        const { message } = await http.post('/admin/health/retry-all');
        toast.success(message);
        load();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'İşlem tamamlanamadı.');
    }
});

root.querySelector('[data-flush]')?.addEventListener('click', async () => {
    if (! await confirm('Başarısız iş listesi tamamen silinsin mi? Bu işler bir daha çalıştırılmaz.', {
        title: 'Listeyi temizle',
        accept: 'Evet, temizle',
    })) {
        return;
    }

    try {
        const { message } = await http.delete('/admin/health/failed');
        toast.success(message);
        load();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'İşlem tamamlanamadı.');
    }
});

load();
