/**
 * Dosyaya çift tıklayınca açılan popup önizleme. Kendi başına hiçbir şeyi
 * değiştirmez — hangi aksiyona basıldığını döndürür, gerçek işlemi çağıran
 * taraf (`media-browser.js`) yürütür.
 *
 *   const action = await mediaPreview.open(media, { selectable, manageable });
 *   // action: null (kapatıldı) | 'select' | 'edit' | 'recrop' | 'delete'
 */

class MediaPreview {
    constructor() {
        this.root = null;
        this.resolver = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'media-preview';
        root.className = 'add-new-popup z-[1004] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[720px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[15px] flex items-center justify-between">
                        <h5 class="!mb-0 truncate" data-preview-name></h5>
                        <button type="button" data-preview-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500 shrink-0 ltr:ml-[15px] rtl:mr-[15px]">
                            <i class="ri-close-fill"></i>
                        </button>
                    </div>

                    <div class="bg-gray-50 dark:bg-[#15203c] rounded-md overflow-hidden flex items-center justify-center min-h-[240px] max-h-[55vh]">
                        <img data-preview-image class="max-w-full max-h-[55vh] object-contain hidden" alt="">
                        <i data-preview-icon class="material-symbols-outlined !text-[64px] text-gray-400 hidden">draft</i>
                    </div>

                    <p data-preview-meta class="!mb-0 mt-[12px] text-xs text-gray-500 dark:text-gray-400"></p>

                    <div data-preview-actions class="flex items-center gap-[10px] flex-wrap mt-[20px] pt-[20px] border-t border-gray-100 dark:border-[#172036]"></div>
                </div>
            </div>`;

        document.body.append(root);

        root.querySelector('[data-preview-close]').addEventListener('click', () => this.settle(null));
        root.addEventListener('click', (event) => {
            if (event.target === root) {
                this.settle(null);
            }

            const action = event.target.closest('[data-preview-action]');

            if (action) {
                this.settle(action.dataset.previewAction);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.resolver) {
                this.settle(null);
            }
        });

        return root;
    }

    settle(result) {
        if (! this.resolver) {
            return;
        }

        this.root.classList.remove('active');
        document.body.classList.remove('overflow-hidden');

        const resolve = this.resolver;
        this.resolver = null;
        resolve(result);
    }

    /**
     * @param {object} media
     * @param {{selectable?: boolean, manageable?: boolean}} options
     * @returns {Promise<string|null>}
     */
    open(media, options = {}) {
        this.root ??= this.build();

        const $ = (selector) => this.root.querySelector(selector);

        $('[data-preview-name]').textContent = media.name;

        const image = $('[data-preview-image]');
        const icon = $('[data-preview-icon]');

        image.classList.toggle('hidden', ! media.is_image);
        icon.classList.toggle('hidden', media.is_image);

        if (media.is_image) {
            image.src = media.url;
            image.alt = media.alt ?? media.name;
        }

        $('[data-preview-meta]').textContent = media.width
            ? `${media.width}×${media.height} · ${media.human_size} · ${media.created_at ?? ''}`
            : `${media.human_size} · ${media.created_at ?? ''}`;

        const buttons = [];

        if (options.selectable) {
            buttons.push('<button type="button" data-preview-action="select" class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400"><i class="material-symbols-outlined !text-[18px]">check</i> Bu Görseli Seç</button>');
        }

        if (options.manageable) {
            buttons.push('<button type="button" data-preview-action="edit" class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]"><i class="material-symbols-outlined !text-[18px]">edit</i> Düzenle</button>');

            if (media.can_recrop) {
                buttons.push('<button type="button" data-preview-action="recrop" class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]"><i class="material-symbols-outlined !text-[18px]">crop</i> Yeniden Kırp</button>');
            }

            buttons.push('<button type="button" data-preview-action="delete" class="ltr:ml-auto rtl:mr-auto inline-flex items-center gap-[6px] py-[9px] px-[18px] text-danger-500 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-danger-100 dark:hover:bg-[#15203c]"><i class="material-symbols-outlined !text-[18px]">delete</i> Sil</button>');
        }

        $('[data-preview-actions]').innerHTML = buttons.join('');

        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }
}

export const mediaPreview = new MediaPreview();
