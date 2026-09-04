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

/**
 * `<option data-custom-properties='{"icon":"/path/icon.svg"}'>` verilen
 * seçenekler simgeyle render edilir — Choices.js bu attribute'u zaten
 * `choice.customProperties` olarak okuyor, biz sadece varsayılan şablonun
 * (erişilebilirlik/aria öznitelikleri korunarak) başına bir `<img>` ekliyoruz.
 * İkonu olmayan seçenekler etkilenmez, her select için ayrı bir bayrak gerekmez.
 */
function withIcons(template) {
    const withIcon = (element, choice) => {
        const icon = choice.customProperties?.icon;

        if (icon) {
            const img = document.createElement('img');
            img.src = icon;
            img.alt = '';
            img.className = 'inline-block w-[16px] h-[16px] align-[-3px] ltr:mr-[6px] rtl:ml-[6px]';
            element.prepend(img);
        }

        return element;
    };

    return {
        item(classNames, choice, removeItemButton) {
            return withIcon(Choices.defaults.templates.item.call(this, classNames, choice, removeItemButton), choice);
        },
        choice(classNames, choice, selectText, groupName) {
            return withIcon(Choices.defaults.templates.choice.call(this, classNames, choice, selectText, groupName), choice);
        },
    };
}

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
            callbackOnCreateTemplates: withIcons,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));

export { init as initSelects };
