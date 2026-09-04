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
    const edit = card.querySelector('[data-integration-edit-wrap]');

    if (toggle) {
        toggle.checked = Boolean(data.enabled);
    }

    if (badge) {
        badge.className = data.enabled
            ? 'px-[8px] py-[3px] inline-block rounded-sm font-medium text-xs bg-success-100 dark:bg-[#15203c] text-success-600'
            : 'px-[8px] py-[3px] inline-block rounded-sm font-medium text-xs bg-danger-100 dark:bg-[#15203c] text-danger-500';
        badge.textContent = data.enabled ? 'Aktif' : 'Pasif';
    }

    edit?.classList.toggle('hidden', ! data.enabled);
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
