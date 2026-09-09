/**
 * Site ayarları — sosyal medya kartları.
 *
 * Liste AJAX ile basılır; ekleme/düzenleme ajax modal, sıra SortableJS ile
 * sürükle-bırak. core/table.js'e dokunulmaz — o tablo sıralaması içindir.
 */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { toast } from '../../core/toast.js';

const ENDPOINT = '/admin/social-link';
const grid = document.getElementById('social-link-grid');
const empty = document.getElementById('social-link-empty');
const canUpdate = grid?.dataset.canUpdate === '1';

const modal = new AjaxModal();
let sortable = null;

function iconUrl(item) {
    return item.icon?.medium || item.icon?.url || '';
}

function card(item) {
    const icon = iconUrl(item)
        ? `<img src="${escapeHtml(iconUrl(item))}" alt="" class="w-[40px] h-[40px] object-contain rounded-md">`
        : `<span class="settings-chip w-[40px] h-[40px] flex items-center justify-center text-gray-400"><i class="material-symbols-outlined !text-[22px]">share</i></span>`;

    const handle = canUpdate
        ? `<i data-reorder-handle class="material-symbols-outlined !text-[19px] text-gray-400 cursor-grab active:cursor-grabbing">drag_indicator</i>`
        : '';

    const actions = canUpdate
        ? `<div class="flex items-center gap-[9px] shrink-0">
            <button type="button" data-edit="${item.id}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-md">edit</i>
            </button>
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`
        : '';

    return `<div data-social-card data-id="${item.id}" class="settings-panel is-tight is-interactive">
        <div class="flex items-center gap-[15px]">
            ${handle}
            ${icon}
            <div class="grow min-w-0">
                <span class="block text-black dark:text-white font-semibold truncate">${escapeHtml(item.name)}</span>
                <span class="block mt-[3px] text-xs text-gray-500 dark:text-gray-400 truncate">${escapeHtml(item.url)}</span>
            </div>
            ${actions}
        </div>
    </div>`;
}

function render(items) {
    const hasItems = items.length > 0;

    empty.classList.toggle('hidden', hasItems);
    grid.classList.toggle('hidden', ! hasItems);
    grid.innerHTML = items.map(card).join('');

    if (hasItems && canUpdate) {
        enableSortable();
    } else {
        disableSortable();
    }
}

async function load() {
    const { data } = await http.get(ENDPOINT);
    render(data ?? []);
}

async function enableSortable() {
    disableSortable();

    const { default: Sortable } = await import('../../vendor/sortablejs/sortable.esm.js');

    sortable = Sortable.create(grid, {
        handle: '[data-reorder-handle]',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: (event) => {
            if (event.oldIndex === event.newIndex) {
                return;
            }

            saveOrder();
        },
    });
}

function disableSortable() {
    sortable?.destroy();
    sortable = null;
}

async function saveOrder() {
    const ids = [...grid.querySelectorAll('[data-social-card]')]
        .map((card) => Number(card.dataset.id))
        .filter(Boolean);

    if (ids.length === 0) {
        return;
    }

    try {
        const { message } = await http.put(`${ENDPOINT}/reorder`, { ids });
        toast.success(message);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Sıralama kaydedilemedi.');
        load();
    }
}

async function open(id = null) {
    await modal.open(`${ENDPOINT}/form/${id ?? ''}`, {
        title: id ? 'Sosyal Medyayı Düzenle' : 'Yeni Sosyal Medya',
    });
}

document.getElementById('social-link-create')?.addEventListener('click', () => open());

grid?.addEventListener('click', async (event) => {
    const edit = event.target.closest('[data-edit]');
    const remove = event.target.closest('[data-delete]');

    if (edit) {
        open(edit.dataset.edit);

        return;
    }

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu sosyal medya silinsin mi?', {
        title: 'Sosyal medyayı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(`${ENDPOINT}/${remove.dataset.delete}`);
        toast.success(message);
        await load();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

modal.onSubmit(async (form) => {
    const id = form.dataset.id;
    const body = new FormData(form);

    const { message } = id
        ? await http.put(`${ENDPOINT}/${id}`, body)
        : await http.post(ENDPOINT, body);

    toast.success(message);
    modal.close();
    await load();
});

if (grid) {
    load().catch((error) => {
        toast.error(error instanceof HttpError ? error.message : 'Liste yüklenemedi.');
    });
}
