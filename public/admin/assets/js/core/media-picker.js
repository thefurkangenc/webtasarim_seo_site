/**
 * Medya kütüphanesinden dosya seçtiren modal.
 *
 *   const media = await mediaPicker.open();   // seçilen medya ya da null
 *
 * İçerik /admin/media/picker'dan çekilir; davranışı MediaBrowser verir.
 * Form modalının (z-1400) üstünde açılır. Tüm modal katmanı TinyMCE'nin
 * kendi taşan araç çubuğunun (`.tox-tinymce-aux`, z-index 1300) üstünde
 * kalacak şekilde 1400'den başlar — editör içinden açılan bu picker'da
 * daha düşük bir değer, TinyMCE'nin "..." araç çubuğu altında kalırdı.
 */

import { http, HttpError } from './http.js';
import { MediaBrowser } from './media-browser.js';
import { toast } from './toast.js';

const TEMPLATE = `
<div class="popup-dialog flex transition-all max-w-[1100px] min-h-full items-center mx-auto">
    <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Medya Kütüphanesi</h5>
            </div>
            <button type="button" data-picker-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                <i class="ri-close-fill"></i>
            </button>
        </div>
        <div class="trezo-card-content pb-[20px]" data-picker-body></div>
    </div>
</div>`;

class MediaPicker {
    constructor() {
        this.root = null;
        this.resolver = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'media-picker-modal';
        root.className = 'add-new-popup z-[1404] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = TEMPLATE;
        document.body.append(root);

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-picker-close]')) {
                this.settle(null);
            }
        });

        document.addEventListener('keydown', (event) => {
            // Picker artık kendi içinde crop/prompt/taşı/önizleme/onay
            // diyalogları açabiliyor (context menu ile yönetim) — bunlardan
            // biri üstteyken Esc önce onu kapatmalı, alttaki picker'ı değil.
            if (event.key === 'Escape' && this.resolver && ! this.hasOverlay()) {
                this.settle(null);
            }
        });

        return root;
    }

    hasOverlay() {
        return ['crop-modal', 'admin-prompt', 'folder-picker', 'media-preview', 'admin-confirm']
            .some((id) => document.getElementById(id)?.classList.contains('active'));
    }

    /** @returns {Promise<object|null>} */
    async open() {
        this.root ??= this.build();

        const body = this.root.querySelector('[data-picker-body]');
        body.innerHTML = '<div class="py-[60px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';

        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        try {
            body.innerHTML = await http.html('/admin/media/picker');
            // core/select.js gibi dinleyiciler (tür filtresi <select data-choices>)
            // kendini bu olayla kurar — AjaxModal'ın yaptığı gibi.
            body.dispatchEvent(new CustomEvent('admin:content-loaded', { bubbles: true }));

            new MediaBrowser(body.querySelector('[data-media-browser]'), {
                onSelect: (media) => this.settle(media),
            });
        } catch (error) {
            this.settle(null);
            toast.error(error instanceof HttpError ? error.message : 'Medya kütüphanesi açılamadı.');

            return null;
        }

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }

    settle(result) {
        this.root.classList.remove('active');

        // Alttaki form modalı hâlâ açıksa gövde kilidini kaldırma.
        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }

        const resolve = this.resolver;
        this.resolver = null;
        this.root.querySelector('[data-picker-body]').innerHTML = '';
        resolve?.(result);
    }
}

export const mediaPicker = new MediaPicker();
