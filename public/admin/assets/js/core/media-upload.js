/**
 * Görsel alanlarının paylaştığı yükleme ilkeleri.
 *
 * Tekil alan (core/media-field.js) ve çoklu galeri (core/media-gallery.js)
 * aynı yükleme akışını kullanır: preset varsa kırpma modalı açılır, dosya
 * /admin/media/upload'a gider, sonuç medya nesnesi olarak döner. Burası
 * hiçbir şey render etmez — çağıran taraf sonucu kendi arayüzüne yazar.
 */

import { http, HttpError, ValidationError } from './http.js';
import { cropModal } from './cropper.js';
import { toast } from './toast.js';

const UPLOAD_URL = '/admin/media/upload';

/** Alanın kırpma hedefi; preset tanımlı değilse null (kırpma yok). */
export function presetOf(root) {
    const { mediaPreset, mediaWidth, mediaHeight, mediaLabel } = root.dataset;

    return mediaWidth && mediaHeight
        ? { preset: mediaPreset, width: Number(mediaWidth), height: Number(mediaHeight), label: mediaLabel }
        : null;
}

export function setBusy(root, busy) {
    root.querySelector('[data-media-busy]')?.classList.toggle('hidden', ! busy);
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
    const target = presetOf(root);

    // SVG kırpılamaz; preset olsa bile doğrudan yüklenir.
    if (! target || file.type === 'image/svg+xml' || ! file.type.startsWith('image/')) {
        return uploadFile(root, file, null);
    }

    const crop = await cropModal.open(file, target);

    return crop ? uploadFile(root, file, crop) : null;
}
