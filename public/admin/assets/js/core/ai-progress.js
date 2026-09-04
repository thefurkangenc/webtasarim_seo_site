/**
 * Yapay zeka üretimini "arka planda bırak"tıktan sonra sayfada gösterilen
 * ilerleme kartı. `aiGenerator.open()` `{ background: true, id }` ile
 * çözüldüğünde `track(id)` çağrılır; kayıt `localStorage`'a yazılır, böylece
 * sayfa yenilense/kapatılıp yeniden açılsa bile (aynı modül kaydı için)
 * hâlâ süren ya da tamamlanmış üretim kaybolmaz.
 *
 *   const progress = new AiProgress(anchorElement, `blog-ai:${blogId ?? 'new'}`, (output) => {
 *       // alanları doldur
 *   });
 *
 *   const result = await aiGenerator.open('blog.content', { defaults: { title } });
 *   if (result?.background) { progress.track(result.id); }
 *   else if (result) { doldur(result); }
 */

import { http, HttpError } from './http.js';

const POLL_INTERVAL = 2000;

// Tailwind derleyicisi kaynak dosyaları tarar; class adları şablon
// değişkeniyle kurulursa (`bg-${tone}-100` gibi) derlenmiş çıktıda bulunamaz
// ve sessizce hiçbir şey uygulamaz. Bu yüzden her durum için tam class
// dizisi burada literal olarak yazılıyor (bkz. ai-provider/index.js'teki
// aynı desenin BADGES sabiti).
const STATES = {
    running: {
        icon: 'auto_awesome',
        iconWrap: 'bg-primary-100 dark:bg-[#15203c] text-primary-500',
        iconSpin: 'animate-spin',
        track: 'bg-primary-100 dark:bg-[#172036]',
        bar: 'bg-primary-500 w-[55%] animate-pulse',
    },
    completed: {
        icon: 'check_circle',
        iconWrap: 'bg-success-100 dark:bg-[#15203c] text-success-500',
        iconSpin: '',
        track: 'bg-success-100 dark:bg-[#172036]',
        bar: 'bg-success-500 w-full',
    },
    failed: {
        icon: 'error',
        iconWrap: 'bg-danger-100 dark:bg-[#15203c] text-danger-500',
        iconSpin: '',
        track: 'bg-danger-100 dark:bg-[#172036]',
        bar: 'bg-danger-500 w-full',
    },
};

export class AiProgress {
    /**
     * @param {HTMLElement} anchor  kart bu elemandan hemen ÖNCE eklenir
     * @param {string} storageKey  arka plandaki üretimi hatırlamak için tekil anahtar
     * @param {(output: object) => void} onComplete  üretim tamamlanınca çağrılır (alanları doldurmak için)
     */
    constructor(anchor, storageKey, onComplete) {
        this.anchor = anchor;
        this.storageKey = `ai-progress:${storageKey}`;
        this.onComplete = onComplete;
        this.card = null;
        this.timer = null;

        const savedId = localStorage.getItem(this.storageKey);

        if (savedId) {
            this.track(Number(savedId));
        }
    }

    track(id) {
        localStorage.setItem(this.storageKey, String(id));
        this.render('running', 'Yapay zeka içerik üretiyor...', 'Bu birkaç dakika sürebilir, sayfadan ayrılabilirsiniz.');
        this.poll(id);
    }

    render(state, title, subtitle) {
        const s = STATES[state];

        if (! this.card) {
            this.card = document.createElement('div');
            this.card.dataset.aiProgress = '';
            this.card.className = 'trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md';
            this.anchor.insertAdjacentElement('beforebegin', this.card);
        }

        this.card.innerHTML = `
            <div class="flex items-start gap-[15px]">
                <div class="shrink-0 w-[46px] h-[46px] rounded-md flex items-center justify-center ${s.iconWrap}">
                    <i class="material-symbols-outlined !text-[24px] ${s.iconSpin}">${s.icon}</i>
                </div>
                <div class="grow min-w-0">
                    <p class="!mb-[3px] font-medium text-black dark:text-white">${title}</p>
                    <p class="!mb-[12px] text-xs text-gray-500 dark:text-gray-400 break-words">${subtitle}</p>
                    <div class="w-full h-[6px] rounded-full overflow-hidden ${s.track}">
                        <div class="h-full rounded-full transition-all ${s.bar}"></div>
                    </div>
                </div>
                ${state !== 'running' ? `
                    <button type="button" data-ai-progress-dismiss title="Kapat"
                        class="shrink-0 text-gray-400 hover:text-primary-500 transition-all leading-none">
                        <i class="ri-close-fill text-lg"></i>
                    </button>` : ''}
            </div>`;

        this.card.querySelector('[data-ai-progress-dismiss]')?.addEventListener('click', () => this.remove());
    }

    poll(id) {
        clearTimeout(this.timer);

        this.timer = setTimeout(async () => {
            try {
                const { data } = await http.get(`/admin/ai/generate/${id}`);

                if (! data.finished) {
                    if (data.status === 'running') {
                        this.render('running', 'Yapay zeka içerik üretiyor...', 'Model yazıyor...');
                    }

                    this.poll(id);

                    return;
                }

                localStorage.removeItem(this.storageKey);

                if (data.status === 'completed') {
                    this.render('completed', 'İçerik üretildi', 'Alanlar dolduruldu.');
                    this.onComplete(data.output);
                    setTimeout(() => this.remove(), 4000);
                } else {
                    this.render('failed', 'Üretim tamamlanamadı', data.error ?? 'Bilinmeyen bir hata oluştu.');
                }
            } catch (error) {
                // Geçici bir ağ hatası olabilir — takip kaydı hâlâ localStorage'da,
                // sayfa yenilenince ya da bir sonraki denemede devam eder.
                this.render(
                    'failed',
                    'Durum sorgulanamadı',
                    error instanceof HttpError ? error.message : 'Sayfayı yenileyip tekrar deneyin.',
                );
            }
        }, POLL_INTERVAL);
    }

    remove() {
        clearTimeout(this.timer);
        this.card?.remove();
        this.card = null;
    }
}
