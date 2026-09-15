/**
 * Site ayarları — e-posta sekmesi. SMTP şifrelemesine göre port önerir,
 * Test Bağlantısı ile sunucuya bağlanmayı dener.
 */

import { clearErrors, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const PORTS = { tls: '587', ssl: '465', none: '25' };

const form = document.getElementById('setting-form');
const encryption = form?.querySelector('[data-mail-encryption]');
const port = form?.querySelector('[data-mail-port]');
const testButton = form?.querySelector('[data-mail-test]');
const status = form?.querySelector('[data-mail-test-status]');
const icon = form?.querySelector('[data-mail-test-icon]');
const result = form?.querySelector('[data-mail-test-result]');

encryption?.addEventListener('change', () => {
    const next = PORTS[encryption.value];

    if (! next || ! port) {
        return;
    }

    if (port.value === '' || Object.values(PORTS).includes(port.value)) {
        port.value = next;
    }
});

testButton?.addEventListener('click', async () => {
    if (! form) {
        return;
    }

    clearErrors(form);
    paintStatus('pending', 'Sunucuya bağlanılıyor...');
    testButton.disabled = true;
    testButton.classList.add('opacity-60', 'pointer-events-none');
    testButton.dataset.originalHtml ??= testButton.innerHTML;
    testButton.innerHTML = '<span class="flex items-center justify-center gap-[5px]">'
        + '<i class="material-symbols-outlined animate-spin !text-[18px]">progress_activity</i>'
        + 'Deneniyor...</span>';

    const body = new FormData(form);
    body.delete('_method');

    try {
        const { message } = await http.post('/admin/setting/mail/test', body);
        paintStatus('success', message);
        toast.success(message);
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(form, error.errors);
            paintStatus('error', 'Girilen bilgileri kontrol edin.');
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            const message = error instanceof HttpError ? error.message : 'Bağlantı kurulamadı.';
            paintStatus('error', message);
            toast.error(message);
        }
    } finally {
        testButton.disabled = false;
        testButton.classList.remove('opacity-60', 'pointer-events-none');
        testButton.innerHTML = testButton.dataset.originalHtml ?? testButton.innerHTML;
    }
});

function paintStatus(state, message) {
    if (! status || ! icon || ! result) {
        return;
    }

    const styles = {
        pending: 'flex items-center gap-[8px] py-[1rem] px-[1rem] rounded-md mb-[16px] border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] text-gray-500 dark:text-gray-400',
        success: 'flex items-center gap-[8px] py-[1rem] px-[1rem] rounded-md mb-[16px] border border-success-200 dark:border-[#15203c] bg-success-50 dark:bg-[#15203c] text-success-500',
        error: 'flex items-center gap-[8px] py-[1rem] px-[1rem] rounded-md mb-[16px] border border-danger-200 dark:border-[#15203c] bg-danger-50 dark:bg-[#15203c] text-danger-500',
    };

    status.className = styles[state] ?? styles.pending;
    icon.textContent = state === 'success' ? 'check_circle' : state === 'error' ? 'error' : 'progress_activity';
    icon.classList.toggle('animate-spin', state === 'pending');
    result.textContent = message;
}
