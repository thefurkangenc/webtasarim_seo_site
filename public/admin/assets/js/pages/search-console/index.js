/**
 * Search Console paneli — bağlantı ayarı, arama performansı, site haritası
 * gönderimi ve URL denetimi. Veri /admin/search-console/* uç noktalarından
 * AJAX ile gelir; grafikler global ApexCharts ile çizilir.
 *
 * Ayar formu POST + _method=PUT ile gönderilir (gerçek PUT gövdesini PHP
 * kendiliğinden ayrıştırmıyor) — setting/analytics.js ile aynı kalıp.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { escapeHtml, http, HttpError, ValidationError } from '../../core/http.js';
import { apexBase, changeBadge, compact, formatMetric } from '../../core/metrics.js';
import { toast } from '../../core/toast.js';

const nf = new Intl.NumberFormat('tr-TR');
const percent = (value) => '%' + (Number(value) * 100).toFixed(1).replace('.', ',');
const position = (value) => Number(value).toFixed(1).replace('.', ',');
const dateText = (value) => (value ? new Date(value).toLocaleString('tr-TR') : '—');

/* ---------------------------------------------------------------- bağlantı */

const form = document.getElementById('search-console-form');

if (form) {
    const input = form.querySelector('[name=site_url]');
    const sitesBox = form.querySelector('[data-sc-sites]');
    const sitesList = form.querySelector('[data-sc-sites-list]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('[type=submit]');
        clearErrors(form);
        setLoading(button, true);

        try {
            const { message } = await http.post(form.action, new FormData(form));
            toast.success(message);
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            if (error instanceof ValidationError) {
                showErrors(form, error.errors);
                toast.error('Girilen bilgileri kontrol edin.');
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
            }
        } finally {
            setLoading(button, false);
        }
    });

    document.getElementById('sc-list-sites')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        setLoading(button, true);

        try {
            const { data } = await http.get(form.dataset.sitesEndpoint);

            if (! data.length) {
                toast.error('Bu Google hesabının eriştiği bir mülk yok. 3. adımı (kullanıcı ekleme) tamamladınız mı?');
                return;
            }

            sitesList.innerHTML = data.map((site) => `
                <button type="button" data-site="${escapeHtml(site.site_url)}"
                    class="inline-flex items-center gap-[6px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[15px]">public</i>
                    ${escapeHtml(site.site_url)}
                    <span class="text-gray-400">${escapeHtml(site.permission)}</span>
                </button>
            `).join('');

            sitesBox.classList.remove('hidden');
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Mülkler alınamadı.');
        } finally {
            setLoading(button, false);
        }
    });

    sitesList?.addEventListener('click', (event) => {
        const chip = event.target.closest('button[data-site]');
        if (! chip) return;

        input.value = chip.dataset.site;
        toast.success('Seçildi — kaydetmeyi unutmayın.');
    });

    document.getElementById('sc-test')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        setLoading(button, true);

        try {
            const { message } = await http.post(form.dataset.testEndpoint);
            toast.success(message);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Bağlantı test edilemedi.');
        } finally {
            setLoading(button, false);
        }
    });
}

/* -------------------------------------------------------------- performans */

const root = document.querySelector('[data-search-console]');

if (root) {
    const rangeBox = root.querySelector('[data-range]');
    const charts = {};
    let range = 28;
    let last = null;

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
        loadPerformance();
    });

    document.getElementById('light-dark-toggle')?.addEventListener('click', () => {
        setTimeout(() => last && render(last), 50);
    });

    async function loadPerformance() {
        try {
            const { data } = await http.get(`${root.dataset.performance}?range=${range}`);
            last = data;
            render(data);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Arama performansı alınamadı.');
        }
    }

    function render(data) {
        root.querySelector('[data-sc-window]').textContent =
            `${new Date(data.start).toLocaleDateString('tr-TR')} – ${new Date(data.end).toLocaleDateString('tr-TR')}`;

        root.querySelector('[data-sc-kpis]').innerHTML = data.kpis.map((kpi) => `
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md">
                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">${escapeHtml(kpi.label)}</span>
                <span class="block text-lg font-bold text-black dark:text-white leading-none mb-[6px]">${formatMetric(kpi.value, kpi.format)}</span>
                ${changeBadge(kpi.change, kpi.lower_is_better)}
            </div>
        `).join('');

        renderTrend(data.timeseries);
        renderRows('[data-sc-queries]', data.queries, (row) => escapeHtml(row.query));
        renderRows('[data-sc-pages]', data.pages, (row) => `
            <a href="${escapeHtml(row.page)}" target="_blank" rel="noopener"
                class="hover:text-primary-500 transition-all">${escapeHtml(row.label)}</a>`);
        renderCountries(data.countries);
        renderDevices(data.devices);
    }

    function renderTrend(ts) {
        const base = apexBase();

        upsert('sc-trend', {
            chart: { type: 'area', height: 320, toolbar: { show: false }, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: [
                { name: 'Tıklama', data: ts.clicks },
                { name: 'Gösterim', data: ts.impressions },
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
            noData: { text: 'Veri yok' },
        });
    }

    function renderRows(selector, rows, labelFn) {
        const body = root.querySelector(selector);

        if (! rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="py-[12px] text-gray-400">Bu aralıkta veri yok.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => `
            <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                <td class="py-[9px] px-[12px]">
                    <span class="block truncate max-w-[260px]" title="${escapeHtml(row.query ?? row.page)}">${labelFn(row)}</span>
                </td>
                <td class="py-[9px] px-[12px] text-right font-medium whitespace-nowrap">${compact(row.clicks)}</td>
                <td class="py-[9px] px-[12px] text-right whitespace-nowrap">${compact(row.impressions)}</td>
                <td class="py-[9px] px-[12px] text-right whitespace-nowrap text-gray-500 dark:text-gray-400">${percent(row.ctr)}</td>
                <td class="py-[9px] px-[12px] text-right whitespace-nowrap text-gray-500 dark:text-gray-400">${position(row.position)}</td>
            </tr>
        `).join('');
    }

    function renderCountries(rows) {
        const list = root.querySelector('[data-sc-countries]');

        if (! rows.length) {
            list.innerHTML = '<li class="text-gray-400">Veri yok</li>';
            return;
        }

        const max = Math.max(...rows.map((r) => r.clicks), 1);

        list.innerHTML = rows.map((row) => `
            <li>
                <div class="flex items-center justify-between mb-[4px]">
                    <span>${escapeHtml(row.name)}</span>
                    <span class="text-gray-500 dark:text-gray-400">${compact(row.clicks)} tıklama</span>
                </div>
                <div class="h-[6px] rounded-full bg-gray-100 dark:bg-[#172036] overflow-hidden">
                    <div class="h-full bg-primary-500 rounded-full" style="width:${(row.clicks / max) * 100}%"></div>
                </div>
            </li>
        `).join('');
    }

    function renderDevices(rows) {
        const base = apexBase();

        upsert('sc-devices', {
            chart: { type: 'donut', height: 280, foreColor: base.foreColor, fontFamily: 'inherit' },
            series: rows.map((r) => r.clicks),
            labels: rows.map((r) => r.name),
            colors: ['#605dff', '#37d80a', '#fd5812', '#0dcaf0', '#ffb264'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '62%' } } },
            stroke: { width: 0 },
            tooltip: { theme: base.tooltipTheme, y: { formatter: (v) => compact(v) + ' tıklama' } },
            noData: { text: 'Veri yok' },
        });
    }

    function upsert(elId, options) {
        const el = document.getElementById(elId);
        if (! el) return;

        charts[elId]?.destroy();
        charts[elId] = new ApexCharts(el, options);
        charts[elId].render();
    }

    /* ------------------------------------------------------ site haritaları */

    const sitemapBox = root.querySelector('[data-sc-sitemaps]');

    async function loadSitemaps() {
        try {
            const { data } = await http.get(root.dataset.sitemaps);
            renderSitemaps(data);
        } catch (error) {
            sitemapBox.innerHTML = `<span class="text-sm text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Site haritaları alınamadı.',
            )}</span>`;
        }
    }

    function renderSitemaps(data) {
        const notice = data.submitted
            ? `<div class="flex items-center gap-[8px] text-sm text-success-600 mb-[15px]">
                   <i class="material-symbols-outlined !text-[18px]">check_circle</i>
                   Site haritanız Search Console'a bildirilmiş.
               </div>`
            : `<div class="flex items-start gap-[8px] p-[12px] rounded-md bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] text-warning-600 text-sm mb-[15px]">
                   <i class="material-symbols-outlined !text-[18px] shrink-0">info</i>
                   <span>Site haritanız (<code>${escapeHtml(data.our_url)}</code>) henüz bildirilmemiş.
                   “Site haritamızı gönder” butonuna basın — Google sayfalarınızı daha hızlı bulur.</span>
               </div>`;

        const rows = data.list.length
            ? data.list.map((entry) => `
                <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                    <td class="py-[9px] px-[12px]">
                        <a href="${escapeHtml(entry.path)}" target="_blank" rel="noopener"
                            class="block truncate max-w-[320px] hover:text-primary-500 transition-all">${escapeHtml(entry.path)}</a>
                        ${entry.is_index ? '<span class="text-[10px] text-gray-400">indeks dosyası</span>' : ''}
                    </td>
                    <td class="py-[9px] px-[12px] text-right whitespace-nowrap">${compact(entry.submitted_urls)}</td>
                    <td class="py-[9px] px-[12px] text-right whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">${dateText(entry.last_downloaded)}</td>
                    <td class="py-[9px] px-[12px] text-right whitespace-nowrap">
                        ${entry.errors > 0 ? `<span class="text-danger-500">${nf.format(entry.errors)} hata</span>` : ''}
                        ${entry.warnings > 0 ? `<span class="text-warning-600">${nf.format(entry.warnings)} uyarı</span>` : ''}
                        ${entry.errors === 0 && entry.warnings === 0 ? '<span class="text-success-600">sorun yok</span>' : ''}
                        ${entry.is_pending ? '<span class="text-gray-400">· işleniyor</span>' : ''}
                    </td>
                </tr>`).join('')
            : '<tr><td colspan="4" class="py-[12px] px-[12px] text-gray-400">Search Console\'a bildirilmiş bir site haritası yok.</td></tr>';

        sitemapBox.innerHTML = `${notice}
            <div class="table-responsive overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md">Dosya</th>
                            <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Bildirilen adres</th>
                            <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Son okuma</th>
                            <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right last:rounded-tr-md">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white">${rows}</tbody>
                </table>
            </div>`;
    }

    document.getElementById('sc-submit-sitemap')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.submit);
            toast.success(message);
            await loadSitemaps();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Gönderilemedi.');
        } finally {
            setLoading(button, false);
        }
    });

    /* ---------------------------------------------------------- URL denetimi */

    const inspectInput = document.getElementById('sc-inspect-url');
    const inspectBox = root.querySelector('[data-sc-inspect-result]');

    document.getElementById('sc-inspect')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const errorSlot = root.querySelector('[data-error="url"]');

        errorSlot.textContent = '';
        setLoading(button, true);

        try {
            const { data } = await http.post(root.dataset.inspect, { url: inspectInput.value });
            renderInspection(data);
        } catch (error) {
            inspectBox.innerHTML = '';

            if (error instanceof ValidationError) {
                errorSlot.textContent = error.errors.url?.[0] ?? 'Adresi kontrol edin.';
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Denetlenemedi.');
            }
        } finally {
            setLoading(button, false);
        }
    });

    inspectInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('sc-inspect').click();
        }
    });

    function renderInspection(data) {
        const tone = { PASS: 'success', PARTIAL: 'warning', FAIL: 'danger' }[data.verdict] ?? 'gray';
        const colors = {
            success: 'text-success-600 bg-success-100 dark:bg-[#ffffff14]',
            warning: 'text-warning-600 bg-warning-100 dark:bg-[#ffffff14]',
            danger: 'text-danger-500 bg-danger-100 dark:bg-[#ffffff14]',
            gray: 'text-gray-500 bg-gray-100 dark:bg-[#ffffff14]',
        };

        const facts = [
            ['Google’daki durumu', data.coverage],
            ['Son tarama', dateText(data.last_crawl)],
            ['Sayfa alınabildi mi', data.fetch],
            ['robots.txt', data.robots],
            ['İndeksleme izni', data.indexing],
            ['Google’ın seçtiği asıl adres', data.google_canonical ?? '—'],
            ['Mobil uyumluluk', data.mobile_verdict],
            ['Zengin sonuçlar', data.rich_results],
        ];

        inspectBox.innerHTML = `
            <div class="flex items-center gap-[10px] mb-[15px] flex-wrap">
                <span class="text-xs font-medium py-[3px] px-[10px] rounded-sm ${colors[tone]}">${escapeHtml(data.verdict_label)}</span>
                <code class="text-xs text-gray-500 dark:text-gray-400 break-all">${escapeHtml(data.url)}</code>
                ${data.link ? `<a href="${escapeHtml(data.link)}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-[4px] text-xs text-primary-500 hover:underline">
                    <i class="material-symbols-outlined !text-[15px]">open_in_new</i>Search Console'da aç</a>` : ''}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px]">
                ${facts.map(([label, value]) => `
                    <div class="p-[12px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400 mb-[3px]">${escapeHtml(label)}</span>
                        <span class="block text-sm text-black dark:text-white break-all">${escapeHtml(String(value ?? '—'))}</span>
                    </div>`).join('')}
            </div>

            ${data.mobile_issues.length ? `
                <ul class="mt-[15px] space-y-[6px] text-sm text-warning-600">
                    ${data.mobile_issues.map((issue) => `<li class="flex items-start gap-[6px]">
                        <i class="material-symbols-outlined !text-[16px] shrink-0">warning</i>${escapeHtml(issue)}</li>`).join('')}
                </ul>` : ''}

            ${data.sitemaps.length ? `
                <p class="mt-[15px] text-xs text-gray-500 dark:text-gray-400">
                    Bu adres şu site haritalarında bulundu: ${data.sitemaps.map((s) => escapeHtml(s)).join(', ')}
                </p>` : ''}`;
    }

    paintTabs();
    loadPerformance();
    loadSitemaps();
}
