/**
 * <x-admin::form.video> davranışı.
 *
 * Bir kayıtta video ya gömülü bir adrestir (YouTube/Vimeo) ya da kütüphaneye
 * yüklenmiş bir dosyadır — ikisi birlikte anlamsızdır, hangisinin gösterileceği
 * belirsiz kalır. Bu yüzden sekme değiştirmek diğer alanı TEMİZLER: forma her
 * zaman tek bir kaynak gider ve sunucu tarafının öncelik kuralı uydurmasına
 * gerek kalmaz.
 */

import { mediaPicker } from './media-picker.js';
import { uploadFile } from './media-upload.js';
import { toast } from './toast.js';

/** Adresten YouTube/Vimeo kimliğini çıkarır — App\Support\VideoEmbed'in eşi. */
function parseEmbed(url) {
    const value = String(url ?? '').trim();

    if (value === '') {
        return null;
    }

    const youtube = value.match(/youtu\.be\/([A-Za-z0-9_-]{6,})/i)
        ?? value.match(/youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/|shorts\/|live\/)([A-Za-z0-9_-]{6,})/i);

    if (youtube) {
        return { provider: 'YouTube', embed: `https://www.youtube-nocookie.com/embed/${youtube[1]}` };
    }

    const vimeo = value.match(/vimeo\.com\/(?:video\/|channels\/[\w]+\/|groups\/[^/]+\/videos\/)?(\d+)/i);

    return vimeo
        ? { provider: 'Vimeo', embed: `https://player.vimeo.com/video/${vimeo[1]}` }
        : null;
}

class VideoField {
    constructor(root) {
        this.root = root;
        this.urlInput = root.querySelector('[data-video-url]');
        this.mediaInput = root.querySelector('[data-video-media]');
        this.fileInput = root.querySelector('[data-video-file]');
        this.status = root.querySelector('[data-video-status]');
        this.preview = root.querySelector('[data-video-preview]');

        this.bind();
        this.setTab(this.mediaInput.value ? 'file' : 'link', false);
        this.renderStatus();
    }

    bind() {
        this.root.addEventListener('click', (event) => {
            const tab = event.target.closest('[data-video-tab]');

            if (tab) {
                this.setTab(tab.dataset.videoTab);

                return;
            }

            const action = event.target.closest('[data-video-action]')?.dataset.videoAction;

            if (action === 'upload') {
                this.fileInput.click();
            } else if (action === 'library') {
                this.fromLibrary();
            } else if (action === 'clear') {
                this.setMedia(null);
            }
        });

        this.urlInput.addEventListener('input', () => this.renderStatus());
        this.fileInput.addEventListener('change', () => this.upload());
    }

    setTab(tab, clearOther = true) {
        this.root.querySelectorAll('[data-video-tab]').forEach((button) => {
            const active = button.dataset.videoTab === tab;
            button.classList.toggle('bg-primary-500', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-primary-500', active);
            button.classList.toggle('text-black', ! active);
            button.classList.toggle('dark:text-white', ! active);
            button.classList.toggle('border-gray-200', ! active);
            button.classList.toggle('dark:border-[#172036]', ! active);
        });

        this.root.querySelectorAll('[data-video-pane]').forEach((pane) => {
            pane.hidden = pane.dataset.videoPane !== tab;
        });

        if (! clearOther) {
            return;
        }

        // Kullanıcı kaynağı değiştirdi: öbür alan forma gitmesin.
        if (tab === 'link') {
            this.setMedia(null);
        } else {
            this.urlInput.value = '';
            this.renderStatus();
        }
    }

    async fromLibrary() {
        const media = await mediaPicker.open();

        if (! media) {
            return;
        }

        if (! media.is_video) {
            toast.error('Bu alan yalnızca video dosyası kabul eder.');

            return;
        }

        this.setMedia(media);
    }

    async upload() {
        const file = this.fileInput.files?.[0];
        this.fileInput.value = '';

        if (! file) {
            return;
        }

        const media = await uploadFile(this.root, file);

        if (media) {
            this.setMedia(media);
        }
    }

    setMedia(media) {
        this.mediaInput.value = media?.id ?? '';

        this.preview.innerHTML = media
            ? `<video src="${media.url}" controls preload="metadata" class="w-full max-h-[220px] rounded-md bg-black"></video>
               <p class="!mb-0 mt-[8px] text-xs text-gray-500 dark:text-gray-400 truncate">${media.name} · ${media.human_size}</p>`
            : '';

        this.preview.hidden = ! media;
        this.root.querySelector('[data-video-action="clear"]').hidden = ! media;
        this.root.querySelector('[data-video-action="upload"] span').textContent = media ? 'Değiştir' : 'Dosya Seç';
    }

    /** Adres kutusunun altındaki "tanındı/tanınmadı" satırı. */
    renderStatus() {
        const value = this.urlInput.value.trim();

        if (value === '') {
            this.status.hidden = true;

            return;
        }

        const parsed = parseEmbed(value);
        this.status.hidden = false;

        this.status.innerHTML = parsed
            ? `<span class="inline-flex items-center gap-[5px] text-success-600">
                   <i class="material-symbols-outlined !text-[15px]">check_circle</i> ${parsed.provider} videosu tanındı.
               </span>`
            : `<span class="inline-flex items-center gap-[5px] text-warning-600">
                   <i class="material-symbols-outlined !text-[15px]">info</i>
                   Adres tanınmadı — ön yüzde gömülü oynatıcı yerine bağlantı olarak gösterilir.
               </span>`;
    }
}

export function initVideoFields(root = document) {
    root.querySelectorAll('[data-video-field]:not([data-video-ready])').forEach((element) => {
        element.dataset.videoReady = '1';
        new VideoField(element);
    });
}

document.addEventListener('DOMContentLoaded', () => initVideoFields());
document.addEventListener('admin:content-loaded', (event) => initVideoFields(event.target));
