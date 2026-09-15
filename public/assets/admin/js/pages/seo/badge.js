/**
 * SEO skor rozeti — SEO Sağlığı sayfası, liste ekranları ve dashboard kartı
 * ortak kullanır. Not eşikleri config/seo.php `grade` ile aynı mantık.
 */

export const GRADE = {
    bad: { label: 'Kötü', dot: 'bg-danger-500', text: 'text-danger-500', ring: '#ef4444' },
    ok: { label: 'İyileştirilebilir', dot: 'bg-warning-500', text: 'text-warning-600', ring: '#f59e0b' },
    good: { label: 'İyi', dot: 'bg-success-500', text: 'text-success-600', ring: '#22c55e' },
};

export function scoreBadge(score, grade) {
    if (score === null || score === undefined) {
        return `<span class="inline-flex items-center gap-[5px] text-xs text-gray-400" title="Analiz edilmedi">
            <span class="w-[8px] h-[8px] rounded-full bg-gray-300 dark:bg-[#172036]"></span>—</span>`;
    }

    const g = GRADE[grade] ?? GRADE.bad;

    return `<span class="inline-flex items-center gap-[5px] text-xs font-medium ${g.text}" title="${g.label}">
        <span class="w-[8px] h-[8px] rounded-full ${g.dot}"></span>${score}</span>`;
}
