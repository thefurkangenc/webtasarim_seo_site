/**
 * Görsel alanlarının paylaştığı yükleme ilkeleri.
 *
 * Tekil alan (core/media-field.js) ve çoklu galeri (core/media-gallery.js)
 * aynı yükleme akışını kullanır: preset varsa kırpma modalı açılır, dosya
 * /admin/media/upload'a gider, sonuç medya nesnesi olarak döner. Burası
 * hiçbir şey render etmez — çağıran taraf sonucu kendi arayüzüne yazar.
 */

import { http, HttpError, ValidationError, adminUrl } from './http.js';
import { cropModal } from './cropper.js';
import { toast } from './toast.js';

const UPLOAD_URL = adminUrl('/media/upload');

/** Alanın kırpma hedefi; preset tanımlı değilse null (kırpma yok). */
export function presetOf(root) {
    const { mediaPreset, mediaWidth, mediaHeight, mediaLabel } = root.dataset;

    return mediaWidth && mediaHeight
        ? { preset: mediaPreset, width: Number(mediaWidth), height: Number(mediaHeight), label: mediaLabel }
        : null;
}

/**
 * Alanın tür kısıtına uyuyor mu? Görsel alanına sürüklenen bir video HTML
 * `accept` özniteliğini atlar — dosya seçici penceresi filtreler, sürükle-bırak
 * filtrelemez. Sunucu tarafı da ayrıca kontrol eder (MediaService::guard);
 * buradaki kontrol yalnızca kullanıcıya anında geri bildirim içindir.
 */
export function isAcceptedFile(root, file) {
    const accept = root.dataset.mediaAccept;

    if (accept === 'image') {
        // Bazı tarayıcılar SVG için boş mime döndürür; uzantı yedek işaret.
        return file.type.startsWith('image/') || String(file.name ?? '').toLowerCase().endsWith('.svg');
    }

    if (accept === 'video') {
        return file.type.startsWith('video/');
    }

    return true;
}

/*
 * Aynı formda birden fazla görsel alanı olabilir (ör. kapak + galeri); ikisi
 * aynı anda yükleniyorsa submit butonu ilki bitince erken açılmamalı. Sayaç
 * her setBusy(true) için +1, her setBusy(false) için -1 yapar, buton yalnızca
 * sayaç sıfıra dönünce tekrar aktif olur.
 */
const formBusyCounts = new WeakMap();

function toggleFormSubmit(root, busy) {
    const form = root.closest('form');
    const submit = form?.querySelector('button[type="submit"]');

    if (! form || ! submit) {
        return;
    }

    const count = Math.max(0, (formBusyCounts.get(form) ?? 0) + (busy ? 1 : -1));
    formBusyCounts.set(form, count);

    submit.disabled = count > 0;
    submit.classList.toggle('opacity-50', count > 0);
    submit.classList.toggle('cursor-not-allowed', count > 0);
}

export function setBusy(root, busy) {
    root.querySelector('[data-media-busy]')?.classList.toggle('hidden', ! busy);
    toggleFormSubmit(root, busy);
}

/**
 * Dosyayı yükler ve medya nesnesini döndürür; hata durumunda toast basıp
 * null döner (çağıran tarafın try/catch yazmasına gerek yok).
 */
export async function uploadFile(root, file, crop = null) {
    const body = new FormData();
    body.append('file', file);

    if (crop) {
        body.append('crop', JSON.stringify(crop));
    }

    if (root.dataset.mediaPreset) {
        body.append('preset', root.dataset.mediaPreset);
    }

    // Sunucu da kısıtı bilsin — istemci kontrolü atlatılabilir.
    if (root.dataset.mediaAccept) {
        body.append('accept', root.dataset.mediaAccept);
    }

    if (root.dataset.mediaFolder) {
        body.append('folder_id', root.dataset.mediaFolder);
    }

    setBusy(root, true);

    try {
        const { data, message } = await http.post(UPLOAD_URL, body);
        toast.success(message);

        return data;
    } catch (error) {
        if (error instanceof ValidationError) {
            toast.error(Object.values(error.errors)[0][0]);
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Dosya yüklenemedi.');
        }

        return null;
    } finally {
        setBusy(root, false);
    }
}

/**
 * Kırpma gerekiyorsa modalı açar, sonra yükler. Kullanıcı kırpmayı iptal
 * ederse null döner ve hiçbir şey yüklenmez.
 */
export async function uploadWithCrop(root, file) {
    if (! isAcceptedFile(root, file)) {
        toast.error(root.dataset.mediaAccept === 'video'
            ? 'Bu alan yalnızca video kabul eder.'
            : 'Bu alan yalnızca görsel kabul eder.');

        return null;
    }

    const target = presetOf(root);

    // SVG kırpılamaz; preset olsa bile doğrudan yüklenir.
    if (! target || file.type === 'image/svg+xml' || ! file.type.startsWith('image/')) {
        return uploadFile(root, file, null);
    }

    const crop = await cropModal.open(file, target);

    return crop ? uploadFile(root, file, crop) : null;
}
