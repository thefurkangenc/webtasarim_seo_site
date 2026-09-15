/**
 * Profil ekranı — iki bağımsız form.
 *
 * Kimlik bilgileri kaydedilince header'daki ad ve avatar SAYFA YENİLENMEDEN
 * güncellenir; kullanıcı değişikliğin işlediğini görmek için F5'e basmak
 * zorunda kalmasın.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

bind(document.getElementById('profile-form'), '/admin/profile', (data) => {
    document.querySelectorAll('[data-user-name]').forEach((node) => {
        node.textContent = data.name;
    });

    document.querySelectorAll('[data-user-avatar]').forEach((node) => {
        if (data.avatar) {
            node.innerHTML = `<img src="${data.avatar}" alt="" class="w-full h-full rounded-full object-cover">`;

            return;
        }

        node.textContent = data.initials;
    });
});

bind(document.getElementById('password-form'), '/admin/profile/password', (_data, form) => {
    // Şifre alanları başarıda temizlenir; tarayıcıda açık kalmasın.
    form.reset();
});

function bind(form, endpoint, onSuccess) {
    if (! form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('[type=submit]');

        clearErrors(form);
        setLoading(button, true);

        try {
            const { message, data } = await http.put(endpoint, new FormData(form));
            toast.success(message);
            onSuccess(data ?? {}, form);
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
}
