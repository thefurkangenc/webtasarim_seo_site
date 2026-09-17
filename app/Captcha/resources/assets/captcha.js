/*
 * Captcha bileşeninin istemci tarafı. Bağımlılığı yoktur (jQuery de dahil),
 * bu yüzden ön yüzde de panelde de aynı dosya çalışır.
 *
 * Google'ın "Ben robot değilim" kutusuyla aynı kalıp: küçük bir onay kutusu
 * formun akışında durur, tıklanınca bulmaca AYRI bir popup'ta açılır. Bulmaca
 * yalnızca popup açıldığında istenir — sayfa yüklenirken hiçbir şey üretilmez,
 * "hazırlanıyor" metni de yalnızca popup içinde ve kısaca görünür.
 *
 * Popup paneli <body>'ye taşınır (bkz. constructor): form bir açılır
 * pencerenin (bülten popup'ı gibi) içinde olsa bile üst katman o kapsayıcı
 * tarafından kırpılmaz/gizlenmez. Onay kutusu ve gizli alan formda kalır,
 * yalnızca görsel panel taşınır.
 */
(() => {
    'use strict';

    const widgets = new Set();
    let lockCount = 0;

    const csrf = (form) => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || form?.querySelector('input[name="_token"]')?.value
        || '';

    // Birden fazla widget (örn. iletişim + bülten formu aynı sayfada) aynı
    // anda açık olabilir; kilit yalnızca hiçbiri açık kalmayınca kalkar.
    function lockScroll(lock) {
        lockCount = Math.max(0, lockCount + (lock ? 1 : -1));
        document.documentElement.classList.toggle('cap-lock', lockCount > 0);
    }

    class Captcha {
        constructor(root) {
            this.root = root;
            this.form = root.closest('form');
            this.el = {
                input: root.querySelector('[data-captcha-input]'),
                error: root.querySelector('.cap-error'),
                toggle: root.querySelector('[data-captcha-toggle]'),
                modal: root.querySelector('[data-captcha-modal]'),
                backdrop: root.querySelector('[data-captcha-backdrop]'),
                close: root.querySelector('[data-captcha-close]'),
                refresh: root.querySelector('[data-captcha-refresh]'),
                stage: root.querySelector('[data-captcha-stage]'),
                bg: root.querySelector('[data-captcha-bg]'),
                piece: root.querySelector('[data-captcha-piece]'),
                veil: root.querySelector('[data-captcha-veil-text]'),
                slider: root.querySelector('[data-captcha-slider]'),
                fill: root.querySelector('[data-captcha-fill]'),
                hint: root.querySelector('[data-captcha-hint]'),
                handle: root.querySelector('[data-captcha-handle]'),
                status: root.querySelector('[data-captcha-status]'),
            };

            this.challenge = null;
            this.loaded = false;
            this.offset = 0;
            this.ratio = 0;
            this.moves = 0;
            this.startedAt = 0;
            this.challengeTimer = null;
            this.closeTimer = null;
            this.ticketTimer = null;

            if (this.el.modal) {
                document.body.appendChild(this.el.modal);
            }

            this.state('idle');
            this.bind();
        }

        bind() {
            this.el.toggle?.addEventListener('click', () => this.open());
            this.el.close?.addEventListener('click', () => this.close());
            this.el.backdrop?.addEventListener('click', () => this.close());
            this.el.refresh?.addEventListener('click', () => this.load());
            this.el.handle?.addEventListener('pointerdown', (event) => this.grab(event));
            this.el.handle?.addEventListener('keydown', (event) => this.key(event));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && this.isOpen()) {
                    this.close();
                }
            });

            // Başarılı gönderimden sonra form.reset() çağrılıyor; bilet
            // yandığı için bileşen de baştan kurulmalı.
            this.form?.addEventListener('reset', () => window.setTimeout(() => this.reset(), 0));

            window.addEventListener('resize', () => this.place(this.ratio * this.travel()));
        }

        isOpen() {
            return !! this.el.modal && ! this.el.modal.hidden;
        }

        open() {
            if (! this.el.modal) {
                return;
            }

            this.el.modal.hidden = false;
            this.el.toggle?.setAttribute('aria-expanded', 'true');
            lockScroll(true);

            if (! this.loaded || ['failed', 'error'].includes(this.root.dataset.state)) {
                this.load();
            }

            window.setTimeout(() => this.el.handle?.focus(), 50);
        }

        close() {
            if (! this.el.modal || this.el.modal.hidden) {
                return;
            }

            // Yarım bırakılan bir sürükleme varsa panel bir dahaki açılışta
            // temiz görünsün; aynı bulmaca korunur, sunucuya tekrar gidilmez.
            if (['dragging', 'failed'].includes(this.root.dataset.state)) {
                this.place(0);
                this.state('ready');
            }

            this.el.modal.hidden = true;
            this.el.toggle?.setAttribute('aria-expanded', 'false');
            lockScroll(false);
            this.el.toggle?.focus();
        }

        async load() {
            this.loaded = true;
            this.stop();
            this.state('loading');
            this.say('');
            this.warn('');
            this.value('');
            this.place(0);
            this.el.veil.textContent = 'Hazırlanıyor…';

            try {
                const response = await fetch(this.root.dataset.captchaChallenge, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (! response.ok || ! payload.data) {
                    throw new Error('challenge');
                }

                this.apply(payload.data);
            } catch {
                this.state('error');
                this.el.veil.textContent = 'Doğrulama yüklenemedi.';
                this.say('Bağlantı kurulamadı. Yenile düğmesine basın.', 'error');
            }
        }

        apply(data) {
            this.challenge = data;

            this.el.stage.style.aspectRatio = `${data.width} / ${data.height}`;
            this.el.bg.src = data.background;
            this.el.piece.src = data.piece;
            this.el.piece.style.width = `${(data.size / data.width) * 100}%`;
            this.el.piece.style.top = `${(data.top / data.height) * 100}%`;

            this.moves = 0;
            this.startedAt = 0;
            this.el.hint.textContent = 'Parçayı yerine kaydırın';
            this.state('ready');

            // Bulmacanın sunucudaki ömrü dolmadan kendini tazele.
            this.challengeTimer = window.setTimeout(
                () => this.load(),
                Math.max(10, (data.expires_in || 300) - 10) * 1000,
            );
        }

        grab(event) {
            if (! this.movable()) {
                return;
            }

            event.preventDefault();
            this.begin();

            // Yakalama bazı tarayıcılarda reddedilebiliyor; olmadan da
            // sürükleme çalışsın diye hata yutulur.
            try {
                this.el.handle.setPointerCapture(event.pointerId);
            } catch { /* yoksay */ }

            this.origin = event.clientX - this.offset;

            const move = (moved) => {
                this.moves++;
                this.place(moved.clientX - this.origin);
            };

            const drop = () => {
                this.el.handle.removeEventListener('pointermove', move);
                this.el.handle.removeEventListener('pointerup', drop);
                this.el.handle.removeEventListener('pointercancel', drop);
                this.check();
            };

            this.el.handle.addEventListener('pointermove', move);
            this.el.handle.addEventListener('pointerup', drop);
            this.el.handle.addEventListener('pointercancel', drop);
        }

        /** Klavyeyle de çözülebilir: oklar kaydırır, Enter onaylar. */
        key(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.check();

                return;
            }

            const step = { ArrowLeft: -1, ArrowRight: 1, Home: -9999, End: 9999 }[event.key];

            if (step === undefined || ! this.movable()) {
                return;
            }

            event.preventDefault();
            this.begin();
            this.moves++;
            this.place(this.offset + step * (event.shiftKey ? 10 : 1));
            this.el.hint.textContent = 'Onaylamak için Enter';
        }

        movable() {
            return ['ready', 'failed', 'dragging'].includes(this.root.dataset.state);
        }

        begin() {
            if (this.root.dataset.state !== 'dragging') {
                this.startedAt = Date.now();
                this.moves = 0;
                this.state('dragging');
            }
        }

        place(px) {
            const max = this.travel();

            this.offset = Math.min(Math.max(px, 0), max);
            this.ratio = max > 0 ? this.offset / max : 0;

            this.el.handle.style.transform = `translateX(${this.offset}px)`;
            this.el.fill.style.width = `${this.offset + this.el.handle.offsetWidth}px`;
            this.el.piece.style.transform = `translateX(${this.ratio * this.pieceTravel()}px)`;
            this.el.handle.setAttribute('aria-valuenow', String(Math.round(this.ratio * 100)));
        }

        travel() {
            return Math.max(0, this.el.slider.clientWidth - this.el.handle.offsetWidth);
        }

        pieceTravel() {
            return Math.max(0, this.el.stage.clientWidth - this.el.piece.offsetWidth);
        }

        async check() {
            if (this.root.dataset.state !== 'dragging') {
                return;
            }

            this.state('checking');
            this.say('Kontrol ediliyor…');

            // Cevap görsel piksel cinsinden gönderilir: kutu ekrana göre
            // küçülse de sunucudaki koordinatla aynı ölçekte kalsın diye.
            const scale = this.challenge.width / Math.max(1, this.el.stage.clientWidth);

            try {
                const response = await fetch(this.root.dataset.captchaVerify, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf(this.form),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        token: this.challenge.token,
                        answer: this.ratio * this.pieceTravel() * scale,
                        duration: Date.now() - this.startedAt,
                        moves: this.moves,
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (response.ok && payload.ticket) {
                    this.pass(payload);

                    return;
                }

                this.fail(payload.message);
            } catch {
                this.fail();
            }
        }

        pass(payload) {
            window.clearTimeout(this.challengeTimer);
            this.value(payload.ticket);
            this.warn('');
            this.state('solved');
            this.say('Doğrulandı.', 'ok');
            this.el.hint.textContent = 'Doğrulama tamam';
            this.el.handle.setAttribute('aria-disabled', 'true');
            this.el.toggle?.setAttribute('aria-checked', 'true');

            // Kullanıcı sonucu görsün diye kısa bir an bekleyip popup kendiliğinden kapanır.
            this.closeTimer = window.setTimeout(() => this.close(), 700);

            // Bilet sunucuda süresini doldurmadan önce sessizce yenile.
            this.ticketTimer = window.setTimeout(
                () => this.reset(),
                Math.max(30, (payload.expires_in || 900) - 30) * 1000,
            );
        }

        fail(message) {
            this.state('failed');
            this.say(message || 'Doğrulama başarısız. Yeniden deneyin.', 'error');
            window.setTimeout(() => this.load(), 900);
        }

        reset() {
            this.close();
            this.loaded = false;
            this.el.toggle?.setAttribute('aria-checked', 'false');
            this.el.handle?.removeAttribute('aria-disabled');
            this.state('idle');
            this.value('');
        }

        state(name) {
            this.root.dataset.state = name;

            // Popup <body>'ye taşındığı için artık .cap'in DOM alt ağacında
            // değil — durum, ondan bağımsız olarak panelin kendi üzerinde de
            // tutulur ki içindeki CSS seçicileri (.cap-modal[data-state=...])
            // hâlâ eşleşsin.
            if (this.el.modal) {
                this.el.modal.dataset.state = name;
            }
        }

        value(ticket) {
            if (this.el.input) {
                this.el.input.value = ticket || '';
            }
        }

        say(message, tone) {
            this.el.status.textContent = message || '';
            this.el.status.dataset.tone = tone || '';
        }

        warn(message) {
            if (this.el.error) {
                this.el.error.textContent = message || '';
                this.el.error.hidden = ! message;
            }
        }

        stop() {
            window.clearTimeout(this.challengeTimer);
            window.clearTimeout(this.closeTimer);
        }
    }

    function scan(root = document) {
        root.querySelectorAll('[data-captcha]:not([data-captcha-ready])').forEach((node) => {
            node.setAttribute('data-captcha-ready', '');
            widgets.add(new Captcha(node));
        });
    }

    window.captcha = {
        scan,
        reset(form) {
            widgets.forEach((widget) => {
                if (! form || widget.form === form) {
                    widget.reset();
                }
            });
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => scan());
    } else {
        scan();
    }
})();
