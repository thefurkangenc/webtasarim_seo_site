/**
 * Analitik (GA4) ayar sekmesi: service account JSON yükleme + property ID kaydı
 * ve "Bağlantıyı test et".
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('analytics-form');
const testButton = document.getElementById('analytics-test');

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = form.querySelector('[type=submit]');
    clearErrors(form);
    setLoading(button, true);

    try {
        const { message } = await http.post(form.action, new FormData(form));
        toast.success(message);
        // Bağlantı durumu kartını ve "test" butonunu tazelemek için yeniden yükle.
        setTimeout(() => window.location.reload(), 600);
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(form, error.errors);
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        }
    } finally {
        setLoading(button, false);
    }
});

testButton?.addEventListener('click', async () => {
    setLoading(testButton, true);

    try {
        const { message } = await http.post('/admin/analytics/test');
        toast.success(message);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Bağlantı testi başarısız.');
    } finally {
        setLoading(testButton, false);
    }
});
