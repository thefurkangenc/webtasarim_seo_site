/**
 * <x-admin::form.image> bileşeninin davranışı.
 *
 * Alan, seçilen görseli hemen yükler ve gizli input'a media id'sini yazar;
 * form gönderildiğinde sadece bu id gider. Preset tanımlıysa yüklemeden önce
 * kırpma modalı açılır ve orana kilitlenir.
 *
 * Ajax modal içinde açılan formlarda da çalışır: bağlama, olay delegasyonu ile
 * document seviyesinde yapılır, sayfa JS'inin bir şey çağırmasına gerek yoktur.
 */

import { http, HttpError, ValidationError } from './http.js';
import { cropModal } from './cropper.js';
import { toast } from './toast.js';
import { confirm } from './confirm.js';
import { mediaPicker } from './media-picker.js';

const UPLOAD_URL = '/admin/media/upload';

function field(element) {
    return element.closest('[data-media-field]');
}

function parts(root) {
    return {
        input: root.querySelector('[data-media-input]'),
        file: root.querySelector('[data-media-file]'),
        preview: root.querySelector('[data-media-preview]'),
        image: root.querySelector('[data-media-image]'),
        empty: root.querySelector('[data-media-empty]'),
        info: root.querySelector('[data-media-info]'),
        recrop: root.querySelector('[data-media-action="recrop"]'),
        busy: root.querySelector('[data-media-busy]'),
    };
}

function preset(root) {
    const { mediaPreset, mediaWidth, mediaHeight, mediaLabel } = root.dataset;

    return mediaWidth && mediaHeight
        ? { preset: mediaPreset, width: Number(mediaWidth), height: Number(mediaHeight), label: mediaLabel }
        : null;
}

function setBusy(root, busy) {
    parts(root).busy?.classList.toggle('hidden', ! busy);
}

/** Alanı verilen medya ile doldurur; null geçilirse temizler. */
function render(root, media) {
    const { input, image, preview, empty, info, recrop } = parts(root);

    input.value = media?.id ?? '';
    root.dataset.mediaCanRecrop = media?.can_recrop ? '1' : '';

    if (! media) {
        preview.classList.add('hidden');
        empty.classList.remove('hidden');
        recrop?.classList.add('hidden');

        return;
    }

    image.src = media.thumb ?? media.url;
    image.alt = media.alt ?? media.name ?? '';
    info.textContent = media.width
        ? `${media.name} · ${media.width}×${media.height} · ${media.human_size}`
        : `${media.name} · ${media.human_size}`;

    preview.classList.remove('hidden');
    empty.classList.add('hidden');
    recrop?.classList.toggle('hidden', ! media.can_recrop);

    root.dispatchEvent(new CustomEvent('media:change', { detail: media, bubbles: true }));
}

async function upload(root, file, crop) {
    const body = new FormData();
    body.append('file', file);

    if (crop) {
        body.append('crop', JSON.stringify(crop));
    }

    if (root.dataset.mediaPreset) {
        body.append('preset', root.dataset.mediaPreset);
    }

    if (root.dataset.mediaFolder) {
        body.append('folder_id', root.dataset.mediaFolder);
    }

    setBusy(root, true);

    try {
        const { data, message } = await http.post(UPLOAD_URL, body);
        render(root, data);
        toast.success(message);
    } catch (error) {
        if (error instanceof ValidationError) {
            toast.error(Object.values(error.errors)[0][0]);
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Dosya yüklenemedi.');
        }
    } finally {
        setBusy(root, false);
        parts(root).file.value = '';
    }
}

async function onFileSelected(root, file) {
    const target = preset(root);

    // SVG kırpılamaz; preset olsa bile doğrudan yüklenir.
    if (! target || file.type === 'image/svg+xml' || ! file.type.startsWith('image/')) {
        return upload(root, file, null);
    }

    const crop = await cropModal.open(file, target);

    if (crop) {
        await upload(root, file, crop);
    } else {
        parts(root).file.value = '';
    }
}

async function onRecrop(root) {
    const id = parts(root).input.value;
    const target = preset(root);

    if (! id || ! target) {
        return;
    }

    // Yeniden kırpma sunucudaki orijinal üzerinden yapılır; dosyayı geri çekiyoruz.
    setBusy(root, true);

    try {
        const response = await fetch(parts(root).image.dataset.original, { credentials: 'same-origin' });
        const blob = await response.blob();
        const crop = await cropModal.open(new File([blob], 'original', { type: blob.type }), target);

        if (! crop) {
            return;
        }

        const { data, message } = await http.post(`/admin/media/${id}/recrop`, { crop });
        render(root, { ...data, thumb: `${data.thumb}?v=${Date.now()}` });
        toast.success(message);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Yeniden kırpılamadı.');
    } finally {
        setBusy(root, false);
    }
}

document.addEventListener('change', (event) => {
    const fileInput = event.target.closest('[data-media-file]');

    if (fileInput?.files?.length) {
        onFileSelected(field(fileInput), fileInput.files[0]);
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-media-action]');

    if (! button) {
        return;
    }

    const root = field(button);
    const action = button.dataset.mediaAction;

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
