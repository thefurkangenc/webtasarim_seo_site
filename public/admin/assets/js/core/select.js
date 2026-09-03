/**
 * <select data-choices> alanlarını Choices.js ile zenginleştirir: tema
 * uyumlu görünüm + arama. Native <select> DOM'da kalır — FormData, `.value`,
 * `change` olayı ve doğrulama hataları etkilenmez; Choices yalnızca üstüne
 * kendi arayüzünü kurar ve seçimi orijinal select'e senkron tutar.
 *
 * Modal içeriği (AJAX ile sonradan gelen select'ler) otomatik yakalanır —
 * sayfa JS'inin ayrıca çağırmasına gerek yok.
 */

import Choices from '../vendor/choices/choices.mjs';

function init(root = document) {
    root.querySelectorAll?.('select[data-choices]:not([data-choices-ready])').forEach((select) => {
        select.dataset.choicesReady = '1';

        new Choices(select, {
            searchEnabled: select.options.length > 7,
            searchPlaceholderValue: 'Ara...',
            noResultsText: 'Sonuç bulunamadı',
            noChoicesText: 'Seçenek yok',
            itemSelectText: '',
            shouldSort: false,
            removeItemButton: select.multiple,
            allowHTML: false,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));

export { init as initSelects };
