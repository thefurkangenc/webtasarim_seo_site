/**
 * Hizmet bölgeleri ekranı — kırılımlı (drill-down) liste.
 *
 * Ağaç binlerce kayda çıkabildiği için tek düz tablo kullanılmaz: tabloda
 * yalnızca bulunulan seviyenin bölgeleri durur, bölgenin adına tıklanınca
 * içine inilir. Seviye, gizli `#region-parent` input'u üzerinden DataTable'a
 * filtre olarak geçer.
 *
 * Arama yapıldığında sunucu seviyeyi yok sayar ve ağacın tamamında arar —
 * o yüzden arama sırasında kırılım çubuğu uyarıya döner.
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const modal = new AjaxModal();

const searchInput = document.getElementById('region-search');
const parentInput = document.getElementById('region-parent');
const pathBar = document.getElementById('region-path');

/** Kökten bulunulan bölgeye kadarki zincir; kök için boş. */
let path = [];

const BADGES = {
    success: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    danger: 'bg-danger-100 dark:bg-[#15203c] text-danger-600 dark:text-danger-500',
};

const badge = (label, variant) =>
    `<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${BADGES[variant]}">${label}</span>`;

const searching = () => (searchInput?.value ?? '').trim() !== '';

const table = new DataTable({
    endpoint: '/admin/service-region/datatable',
    body: document.getElementById('region-table-body'),
    search: searchInput,
    filters: {
        parent_id: parentInput,
        is_active: document.getElementById('region-active'),
    },
    sort: 'sort_order',
    direction: 'asc',
    empty: 'Bu seviyede bölge yok.',
    reorder: {
        button: document.getElementById('region-reorder'),
        endpoint: '/admin/service-region/reorder',
        // Sıralama seviye içinde tekil; yalnızca seviye filtresi taşınır.
        // Durum filtresi taşınsaydı gizlenen kayıtların sırası bozulurdu.
        withFilters: ['parent_id'],
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(`<button type="button" data-open="${item.id}" class="flex items-center gap-[5px] font-medium transition-all hover:text-primary-500">
            ${escapeHtml(item.name)}
            <i class="material-symbols-outlined !text-[16px] text-gray-500 dark:text-gray-400">chevron_right</i>
        </button>
        ${item.has_description ? '<i class="material-symbols-outlined !text-[15px] text-success-500 align-[-2px]" title="Bölgeye özel metin var">edit_note</i>' : ''}
        ${item.depth > 0 ? `<span class="block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.path ?? '')}</span>` : ''}`)}
        ${cell(`<code class="text-xs">${escapeHtml(item.slug)}</code>`)}
        ${cell(item.children_count)}
        ${cell(item.services_count)}
        ${cell(item.is_active ? badge('aktif', 'success') : badge('pasif', 'danger'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\ServiceRegion\\ServiceRegion', item.id)}
            <button type="button" data-edit="${item.id}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </button>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

function renderPath() {
    if (searching()) {
        pathBar.innerHTML =
            '<span class="text-gray-500 dark:text-gray-400">Arama tüm bölgelerde yapılıyor — seviye göz ardı edildi.</span>';

        return;
    }

    const crumb = (label, id, isLast) => isLast
        ? `<span class="font-medium text-black dark:text-white">${escapeHtml(label)}</span>`
        : `<button type="button" data-crumb="${id ?? ''}" class="transition-all hover:text-primary-500">${escapeHtml(label)}</button>`;

    const separator = '<i class="material-symbols-outlined !text-[16px] text-gray-500 dark:text-gray-400">chevron_right</i>';

    pathBar.innerHTML = [crumb('Tüm İller', '', path.length === 0)]
        .concat(path.map((item, index) => crumb(item.name, item.id, index === path.length - 1)))
        .join(separator);
}

/** Seviye değiştirir; `region` null ise köke (iller) döner. */
async function goTo(regionId) {
    if (! regionId) {
        path = [];
    } else {
        try {
            const { data } = await http.get(`/admin/service-region/breadcrumb/${regionId}`);
            path = data ?? [];
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Bölge açılamadı.');

            return;
        }
    }

    // Arama açıkken sunucu seviye filtresini yok sayıp ağacın tamamında arar
    // (bkz. ServiceRegionService::list) — arama metni kalırsa tıklama hiçbir
    // şey değiştirmemiş gibi görünür (aynı arama sonucu döner). Bir sonuca
    // tıklanıp seviye değiştirildiğinde arama bu yüzden temizlenir.
    if (searchInput) {
        searchInput.value = '';
        table.state.search = '';
    }

    parentInput.value = regionId ?? '';
    renderPath();
    table.state.page = 1;
    // reload(): sıralama modu açıksa o görünümde kalır, yeni seviyeyi
    // sıralanabilir biçimde getirir.
    table.reload();
}

async function open(id = null) {
    const url = id
        ? `/admin/service-region/form/${id}`
        : `/admin/service-region/form?parent_id=${parentInput.value}`;

    await modal.open(url, {
        title: id ? 'Bölgeyi Düzenle' : 'Yeni Bölge',
        width: 'max-w-[600px]',
    });
}

// Arama kutusu kırılım çubuğunun anlamını değiştirir; etiketi hemen tazele.
searchInput?.addEventListener('input', () => renderPath());

pathBar.addEventListener('click', (event) => {
    const crumb = event.target.closest('[data-crumb]');

    if (crumb) {
        goTo(crumb.dataset.crumb || null);
    }
});

document.getElementById('region-create')?.addEventListener('click', () => open());

document.getElementById('region-table-body').addEventListener('click', async (event) => {
    const drill = event.target.closest('[data-open]');
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (drill) {
        goTo(drill.dataset.open);

        return;
    }

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu bölge silinsin mi?', {
        title: 'Bölgeyi sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/service-region/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

modal.onSubmit(async (form) => {
    const id = form.dataset.id;
    const body = new FormData(form);

    const { message } = id
        ? await http.put(`/admin/service-region/${id}`, body)
        : await http.post('/admin/service-region', body);

    toast.success(message);
    modal.close();
    table.reload();
});

renderPath();
table.load();
