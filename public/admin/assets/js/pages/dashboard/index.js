/**
 * Dashboard — GA4 özet kartı (4 KPI + mini eğilim grafiği).
 * Ayrıntı /admin/analytics'te; burada yalnızca 28 günlük hızlı bakış.
 */

import { escapeHtml, http } from '../../core/http.js';
import { apexBase, changeBadge, compact, formatMetric } from '../analytics/format.js';

const root = document.querySelector('[data-dashboard-analytics]');

if (root) {
    const KEYS = ['activeUsers', 'sessions', 'screenPageViews', 'bounceRate'];
    let chart = null;

    load();
    document.getElementById('light-dark-toggle')?.addEventListener('click', () => {
        setTimeout(() => last && renderTrend(last.timeseries), 50);
    });

    let last = null;

    async function load() {
        try {
            const { data } = await http.get(`${root.dataset.endpoint}?range=28`);
            last = data;
            renderKpis(data.kpis);
            renderTrend(data.timeseries);
        } catch {
            root.querySelector('[data-kpis]').innerHTML =
                '<p class="text-sm text-gray-400 col-span-full">Analitik verisi şu an alınamadı.</p>';
        }
    }

    function renderKpis(kpis) {
        const pick = KEYS.map((key) => kpis.find((k) => k.key === key)).filter(Boolean);

        root.querySelector('[data-kpis]').innerHTML = pick.map((kpi) => `
            <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">${escapeHtml(kpi.label)}</span>
                <span class="block text-lg font-bold text-black dark:text-white leading-none mb-[5px]">${formatMetric(kpi.value, kpi.format)}</span>
                ${changeBadge(kpi.change, kpi.lower_is_better)}
            </div>
        `).join('');
    }

    function renderTrend(ts) {
        const base = apexBase();
        const options = {
            chart: { type: 'area', height: 260, toolbar: { show: false }, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: [{ name: 'Oturum', data: ts.sessions }],
            colors: ['#605dff'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.03 } },
            xaxis: { categories: ts.labels, tickAmount: 6, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: (v) => compact(v) } },
            grid: { borderColor: base.gridBorder, strokeDashArray: 4 },
            tooltip: { theme: base.tooltipTheme },
        };

        if (chart) {
            chart.destroy();
        }

        chart = new ApexCharts(document.getElementById('dashboard-trend'), options);
        chart.render();
    }
}
