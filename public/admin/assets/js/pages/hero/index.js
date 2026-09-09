/**
 * Tanıtım alanı ekranı.
 *
 * Tekil kayıt: liste, modal ve silme yok — sayfada tek bir AJAX form var.
 * Galeri alanının kendi davranışı core/media-gallery.js'te, layout'ta yüklü.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('hero-form');

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message } = await http.put(form.action, new FormData(form));
        toast.success(message);
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
