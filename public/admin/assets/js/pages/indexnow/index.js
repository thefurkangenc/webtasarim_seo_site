/**
 * Hızlı İndeksleme (IndexNow) paneli — ayar kaydetme, anahtar yenileme,
 * elle adres bildirme ve "tüm adresleri bildir".
 *
 * Ayar formu POST + _method=PUT ile gider (FormData ile gerçek PUT gövdesi
 * PHP tarafında ayrıştırılmıyor) — sitemap/search-console ile aynı kalıp.
 */

import { confirm } from '../../core/confirm.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-indexnow]');

if (root) {
    const form = document.getElementById('indexnow-form');
    const urlsField = root.querySelector('[name=urls]');

    /* ---- ayarlar ---- */

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('[type=submit]');
        clearErrors(form);
        setLoading(button, true);

        try {
            const { message } = await http.post(form.action, new FormData(form));
            toast.success(message);
        } catch (error) {
            if (error instanceof ValidationError) {
                showErrors(form, error.errors);
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
            }
        } finally {
            setLoading(button, false);
        }
    });

    /* ---- anahtar yenileme ---- */

    document.getElementById('indexnow-new-key')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;

        const ok = await confirm(
            'Eski anahtar anında geçersiz olur. Arama motorları sonraki bildirimde yeni anahtarı doğrular; yapmanız gereken başka bir şey yok.',
            { title: 'Anahtar yenilensin mi?', accept: 'Yenile' },
        );

        if (! ok) return;

        setLoading(button, true);

        try {
            const { message, data } = await http.post(root.dataset.keyEndpoint);
            root.querySelector('[data-indexnow-key]').textContent = data.key;
            root.querySelector('[data-indexnow-key-url]').href = `${window.location.origin}/${data.key}.txt`;
            toast.success(message);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Anahtar yenilenemedi.');
        } finally {
            setLoading(button, false);
        }
    });

    /* ---- elle bildirim ---- */

    document.getElementById('indexnow-submit')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const errorSlot = root.querySelector('[data-error="urls"]');

        if (errorSlot) errorSlot.textContent = '';
        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.submit, { urls: urlsField.value });
            toast.success(message);
            urlsField.value = '';
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            if (error instanceof ValidationError && errorSlot) {
                errorSlot.textContent = error.errors.urls?.[0] ?? 'Adresleri kontrol edin.';
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Bildirilemedi.');
            }
        } finally {
            setLoading(button, false);
        }
    });

    document.getElementById('indexnow-submit-all')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;

        const ok = await confirm(
            'Site haritasındaki bütün adresler arama motorlarına bildirilecek. Bunu sık sık yapmanıza gerek yok — normalde içerik kaydedildikçe tek tek bildirilir.',
            { title: 'Tüm adresler bildirilsin mi?', accept: 'Gönder' },
        );

        if (! ok) return;

        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.submitAll);
            toast.success(message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Gönderilemedi.');
        } finally {
            setLoading(button, false);
        }
    });
}
