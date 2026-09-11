/**
 * GA4 metrik biçimlendirme — Analitik sayfası ve Dashboard özet kartı ortak kullanır.
 */

const nf = new Intl.NumberFormat('tr-TR');

export function formatMetric(value, format) {
    const n = Number(value) || 0;

    if (format === 'duration') {
        const total = Math.round(n);
        const m = Math.floor(total / 60);
        const s = total % 60;
        return m > 0 ? `${m}d ${s}sn` : `${s}sn`;
    }

    if (format === 'rate') {
        // GA4 bounceRate 0..1 arası oran döndürür.
        return `%${(n * 100).toFixed(1).replace('.', ',')}`;
    }

    return nf.format(Math.round(n));
}

export const compact = (value) => nf.format(Math.round(Number(value) || 0));

/**
 * Değişim rozeti HTML'i. lowerIsBetter true ise düşüş yeşil sayılır.
 */
export function changeBadge(change, lowerIsBetter = false) {
    if (change === null || change === undefined) {
        return '<span class="text-[11px] text-gray-400">yeni</span>';
    }

    const up = change >= 0;
    const good = lowerIsBetter ? ! up : up;
    const cls = change === 0
        ? 'text-gray-400'
        : good ? 'text-success-600' : 'text-danger-500';
    const icon = change === 0 ? 'remove' : up ? 'arrow_upward' : 'arrow_downward';
    const text = `${Math.abs(change).toFixed(1).replace('.', ',')}%`;

    return `<span class="inline-flex items-center gap-[2px] text-[11px] font-medium ${cls}">
        <i class="material-symbols-outlined !text-[13px]">${icon}</i>${text}</span>`;
}

/** ApexCharts için tema uyumlu ortak ayarlar. */
export function apexBase() {
    const dark = document.documentElement.classList.contains('dark');

    return {
        foreColor: dark ? '#8695aa' : '#64748b',
        gridBorder: dark ? '#172036' : '#eef1f6',
        tooltipTheme: dark ? 'dark' : 'light',
    };
}
