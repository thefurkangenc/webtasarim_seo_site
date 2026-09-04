/**
 * Kırpma modalı. Bir dosya alır, kullanıcıya kırpma arayüzünü gösterir ve
 * onaylanırsa kırpım koordinatlarını döndürür.
 *
 *   const result = await cropModal.open(file, { preset: 'blog.cover', width: 1200, height: 630 });
 *   // result === null  -> kullanıcı vazgeçti
 *   // result === { x, y, width, height, rotate, scaleX, scaleY }
 *
 * Cropper.js ilk kullanımda tembel yüklenir; görsel alanı olmayan sayfalar
 * bu maliyeti ödemez.
 */

const CROPPER_SRC = '/admin/assets/js/vendor/cropper/cropper.min.js';

let cropperLoader = null;

function ensureCropper() {
    if (window.Cropper) {
        return Promise.resolve(window.Cropper);
    }

    cropperLoader ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = CROPPER_SRC;
        script.onload = () => resolve(window.Cropper);
        script.onerror = () => reject(new Error('Kırpma aracı yüklenemedi.'));
        document.head.append(script);
    });

    return cropperLoader;
}

const TOOL = 'w-[36px] h-[36px] inline-flex items-center justify-center rounded-md bg-white dark:bg-[#0c1427] border border-gray-200 dark:border-[#172036] text-black dark:text-white transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500 disabled:opacity-40';

const TEMPLATE = `
<div class="popup-dialog flex transition-all max-w-[1320px] min-h-full items-center mx-auto">
    <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">

        <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
            <div class="trezo-card-title flex items-center gap-[12px] flex-wrap">
                <h5 class="!mb-0">Görseli Kırp</h5>
                <span data-crop-preset class="text-[10px] font-medium py-[2px] px-[8px] text-primary-500 bg-primary-50 dark:bg-[#ffffff14] inline-block rounded-sm"></span>
                <span data-crop-ratio class="text-[10px] font-medium py-[2px] px-[8px] text-purple-500 bg-purple-100 dark:bg-[#ffffff14] inline-block rounded-sm"></span>
            </div>
            <button type="button" data-crop-cancel class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                <i class="ri-close-fill"></i>
            </button>
        </div>

        <div class="trezo-card-content">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-[20px]">

                <div class="bg-[#0a0e19] rounded-md overflow-hidden border border-gray-100 dark:border-[#172036]">
                    <div class="h-[540px] flex items-center justify-center">
                        <img data-crop-image alt="" class="max-w-full block">
                    </div>
                </div>

                <div class="flex flex-col gap-[15px]">
                    <div>
                        <span class="block text-xs font-medium uppercase text-gray-400 mb-[8px]">Önizleme</span>
                        <div data-crop-preview class="w-full overflow-hidden rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c]"></div>
                    </div>

                    <div class="rounded-md border border-gray-100 dark:border-[#172036] divide-y divide-gray-100 dark:divide-[#172036]">
                        <div class="flex items-center justify-between px-[12px] py-[8px]">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Seçim</span>
                            <strong data-crop-selection class="text-xs text-black dark:text-white">—</strong>
                        </div>
                        <div class="flex items-center justify-between px-[12px] py-[8px]">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Çıktı</span>
                            <strong data-crop-output class="text-xs text-black dark:text-white">—</strong>
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-100 dark:border-[#172036] p-[12px]">
                        <div class="flex items-center justify-between mb-[8px]">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Yakınlaştırma</span>
                            <strong data-crop-zoom class="text-xs text-black dark:text-white">100%</strong>
                        </div>
                        <input type="range" data-crop-zoom-range min="0" max="3" step="0.01" value="0"
                            class="w-full h-[4px] rounded-full appearance-none cursor-pointer bg-gray-200 dark:bg-[#172036] accent-primary-500">
                    </div>

                    <p data-crop-warning class="hidden text-[11px] leading-[1.5] text-warning-600 bg-warning-100 dark:bg-[#ffffff14] rounded-md px-[10px] py-[8px]"></p>
                </div>
            </div>

            <div class="mt-[20px] flex items-center gap-[6px] flex-wrap bg-gray-50 dark:bg-[#15203c] rounded-md p-[10px]">
                <button type="button" data-crop-tool="zoom-out" title="Uzaklaştır"         class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">zoom_out</i></button>
                <button type="button" data-crop-tool="zoom-in"  title="Yakınlaştır"        class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">zoom_in</i></button>
                <span class="w-px h-[24px] bg-gray-200 dark:bg-[#172036] mx-[4px]"></span>
                <button type="button" data-crop-tool="rotate-left"  title="Sola döndür"    class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">rotate_left</i></button>
                <button type="button" data-crop-tool="rotate-right" title="Sağa döndür"    class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">rotate_right</i></button>
                <span class="w-px h-[24px] bg-gray-200 dark:bg-[#172036] mx-[4px]"></span>
                <button type="button" data-crop-tool="flip-x" title="Yatay çevir"          class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">swap_horiz</i></button>
                <button type="button" data-crop-tool="flip-y" title="Dikey çevir"          class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">swap_vert</i></button>
                <span class="w-px h-[24px] bg-gray-200 dark:bg-[#172036] mx-[4px]"></span>
                <button type="button" data-crop-tool="center" title="Ortala"               class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">filter_center_focus</i></button>
                <button type="button" data-crop-tool="reset" title="Sıfırla"               class="${TOOL}"><i class="material-symbols-outlined !text-[19px]">restart_alt</i></button>

                <span class="ltr:ml-auto rtl:mr-auto text-xs text-gray-500 dark:text-gray-400 hidden md:inline">
                    Ok tuşları ile kaydır · R sıfırla · Esc kapat
                </span>
            </div>
        </div>

        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
            <button type="button" data-crop-cancel
                class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md bg-gray-100 dark:bg-[#172036] border border-gray-200 dark:border-[#172036] hover:bg-gray-200 dark:hover:bg-[#1f2941]">
                Vazgeç
            </button>
            <button type="button" data-crop-confirm
                class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                Kırp ve Yükle
            </button>
        </div>
    </div>
</div>`;

class CropModal {
    constructor() {
        this.root = null;
        this.cropper = null;
        this.resolver = null;
        this.objectUrl = null;
        this.target = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'crop-modal';
        root.className = 'add-new-popup z-[1005] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = TEMPLATE;
        document.body.append(root);

        root.addEventListener('click', (event) => {
            if (event.target.closest('[data-crop-cancel]')) {
                this.settle(null);

                return;
            }

            const tool = event.target.closest('[data-crop-tool]');

            if (tool) {
                this.applyTool(tool.dataset.cropTool);

                return;
            }

            if (event.target.closest('[data-crop-confirm]')) {
                this.confirm();
            }
        });

        // Slider fare ile sürüklenirken sürekli 'input' tetikler; anlık zoom.
        root.addEventListener('input', (event) => {
            const range = event.target.closest('[data-crop-zoom-range]');

            if (range && this.cropper) {
                this.cropper.zoomTo(Number(range.value));
            }
        });

        document.addEventListener('keydown', (event) => this.onKeydown(event));

        return root;
    }

    onKeydown(event) {
        if (! this.resolver || ! this.cropper) {
            return;
        }

        const step = event.shiftKey ? 10 : 1;
        const moves = {
            ArrowLeft: [-step, 0], ArrowRight: [step, 0],
            ArrowUp: [0, -step], ArrowDown: [0, step],
        };

        if (moves[event.key]) {
            event.preventDefault();
            this.cropper.move(...moves[event.key]);

            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.settle(null);
        }

        if (event.key.toLowerCase() === 'r') {
            this.applyTool('reset');
        }
    }

    applyTool(tool) {
        const actions = {
            'zoom-in': () => this.cropper.zoom(0.1),
            'zoom-out': () => this.cropper.zoom(-0.1),
            'rotate-left': () => this.cropper.rotate(-90),
            'rotate-right': () => this.cropper.rotate(90),
            'flip-x': () => this.cropper.scaleX(-(this.cropper.getData().scaleX || 1)),
            'flip-y': () => this.cropper.scaleY(-(this.cropper.getData().scaleY || 1)),
            center: () => this.centerCropBox(),
            reset: () => this.cropper.reset(),
        };

        actions[tool]?.();
    }

    /** Zoom/döndürmeye dokunmadan seçim kutusunu görünür alanın ortasına taşır. */
    centerCropBox() {
        const canvas = this.cropper.getCanvasData();
        const box = this.cropper.getCropBoxData();

        this.cropper.setCropBoxData({
            left: canvas.left + (canvas.width - box.width) / 2,
            top: canvas.top + (canvas.height - box.height) / 2,
        });
    }

    /**
     * @param {File} file
     * @param {{preset?: string, label?: string, width: number, height: number}} options
     * @returns {Promise<object|null>} kırpım verisi ya da vazgeçildiyse null
     */
    async open(file, options) {
        const Cropper = await ensureCropper();

        this.root ??= this.build();
        this.target = options;

        const $ = (selector) => this.root.querySelector(selector);

        $('[data-crop-preset]').textContent = options.label ?? 'Serbest';
        $('[data-crop-ratio]').textContent = `${options.width} × ${options.height}`;
        $('[data-crop-warning]').classList.add('hidden');

        // Önizleme kutusunu hedef orana getir.
        const preview = $('[data-crop-preview]');
        preview.style.aspectRatio = `${options.width} / ${options.height}`;

        const image = $('[data-crop-image]');
        this.revokeUrl();
        this.objectUrl = URL.createObjectURL(file);
        image.src = this.objectUrl;

        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        this.cropper?.destroy();
        this.cropper = new Cropper(image, {
            aspectRatio: options.width / options.height,
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            responsive: true,
            preview,
            crop: () => this.updateReadout(),
            ready: () => this.updateReadout(),
            zoom: () => this.updateReadout(),
        });

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }

    updateReadout() {
        if (! this.cropper) {
            return;
        }

        const data = this.cropper.getData(true);
        const $ = (selector) => this.root.querySelector(selector);
        const ratio = this.cropper.getImageData().width / this.cropper.getImageData().naturalWidth;

        $('[data-crop-selection]').textContent = `${data.width} × ${data.height} px`;
        $('[data-crop-output]').textContent = `${this.target.width} × ${this.target.height} px`;
        $('[data-crop-zoom]').textContent = `${Math.round(ratio * 100)}%`;

        // Slider'ı fare tekerleği/pinch ile yapılan zoom'a senkron tut — değer
        // ataması 'input' olayı fırlatmaz, döngüye girmez.
        const range = $('[data-crop-zoom-range]');

        if (range && document.activeElement !== range) {
            range.value = ratio;
        }

        // Seçim hedeften küçükse sunucu büyütmek zorunda kalır — uyar.
        const warning = $('[data-crop-warning]');
        const tooSmall = data.width < this.target.width;

        warning.classList.toggle('hidden', ! tooSmall);

        if (tooSmall) {
            warning.textContent = `Seçtiğiniz alan ${data.width}px genişliğinde; hedef ${this.target.width}px. `
                + 'Görsel büyütüleceği için netlik kaybı olabilir.';
        }
    }

    confirm() {
        const data = this.cropper.getData(true);

        this.settle({
            x: data.x,
            y: data.y,
            width: data.width,
            height: data.height,
            rotate: data.rotate ?? 0,
            scaleX: data.scaleX ?? 1,
            scaleY: data.scaleY ?? 1,
        });
    }

    settle(result) {
        if (! this.resolver) {
            return;
        }

        this.root.classList.remove('active');
        document.body.classList.remove('overflow-hidden');

        this.cropper?.destroy();
        this.cropper = null;
        this.revokeUrl();

        const resolve = this.resolver;
        this.resolver = null;
        resolve(result);
    }

    revokeUrl() {
        if (this.objectUrl) {
            URL.revokeObjectURL(this.objectUrl);
            this.objectUrl = null;
        }
    }
}

export const cropModal = new CropModal();
