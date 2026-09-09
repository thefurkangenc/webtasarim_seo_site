/** Rol formu: kayıt ve kategori bazlı tümünü seç. */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('role-form');

form.querySelectorAll('[data-permission-group]').forEach((group) => {
    const master = group.querySelector('[data-select-all]');
    const boxes = [...group.querySelectorAll('[data-permission]')];

    if (! master || boxes.length === 0) {
        return;
    }

    const syncMaster = () => {
        master.checked = boxes.every((box) => box.checked);
    };

    master.addEventListener('change', () => {
        boxes.forEach((box) => {
            box.checked = master.checked;
        });
    });

    boxes.forEach((box) => box.addEventListener('change', syncMaster));
    syncMaster();
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(`/admin/role/${id}`, new FormData(form))
            : await http.post('/admin/role', new FormData(form));

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
