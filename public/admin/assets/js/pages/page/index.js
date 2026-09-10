/**
 * Sayfalar listesi.
 *
 * Liste varsayılan olarak `path` sırasındadır; yol alfabetik sıralaması aynı
 * zamanda ağaç sırası olduğu için satırlar derinliklerine göre girintilenerek
 * site yapısını olduğu gibi gösterir.
 */

import { historyButton } from '../../core/activity-log.js';
import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable, reorderHandle } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const BADGES = {
    published: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    draft: 'bg-warning-100 dark:bg-[#15203c] text-warning-600 dark:text-warning-500',
    scheduled: 'bg-info-100 dark:bg-[#15203c] text-info-600 dark:text-info-500',
};

const parentFilter = document.getElementById('page-parent');
const reorderButton = document.getElementById('page-reorder');

/*
 * Sıralama kardeşler arasında tekildir: bir alt sayfayı kök sayfaların arasına
 * sürüklemek kaydı taşımaz, yalnızca anlamsız bir sıra numarası yazar. Bu
 * yüzden sıralama modu bir seviye seçilmeden açılmaz.
 *
 * Dinleyici DataTable'dan ÖNCE bağlanıyor: aynı elemandaki dinleyiciler kayıt
 * sırasına göre çalışır, dolayısıyla stopImmediatePropagation core/table.js'in
 * kendi dinleyicisine ulaşmasını engelleyebiliyor. Mod açıkken aynı buton onu
 * kapatır — kontrol yalnızca açılışta gerekli.
 */
let reordering = false;

reorderButton?.addEventListener('click', (event) => {
    if (reordering) {
        reordering = false;

        return;
    }

    if (parentFilter && parentFilter.value === '') {
        event.stopImmediatePropagation();
        toast.error('Sıralama bir seviye içinde yapılır. Önce “Yalnızca kök sayfalar” ya da bir üst sayfa seçin.');

        return;
    }

    reordering = true;
});

const badge = (label, classes, title = '') =>
    `<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs ${classes}"${title ? ` title="${escapeHtml(title)}"` : ''}>${escapeHtml(label)}</span>`;

/** Yayında ama yayın tarihi gelmemiş sayfa kullanıcıya "Zamanlandı" görünür. */
const statusBadge = (item) => item.status === 'published' && ! item.is_visible
    ? badge('Zamanlandı', BADGES.scheduled, `${item.published_at} tarihinde yayınlanacak`)
    : badge(item.status_label, BADGES[item.status] ?? BADGES.draft);

const titleCell = (item) => `<div class="flex items-start gap-[8px]" style="padding-inline-start:${item.depth * 20}px">
    ${item.depth > 0 ? '<i class="ri-corner-down-right-line !text-[15px] text-gray-400 dark:text-gray-600 mt-[3px] shrink-0"></i>' : ''}
    <div class="min-w-0">
        <span class="font-medium block truncate max-w-[300px]">${escapeHtml(item.title)}</span>
        <span class="text-xs text-gray-500 dark:text-gray-400 break-all">/${escapeHtml(item.path)}</span>
    </div>
</div>`;

const table = new DataTable({
    endpoint: '/admin/page/datatable',
    body: document.getElementById('page-table-body'),
    search: document.getElementById('page-search'),
    filters: {
        status: document.getElementById('page-status'),
        template: document.getElementById('page-template'),
        parent_id: parentFilter,
    },
    sort: 'path',
    direction: 'asc',
    perPage: 20,
    empty: 'Henüz sayfa eklenmedi.',
    reorder: {
        button: reorderButton,
        endpoint: '/admin/page/reorder',
        // Sıra seviye içinde tekil; yalnızca seviye filtresi taşınır.
        withFilters: ['parent_id'],
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(titleCell(item))}
        ${cell(`<span class="text-sm">${escapeHtml(item.template_label)}</span>`)}
        ${cell(item.children_count || '—')}
        ${cell(statusBadge(item))}
        ${cell(escapeHtml(item.created_at ?? '—'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            <a href="${escapeHtml(item.url)}" target="_blank" rel="noopener" title="Sitede görüntüle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">open_in_new</i>
            </a>
            ${historyButton('App\\Models\\Page\\Page', item.id)}
            <a href="/admin/page/${item.id}/edit" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </a>
            <button type="button" data-delete="${item.id}" data-children="${item.children_count}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('page-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    // Alt sayfalar silinmez, bir üst seviyeye çıkar — kullanıcı bunu onaydan
    // önce bilmeli, yoksa bir bölümü sildiğini sanır.
    const children = Number(remove.dataset.children || 0);
    const message = children > 0
        ? `Bu sayfa silinsin mi? Altındaki ${children} sayfa silinmez, bir üst seviyeye taşınır ve adresleri değişir.`
        : 'Bu sayfa silinsin mi? Üst görseli kütüphanede kalır.';

    if (! await confirm(message, { title: 'Sayfayı sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message: response } = await http.delete(`/admin/page/${remove.dataset.delete}`);
        toast.success(response);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
