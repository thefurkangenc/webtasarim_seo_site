/**
 * <x-admin::form.image> bileşeninin TEKİL modunun davranışı.
 *
 * Alan, seçilen görseli hemen yükler ve gizli input'a media id'sini yazar;
 * form gönderildiğinde sadece bu id gider. Preset tanımlıysa yüklemeden önce
 * kırpma modalı açılır ve orana kilitlenir. Dosya seçme, kütüphaneden seçme
 * ve sürükle-bırak aynı yükleme akışından geçer.
 *
 * Bileşene `multiple` verildiğinde alan bu modülü değil core/media-gallery.js'i
 * kullanır; buradaki tüm olay dinleyicileri `data-media-multiple` taşıyan
 * alanları görmezden gelir (ikisi de document seviyesinde delegasyon yapar).
 *
 * Ajax modal içinde açılan formlarda da çalışır: bağlama, olay delegasyonu ile
 * document seviyesinde yapılır, sayfa JS'inin bir şey çağırmasına gerek yoktur.
 */

import { http, HttpError } from './http.js';
import { cropModal } from './cropper.js';
import { toast } from './toast.js';
import { confirm } from './confirm.js';
import { mediaPicker } from './media-picker.js';
import { presetOf, setBusy, uploadWithCrop } from './media-upload.js';

/** Çoklu alanlar core/media-gallery.js'e ait; buradaki akışlar onlara dokunmaz. */
function field(element) {
    const root = element.closest('[data-media-field]');

    return root?.dataset.mediaMultiple ? null : root;
}

function parts(root) {
    return {
        input: root.querySelector('[data-media-input]'),
        file: root.querySelector('[data-media-file]'),
        drop: root.querySelector('[data-media-drop]'),
        dragover: root.querySelector('[data-media-dragover]'),
        preview: root.querySelector('[data-media-preview]'),
        image: root.querySelector('[data-media-image]'),
        empty: root.querySelector('[data-media-empty]'),
        info: root.querySelector('[data-media-info]'),
        actions: root.querySelector('[data-media-actions]'),
        recrop: root.querySelector('[data-media-action="recrop"]'),
        remove: root.querySelector('[data-media-action="remove"]'),
        selectLabel: root.querySelector('[data-media-select-label]'),
    };
}

/** Alanı verilen medya ile doldurur; null geçilirse temizler. */
function render(root, media) {
    const { input, image, preview, empty, info, actions, recrop, remove, selectLabel } = parts(root);

    input.value = media?.id ?? '';
    root.dataset.mediaCanRecrop = media?.can_recrop ? '1' : '';

    if (! media) {
        preview.classList.add('hidden');
        empty.classList.remove('hidden');
        info.classList.add('hidden');
        info.textContent = '';
        remove?.classList.add('hidden');
        recrop?.classList.add('hidden');
        actions.classList.remove('grid-cols-3');
        actions.classList.add('grid-cols-2');

        if (selectLabel) {
            selectLabel.textContent = 'Dosya Seç';
        }

        // Bağlı alanlar (örn. SEO paylaşım görseli) temizlendiğini bilsin.
        root.dispatchEvent(new CustomEvent('media:change', { detail: null, bubbles: true }));

        return;
    }

    // 'thumb' kütüphane ızgarası için sabit karedir (400x400 cover); preset
    // oranını bozar. Alan önizlemesi kırpımın gerçek oranını göstermeli.
    image.src = media.medium ?? media.url;
    image.alt = media.alt ?? media.name ?? '';
    // Yeniden kırpma her zaman bu URL'den başlar — 'url'/'medium' önceki
    // kırpımın SONUCUdur, kaynak olarak kullanılırsa her seferinde biraz
    // daha fazla kırpar (ya da hiç güncellenmemişse boş kalıp canvas'ı
    // siyah bırakır).
    image.dataset.original = media.original ?? media.url;
    info.textContent = media.width
        ? `${media.name} · ${media.width}×${media.height} · ${media.human_size}`
        : `${media.name} · ${media.human_size}`;

    preview.classList.remove('hidden');
    empty.classList.add('hidden');
    info.classList.remove('hidden');
    remove?.classList.remove('hidden');

    if (selectLabel) {
        selectLabel.textContent = 'Değiştir';
    }

    const showRecrop = Boolean(media.can_recrop && root.dataset.mediaWidth && root.dataset.mediaHeight);
    recrop?.classList.toggle('hidden', ! showRecrop);
    actions.classList.toggle('grid-cols-3', showRecrop);
    actions.classList.toggle('grid-cols-2', ! showRecrop);

    root.dispatchEvent(new CustomEvent('media:change', { detail: media, bubbles: true }));
}

async function onFileSelected(root, file) {
    const media = await uploadWithCrop(root, file);

    if (media) {
        render(root, media);
    }

    parts(root).file.value = '';
}

async function onRecrop(root) {
    const id = parts(root).input.value;
    const target = presetOf(root);
    const originalUrl = parts(root).image.dataset.original;

    if (! id || ! target || ! originalUrl) {
        return;
    }

    // Yeniden kırpma sunucudaki orijinal üzerinden yapılır; dosyayı geri çekiyoruz.
    setBusy(root, true);

    try {
        const response = await fetch(originalUrl, { credentials: 'same-origin' });
        const blob = await response.blob();
        const crop = await cropModal.open(new File([blob], 'original', { type: blob.type }), target);

        if (! crop) {
            return;
        }

        const { data, message } = await http.post(`/admin/media/${id}/recrop`, { crop });
        // Değişmeyen bir URL tarayıcı önbelleğinden eskisini gösterebilir.
        render(root, { ...data, medium: `${data.medium}?v=${Date.now()}` });
        toast.success(message);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Yeniden kırpılamadı.');
    } finally {
        setBusy(root, false);
    }
}

document.addEventListener('change', (event) => {
    const fileInput = event.target.closest('[data-media-file]');
    const root = fileInput ? field(fileInput) : null;

    if (root && fileInput.files?.length) {
        onFileSelected(root, fileInput.files[0]);
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-media-action]');

    if (! button) {
        return;
    }

    const root = field(button);
    const action = button.dataset.mediaAction;

    if (! root) {
        return;
    }

    if (action === 'select') {
        parts(root).file.click();
    }

    if (action === 'library') {
        const media = await mediaPicker.open();

        if (media) {
            render(root, media);
        }
    }

    if (action === 'recrop') {
        onRecrop(root);
    }

    if (action === 'remove' && await confirm('Görsel alandan kaldırılacak. Dosya medya kütüphanesinde kalır.', {
        title: 'Görseli kaldır', accept: 'Kaldır',
    })) {
        render(root, null);
    }
});

/*
 * Sürükle-bırak: sadece dosya sürüklenirken vurgu katmanı gösterilir; alan
 * içindeki alt elemanlar arasında geçişte tetiklenen dragenter/dragleave
 * çifti bir sayaçla dengelenir, aksi halde vurgu titrer.
 */
let dragDepth = 0;

function isFileDrag(event) {
    return [...(event.dataTransfer?.types ?? [])].includes('Files');
}

document.addEventListener('dragenter', (event) => {
    const drop = event.target.closest('[data-media-drop]');

    if (! drop || ! isFileDrag(event)) {
        return;
    }

    dragDepth += 1;
    drop.closest('[data-media-field]').querySelector('[data-media-dragover]')?.classList.remove('hidden');
});

document.addEventListener('dragover', (event) => {
    if (event.target.closest('[data-media-drop]') && isFileDrag(event)) {
        event.preventDefault();
    }
});

document.addEventListener('dragleave', (event) => {
    if (! event.target.closest('[data-media-drop]')) {
        return;
    }

    dragDepth = Math.max(0, dragDepth - 1);

    if (dragDepth === 0) {
        document.querySelectorAll('[data-media-dragover]').forEach((el) => el.classList.add('hidden'));
    }
});

document.addEventListener('drop', (event) => {
    const drop = event.target.closest('[data-media-drop]');

    dragDepth = 0;
    document.querySelectorAll('[data-media-dragover]').forEach((el) => el.classList.add('hidden'));

    if (! drop || ! isFileDrag(event)) {
        return;
    }

    // Çoklu alana bırakılan dosyaları core/media-gallery.js karşılar.
    const root = field(drop);
    const file = event.dataTransfer.files[0];

    if (! root) {
        return;
    }

    event.preventDefault();

    if (file) {
        onFileSelected(root, file);
    }
});

/**
 * Başka bir core modülünün bir görsel alanını programatik doldurması için
 * (örn. core/seo-field.js, kapak görseli seçilince paylaşım görselini
 * eşzamanlı doldurur). Kullanıcının kendi seçtiği bir görseli ezmez —
 * çağıran taraf "dokunulmamış" kontrolünü kendisi yapar.
 *
 *   setFieldMedia(root.querySelector('[data-media-field]'), media);
 */
export function setFieldMedia(root, media) {
    if (root) {
        render(root, media);
    }
}
