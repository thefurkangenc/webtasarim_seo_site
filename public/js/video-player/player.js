/**
 * YouTube benzeri HTML5 oynatıcı.
 *
 * Kök `[data-player]` + `data-player-config` JSON. Gömme (YouTube/Vimeo)
 * bu sınıfa girmez. Sayfada biri play deyince diğer örnekler pause olur.
 */

import { clamp, escapeHtml, formatTime } from './format.js';
import { prefs } from './prefs.js';
import { cueAt, parseSpriteVtt } from './sprite-preview.js';

const SPEEDS = [0.5, 0.75, 1, 1.25, 1.5, 2];
const SKIP = 10;
const IDLE_MS = 2500;
const POLL_MS = 5000;
const CLICK_WAIT = 250;

/** @type {Set<VideoPlayer>} */
const instances = new Set();

const ICONS = {
    fa: {
        play: '<i class="fa-solid fa-play"></i>',
        pause: '<i class="fa-solid fa-pause"></i>',
        back: '<i class="fa-solid fa-rotate-left"></i>',
        forward: '<i class="fa-solid fa-rotate-right"></i>',
        volume: '<i class="fa-solid fa-volume-high"></i>',
        muted: '<i class="fa-solid fa-volume-xmark"></i>',
        settings: '<i class="fa-solid fa-gear"></i>',
        pip: '<i class="fa-solid fa-clone"></i>',
        fs: '<i class="fa-solid fa-expand"></i>',
        fsexit: '<i class="fa-solid fa-compress"></i>',
    },
    material: {
        play: '<i class="material-symbols-outlined">play_arrow</i>',
        pause: '<i class="material-symbols-outlined">pause</i>',
        back: '<i class="material-symbols-outlined">replay_10</i>',
        forward: '<i class="material-symbols-outlined">forward_10</i>',
        volume: '<i class="material-symbols-outlined">volume_up</i>',
        muted: '<i class="material-symbols-outlined">volume_off</i>',
        settings: '<i class="material-symbols-outlined">settings</i>',
        pip: '<i class="material-symbols-outlined">picture_in_picture_alt</i>',
        fs: '<i class="material-symbols-outlined">fullscreen</i>',
        fsexit: '<i class="material-symbols-outlined">fullscreen_exit</i>',
    },
};

export class VideoPlayer {
    constructor(root) {
        this.root = root;
        this.config = this.readConfig();
        this.icons = ICONS[root.dataset.playerIcons === 'fa' ? 'fa' : 'material'];
        this.abort = new AbortController();
        this.pollTimer = 0;
        this.idleTimer = 0;
        this.clickTimer = 0;
        this.pollFails = 0;
        this.dragging = false;
        this.menu = null;
        this.cues = [];
        this.qualityId = 'source';

        this.build();
        this.bind();
        this.applyPrefs();
        this.applyConfig(this.config, { keepSrc: false });
        this.sync();

        root.dataset.playerReady = '1';
        root._vp = this;
        instances.add(this);
    }

    readConfig() {
        const node = this.root.querySelector('[data-player-config]');

        try {
            return node ? JSON.parse(node.textContent) : {};
        } catch {
            return {};
        }
    }

    build() {
        if (this.root.querySelector('.vp__media')) {
            this.cache();

            return;
        }

        const title = escapeHtml(this.config.title ?? '');
        const src = escapeHtml(this.config.src ?? '');
        const poster = this.config.poster ? ` poster="${escapeHtml(this.config.poster)}"` : '';
        const pip = document.pictureInPictureEnabled
            ? `<button type="button" class="vp__btn" data-player-action="pip" aria-label="Resim içinde resim">${this.icons.pip}</button>`
            : '';

        this.root.classList.add('vp');
        this.root.tabIndex = 0;
        this.root.setAttribute('role', 'region');
        this.root.setAttribute('aria-label', title || 'Video oynatıcı');

        if (this.root.hasAttribute('data-player-compact')) {
            this.root.classList.add('vp--compact');
        }

        if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            this.root.classList.add('vp--ios');
        }

        this.root.insertAdjacentHTML('afterbegin', `
            <video class="vp__media" playsinline preload="metadata"${poster} src="${src}"></video>
            <div class="vp__ui">
                <button type="button" class="vp__big-play" data-player-action="toggle" aria-label="Oynat">${this.icons.play}</button>
                <div class="vp__badge" data-player-badge hidden></div>
                <div class="vp__spinner" data-player-spinner hidden></div>
                <div class="vp__error" data-player-error hidden>
                    <p>Video oynatılamadı.</p>
                    <a data-player-download href="${src}" download>Videoyu indir</a>
                </div>
                <div class="vp__chrome" data-player-chrome>
                    <div class="vp__progress" data-player-progress>
                        <div class="vp__buf" data-player-buf></div>
                        <div class="vp__played" data-player-played></div>
                        <div class="vp__knob" data-player-knob></div>
                        <div class="vp__preview" data-player-preview hidden>
                            <div class="vp__preview-img" data-player-preview-img></div>
                            <span data-player-preview-time>0:00</span>
                        </div>
                    </div>
                    <div class="vp__bar">
                        <button type="button" class="vp__btn" data-player-action="toggle" aria-label="Oynat">${this.icons.play}</button>
                        <button type="button" class="vp__btn" data-player-action="back" aria-label="10 saniye geri">${this.icons.back}</button>
                        <button type="button" class="vp__btn" data-player-action="forward" aria-label="10 saniye ileri">${this.icons.forward}</button>
                        <div class="vp__vol">
                            <button type="button" class="vp__btn" data-player-action="mute" aria-label="Ses">${this.icons.volume}</button>
                            <input type="range" class="vp__range" data-player-volume min="0" max="1" step="0.01" aria-label="Ses düzeyi">
                        </div>
                        <span class="vp__time"><span data-player-current>0:00</span> / <span data-player-duration>0:00</span></span>
                        <span class="vp__spacer"></span>
                        <div class="vp__settings">
                            <button type="button" class="vp__btn" data-player-action="settings" aria-label="Ayarlar">${this.icons.settings}</button>
                            <div class="vp__menu" data-player-menu hidden></div>
                        </div>
                        ${pip}
                        <button type="button" class="vp__btn" data-player-action="fs" aria-label="Tam ekran">${this.icons.fs}</button>
                    </div>
                </div>
            </div>
        `);

        this.cache();
    }

    cache() {
        this.video = this.root.querySelector('.vp__media');
        this.bigPlay = this.root.querySelector('.vp__big-play');
        this.badge = this.root.querySelector('[data-player-badge]');
        this.spinner = this.root.querySelector('[data-player-spinner]');
        this.errorBox = this.root.querySelector('[data-player-error]');
        this.chrome = this.root.querySelector('[data-player-chrome]');
        this.progress = this.root.querySelector('[data-player-progress]');
        this.buf = this.root.querySelector('[data-player-buf]');
        this.played = this.root.querySelector('[data-player-played]');
        this.knob = this.root.querySelector('[data-player-knob]');
        this.preview = this.root.querySelector('[data-player-preview]');
        this.previewImg = this.root.querySelector('[data-player-preview-img]');
        this.previewTime = this.root.querySelector('[data-player-preview-time]');
        this.volume = this.root.querySelector('[data-player-volume]');
        this.currentEl = this.root.querySelector('[data-player-current]');
        this.durationEl = this.root.querySelector('[data-player-duration]');
        this.menuEl = this.root.querySelector('[data-player-menu]');
        this.toggleBtns = this.root.querySelectorAll('[data-player-action="toggle"]');
        this.muteBtn = this.root.querySelector('[data-player-action="mute"]');
        this.fsBtn = this.root.querySelector('[data-player-action="fs"]');
        this.download = this.root.querySelector('[data-player-download]');
    }

    bind() {
        const { signal } = this.abort;
        const on = (el, type, fn, opts = {}) => el?.addEventListener(type, fn, { signal, ...opts });

        on(this.root, 'click', (event) => this.onClick(event));
        on(this.root, 'dblclick', (event) => this.onDblClick(event));
        on(this.root, 'keydown', (event) => this.onKey(event));
        on(this.root, 'mousemove', () => this.wake());
        on(this.root, 'mouseleave', () => this.scheduleIdle());

        on(this.video, 'play', () => this.onPlay());
        on(this.video, 'pause', () => this.sync());
        on(this.video, 'timeupdate', () => this.sync());
        on(this.video, 'progress', () => this.sync());
        on(this.video, 'waiting', () => this.setBusy(true));
        on(this.video, 'playing', () => this.setBusy(false));
        on(this.video, 'canplay', () => this.setBusy(false));
        on(this.video, 'loadedmetadata', () => this.sync());
        on(this.video, 'ended', () => this.sync());
        on(this.video, 'volumechange', () => this.syncVolume());
        on(this.video, 'ratechange', () => this.paintMenu());
        on(this.video, 'error', () => this.showError(true));

        on(this.volume, 'input', () => {
            this.video.muted = false;
            this.video.volume = Number(this.volume.value);
            prefs.setVolume(this.video.volume);
            prefs.setMuted(false);
        });

        on(this.progress, 'pointerdown', (event) => this.beginSeek(event));
        on(window, 'pointermove', (event) => this.moveSeek(event));
        on(window, 'pointerup', () => this.endSeek());
        on(this.progress, 'mousemove', (event) => this.hoverPreview(event));
        on(this.progress, 'mouseleave', () => {
            if (! this.dragging) {
                this.preview.hidden = true;
            }
        });

        on(document, 'fullscreenchange', () => this.syncFs());
        on(document, 'click', (event) => {
            if (this.menu && ! event.target.closest('.vp__settings')) {
                this.closeMenu();
            }
        });
    }

    applyPrefs() {
        this.video.volume = prefs.volume();
        this.video.muted = prefs.muted();
        this.video.playbackRate = prefs.rate();
        this.volume.value = String(this.video.volume);
    }

    applyConfig(config, { keepSrc = true } = {}) {
        this.config = { ...this.config, ...config };

        if (this.download && this.config.src) {
            this.download.href = this.config.src;
        }

        if (! keepSrc && this.config.src && this.video.src !== this.config.src) {
            this.video.src = this.config.src;
        }

        if (this.config.poster) {
            this.video.poster = this.config.poster;
        }

        const wanted = prefs.quality();
        const match = (this.config.qualities ?? []).find((item) => item.id === wanted);

        if (match && this.video.src !== match.src && ! keepSrc) {
            this.qualityId = match.id;
            this.video.src = match.src;
        } else {
            const current = (this.config.qualities ?? []).find((item) => item.src === this.video.currentSrc || item.src === this.video.src);
            this.qualityId = current?.id ?? this.qualityId;
        }

        this.loadSprite(this.config.sprite);
        this.paintMenu();
        this.poll();
        this.sync();
    }

    async loadSprite(sprite) {
        this.cues = [];

        if (! sprite?.vtt || ! sprite?.url) {
            return;
        }

        try {
            const response = await fetch(sprite.vtt, { signal: this.abort.signal });
            this.cues = parseSpriteVtt(await response.text());
            this.previewImg.style.backgroundImage = `url("${sprite.url}")`;
            this.previewImg.style.width = `${sprite.width}px`;
            this.previewImg.style.height = `${sprite.height}px`;
        } catch {
            this.cues = [];
        }
    }

    onClick(event) {
        const action = event.target.closest('[data-player-action]')?.dataset.playerAction;

        if (action) {
            event.preventDefault();
            this.run(action, event);

            return;
        }

        if (event.target.closest('.vp__chrome') || event.target.closest('.vp__error')) {
            return;
        }

        if (this.clickTimer) {
            return;
        }

        this.clickTimer = window.setTimeout(() => {
            this.clickTimer = 0;
            this.toggle();
        }, CLICK_WAIT);
    }

    onDblClick(event) {
        if (event.target.closest('.vp__chrome') || event.target.closest('[data-player-action]')) {
            return;
        }

        window.clearTimeout(this.clickTimer);
        this.clickTimer = 0;

        const rect = this.root.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width;

        if (x < 1 / 3) {
            this.skip(-SKIP);
        } else if (x > 2 / 3) {
            this.skip(SKIP);
        } else {
            this.fullscreen();
        }
    }

    run(action) {
        if (action === 'toggle') {
            this.toggle();
        } else if (action === 'back') {
            this.skip(-SKIP);
        } else if (action === 'forward') {
            this.skip(SKIP);
        } else if (action === 'mute') {
            this.video.muted = ! this.video.muted;
            prefs.setMuted(this.video.muted);
        } else if (action === 'settings') {
            this.toggleMenu();
        } else if (action === 'pip') {
            this.pip();
        } else if (action === 'fs') {
            this.fullscreen();
        } else if (action === 'speed') {
            // handled via data-player-speed
        }
    }

    onKey(event) {
        if (event.target.matches('input, textarea, select')) {
            return;
        }

        const key = event.key;

        if (key === ' ' || key === 'k' || key === 'K') {
            event.preventDefault();
            this.toggle();
        } else if (key === 'j' || key === 'J' || key === 'ArrowLeft') {
            event.preventDefault();
            this.skip(-SKIP);
        } else if (key === 'l' || key === 'L' || key === 'ArrowRight') {
            event.preventDefault();
            this.skip(SKIP);
        } else if (key === 'm' || key === 'M') {
            this.video.muted = ! this.video.muted;
            prefs.setMuted(this.video.muted);
        } else if (key === 'f' || key === 'F') {
            event.preventDefault();
            this.fullscreen();
        } else if (key === 'ArrowUp') {
            event.preventDefault();
            this.nudgeVolume(0.05);
        } else if (key === 'ArrowDown') {
            event.preventDefault();
            this.nudgeVolume(-0.05);
        } else if (key === 'Escape') {
            if (this.menu) {
                this.closeMenu();
            } else if (document.fullscreenElement) {
                document.exitFullscreen?.();
            }
        } else if (/^[0-9]$/.test(key) && this.video.duration) {
            this.video.currentTime = this.video.duration * (Number(key) / 10);
        }
    }

    toggle() {
        if (this.video.paused) {
            this.play();
        } else {
            this.video.pause();
        }
    }

    play() {
        instances.forEach((player) => {
            if (player !== this) {
                player.video.pause();
            }
        });

        this.showError(false);
        this.video.play().catch(() => this.showError(true));
    }

    onPlay() {
        instances.forEach((player) => {
            if (player !== this) {
                player.video.pause();
            }
        });
        this.wake();
        this.sync();
    }

    skip(delta) {
        if (! Number.isFinite(this.video.duration)) {
            return;
        }

        this.video.currentTime = clamp(this.video.currentTime + delta, 0, this.video.duration);
        this.flash(delta < 0 ? '« 10' : '10 »');
    }

    nudgeVolume(delta) {
        this.video.muted = false;
        this.video.volume = clamp(this.video.volume + delta, 0, 1);
        prefs.setVolume(this.video.volume);
        prefs.setMuted(false);
    }

    flash(text) {
        this.badge.hidden = false;
        this.badge.textContent = text;
        window.clearTimeout(this.badgeTimer);
        this.badgeTimer = window.setTimeout(() => {
            this.badge.hidden = true;
        }, 600);
    }

    ratio(event) {
        const rect = this.progress.getBoundingClientRect();

        return clamp((event.clientX - rect.left) / rect.width, 0, 1);
    }

    beginSeek(event) {
        if (! this.video.duration) {
            return;
        }

        this.dragging = true;
        this.progress.setPointerCapture?.(event.pointerId);
        this.video.currentTime = this.ratio(event) * this.video.duration;
        this.hoverPreview(event);
        this.sync();
    }

    moveSeek(event) {
        if (! this.dragging || ! this.video.duration) {
            return;
        }

        this.video.currentTime = this.ratio(event) * this.video.duration;
        this.hoverPreview(event);
        this.sync();
    }

    endSeek() {
        this.dragging = false;
        this.preview.hidden = true;
    }

    hoverPreview(event) {
        if (! this.video.duration) {
            return;
        }

        const ratio = this.ratio(event);
        const seconds = ratio * this.video.duration;

        this.preview.hidden = false;
        this.previewTime.textContent = formatTime(seconds);
        this.preview.style.left = `${ratio * 100}%`;

        const cue = cueAt(this.cues, seconds);

        if (cue && this.config.sprite?.url) {
            this.previewImg.hidden = false;
            this.previewImg.style.backgroundPosition = `-${cue.x}px -${cue.y}px`;
        } else {
            this.previewImg.hidden = true;
        }
    }

    async setQuality(id) {
        const item = (this.config.qualities ?? []).find((entry) => entry.id === id);

        if (! item || this.video.src === item.src) {
            this.closeMenu();

            return;
        }

        const time = this.video.currentTime;
        const playing = ! this.video.paused;
        const previous = this.video.src;

        this.qualityId = id;
        prefs.setQuality(id);
        this.video.src = item.src;
        this.video.currentTime = time;

        const ok = await new Promise((resolve) => {
            const pass = () => resolve(true);
            const fail = () => resolve(false);

            this.video.addEventListener('loadeddata', pass, { once: true });
            this.video.addEventListener('error', fail, { once: true });
        });

        if (! ok) {
            this.video.src = previous;
            this.video.currentTime = time;
            this.flash('Bu kalite oynatılamadı.');

            return;
        }

        if (playing) {
            this.video.play().catch(() => {});
        }

        this.closeMenu();
        this.paintMenu();
    }

    setSpeed(rate) {
        this.video.playbackRate = rate;
        prefs.setRate(rate);
        this.closeMenu();
        this.paintMenu();
    }

    toggleMenu() {
        if (this.menu) {
            this.closeMenu();

            return;
        }

        this.menu = 'root';
        this.paintMenu();
        this.menuEl.hidden = false;
    }

    closeMenu() {
        this.menu = null;
        this.menuEl.hidden = true;
    }

    paintMenu() {
        if (! this.menuEl) {
            return;
        }

        const processing = this.config.status === 'processing';
        const qualities = this.config.qualities ?? [];
        const rate = this.video.playbackRate;

        if (this.menu === 'speed') {
            this.menuEl.innerHTML = `
                <button type="button" class="vp__menu-back" data-menu="root">Geri</button>
                ${SPEEDS.map((value) => `
                    <button type="button" class="vp__menu-item${value === rate ? ' is-active' : ''}" data-speed="${value}">
                        ${value === 1 ? 'Normal' : `${value}x`}
                    </button>
                `).join('')}`;
        } else if (this.menu === 'quality') {
            this.menuEl.innerHTML = `
                <button type="button" class="vp__menu-back" data-menu="root">Geri</button>
                ${qualities.map((item) => `
                    <button type="button" class="vp__menu-item${item.id === this.qualityId ? ' is-active' : ''}" data-quality="${escapeHtml(item.id)}">
                        ${escapeHtml(item.label)}
                    </button>
                `).join('')}`;
        } else {
            const qualityLabel = processing
                ? 'Hazırlanıyor'
                : (qualities.find((item) => item.id === this.qualityId)?.label ?? 'Otomatik');

            this.menuEl.innerHTML = `
                <button type="button" class="vp__menu-item" data-menu="speed">Hız<span>${rate === 1 ? 'Normal' : `${rate}x`}</span></button>
                <button type="button" class="vp__menu-item" data-menu="quality" ${processing || qualities.length === 0 ? 'disabled' : ''}>
                    Kalite<span>${qualityLabel}</span>
                </button>
            `;
        }

        this.menuEl.querySelectorAll('[data-menu]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                this.menu = button.dataset.menu;
                this.paintMenu();
                this.menuEl.hidden = false;
            });
        });
        this.menuEl.querySelectorAll('[data-speed]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                this.setSpeed(Number(button.dataset.speed));
            });
        });
        this.menuEl.querySelectorAll('[data-quality]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                this.setQuality(button.dataset.quality);
            });
        });
    }

    async pip() {
        if (! document.pictureInPictureEnabled) {
            return;
        }

        if (document.pictureInPictureElement) {
            await document.exitPictureInPicture();
        } else {
            await this.video.requestPictureInPicture().catch(() => {});
        }
    }

    fullscreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen?.();
        } else {
            this.root.requestFullscreen?.().catch(() => this.video.webkitEnterFullscreen?.());
        }
    }

    syncFs() {
        const on = document.fullscreenElement === this.root;
        this.root.classList.toggle('is-fs', on);
        this.fsBtn.innerHTML = on ? this.icons.fsexit : this.icons.fs;
        this.fsBtn.setAttribute('aria-label', on ? 'Tam ekrandan çık' : 'Tam ekran');
    }

    wake() {
        this.root.classList.remove('is-idle');
        this.scheduleIdle();
    }

    scheduleIdle() {
        window.clearTimeout(this.idleTimer);

        if (this.video.paused || this.menu || this.dragging) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        this.idleTimer = window.setTimeout(() => {
            if (! this.video.paused && ! this.menu) {
                this.root.classList.add('is-idle');
            }
        }, IDLE_MS);
    }

    setBusy(busy) {
        this.spinner.hidden = ! busy;
        this.root.classList.toggle('is-buffering', busy);
    }

    showError(show) {
        this.errorBox.hidden = ! show;
        this.root.classList.toggle('is-error', show);
    }

    sync() {
        const duration = this.video.duration || this.config.duration || 0;
        const current = this.video.currentTime || 0;
        const ratio = duration ? current / duration : 0;
        const playing = ! this.video.paused && ! this.video.ended;

        this.played.style.width = `${ratio * 100}%`;
        this.knob.style.left = `${ratio * 100}%`;
        this.currentEl.textContent = formatTime(current);
        this.durationEl.textContent = formatTime(duration);
        this.root.classList.toggle('is-playing', playing);
        this.root.classList.toggle('is-paused', ! playing);

        const icon = playing ? this.icons.pause : this.icons.play;
        const label = playing ? 'Duraklat' : 'Oynat';

        this.toggleBtns.forEach((button) => {
            button.innerHTML = icon;
            button.setAttribute('aria-label', label);
        });
        this.bigPlay.innerHTML = this.icons.play;
        this.bigPlay.hidden = playing;

        if (this.video.buffered.length) {
            const end = this.video.buffered.end(this.video.buffered.length - 1);
            this.buf.style.width = `${duration ? (end / duration) * 100 : 0}%`;
        }

        this.syncVolume();
        this.scheduleIdle();
    }

    syncVolume() {
        const muted = this.video.muted || this.video.volume === 0;

        this.muteBtn.innerHTML = muted ? this.icons.muted : this.icons.volume;
        this.muteBtn.setAttribute('aria-label', muted ? 'Sesi aç' : 'Sesi kapat');
        this.root.classList.toggle('is-muted', muted);

        if (! muted) {
            this.volume.value = String(this.video.volume);
        }
    }

    poll() {
        window.clearInterval(this.pollTimer);

        if (this.config.status !== 'processing' || ! this.config.status_url) {
            return;
        }

        this.pollTimer = window.setInterval(() => this.refresh(), POLL_MS);
    }

    async refresh() {
        try {
            const response = await fetch(this.config.status_url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: this.abort.signal,
            });
            const payload = await response.json();
            const data = payload.data ?? payload;

            this.pollFails = 0;

            if (data.status === 'ready' || data.status === 'failed') {
                window.clearInterval(this.pollTimer);
                this.applyConfig(data, { keepSrc: true });
            } else {
                this.config = { ...this.config, ...data };
                this.paintMenu();
            }
        } catch {
            this.pollFails += 1;

            if (this.pollFails >= 3) {
                window.clearInterval(this.pollTimer);
            }
        }
    }

    destroy() {
        this.abort.abort();
        window.clearInterval(this.pollTimer);
        window.clearTimeout(this.idleTimer);
        window.clearTimeout(this.clickTimer);
        instances.delete(this);
        delete this.root.dataset.playerReady;
        delete this.root._vp;
    }
}

export function mountPlayer(host, config, options = {}) {
    if (host._vp instanceof VideoPlayer) {
        host._vp.destroy();
    }

    host.innerHTML = '';
    const root = document.createElement('div');
    root.dataset.player = '';
    root.dataset.playerIcons = options.icons ?? 'material';

    if (options.compact) {
        root.dataset.playerCompact = '';
    }

    const script = document.createElement('script');
    script.type = 'application/json';
    script.dataset.playerConfig = '';
    script.textContent = JSON.stringify(config ?? {});
    root.append(script);
    host.append(root);
    host._vp = new VideoPlayer(root);

    return host._vp;
}

export function boot(scope = document) {
    scope.querySelectorAll('[data-player]:not([data-player-ready])').forEach((element) => {
        new VideoPlayer(element);
    });
}
