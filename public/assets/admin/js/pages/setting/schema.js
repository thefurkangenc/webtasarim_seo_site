/**
 * Schema.org ayar sekmesi — "Kapalı" işaretli günün saat alanlarını soluklaştırır.
 * Gönderim ortak setting/form.js tarafından yapılır.
 */

const rows = document.querySelectorAll('[data-schema-hours] [data-hour-row]');

rows.forEach((row) => {
    const closed = row.querySelector('[data-hour-closed]');
    const times = row.querySelector('[data-hour-times]');

    if (! closed || ! times) {
        return;
    }

    const sync = () => {
        const off = closed.checked;
        times.style.opacity = off ? '0.4' : '';
        times.style.pointerEvents = off ? 'none' : '';
        times.querySelectorAll('input').forEach((input) => {
            input.disabled = off;
        });
    };

    closed.addEventListener('change', sync);
    sync();
});
