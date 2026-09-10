/**
 * Yardım popover'ı — form label'larının yanındaki (?) ikonu.
 *
 * <x-admin::form.help> bileşeni bir <button data-help-trigger> ve yanına gizli
 * bir <template data-help-content> basar. Bu modül olay delegasyonuyla çalışır,
 * sayfa JS'i gerektirmez (scripts.blade.php'de global yüklenir).
 *
 * Davranış:
 *  - Fare ikonun üstüne gelince açılır, ayrılınca kısa gecikmeyle kapanır.
 *  - İkona tıklanınca sabitlenir (mobil + metin seçmek için); dışarı tıkla / Esc kapatır.
 *  - Popover <body>'ye taşınır (kart/tablo taşmalarından etkilenmesin) ve
 *    tetikleyiciye göre yukarı/aşağı otomatik konumlanır.
 */

const MARGIN = 8;
const MAX_WIDTH = 340;
const HIDE_DELAY = 140;

let layer = null;
let active = null; // { trigger, box, pinned }
let hideTimer = null;

function ensureLayer() {
    if (layer && layer.isConnected) return layer;
    layer = document.createElement('div');
    layer.className = 'help-pop-layer';
    document.body.appendChild(layer);
    return layer;
}

function close() {
    clearTimeout(hideTimer);
    if (!active) return;
    active.trigger.setAttribute('aria-expanded', 'false');
    active.box.remove();
    active = null;
}

function scheduleHide() {
    if (!active || active.pinned) return;
    clearTimeout(hideTimer);
    hideTimer = setTimeout(close, HIDE_DELAY);
}

function cancelHide() {
    clearTimeout(hideTimer);
}

function open(trigger, pinned) {
    cancelHide();

    if (active && active.trigger === trigger) {
        if (pinned) active.pinned = true;
        position();
        return;
    }

    close();

    const tpl = trigger.parentElement?.querySelector('[data-help-content]');
    if (!tpl) return;

    const box = document.createElement('div');
    box.className = 'help-pop-box';
    box.appendChild(tpl.content.cloneNode(true));
    ensureLayer().appendChild(box);

    trigger.setAttribute('aria-expanded', 'true');
    active = { trigger, box, pinned: !!pinned };
    position();
}

function position() {
    if (!active) return;
    const { trigger, box } = active;

    const vw = document.documentElement.clientWidth;
    const vh = document.documentElement.clientHeight;
    const r = trigger.getBoundingClientRect();

    box.style.maxWidth = Math.min(MAX_WIDTH, vw - MARGIN * 2) + 'px';
    box.style.left = '0px';
    box.style.top = '0px';

    const b = box.getBoundingClientRect();

    let left = r.left + r.width / 2 - b.width / 2;
    left = Math.max(MARGIN, Math.min(left, vw - b.width - MARGIN));

    let top = r.bottom + 7;
    let placement = 'bottom';
    if (top + b.height > vh - MARGIN && r.top - 7 - b.height > MARGIN) {
        top = r.top - 7 - b.height;
        placement = 'top';
    }

    box.style.left = Math.round(left) + 'px';
    box.style.top = Math.round(top) + 'px';
    box.dataset.placement = placement;
    box.style.setProperty('--help-arrow', Math.round(r.left + r.width / 2 - left) + 'px');
}

// Hover — dokunmatikte açılışı click'e bırak.
document.addEventListener('pointerover', (event) => {
    if (event.pointerType === 'touch') return;
    const trigger = event.target.closest?.('[data-help-trigger]');
    if (trigger) {
        open(trigger, false);
        return;
    }
    if (event.target.closest?.('.help-pop-box')) cancelHide();
});

document.addEventListener('pointerout', (event) => {
    const from = event.target.closest?.('[data-help-trigger], .help-pop-box');
    if (!from) return;
    const to = event.relatedTarget;
    if (to && to.closest?.('[data-help-trigger], .help-pop-box')) return;
    scheduleHide();
});

// Klavye erişilebilirliği.
document.addEventListener('focusin', (event) => {
    const trigger = event.target.closest?.('[data-help-trigger]');
    if (trigger) open(trigger, false);
});
document.addEventListener('focusout', (event) => {
    if (event.target.closest?.('[data-help-trigger]')) scheduleHide();
});

// Tıklama — sabitle / kapat.
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-help-trigger]');
    if (trigger) {
        event.preventDefault();
        event.stopPropagation();
        if (active && active.trigger === trigger) {
            active.pinned ? close() : (active.pinned = true);
        } else {
            open(trigger, true);
        }
        return;
    }
    if (active && active.pinned && !event.target.closest('.help-pop-box')) close();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
});

window.addEventListener('scroll', () => position(), true);
window.addEventListener('resize', () => position());

// Modal kapanınca / içerik değişince açık popover'ı temizle.
document.addEventListener('admin:content-loaded', () => close());
