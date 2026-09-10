/**
 * Merkezi log kayıtları sayfası. Satır şablonu ve detay modalı
 * core/activity-log.js'ten gelir — modül modallarıyla aynı görünüm.
 */

import { DataTable } from '../../core/table.js';
import { logRow, openLogDetail } from '../../core/activity-log.js';

const body = document.getElementById('log-table-body');

const table = new DataTable({
    endpoint: '/admin/activity-log/datatable',
    body,
    search: document.getElementById('log-search'),
    perPage: 20,
    sort: 'created_at',
    direction: 'desc',
    empty: 'Bu filtrelerle eşleşen log kaydı yok.',
    filters: {
        module: document.getElementById('log-module'),
        event: document.getElementById('log-event'),
        severity: document.getElementById('log-severity'),
        device_type: document.getElementById('log-device'),
        date_from: document.getElementById('date_from'),
        date_to: document.getElementById('date_to'),
    },
    row: (item) => logRow(item),
});

// Modül filtresi URL'den gelebilir: modül modalındaki "Tümünü gör" bağlantısı
// /admin/activity-log?module=blog şeklinde açar.
const preset = new URLSearchParams(window.location.search).get('module');

if (preset) {
    const select = document.getElementById('log-module');
    select.value = preset;
    // Choices.js native select'i sarmalıyor; görünen etiketi de tazelemek gerek.
    select.choicesInstance?.setChoiceByValue(preset);
}

// DataTable kendiliğinden yüklenmez — ilk sayfa her zaman burada tetiklenir.
table.load();

body.addEventListener('click', (event) => {
    const button = event.target.closest('[data-log-detail]');

    if (button) {
        openLogDetail(button.dataset.logDetail);
    }
});
