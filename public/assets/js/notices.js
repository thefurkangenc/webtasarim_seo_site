/*
 * Duyuru şeridi + açılır pencere + bülten formu.
 *
 * Kapatma tercihi tarayıcıda saklanır (localStorage), böylece ziyaretçi her
 * sayfada aynı duyuruyla karşılaşmaz. Açılış/kapanış geçişleri CSS'te; burada
 * yalnızca `is-open` sınıfı ve şerit yüksekliği yönetilir.
 */
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
            // gizli mod / kota — duyuru bu oturumda kapanır, kalıcı olmaz
        }
    };

    /* ------------------------------------------------------- duyuru şeridi */

    const bar = document.querySelector('[data-site-bar]');

    if (bar && ! dismissed('bar', bar.dataset.noticeId)) {
        // Yükseklik CSS'e değişkenle verilir: sayfa ve header tam o kadar
        // aşağı iter, metin iki satıra taşsa bile çakışma olmaz.
        const applyHeight = () => {
            document.documentElement.style.setProperty('--site-bar-h', `${bar.offsetHeight}px`);
        };

        bar.hidden = false;
        applyHeight();
        document.documentElement.classList.add('has-site-bar');

        // Bir sonraki karede sınıf eklenirse CSS geçişi çalışır.
        requestAnimationFrame(() => bar.classList.add('is-open'));

        window.addEventListener('resize', applyHeight);

        bar.querySelector('[data-notice-close]')?.addEventListener('click', () => {
            bar.classList.remove('is-open');
            document.documentElement.classList.remove('has-site-bar');
            document.documentElement.style.setProperty('--site-bar-h', '0px');
            dismiss('bar', bar.dataset.noticeId);

            // Geçiş bitince DOM'dan kaldır.
            window.setTimeout(() => { bar.hidden = true; }, 400);
        });
    }

    /* ------------------------------------------------------ açılır pencere */

    const popup = document.querySelector('[data-site-popup]');

    const closePopup = () => {
        if (! popup) {
            return;
        }

        popup.classList.remove('is-open');
        dismiss('popup', popup.dataset.noticeId);
        document.body.style.removeProperty('overflow');

        window.setTimeout(() => { popup.hidden = true; }, 350);
    };

    if (popup && ! dismissed('popup', popup.dataset.noticeId)) {
        const delay = Number(popup.dataset.delay || 0) * 1000;

        window.setTimeout(() => {
            popup.hidden = false;
            // Arka plan kaymasın; pencere kapanınca geri açılır.
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => popup.classList.add('is-open'));
        }, Number.isFinite(delay) ? delay : 0);

        popup.querySelectorAll('[data-notice-close]').forEach((button) => {
            button.addEventListener('click', closePopup);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! popup.hidden) {
                closePopup();
            }
        });
    }

    /* --------------------------------------------------------- bülten formu */

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('[data-subscribe-form]').forEach((form) => {
        const alertBox = form.querySelector('[data-subscribe-alert]');
        const submit = form.querySelector('[type="submit"]');
        const submitLabel = submit?.textContent;

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

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            form.querySelectorAll('[data-error]').forEach((el) => {
                el.hidden = true;
                el.textContent = '';
            });

            paint(null);

            if (submit) {
                submit.disabled = true;
                submit.textContent = 'Gönderiliyor…';
            }

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

                // Pencere içindeki formda kayıt tamamlanınca pencere kapanır.
                if (popup && form.closest('[data-site-popup]')) {
                    window.setTimeout(closePopup, 1600);
                }
            } catch {
                paint('Kayıt alınamadı. Lütfen tekrar deneyin.', false);
            } finally {
                if (submit) {
                    submit.disabled = false;
                    submit.textContent = submitLabel;
                }
            }
        });
    });
})();
