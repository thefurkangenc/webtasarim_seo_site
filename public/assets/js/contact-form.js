(() => {
    const form = document.querySelector('[data-contact-form]');

    if (! form) {
        return;
    }

    const alertBox = form.querySelector('[data-contact-alert]');
    const submit = form.querySelector('[data-contact-submit]');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();
        paintAlert(null);
        setBusy(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ?? '',
                },
                credentials: 'same-origin',
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.status === 422 && payload.errors) {
                showErrors(payload.errors);
                paintAlert(payload.message || 'Girilen bilgileri kontrol edin.', false);
                return;
            }

            if (! response.ok) {
                paintAlert(payload.message || 'Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.', false);
                return;
            }

            form.reset();
            paintAlert(payload.message || 'Mesajınız alındı.', true);
        } catch {
            paintAlert('Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.', false);
        } finally {
            setBusy(false);
        }
    });

    function setBusy(busy) {
        if (! submit) {
            return;
        }

        submit.disabled = busy;
        submit.classList.toggle('is-busy', busy);
    }

    function clearErrors() {
        form.querySelectorAll('[data-error]').forEach((el) => {
            el.hidden = true;
            el.textContent = '';
        });
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([name, messages]) => {
            const el = form.querySelector(`[data-error="${name}"]`);

            if (! el) {
                return;
            }

            el.textContent = Array.isArray(messages) ? (messages[0] ?? '') : String(messages);
            el.hidden = false;
        });
    }

    function paintAlert(message, success) {
        if (! alertBox) {
            return;
        }

        if (! message) {
            alertBox.hidden = true;
            alertBox.textContent = '';
            alertBox.classList.remove('is-success', 'is-error');
            return;
        }

        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.classList.toggle('is-success', success);
        alertBox.classList.toggle('is-error', ! success);
    }
})();
