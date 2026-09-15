/**
 * SEO Sağlığı sayfası — özet kartlar + sekmeli sorun listeleri.
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { toast } from '../../core/toast.js';
import { GRADE, scoreBadge } from './badge.js';

const root = document.querySelector('[data-seo-health]');

if (root) {
    const body = document.getElementById('seo-health-body');
    const tabsBox = root.querySelector('[data-tabs]');
    const activeTab = 'bg-primary-500 text-white';
    const idleTab = 'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]';
    let tab = 'low_score';

    function paintTabs() {
        tabsBox.querySelectorAll('button[data-tab]').forEach((button) => {
            const on = button.dataset.tab === tab;
            button.className = `py-[7px] px-[14px] text-sm rounded-md transition-all ${on ? activeTab : idleTab}`;
        });
    }

    tabsBox.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-tab]');
        if (! button) return;
        tab = button.dataset.tab;
        paintTabs();
        load();
    });

    async function load() {
        body.innerHTML = `<tr><td colspan="5" class="py-[20px] text-center text-gray-400">Yükleniyor…</td></tr>`;

        try {
            const { data } = await http.get(`${root.dataset.endpoint}?tab=${encodeURIComponent(tab)}`);
            render(data);
        } catch (error) {
            body.innerHTML = `<tr><td colspan="5" class="py-[20px] text-center text-danger-500">${escapeHtml(error instanceof HttpError ? error.message : 'Yüklenemedi.')}</td></tr>`;
        }
    }

    function render(rows) {
        if (! rows.length) {
            body.innerHTML = `<tr><td colspan="5" class="py-[24px] text-center text-success-600">
                <i class="material-symbols-outlined align-middle !text-[18px]">check_circle</i> Bu kontrolde sorun yok.</td></tr>`;
            return;
        }

        body.innerHTML = rows.map((row) => `
            <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                <td class="px-[16px] py-[10px] text-sm">
                    <span class="block truncate max-w-[320px]">${escapeHtml(row.title)}</span>
                    ${row.keyword ? `<span class="block text-xs text-gray-400">odak: ${escapeHtml(row.keyword)}</span>` : ''}
                </td>
                <td class="px-[16px] py-[10px] text-sm text-gray-500 dark:text-gray-400">${escapeHtml(row.type)}</td>
                <td class="px-[16px] py-[10px] text-sm">${escapeHtml(row.issue)}</td>
                <td class="px-[16px] py-[10px]">${scoreBadge(row.score, row.grade)}</td>
                <td class="px-[16px] py-[10px] text-right">
                    <a href="${row.edit_url}" class="text-primary-500 hover:underline text-sm inline-flex items-center gap-[3px]">
                        Düzenle <i class="material-symbols-outlined !text-[15px]">arrow_forward</i>
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function renderOverview(overview) {
        const box = document.querySelector('[data-seo-overview]');
        const avg = overview.average;
        const grade = avg === null ? 'bad' : avg <= 40 ? 'bad' : avg <= 70 ? 'ok' : 'good';

        const ring = box.querySelector('[data-ov-ring]');
        ring.setAttribute('stroke-dasharray', `${avg ?? 0} 100`);
        ring.style.stroke = GRADE[grade].ring;
        box.querySelector('[data-ov-average]').textContent = avg ?? '–';
        box.querySelector('[data-ov-analyzed]').textContent = `${overview.analyzed}/${overview.total} içerik analizli`;
        box.querySelector('[data-ov-good]').textContent = overview.distribution.good;
        box.querySelector('[data-ov-ok]').textContent = overview.distribution.ok;
        box.querySelector('[data-ov-bad]').textContent = overview.distribution.bad + overview.distribution.none;

        Object.entries(overview.tabs).forEach(([key, count]) => {
            const el = root.querySelector(`[data-tab-count="${key}"]`);
            if (el) el.textContent = `(${count})`;
        });
    }

    document.getElementById('seo-rescore')?.addEventListener('click', async () => {
        if (! await confirm('Tüm blog, hizmet ve sayfa içerikleri yeniden analiz edilecek. Sürebilir.', {
            title: 'Yeniden puanla',
            accept: 'Başlat',
        })) return;

        const button = document.getElementById('seo-rescore');
        button.disabled = true;
        button.classList.add('opacity-60');

        try {
            const { message, data } = await http.post('/admin/seo/rescore');
            toast.success(message);
            renderOverview(data);
            load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Yeniden puanlanamadı.');
        } finally {
            button.disabled = false;
            button.classList.remove('opacity-60');
        }
    });

    try {
        renderOverview(JSON.parse(document.querySelector('[data-seo-overview]').dataset.overview));
    } catch {
        // sunucu zaten sayıları bastı
    }

    paintTabs();
    load();
}
