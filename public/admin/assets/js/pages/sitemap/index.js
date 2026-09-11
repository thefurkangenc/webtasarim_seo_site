/**
 * Site Haritası: kaynak/hariç tutma ayarlarını kaydetme + "Şimdi Yeniden
 * Oluştur". Her iki işlem de kuyruğa bir GenerateSitemapJob atar; sonuç
 * hemen görünmez (queue:work çalışıyor olmalı), bu yüzden kaydetme sonrası
 * sayfa yeniden yüklenir ama üretim raporu bir iki saniye eski kalabilir.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('sitemap-form');
const generateButton = document.getElementById('sitemap-generate');

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = form.querySelector('[type=submit]');
    clearErrors(form);
    setLoading(button, true);

    try {
        const { message } = await http.post(form.action, new FormData(form));
        toast.success(message);
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

generateButton?.addEventListener('click', async () => {
    setLoading(generateButton, true);

    try {
        const { message } = await http.post(form.dataset.generateEndpoint);
        toast.success(message);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Kuyruğa alınamadı.');
    } finally {
        setLoading(generateButton, false);
    }
});
