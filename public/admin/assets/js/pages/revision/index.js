/**
 * Revizyonlar — merkezi liste. Satır şablonu ve karşılaştırma modalı
 * core/revisions.js'te; burada yalnızca tablo kurulur.
 */

import { openRevisionCompare, revisionRow } from '../../core/revisions.js';
import { DataTable } from '../../core/table.js';

const body = document.getElementById('revision-table-body');

const table = new DataTable({
    endpoint: '/admin/revision/datatable',
    body,
    search: document.getElementById('revision-search'),
    filters: {
        module: document.getElementById('revision-module'),
    },
    empty: 'Henüz revizyon yok. İçerikler düzenlendikçe eski sürümleri burada birikir.',
    row: (item) => revisionRow(item),
});

body.addEventListener('click', (event) => {
    const compare = event.target.closest('[data-revision-compare]');

    if (compare) {
        openRevisionCompare(compare.dataset.revisionCompare);
    }
});

// Geri yükleme yeni bir sürüm daha yaratır; liste tazelenmeli.
document.addEventListener('revision:restored', () => table.reload());

table.load();
