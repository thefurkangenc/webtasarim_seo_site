/**
 * Hedef seçimi: "Seçili sayfa ve yazılar" seçilince sayfa/yazı/hizmet
 * listeleri görünür. Modal içeriği AJAX ile geldiği için olay dinlenir.
 */

function sync(root) {
    const select = root.querySelector('[data-audience-select]');
    const targets = root.querySelector('[data-audience-targets]');

    if (! select || ! targets) {
        return;
    }

    targets.classList.toggle('hidden', select.value !== 'selected');
}

document.addEventListener('admin:content-loaded', (event) => {
    const root = event.target;

    if (! (root instanceof HTMLElement)) {
        return;
    }

    sync(root);
    root.querySelector('[data-audience-select]')?.addEventListener('change', () => sync(root));
});
