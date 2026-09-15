/**
 * Kullanıcı formu.
 *
 * Gönderim AJAX ile yapılır: doğrulama hatası sayfayı yenilemesin.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError, adminUrl } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('user-form');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(adminUrl(`/user/${id}`), new FormData(form))
            : await http.post(adminUrl('/user'), new FormData(form));

        toast.success(message);

        if (data?.redirect) {
            window.location.href = data.redirect;
        }
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
