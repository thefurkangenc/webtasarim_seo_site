/**
 * Site ayarları — entegrasyon kartları.
 * Anahtar açılınca ajax modal ile bilgiler istenir; kayıt edilmeden aktif olmaz.
 */

import { http, HttpError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { toast } from '../../core/toast.js';

const ENDPOINT = '/admin/integration';
const grid = document.getElementById('integration-grid');
const canUpdate = grid?.dataset.canUpdate === '1';
const modal = new AjaxModal();

let pendingKey = null;

const originalClose = modal.close.bind(modal);

modal.close = () => {
    originalClose();

    if (! pendingKey) {
        return;
    }

    const toggle = grid?.querySelector(`[data-integration="${pendingKey}"] [data-integration-toggle]`);

    if (toggle) {
        toggle.checked = false;
    }

    pendingKey = null;
};

function applyCard(data) {
    const card = grid?.querySelector(`[data-integration="${data.key}"]`);

    if (! card) {
        return;
    }

    const toggle = card.querySelector('[data-integration-toggle]');
    const badge = card.querySelector('[data-integration-badge]');
    const warning = card.querySelector('[data-integration-warning]');
    const editButton = card.querySelector('[data-integration-edit]');

    // Üç durum: yayında (açık + bilgiler tam), bilgi eksik, kapalı.
    const live = Boolean(data.enabled) && Boolean(data.ready);
    const incomplete = Boolean(data.enabled) && ! data.ready;

    if (toggle) {
        toggle.checked = Boolean(data.enabled);
    }

    if (badge) {
        const tone = live
            ? 'bg-success-100 dark:bg-[#15203c] text-success-600'
            : incomplete
                ? 'bg-warning-100 dark:bg-[#15203c] text-warning-600'
                : 'bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400';

        badge.className = `px-[8px] py-[2px] inline-block rounded-sm font-medium text-[10px] ${tone}`;
        badge.textContent = live ? 'Yayında' : incomplete ? 'Bilgi eksik' : 'Kapalı';
    }

    warning?.classList.toggle('hidden', ! incomplete);

    if (editButton) {
        editButton.lastChild.textContent = data.ready ? ' Ayarları düzenle' : ' Ayarla';
    }

    // Kartın kenarlığı da durumu yansıtır.
    card.className = card.className.replace(
        /border-(success-200|warning-300|gray-100)/,
        live ? 'border-success-200' : incomplete ? 'border-warning-300' : 'border-gray-100',
    );
}

async function openModal(key, title) {
    await modal.open(`${ENDPOINT}/${key}/form`, {
        title: `${title} entegrasyonu`,
    });
}

grid?.addEventListener('change', async (event) => {
    const toggle = event.target.closest('[data-integration-toggle]');

    if (! toggle || ! canUpdate) {
        return;
    }

    const card = toggle.closest('[data-integration]');
    const key = card?.dataset.integration;

    if (! key) {
        return;
    }

    if (toggle.checked) {
        pendingKey = key;
        await openModal(key, card.dataset.title ?? '');

        return;
    }

    try {
        const { message, data } = await http.put(`${ENDPOINT}/${key}/toggle`, { enabled: false });
        toast.success(message);
        applyCard(data);
    } catch (error) {
        toggle.checked = true;
        toast.error(error instanceof HttpError ? error.message : 'Kapatılamadı.');
    }
});

grid?.addEventListener('click', (event) => {
    const edit = event.target.closest('[data-integration-edit]');

    if (! edit || ! canUpdate) {
        return;
    }

    const card = edit.closest('[data-integration]');
    const key = card?.dataset.integration;

    if (key) {
        openModal(key, card.dataset.title ?? '');
    }
});

modal.onSubmit(async (form) => {
    const key = form.dataset.key;
    const { message, data } = await http.put(`${ENDPOINT}/${key}`, new FormData(form));

    pendingKey = null;
    toast.success(message);
    applyCard(data);
    modal.close();
});
