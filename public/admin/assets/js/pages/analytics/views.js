/**
 * Liste ekranlarında satır başına "son 28 gün görüntüleme" hücresi.
 * Blog, Sayfa ve Hizmet listeleri ortak kullanır.
 *
 * Sayılar listeyle birlikte gelmez, satırlar basıldıktan sonra tek bir
 * istekle doldurulur — böylece içerik listesi GA4'in hızına (ya da
 * bağlanmamış olmasına) takılmaz. Veri yoksa kolon tamamen gizlenir.
 */

import { http } from '../../core/http.js';
import { cell } from '../../core/table.js';

const formatter = new Intl.NumberFormat('tr-TR');

/**
 * @param {'page'|'blog'|'service'} type Sunucunun kaydı çözeceği içerik türü.
 */
export function pageViews(type) {
    // Kolon Blade'de yetkiye göre basılır; yoksa hücre de üretilmez.
    const column = document.querySelector('[data-views-column]');

    // Veri alınamadığında kolon bir daha denenmez: sonraki sayfalarda hücre
    // hiç üretilmez, başlık gizli kalır.
    let disabled = ! column;

    return {
        cell: (item) => disabled
            ? ''
            : cell(`<span data-views="${item.id}" class="text-sm text-gray-400">…</span>`),

        fill: async (items) => {
            if (disabled || items.length === 0) {
                return;
            }

            try {
                const { data } = await http.get('/admin/analytics/page-views', {
                    type,
                    ids: items.map((item) => item.id).join(','),
                });

                if (! data.available) {
                    disabled = true;

                    return hideColumn(column);
                }

                items.forEach((item) => {
                    const slot = document.querySelector(`[data-views="${item.id}"]`);

                    if (slot) {
                        const views = data.views[item.id] ?? 0;
                        slot.textContent = formatter.format(views);
                        slot.className = views > 0 ? 'text-sm font-medium' : 'text-sm text-gray-400';
                        slot.title = `Son ${data.days} gün`;
                    }
                });
            } catch {
                // Yetki yok ya da Google yanıt vermedi: kolonu sessizce kaldır,
                // liste ekranı bir analitik hatası yüzünden uyarı basmamalı.
                disabled = true;
                hideColumn(column);
            }
        },
    };
}

function hideColumn(column) {
    column.classList.add('hidden');
    document.querySelectorAll('[data-views]').forEach((slot) => slot.closest('td')?.classList.add('hidden'));
}
