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

/* Kontrole özel küçük görselleştirmeler: sayı yerine bakışta anlaşılan bir şerit. */
function metaVisual(check) {
    const meta = check.meta ?? {};

    if (check.key === 'disk' && typeof meta.used_percent === 'number') {
        const percent = Math.min(100, Math.max(0, meta.used_percent));
        const bar = percent >= 95 ? 'bg-danger-500' : percent >= 85 ? 'bg-warning-500' : 'bg-success-500';

        return `<div class="mt-[12px]">
            <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-[5px]">
                <span>Doluluk</span><span>%${percent} · ${escapeHtml(String(meta.free ?? ''))} boş</span>
            </div>
            <div class="h-[6px] rounded-full bg-gray-100 dark:bg-[#172036] overflow-hidden">
                <div class="h-full ${bar} rounded-full transition-all" style="width:${percent}%"></div>
            </div>
        </div>`;
    }

    if (check.key === 'queue_worker') {
        return `<div class="flex flex-wrap gap-[14px] mt-[12px] text-[11px] text-gray-500 dark:text-gray-400">
            <span><strong class="text-black dark:text-white">${meta.pending ?? 0}</strong> iş sırada</span>
            ${meta.heartbeat_label ? `<span>Son sinyal: ${escapeHtml(meta.heartbeat_label)}</span>` : ''}
        </div>`;
    }

    if (check.key === 'failed_jobs') {
        return `<div class="flex flex-wrap gap-[14px] mt-[12px] text-[11px] text-gray-500 dark:text-gray-400">
            <span><strong class="text-black dark:text-white">${meta.count ?? 0}</strong> başarısız iş</span>
        </div>`;
    }

    if (check.key === 'cron' && meta.last_run_label) {
        return `<div class="mt-[12px] text-[11px] text-gray-500 dark:text-gray-400">
            Son çalışma: ${escapeHtml(meta.last_run_label)}
        </div>`;
    }

    return '';
}

const checkCard = (check) => {
    const tone = style(check.status);
    const faded = check.status === 'skipped' ? 'opacity-70' : '';

    return `<div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md border ${tone.border} ${faded}">
        <div class="flex items-start gap-[14px]">
            <span class="shrink-0 w-[40px] h-[40px] rounded-[12px] flex items-center justify-center ${tone.chip}">
                <i class="material-symbols-outlined !text-[21px]">${tone.icon}</i>
            </span>
            <div class="min-w-0 flex-1">
                <span class="block font-medium text-black dark:text-white">
                    ${escapeHtml(check.label)}
                    <span class="text-xs font-normal ${tone.text}">· ${tone.label}</span>
                </span>
                <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[3px]">${escapeHtml(check.message)}</span>
                ${metaVisual(check)}
                ${check.hint ? `<div class="mt-[12px] pt-[10px] border-t border-gray-100 dark:border-[#172036]">
                    <span class="flex items-start gap-[6px] text-xs text-gray-500 dark:text-gray-400 leading-[1.6]">
                        <i class="material-symbols-outlined !text-[15px] text-primary-500 shrink-0 mt-[1px]">tips_and_updates</i>
                        <span><strong class="text-black dark:text-white">Nasıl düzeltilir:</strong> ${escapeHtml(check.hint)}</span>
                    </span>
                </div>` : ''}
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
    const { critical, warning, ok, skipped } = report.counts;
    const chip = root.querySelector('[data-summary-chip]');

    chip.className = `shrink-0 w-[26px] h-[26px] rounded-full flex items-center justify-center ${tone.chip}`;
    root.querySelector('[data-summary-icon]').textContent = tone.icon;

    root.querySelector('[data-summary-title]').textContent = critical > 0
        ? `${critical} kritik sorun var`
        : (warning > 0 ? `${warning} uyarı var, kritik sorun yok` : 'Her şey yolunda');

    root.querySelector('[data-summary-note]').textContent =
        `${report.checks.length} kontrol · son tarama ${report.checked_ago}`;

    Object.entries({ critical, warning, ok, skipped }).forEach(([key, value]) => {
        const el = root.querySelector(`[data-count="${key}"]`);
        if (el) el.textContent = value;
    });

    // Skor: kurulu olmayan kontroller paydaya girmez — "kurmadım" bir hata değil.
    const graded = critical + warning + ok;
    const score = graded === 0 ? 100 : Math.round((ok / graded) * 100);
    const ring = root.querySelector('[data-summary-ring]');

    root.querySelector('[data-summary-score]').textContent = `%${score}`;
    ring.setAttribute('stroke-dasharray', `${score} 100`);
    ring.setAttribute('stroke', 'currentColor');
    ring.parentElement.parentElement.className =
        `relative shrink-0 w-[84px] h-[84px] ${tone.text}`;

    const strip = root.querySelector('[data-critical-strip]');
    const items = report.checks.filter((check) => check.status === 'critical');

    strip.classList.toggle('hidden', items.length === 0);
    root.querySelector('[data-critical-list]').innerHTML = items.map((check) => `
        <li class="flex items-start gap-[6px]">
            <i class="material-symbols-outlined !text-[14px] text-danger-500 shrink-0 mt-[2px]">arrow_right</i>
            <span><strong class="text-black dark:text-white">${escapeHtml(check.label)}:</strong>
            ${escapeHtml(check.hint ?? check.message)}</span>
        </li>`).join('');
}

/* Kontroller config'teki gruplara dağıtılır; boş grup hiç basılmaz. */
function renderGroups(checks) {
    const groups = JSON.parse(checksBox.dataset.groups || '{}');
    const byGroup = {};

    checks.forEach((check) => {
        (byGroup[check.group] ??= []).push(check);
    });

    checksBox.innerHTML = Object.entries(groups).map(([key, group]) => {
        const items = byGroup[key] ?? [];

        if (items.length === 0) {
            return '';
        }

        const worst = items.some((c) => c.status === 'critical')
            ? 'critical'
            : items.some((c) => c.status === 'warning') ? 'warning' : 'ok';

        return `<section class="mb-[25px] last:mb-0">
            <div class="flex items-start gap-[10px] mb-[14px]">
                <span class="shrink-0 w-[32px] h-[32px] rounded-[10px] flex items-center justify-center ${style(worst).chip}">
                    <i class="material-symbols-outlined !text-[18px]">${escapeHtml(group.icon)}</i>
                </span>
                <div class="min-w-0">
                    <h6 class="!mb-[2px] text-black dark:text-white">${escapeHtml(group.label)}</h6>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 leading-[1.6] max-w-[720px]">${escapeHtml(group.description ?? '')}</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-[15px] md:gap-[25px]">
                ${items.map(checkCard).join('')}
            </div>
        </section>`;
    }).join('');
}

function render(report) {
    renderSummary(report);

    renderGroups(report.checks);

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
