/**
 * Yapay zeka üretim modalı.
 *
 *   const output = await aiGenerator.open('blog.content', { defaults: { title: '...' } });
 *   if (output) { ...alanları doldur... }
 *
 * Üretim kuyrukta çalışır: istek bir takip kaydı açar, bu modal kaydın
 * durumunu sorar. Kullanıcı beklerken modalı kapatabilir — iş kuyrukta
 * devam eder, sonuç yalnızca bu ekrana yansımaz.
 */

import { clearErrors, setLoading, showErrors } from './form.js';
import { http, HttpError, ValidationError } from './http.js';
import { toast } from './toast.js';

const POLL_INTERVAL = 2000;
// Bu süre boyunca "queued" kalırsa kuyruk işçisi muhtemelen çalışmıyordur.
const WORKER_WARNING_AFTER = 20;

const TEMPLATE = `
<div class="popup-dialog flex transition-all max-w-[620px] min-h-full items-center mx-auto">
    <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Yapay Zeka ile Oluştur</h5>
            </div>
            <button type="button" data-ai-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                <i class="ri-close-fill"></i>
            </button>
        </div>
        <div class="trezo-card-content pb-[20px] md:pb-[25px]" data-ai-body></div>
    </div>
</div>`;

class AiGenerator {
    constructor() {
        this.root = null;
        this.body = null;
        this.resolver = null;
        this.timer = null;
        this.elapsedTimer = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'ai-generator-modal';
        root.className = 'add-new-popup z-[1005] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = TEMPLATE;
        document.body.append(root);

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-ai-close]')) {
                this.settle(null);
            }

            // Form hâlâ kuyrukta/çalışırken kapatılırsa iş kuyrukta devam eder;
            // sayfa bunu `{ background: true, id }` ile öğrenip kendi
            // ilerleme kartını (bkz. core/ai-progress.js) başlatabilir.
            if (event.target.closest('[data-ai-background]')) {
                this.settle({ background: true, id: this.generationId });
            }

            if (event.target.closest('[data-ai-retry]')) {
                this.panel('form');
            }
        });

        root.addEventListener('submit', (event) => {
            event.preventDefault();
            this.submit(event.target);
        });

        this.body = root.querySelector('[data-ai-body]');

        return root;
    }

    /**
     * @param {string} key Prompt şablonu anahtarı (blog.content)
     * @param {{defaults?: Record<string, string>}} options
     * @returns {Promise<object|{background: true, id: number}|null>} Üretilen
     *   JSON, arka planda bırakıldıysa takip kaydı, yoksa null
     */
    async open(key, options = {}) {
        this.root ??= this.build();
        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.generationId = null;
        this.show();

        try {
            this.body.innerHTML = await http.html(`/admin/ai/generate/${encodeURIComponent(key)}/form`);
            this.applyDefaults(options.defaults ?? {});
        } catch (error) {
            this.hide();
            toast.error(error instanceof HttpError ? error.message : 'Üretim ekranı açılamadı.');

            return null;
        }

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }

    /** Modül formundan gelen değerler (başlık, kategori) alanlara önceden yazılır. */
    applyDefaults(defaults) {
        Object.entries(defaults).forEach(([key, value]) => {
            const input = this.body.querySelector(`[name="input[${key}]"]`);

            if (input && value) {
                input.value = value;
            }
        });
    }

    panel(name) {
        this.body.querySelectorAll('[data-ai-panel]').forEach((element) => {
            element.classList.toggle('hidden', element.dataset.aiPanel !== name);
        });
    }

    async submit(form) {
        const button = form.querySelector('[type=submit]');

        clearErrors(form);
        setLoading(button, true);

        try {
            const { data } = await http.post('/admin/ai/generate', new FormData(form));

            this.generationId = data.id;
            this.panel('progress');
            this.startElapsed();
            this.poll(data.id);
        } catch (error) {
            if (error instanceof ValidationError) {
                showErrors(form, error.errors);
            } else {
                this.fail(error instanceof HttpError ? error.message : 'Üretim başlatılamadı.');
            }
        } finally {
            setLoading(button, false);
        }
    }

    startElapsed() {
        const output = this.body.querySelector('[data-ai-elapsed]');
        const warning = this.body.querySelector('[data-ai-worker-warning]');
        let seconds = 0;

        clearInterval(this.elapsedTimer);
        warning?.classList.add('hidden');

        this.elapsedTimer = setInterval(() => {
            seconds += 1;
            output.textContent = seconds;

            if (seconds === WORKER_WARNING_AFTER && this.status === 'queued') {
                warning?.classList.remove('hidden');
            }
        }, 1000);
    }

    poll(id) {
        clearTimeout(this.timer);

        this.timer = setTimeout(async () => {
            try {
                const { data } = await http.get(`/admin/ai/generate/${id}`);

                this.status = data.status;

                if (data.status === 'running') {
                    this.body.querySelector('[data-ai-status]').textContent = 'Model yazıyor...';
                    this.body.querySelector('[data-ai-worker-warning]')?.classList.add('hidden');
                }

                if (! data.finished) {
                    this.poll(id);

                    return;
                }

                if (data.status === 'completed') {
                    this.settle(data.output);

                    return;
                }

                this.fail(data.error ?? 'Üretim tamamlanamadı.');
            } catch (error) {
                this.fail(error instanceof HttpError ? error.message : 'Durum sorgulanamadı.');
            }
        }, POLL_INTERVAL);
    }

    fail(message) {
        this.stopTimers();
        this.panel('error');
        this.body.querySelector('[data-ai-error]').textContent = message;
    }

    stopTimers() {
        clearTimeout(this.timer);
        clearInterval(this.elapsedTimer);
        this.status = null;
    }

    show() {
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');
    }

    hide() {
        this.root?.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    }

    settle(output) {
        this.stopTimers();
        this.hide();

        const resolve = this.resolver;
        this.resolver = null;
        resolve?.(output);
    }
}

export const aiGenerator = new AiGenerator();
