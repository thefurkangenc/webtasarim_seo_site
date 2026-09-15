/**
 * Analitik (GA4) paneli — özet raporlar + otomatik yenilenen canlı kart.
 * Veri /admin/analytics/data?range= ve /admin/analytics/realtime'dan gelir.
 * Grafikler global ApexCharts ile çizilir.
 */

import { escapeHtml, http, HttpError } from '../../core/http.js';
import { toast } from '../../core/toast.js';
import { apexBase, changeBadge, compact, formatMetric } from '../../core/metrics.js';

const root = document.querySelector('[data-analytics]');

if (root) {
    const dataUrl = root.dataset.endpoint;
    const realtimeUrl = root.dataset.realtime;
    const rangeBox = root.querySelector('[data-range]');
    const charts = {};
    let range = 28;

    const activeTab = 'bg-primary-500 text-white';
    const idleTab = 'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]';

    function paintTabs() {
        rangeBox.querySelectorAll('button').forEach((button) => {
            const on = Number(button.dataset.days) === range;
            button.className = button.className.replace(activeTab, '').replace(idleTab, '').trim()
                + ' ' + (on ? activeTab : idleTab);
        });
    }

    rangeBox.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-days]');
        if (! button) return;
        range = Number(button.dataset.days);
        paintTabs();
        load();
    });

    // Karanlık mod değişince grafikleri yeniden çiz.
    document.getElementById('light-dark-toggle')?.addEventListener('click', () => {
        setTimeout(() => lastSummary && render(lastSummary), 50);
    });

    let lastSummary = null;

    async function load() {
        try {
            const { data } = await http.get(`${dataUrl}?range=${range}`);
            lastSummary = data;
            render(data);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Analitik verisi alınamadı.');
        }
    }

    function render(data) {
        root.querySelector('[data-updated]').textContent =
            'Son güncelleme: ' + new Date(data.updated_at).toLocaleString('tr-TR');

        renderKpis(data.kpis);
        renderTrend(data.timeseries);
        renderTopPages(data.top_pages);
        renderDonut('analytics-channels', 'channels', data.channels);
        renderDonut('analytics-devices', 'devices', data.devices, deviceLabel);
        renderCountries(data.countries);
    }

    function renderKpis(kpis) {
        root.querySelector('[data-kpis]').innerHTML = kpis.map((kpi) => `
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md">
                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">${escapeHtml(kpi.label)}</span>
                <span class="block text-lg font-bold text-black dark:text-white leading-none mb-[6px]">${formatMetric(kpi.value, kpi.format)}</span>
                ${changeBadge(kpi.change, kpi.lower_is_better)}
            </div>
        `).join('');
    }

    function renderTrend(ts) {
        const base = apexBase();
        const options = {
            chart: { type: 'area', height: 320, toolbar: { show: false }, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: [
                { name: 'Oturum', data: ts.sessions },
                { name: 'Kullanıcı', data: ts.users },
            ],
            colors: ['#605dff', '#37d80a'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.03 } },
            xaxis: { categories: ts.labels, tickAmount: 8, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: (v) => compact(v) } },
            grid: { borderColor: base.gridBorder, strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'left' },
            tooltip: { theme: base.tooltipTheme },
        };

        upsert('analytics-trend', options);
    }

    function renderDonut(elId, key, rows, labelFn = (x) => x) {
        const base = apexBase();
        const options = {
            chart: { type: 'donut', height: 280, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: rows.map((r) => r.sessions),
            labels: rows.map((r) => labelFn(r.name)),
            colors: ['#605dff', '#37d80a', '#fd5812', '#0dcaf0', '#ffb264', '#a855f7', '#f43f5e', '#64748b'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '62%' } } },
            stroke: { width: 0 },
            tooltip: { theme: base.tooltipTheme, y: { formatter: (v) => compact(v) + ' oturum' } },
            noData: { text: 'Veri yok' },
        };

        upsert(elId, options);
    }

    function renderTopPages(pages) {
        const body = root.querySelector('[data-top-pages]');

        if (! pages.length) {
            body.innerHTML = '<tr><td class="py-[10px] text-gray-400">Veri yok</td></tr>';
            return;
        }

        body.innerHTML = pages.map((page) => `
            <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                <td class="py-[9px] pr-[10px]">
                    <span class="block truncate max-w-[280px]" title="${escapeHtml(page.path)}">${escapeHtml(page.title || page.path)}</span>
                    <span class="block text-xs text-gray-400 truncate max-w-[280px]">${escapeHtml(page.path)}</span>
                </td>
                <td class="py-[9px] text-right font-medium whitespace-nowrap">${compact(page.views)}</td>
            </tr>
        `).join('');
    }

    function renderCountries(rows) {
        const list = root.querySelector('[data-countries]');

        if (! rows.length) {
            list.innerHTML = '<li class="text-gray-400">Veri yok</li>';
            return;
        }

        const max = Math.max(...rows.map((r) => r.users), 1);

        list.innerHTML = rows.map((row) => `
            <li>
                <div class="flex items-center justify-between mb-[4px]">
                    <span>${escapeHtml(row.name)}</span>
                    <span class="text-gray-500 dark:text-gray-400">${compact(row.users)}</span>
                </div>
                <div class="h-[6px] rounded-full bg-gray-100 dark:bg-[#172036] overflow-hidden">
                    <div class="h-full bg-primary-500 rounded-full" style="width:${(row.users / max) * 100}%"></div>
                </div>
            </li>
        `).join('');
    }

    function upsert(elId, options) {
        const el = document.getElementById(elId);
        if (! el) return;

        if (charts[elId]) {
            charts[elId].destroy();
        }

        charts[elId] = new ApexCharts(el, options);
        charts[elId].render();
    }

    function deviceLabel(name) {
        return { desktop: 'Masaüstü', mobile: 'Mobil', tablet: 'Tablet' }[name] ?? name;
    }

    /* ---- canlı ---- */

    async function loadRealtime() {
        try {
            const { data } = await http.get(realtimeUrl);
            root.querySelector('[data-rt-users]').textContent = compact(data.active_users);
            root.querySelector('[data-rt-time]').textContent =
                new Date(data.updated_at).toLocaleTimeString('tr-TR');
            root.querySelector('[data-rt-pages]').innerHTML = data.pages.slice(0, 5).map((page) =>
                `<li class="truncate max-w-[220px]">${escapeHtml(page.name)} <span class="text-gray-400">· ${compact(page.users)}</span></li>`,
            ).join('');
        } catch {
            // canlı kart sessizce boş kalır
        }
    }

    paintTabs();
    load();
    loadRealtime();
    const timer = setInterval(loadRealtime, 30000);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(timer);
        }
    });
}
