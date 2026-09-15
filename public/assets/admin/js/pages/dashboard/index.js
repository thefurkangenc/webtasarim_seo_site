/**
 * Dashboard.
 *
 * GA4 tarafı AJAX ile gelir (ağ gerektirir, bağlantı kurulmamış olabilir);
 * geri kalan grafiklerin verisi sunucu render'ında data-* özniteliklerine
 * basılı gelir, bu yüzden bağlantı olmasa da sayfa dolu görünür.
 *
 * Trafik kartı ile "En Çok Görüntülenen" listesi AYNI isteği paylaşır —
 * ikisi de summary() çıktısından beslenir, aralık değişince tek istek atılır.
 */

import { escapeHtml, http } from '../../core/http.js';
import { apexBase, changeBadge, compact, formatMetric } from '../../core/metrics.js';

const KPI_KEYS = ['activeUsers', 'sessions', 'screenPageViews', 'bounceRate'];
const ACTIVE_RANGE = ['bg-primary-500', 'text-white'];

/** Tema değişince ApexCharts renkleri güncellenmeli; hepsi tek yerden tazelenir. */
const redraws = [];

document.getElementById('light-dark-toggle')?.addEventListener('click', () => {
    setTimeout(() => redraws.forEach((redraw) => redraw()), 50);
});

/* ── Şu an sitede kaç kişi var ─────────────────────────────────────── */
const realtime = document.querySelector('[data-realtime]');

if (realtime) {
    const count = realtime.querySelector('[data-realtime-count]');

    const tick = async () => {
        try {
            const { data } = await http.get('/admin/analytics/realtime');
            count.textContent = data.active_users;
            // Kimse yokken "0 kişi sitede" yazmak bilgi değil gürültü.
            realtime.hidden = data.active_users === 0;
            realtime.classList.toggle('flex', data.active_users > 0);
        } catch {
            // Bağlantı yoksa rozet hiç görünmez; hata göstermeye değmez.
            realtime.hidden = true;
        }
    };

    tick();
    setInterval(tick, 30000);
}

/* ── Trafik kartı + en çok görüntülenen ────────────────────────────── */
const traffic = document.querySelector('[data-traffic]');

if (traffic) {
    const topPagesList = document.querySelector('[data-top-pages-list]');
    let chart = null;
    let latest = null;
    let range = 28;

    const rangeButtons = [...traffic.querySelectorAll('[data-range]')];

    rangeButtons.forEach((button) => button.addEventListener('click', () => {
        range = Number(button.dataset.range);
        markRange();
        load();
    }));

    function markRange() {
        rangeButtons.forEach((button) => {
            const active = Number(button.dataset.range) === range;
            ACTIVE_RANGE.forEach((cls) => button.classList.toggle(cls, active));
            button.classList.toggle('text-black', ! active);
            button.classList.toggle('dark:text-white', ! active);
        });
    }

    async function load() {
        try {
            const { data } = await http.get(`${traffic.dataset.endpoint}?range=${range}`);
            latest = data;
            renderKpis(data.kpis);
            renderTrend(data.timeseries);
            renderTopPages(data.top_pages);
        } catch {
            traffic.querySelector('[data-kpis]').innerHTML =
                '<p class="text-sm text-gray-400 col-span-full">Analitik verisi şu an alınamadı.</p>';

            if (topPagesList) {
                topPagesList.innerHTML = '<span class="text-sm text-gray-400">Veri alınamadı.</span>';
            }
        }
    }

    function renderKpis(kpis) {
        const picked = KPI_KEYS.map((key) => kpis.find((kpi) => kpi.key === key)).filter(Boolean);

        traffic.querySelector('[data-kpis]').innerHTML = picked.map((kpi) => `
            <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">${escapeHtml(kpi.label)}</span>
                <span class="block text-lg font-bold text-black dark:text-white leading-none mb-[5px]">${formatMetric(kpi.value, kpi.format)}</span>
                ${changeBadge(kpi.change, kpi.lower_is_better)}
            </div>`).join('');
    }

    function renderTrend(series) {
        const base = apexBase();

        chart?.destroy();
        chart = new ApexCharts(traffic.querySelector('[data-traffic-chart]'), {
            chart: { type: 'area', height: 270, toolbar: { show: false }, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: [
                { name: 'Oturum', data: series.sessions },
                { name: 'Ziyaretçi', data: series.users },
            ],
            colors: ['#605dff', '#37d80a'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.32, opacityTo: 0.02 } },
            xaxis: { categories: series.labels, tickAmount: 7, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: (value) => compact(value) } },
            legend: { position: 'top', horizontalAlign: 'right', markers: { radius: 10 } },
            grid: { borderColor: base.gridBorder, strokeDashArray: 4 },
            tooltip: { theme: base.tooltipTheme },
        });
        chart.render();
    }

    function renderTopPages(pages) {
        if (! topPagesList) {
            return;
        }

        if (! pages?.length) {
            topPagesList.innerHTML = '<span class="text-sm text-gray-400">Bu aralıkta veri yok.</span>';

            return;
        }

        // Çubuk genişliği en yüksek satıra göre ölçeklenir; mutlak sayı yerine
        // oran göstermek hangi sayfanın baskın olduğunu bir bakışta verir.
        const top = Math.max(...pages.map((page) => page.views), 1);

        topPagesList.className = 'flex flex-col';
        topPagesList.innerHTML = pages.slice(0, 6).map((page) => `
            <div class="py-[9px] border-b border-gray-100 dark:border-[#172036] last:border-0">
                <div class="flex items-center justify-between gap-[10px] mb-[6px]">
                    <span class="text-xs text-black dark:text-white truncate" title="${escapeHtml(page.title || page.path)}">
                        ${escapeHtml(page.title || page.path)}
                    </span>
                    <span class="text-xs font-semibold text-black dark:text-white shrink-0">${compact(page.views)}</span>
                </div>
                <span class="block h-[4px] rounded-full bg-gray-100 dark:bg-[#15203c] overflow-hidden">
                    <span class="block h-full rounded-full bg-primary-500" style="width: ${Math.round(page.views / top * 100)}%"></span>
                </span>
            </div>`).join('');
    }

    redraws.push(() => latest && renderTrend(latest.timeseries));
    markRange();
    load();
}

/* ── Talep durumları (halka) ───────────────────────────────────────── */
const donut = document.querySelector('[data-lead-donut]');

if (donut) {
    const rows = JSON.parse(donut.dataset.series || '[]');
    let chart = null;

    const render = () => {
        const base = apexBase();

        chart?.destroy();
        chart = new ApexCharts(donut, {
            chart: { type: 'donut', height: 250, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: rows.map((row) => row.value),
            labels: rows.map((row) => row.label),
            colors: rows.map((row) => row.color),
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            legend: { position: 'bottom', markers: { radius: 10 } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { fontSize: '12px' },
                            value: { fontSize: '20px', fontWeight: 700, color: base.foreColor },
                            total: { show: true, label: 'Toplam', fontSize: '12px' },
                        },
                    },
                },
            },
            tooltip: { theme: base.tooltipTheme },
        });
        chart.render();
    };

    redraws.push(render);
    render();
}

/* ── İçerik üretimi (yığılmış kolon) ───────────────────────────────── */
const production = document.querySelector('[data-production]');

if (production) {
    const payload = JSON.parse(production.dataset.payload || '{"labels":[],"series":[]}');
    let chart = null;

    const render = () => {
        const base = apexBase();

        chart?.destroy();
        chart = new ApexCharts(production, {
            chart: {
                type: 'bar', height: 290, stacked: true, toolbar: { show: false },
                foreColor: base.foreColor, fontFamily: 'inherit',
            },
            series: payload.series,
            colors: ['#605dff', '#37d80a', '#ffb264', '#39b2de'],
            dataLabels: { enabled: false },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 4, borderRadiusApplication: 'end' } },
            xaxis: { categories: payload.labels, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: (value) => compact(value) } },
            legend: { position: 'top', horizontalAlign: 'right', markers: { radius: 10 } },
            grid: { borderColor: base.gridBorder, strokeDashArray: 4 },
            tooltip: { theme: base.tooltipTheme },
        });
        chart.render();
    };

    redraws.push(render);
    render();
}
