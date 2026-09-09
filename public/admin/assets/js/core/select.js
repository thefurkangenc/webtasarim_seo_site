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
 *
 * Aynı mekanizma `depth` için de çalışır — `App\Support\Tree::options()` ile
 * üretilen ağaç seçeneklerinde (`{"depth":1}`) açılır listedeki satır sola
 * girinti alır ve önüne bir "alt dal" oku eklenir. Yalnızca açılır listede
 * (`choice`) uygulanır; seçildikten sonra kutuda görünen etiket (`item`)
 * karmaşıklaşmasın diye düz kalır.
 */
function withIcons(template) {
    const addIcon = (element, choice) => {
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

    const addTreeIndent = (element, choice) => {
        const depth = choice.customProperties?.depth;

        if (! depth) {
            return element;
        }

        element.style.paddingLeft = `${14 + depth * 18}px`;

        // <option> metni native/plain select (Choices henüz kurulmadan ya da
        // 'plain' modda) için görünmez boşluk + "↳" oku ile girintili gelir
        // — bkz. App\Support\Tree::render(). Choices kurulunca aynı işi bu
        // ikon üstlenir; metindeki ham öneki burada temizlemezsek ikisi
        // üst üste görünür (çift ok).
        element.textContent = element.textContent.replace(/^[\u00A0]*\u21B3\s*/, '');

        const marker = document.createElement('i');
        marker.className = 'material-symbols-outlined ltr:mr-[4px] rtl:ml-[4px] !text-[15px] align-[-3px] text-gray-400 dark:text-gray-500';
        marker.textContent = 'subdirectory_arrow_right';
        element.prepend(marker);

        return element;
    };

    return {
        item(classNames, choice, removeItemButton) {
            return addIcon(Choices.defaults.templates.item.call(this, classNames, choice, removeItemButton), choice);
        },
        choice(classNames, choice, selectText, groupName) {
            const element = addIcon(Choices.defaults.templates.choice.call(this, classNames, choice, selectText, groupName), choice);

            return addTreeIndent(element, choice);
        },
    };
}

function init(root = document) {
    root.querySelectorAll?.('select[data-choices]:not([data-choices-ready])').forEach((select) => {
        select.dataset.choicesReady = '1';

        // Örnek select üzerinde saklanır: sayfa JS'i programatik seçim yapmak
        // istediğinde (çoklu alanlarda "tümünü seç" gibi kısayollar) native
        // select'e yazmak yetmez, Choices kendi arayüzünü tazelemelidir.
        select.choicesInstance = new Choices(select, {
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
