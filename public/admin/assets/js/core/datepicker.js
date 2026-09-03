/**
 * <input data-datepicker> alanlarını Flatpickr ile zenginleştirir.
 *
 * Gönderilen değer Laravel'in `date` kuralının doğrudan anladığı
 * `Y-m-d H:i` biçiminde kalır (`altInput`); kullanıcıya gösterilen
 * `d.m.Y H:i` yalnızca görünümdür, forma gitmez.
 *
 * Modal içeriği otomatik yakalanır — sayfa JS'inin çağırmasına gerek yok.
 *
 * Flatpickr UMD bir dosya (ESM export'u yok); `layout/partials/scripts.blade.php`
 * içinde klasik <script> ile yüklenir ve `window.flatpickr` global'ini kurar.
 * Bu dosya o global'i kullanır, import etmez.
 */

let localized = false;

function init(root = document) {
    const flatpickr = window.flatpickr;

    if (! flatpickr) {
        return;
    }

    if (! localized && flatpickr.l10ns.tr) {
        flatpickr.localize(flatpickr.l10ns.tr);
        localized = true;
    }

    root.querySelectorAll?.('[data-datepicker]:not([data-datepicker-ready])').forEach((input) => {
        input.dataset.datepickerReady = '1';

        const withTime = input.dataset.datepickerTime !== '0';

        flatpickr(input, {
            enableTime: withTime,
            time_24hr: true,
            dateFormat: withTime ? 'Y-m-d H:i' : 'Y-m-d',
            altInput: true,
            altFormat: withTime ? 'd.m.Y H:i' : 'd.m.Y',
            allowInput: true,
            disableMobile: true,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));

export { init as initDatePickers };
