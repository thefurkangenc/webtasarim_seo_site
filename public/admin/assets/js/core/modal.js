/**
 * AJAX modal. İçerik sunucudan Blade parçası olarak çekilip #ajax-modal-body
 * içine basılır.
 *
 * Beklenen iskelet: resources/views/admin/layout/modals/ajax-modal.blade.php
 *   #ajax-modal        kök (.add-new-popup, .active ile açılır)
 *   #ajax-modal-title  başlık
 *   #ajax-modal-body   içerik
 *   [data-modal-close] kapatma tetikleyicileri
 *
 * İskelet sayfada yoksa aynı markup çalışma anında oluşturulur.
 */

import { http, HttpError, ValidationError } from './http.js';
import { clearErrors, setLoading, showErrors } from './form.js';
import { toast } from './toast.js';

const SKELETON = `
    <div class="popup-dialog flex transition-all max-w-[550px] min-h-full items-center mx-auto">
        <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                <div class="trezo-card-title">
                    <h5 class="!mb-0" id="ajax-modal-title"></h5>
                </div>
                <div class="trezo-card-subtitle">
                    <button type="button" data-modal-close
                        class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                        <i class="ri-close-fill"></i>
                    </button>
                </div>
            </div>
            <div class="trezo-card-content pb-[20px] md:pb-[25px]" id="ajax-modal-body"></div>
        </div>
    </div>`;

export class AjaxModal {
    constructor(selector = '#ajax-modal') {
        this.root = document.querySelector(selector) ?? this.createRoot(selector);

        if (! this.root.querySelector('#ajax-modal-body')) {
            this.root.innerHTML = SKELETON;
        }

        this.dialog = this.root.querySelector('.popup-dialog');
        this.body = this.root.querySelector('#ajax-modal-body');
        this.titleElement = this.root.querySelector('#ajax-modal-title');
        this.defaultWidth = this.dialog?.className.match(/max-w-\[[^\]]+\]/)?.[0] ?? 'max-w-[550px]';
        this.submitHandler = null;

        this.bind();
    }

    createRoot(selector) {
        const root = document.createElement('div');
        root.id = selector.replace('#', '');
        root.className = 'add-new-popup z-[999] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        document.body.append(root);

        return root;
    }

    bind() {
        this.root.addEventListener('click', (event) => {
            if (event.target === this.root || event.target.closest('[data-modal-close]')) {
                this.close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // İçerik her açılışta yeniden basıldığı için submit'i kökte dinliyoruz.
        this.root.addEventListener('submit', (event) => {
            if (! this.submitHandler) {
                return;
            }

            event.preventDefault();
            this.handleSubmit(event.target);
        });
    }

    isOpen() {
        return this.root.classList.contains('active');
    }

    /**
     * @param {string} url  Blade parçası döndüren uç nokta
     * @param {{title?: string, width?: string}} options
     */
    async open(url, options = {}) {
        this.titleElement.textContent = options.title ?? '';
        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.setWidth(options.width);
        this.show();

        try {
            this.body.innerHTML = await http.html(url);
            this.body.querySelector('input:not([type=hidden]), select, textarea')?.focus();

            // core/select.js, core/datepicker.js, core/seo-field.js gibi
            // modüller bunu dinleyip kendi alanlarını sayfa JS'i beklemeden kurar.
            this.body.dispatchEvent(new CustomEvent('admin:content-loaded', { bubbles: true }));
        } catch (error) {
            this.close();
            toast.error(error instanceof HttpError ? error.message : 'İçerik yüklenemedi.');
        }
    }

    setWidth(width) {
        if (! this.dialog) {
            return;
        }

        this.dialog.classList.remove(this.defaultWidth);
        this.dialog.classList.remove(...[...this.dialog.classList].filter((c) => c.startsWith('max-w-[')));
        this.dialog.classList.add(width ?? this.defaultWidth);
    }

    show() {
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');
    }

    close() {
        this.root.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
        this.body.innerHTML = '';
    }

    /**
     * Modal içindeki formun submit'ini devralır. Yükleniyor durumu,
     * doğrulama hataları ve beklenmeyen hatalar burada karşılanır —
     * verdiğin fonksiyon sadece isteği atıp sonucu işler.
     *
     * @param {(form: HTMLFormElement) => Promise<void>} handler
     */
    onSubmit(handler) {
        this.submitHandler = handler;
    }

    async handleSubmit(form) {
        const button = form.querySelector('[type=submit]')
            ?? this.root.querySelector('[type=submit][form="' + form.id + '"]');

        clearErrors(form);
        setLoading(button, true);

        try {
            await this.submitHandler(form);
        } catch (error) {
            if (error instanceof ValidationError) {
                showErrors(form, error.errors);
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Beklenmeyen bir hata oluştu.');
            }
        } finally {
            setLoading(button, false);
        }
    }
}
