/**
 * Toplu işlem seçimi.
 *
 * Sayfa JS'i iki şey yapar:
 *
 *   1. Satır şablonuna seçim kutusunu ekler:  ${bulkCell(item)}
 *   2. Tabloyu çubuğa bağlar:                 bindBulk({ module: 'blog', body, table })
 *
 * Çubuğun kendisi <x-admin::bulk-bar module="blog" /> ile basılır; butonlar
 * config/bulk-actions.php'den gelir. Bu dosya yalnızca seçim durumunu ve
 * gönderimi yönetir.
 */

import { confirm } from './confirm.js';
import { http, HttpError } from './http.js';
import { toast } from './toast.js';

const CELL = 'ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]';

/** Satırın seçim hücresi. Tablo başlığındaki "tümünü seç" Blade'de. */
export function bulkCell(item) {
    return `<td class="${CELL} w-[46px]">
        <input type="checkbox" data-bulk-id="${item.id}"
            class="w-[15px] h-[15px] align-middle cursor-pointer accent-primary-500">
    </td>`;
}

class BulkSelection {
    constructor({ module, body, table, onDone }) {
        this.bar = document.querySelector(`[data-bulk-bar="${module}"]`);

        // Yetkisi olmayan kullanıcıda çubuk basılmaz; seçim de kurulmaz.
        if (! this.bar) {
            return;
        }

        this.module = module;
        this.body = body;
        this.table = table ?? body.closest('table');
        this.onDone = onDone;
        this.endpoint = this.bar.dataset.bulkEndpoint;
        this.count = this.bar.querySelector('[data-bulk-count]');
        this.selectAll = this.table?.querySelector('[data-bulk-all]');

        this.bind();
    }

    bind() {
        this.body.addEventListener('change', (event) => {
            if (event.target.matches('[data-bulk-id]')) {
                this.sync();
            }
        });

        this.selectAll?.addEventListener('change', () => {
            this.boxes().forEach((box) => {
                box.checked = this.selectAll.checked;
            });

            this.sync();
        });

        this.bar.addEventListener('click', (event) => {
            if (event.target.closest('[data-bulk-clear]')) {
                this.clear();

                return;
            }

            const button = event.target.closest('[data-bulk-action]');

            if (button) {
                this.run(button);
            }
        });
    }

    boxes() {
        return [...this.body.querySelectorAll('[data-bulk-id]')];
    }

    ids() {
        return this.boxes().filter((box) => box.checked).map((box) => Number(box.dataset.bulkId));
    }

    /** Tablo yeniden yüklendiğinde satırlar değişir; seçim sıfırlanır. */
    sync() {
        // Yetkisiz kullanıcıda çubuk yok: seçim de yok, sessizce çık.
        if (! this.bar) {
            return;
        }

        const selected = this.ids().length;
        const total = this.boxes().length;

        this.count.textContent = selected;
        this.bar.classList.toggle('hidden', selected === 0);
        this.bar.classList.toggle('flex', selected > 0);

        if (this.selectAll) {
            this.selectAll.checked = selected > 0 && selected === total;
            this.selectAll.indeterminate = selected > 0 && selected < total;
        }
    }

    clear() {
        if (! this.bar) {
            return;
        }

        this.boxes().forEach((box) => {
            box.checked = false;
        });

        this.sync();
    }

    async run(button) {
        const action = button.dataset.bulkAction;
        const ids = this.ids();
        const input = this.bar.querySelector(`[data-bulk-value="${action}"]`);
        const value = input?.value ?? null;

        if (button.dataset.bulkDanger && ! await confirm(`Seçili ${ids.length} kayıt silinecek. Bu işlem geri alınamaz.`, {
            title: 'Toplu silme',
            accept: 'Evet, sil',
        })) {
            return;
        }

        button.disabled = true;

        try {
            const { message } = await http.post(this.endpoint, { action, ids, value });
            toast.success(message);

            if (input) {
                input.value = '';
            }

            this.clear();
            this.onDone?.();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'İşlem tamamlanamadı.');
        } finally {
            button.disabled = false;
        }
    }
}

/**
 * @param {{module: string, body: HTMLElement, table?: HTMLElement, onDone?: () => void}} options
 * @returns {BulkSelection}
 */
export function bindBulk(options) {
    return new BulkSelection(options);
}
