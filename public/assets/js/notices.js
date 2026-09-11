(() => {
    const storageKey = (kind, id) => `notice:${kind}:${id}`;

    const dismissed = (kind, id) => {
        try {
            return localStorage.getItem(storageKey(kind, id)) === '1';
        } catch {
            return false;
        }
    };

    const dismiss = (kind, id) => {
        try {
            localStorage.setItem(storageKey(kind, id), '1');
        } catch {
            // gizli mod / kota
        }
    };

    const bar = document.querySelector('[data-site-bar]');

    if (bar && ! dismissed('bar', bar.dataset.noticeId)) {
        bar.hidden = false;
        bar.querySelector('[data-notice-close]')?.addEventListener('click', () => {
            bar.hidden = true;
            dismiss('bar', bar.dataset.noticeId);
        });
    }

    const popup = document.querySelector('[data-site-popup]');

    if (popup && ! dismissed('popup', popup.dataset.noticeId)) {
        const delay = Number(popup.dataset.delay || 0) * 1000;

        window.setTimeout(() => {
            popup.hidden = false;
        }, Number.isFinite(delay) ? delay : 0);

        popup.querySelectorAll('[data-notice-close]').forEach((button) => {
            button.addEventListener('click', () => {
                popup.hidden = true;
                dismiss('popup', popup.dataset.noticeId);
            });
        });
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('[data-subscribe-form]').forEach((form) => {
        const alertBox = form.querySelector('[data-subscribe-alert]');
        const submit = form.querySelector('[type="submit"]');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            form.querySelectorAll('[data-error]').forEach((el) => {
                el.hidden = true;
                el.textContent = '';
            });
            paint(null);
            if (submit) submit.disabled = true;

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
                    Object.entries(payload.errors).forEach(([name, messages]) => {
                        const el = form.querySelector(`[data-error="${name}"]`);
                        if (el) {
                            el.textContent = Array.isArray(messages) ? (messages[0] ?? '') : String(messages);
                            el.hidden = false;
                        }
                    });
                    paint(payload.message || 'Girilen bilgileri kontrol edin.', false);
                    return;
                }

                if (! response.ok) {
                    paint(payload.message || 'Kayıt alınamadı. Lütfen tekrar deneyin.', false);
                    return;
                }

                form.reset();
                paint(payload.message || 'Aboneliğiniz alındı.', true);

                if (popup && form.closest('[data-site-popup]')) {
                    window.setTimeout(() => {
                        popup.hidden = true;
                        dismiss('popup', popup.dataset.noticeId);
                    }, 1200);
                }
            } catch {
                paint('Kayıt alınamadı. Lütfen tekrar deneyin.', false);
            } finally {
                if (submit) submit.disabled = false;
            }
        });

        function paint(message, success) {
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
    });
})();
