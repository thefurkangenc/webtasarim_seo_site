/**
 * Yönlendirme yöneticisi — iki tablo (yönlendirmeler + 404 kayıtları),
 * kayıt modalı, canlı zincir/döngü uyarısı ve CSV içe/dışa aktarma.
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const MATCH_HINTS = {
    exact: 'Adres birebir bu olduğunda yönlendirir.',
    prefix: 'Bu adres ve altındaki her şey yönlendirilir; kalan yol hedefe eklenir.',
    regex: 'Kaynak bir desendir; hedefte $1, $2… ile yakalanan grupları kullanabilirsiniz.',
};

const CODE_BADGES = {
    301: 'bg-success-100 text-success-600',
    302: 'bg-primary-100 text-primary-600',
    307: 'bg-primary-100 text-primary-600',
    410: 'bg-danger-100 text-danger-500',
};

const TYPE_BADGES = {
    exact: 'bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-300',
    prefix: 'bg-secondary-100 text-secondary-600',
    regex: 'bg-purple-100 text-purple-600',
};

const modal = new AjaxModal();

/* ---------------------------------------------------------------------- *
 | Yönlendirmeler tablosu
 * ---------------------------------------------------------------------- */

const badge = (text, classes) =>
    `<span class="text-[10px] font-medium py-[2px] px-[8px] rounded-sm inline-block ${classes}">${escapeHtml(text)}</span>`;

const fromToCell = (item) => `<div class="min-w-0">
    <span class="font-medium block truncate max-w-[360px]">/${escapeHtml(item.from_path)}</span>
    <span class="text-xs text-gray-500 dark:text-gray-400 block truncate max-w-[360px]">
        ${item.is_gone ? '<i class="ri-close-circle-line align-[-2px]"></i> içerik kaldırıldı' : '→ ' + escapeHtml(item.to_url ?? '')}
    </span>
    ${item.source === 'auto' ? '<span class="text-[10px] text-secondary-500">otomatik</span>' : ''}
    ${item.source === 'import' ? '<span class="text-[10px] text-gray-400">içe aktarıldı</span>' : ''}
</div>`;

const activeToggle = (item) => `<button type="button" data-toggle="${item.id}" title="${item.is_active ? 'Pasifleştir' : 'Aktifleştir'}"
    class="relative inline-block w-[40px] h-[22px] rounded-full transition-all ${item.is_active ? 'bg-primary-500' : 'bg-gray-200 dark:bg-[#172036]'}">
    <span class="absolute top-[3px] w-[16px] h-[16px] rounded-full bg-white transition-all ${item.is_active ? 'ltr:left-[21px] rtl:right-[21px]' : 'ltr:left-[3px] rtl:right-[3px]'}"></span>
</button>`;

const redirects = new DataTable({
    endpoint: '/admin/redirect/datatable',
    body: document.getElementById('redirect-table-body'),
    search: document.getElementById('redirect-search'),
    filters: {
        match_type: document.getElementById('redirect-type'),
        status: document.getElementById('redirect-status'),
    },
    sort: 'created_at',
    direction: 'desc',
    empty: 'Henüz yönlendirme yok.',
    row: (item) => `<tr data-id="${item.id}">
        ${cell(fromToCell(item))}
        ${cell(badge(item.match_type_label, TYPE_BADGES[item.match_type] ?? TYPE_BADGES.exact))}
        ${cell(badge(String(item.status_code), CODE_BADGES[item.status_code] ?? CODE_BADGES[301]))}
        ${cell(`<span class="font-medium">${item.hits}</span>`)}
        ${cell(escapeHtml(item.last_hit_at ?? '—'))}
        ${cell(activeToggle(item))}
        ${cell(`<div class="flex items-center gap-[9px]">
            <button type="button" data-edit="${item.id}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </button>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('redirect-table-body').addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');
    const toggle = event.target.closest('[data-toggle]');

    if (edit) {
        return openForm(edit.dataset.edit);
    }

    if (toggle) {
        try {
            await http.put(`/admin/redirect/${toggle.dataset.toggle}/toggle`);
            redirects.reload();
            refreshStats();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Güncellenemedi.');
        }

        return;
    }

    if (! remove) {
        return;
    }

    if (! await confirm('Bu yönlendirme silinsin mi?', { title: 'Yönlendirmeyi sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/redirect/${remove.dataset.delete}`);
        toast.success(message);
        redirects.reload();
        refreshStats();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

document.getElementById('redirect-create')?.addEventListener('click', () => openForm());

async function openForm(id = null, query = '') {
    await modal.open(`/admin/redirect/form/${id ?? ''}${query}`, {
        title: id ? 'Yönlendirmeyi Düzenle' : 'Yeni Yönlendirme',
    });
}

modal.onSubmit(async (form) => {
    const id = form.dataset.id;
    const body = new FormData(form);

    const { message } = id
        ? await http.put(`/admin/redirect/${id}`, body)
        : await http.post('/admin/redirect', body);

    toast.success(message);
    modal.close();
    redirects.reload();
    notFound.reload();
    refreshStats();
});

/* Canlı zincir/döngü uyarısı — modal içeriği geldikçe bağlanır. */
document.getElementById('ajax-modal').addEventListener('admin:content-loaded', () => {
    const form = document.getElementById('redirect-form');

    if (! form) {
        return;
    }

    const matchHint = form.querySelector('[data-match-hint]');
    const warning = form.querySelector('[data-chain-warning]');
    const typeSelect = form.querySelector('[name="match_type"]');

    const renderHint = () => (matchHint.textContent = MATCH_HINTS[typeSelect.value] ?? '');
    renderHint();
    typeSelect.addEventListener('change', renderHint);

    let timer;
    const analyze = async () => {
        const from = form.querySelector('[name="from_path"]').value.trim();
        const to = form.querySelector('[name="to_url"]').value.trim();

        if (! from || ! to || typeSelect.value === 'regex') {
            warning.classList.add('hidden');

            return;
        }

        try {
            const params = { from, to };

            if (form.dataset.id) {
                params.ignore = form.dataset.id;
            }

            const { data } = await http.get('/admin/redirect/analyze', params);

            if (data.loop) {
                warning.className = 'mb-[20px] md:mb-[25px] p-[12px] rounded-md text-xs border border-danger-500 bg-danger-100 text-danger-600';
                warning.textContent = 'Bu hedef bir yönlendirme döngüsü oluşturur — kaydedilemez.';
            } else if (data.chain.length) {
                warning.className = 'mb-[20px] md:mb-[25px] p-[12px] rounded-md text-xs border border-warning-500 bg-warning-100 text-warning-700';
                warning.textContent = `Bu hedef başka bir yönlendirmeye zincirleniyor: /${data.chain.join(' → /')}. Doğrudan son adrese işaret etmek daha hızlıdır.`;
            } else {
                warning.classList.add('hidden');
            }
        } catch {
            warning.classList.add('hidden');
        }
    };

    ['from_path', 'to_url'].forEach((name) => {
        form.querySelector(`[name="${name}"]`).addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(analyze, 350);
        });
    });
});

/* ---------------------------------------------------------------------- *
 | CSV içe aktarma
 * ---------------------------------------------------------------------- */

const importButton = document.getElementById('redirect-import');
const importFile = document.getElementById('redirect-import-file');
const importModal = document.querySelector('[data-import-result-modal]');

importButton?.addEventListener('click', () => importFile.click());

importModal?.addEventListener('click', (event) => {
    if (event.target === importModal || event.target.closest('[data-modal-close]')) {
        importModal.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    }
});

importFile?.addEventListener('change', async () => {
    if (! importFile.files.length) {
        return;
    }

    const body = new FormData();
    body.append('file', importFile.files[0]);
    importFile.value = '';

    try {
        const { message, data } = await http.post('/admin/redirect/import', body);
        toast.success(message);

        importModal.querySelector('[data-import-created]').textContent = data.created;
        importModal.querySelector('[data-import-updated]').textContent = data.updated;
        importModal.querySelector('[data-import-skipped]').textContent = data.skipped;

        const errorBox = importModal.querySelector('[data-import-errors]');
        const list = errorBox.querySelector('ul');
        list.innerHTML = data.errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('');
        errorBox.classList.toggle('hidden', data.errors.length === 0);

        importModal.classList.add('active');
        document.body.classList.add('overflow-hidden');

        redirects.reload();
        refreshStats();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Dosya işlenemedi.');
    }
});

/* ---------------------------------------------------------------------- *
 | 404 kayıtları tablosu
 * ---------------------------------------------------------------------- */

const refererCell = (item) => item.last_referer
    ? `<span class="text-xs text-gray-500 dark:text-gray-400 block truncate max-w-[240px]" title="${escapeHtml(item.last_referer)}">${escapeHtml(item.last_referer)}</span>`
    : '<span class="text-xs text-gray-400">—</span>';

const notFound = new DataTable({
    endpoint: '/admin/not-found/datatable',
    body: document.getElementById('nf-table-body'),
    search: document.getElementById('nf-search'),
    filters: { include_resolved: document.getElementById('nf-resolved') },
    sort: 'last_seen_at',
    direction: 'desc',
    empty: 'Kayıtlı 404 yok.',
    row: (item) => `<tr data-id="${item.id}" class="${item.resolved ? 'opacity-50' : ''}">
        ${cell(`<div class="min-w-0">
            <span class="font-medium block truncate max-w-[320px]">/${escapeHtml(item.path)}</span>
            ${item.resolved ? '<span class="text-[10px] text-success-600">çözüldü</span>' : ''}
        </div>`)}
        ${cell(`<span class="font-medium">${item.hits}</span>`)}
        ${cell(refererCell(item))}
        ${cell(escapeHtml(item.last_seen_at ?? '—'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${item.resolved ? '' : `<button type="button" data-redirect-from="${item.id}" data-path="${escapeHtml(item.path)}" title="Yönlendirme oluştur"
                class="inline-flex items-center gap-[4px] text-xs text-primary-500 py-[5px] px-[10px] rounded-md border border-primary-200 hover:bg-primary-50 transition-all">
                <i class="material-symbols-outlined !text-[15px]">add_link</i> Yönlendir
            </button>`}
            <button type="button" data-nf-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('nf-table-body').addEventListener('click', async (event) => {
    const makeRedirect = event.target.closest('[data-redirect-from]');
    const remove = event.target.closest('[data-nf-delete]');

    if (makeRedirect) {
        return openForm(null, `?from=/${encodeURIComponent(makeRedirect.dataset.path)}&not_found=${makeRedirect.dataset.redirectFrom}`);
    }

    if (! remove) {
        return;
    }

    if (! await confirm('Bu 404 kaydı silinsin mi?', { title: 'Kaydı sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/not-found/${remove.dataset.nfDelete}`);
        toast.success(message);
        notFound.reload();
        refreshStats();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

/* ---------------------------------------------------------------------- *
 | Özet kartlar
 * ---------------------------------------------------------------------- */

async function refreshStats() {
    try {
        const { data } = await http.get('/admin/redirect/stats');

        Object.entries(data).forEach(([key, value]) => {
            const el = document.querySelector(`[data-stat="${key}"]`);

            if (el) {
                el.textContent = new Intl.NumberFormat('tr-TR').format(value);
            }
        });
    } catch {
        // Özet güncellenemezse tablo yine tazelenmiş olur; sessiz geç.
    }
}

redirects.load();
notFound.load();
