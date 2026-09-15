/**
 * <x-admin::form.faqs> davranışı: mevcut FAQ havuzundan çoklu seçim.
 *
 * Alan kendi başına düzenlenmez — tıklanınca bir modal açılır, seçim orada
 * yapılır ve yalnızca modaldaki "Kaydet" ile dışarı yansır ("Vazgeç" ya da
 * kapatma önceki seçimi hiç değiştirmez). Seçilenler gizli input olarak
 * tutulur (name="faqs[]"), form normal FormData ile gönderilir.
 *
 * Liste /admin/faq/datatable'dan gelir — FAQ modülünün kendi liste uç
 * noktası, ayrı bir backend gerekmez. Aynı bileşen ileride başka bir modüle
 * eklendiğinde de bu tek endpoint'i kullanır.
 */

import { escapeHtml, http, HttpError } from './http.js';

const TEMPLATE = `
<div class="popup-dialog flex transition-all max-w-[640px] min-h-full items-center mx-auto">
    <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[16px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Sıkça Sorulan Soru Seç</h5>
            </div>
            <button type="button" data-faq-picker-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                <i class="ri-close-fill"></i>
            </button>
        </div>

        <div class="relative mb-[16px]">
            <input type="text" data-faq-picker-search placeholder="Soru ara..." autocomplete="off"
                class="bg-gray-50 border border-gray-50 h-[42px] rounded-md w-full block text-black ltr:pl-[14px] rtl:pr-[14px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
            <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
        </div>

        <div data-faq-picker-list class="flex flex-col gap-[8px] max-h-[360px] overflow-y-auto pr-[2px]"></div>

        <div class="flex items-center justify-between gap-[12px] mt-[20px] pt-[16px] border-t border-gray-100 dark:border-[#172036]">
            <span data-faq-picker-count class="text-xs text-gray-500 dark:text-gray-400">0 soru seçili</span>
            <div class="flex items-center gap-[10px]">
                <button type="button" data-faq-picker-close
                    class="inline-block py-[9px] px-[22px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    Vazgeç
                </button>
                <button type="button" data-faq-picker-save
                    class="inline-block py-[9px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>`;

/** Bir soru satırı — seçili durumuna göre stil değişir. */
function faqRow(item, isSelected) {
    return `
    <div data-faq-picker-item data-faq-id="${item.id}"
        data-faq-question="${escapeHtml(item.question)}"
        class="flex items-start gap-[10px] p-[12px] rounded-md border cursor-pointer transition-all ${isSelected
            ? 'border-primary-500 bg-primary-50/60 dark:bg-[#15203c]'
            : 'border-gray-100 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#0c1427]'}">
        <i class="material-symbols-outlined !text-[21px] mt-[1px] shrink-0 ${isSelected ? 'text-primary-500' : 'text-gray-300 dark:text-gray-600'}">
            ${isSelected ? 'check_circle' : 'radio_button_unchecked'}
        </i>
        <div class="min-w-0">
            <p class="!mb-0 font-medium text-black dark:text-white">${escapeHtml(item.question)}</p>
            ${item.answer ? `<p class="!mb-0 mt-[3px] text-xs text-gray-500 dark:text-gray-400 line-clamp-2">${escapeHtml(item.answer)}</p>` : ''}
        </div>
    </div>`;
}

class FaqPicker {
    constructor() {
        this.root = null;
        this.resolver = null;
        /** id -> {id, question} — sıra korunur (Map insertion order). */
        this.selection = new Map();
        /** id -> {id, question, answer} — son yüklenen listenin tam verisi;
         *  toggle() satırı yeniden çizerken DOM'dan kazımak yerine buradan okur. */
        this.items = new Map();
        this.searchTimer = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'faq-picker-modal';
        root.className = 'add-new-popup z-[1404] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = TEMPLATE;
        document.body.append(root);

        this.list = root.querySelector('[data-faq-picker-list]');
        this.search = root.querySelector('[data-faq-picker-search]');
        this.count = root.querySelector('[data-faq-picker-count]');

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-faq-picker-close]')) {
                this.settle(null);

                return;
            }

            if (event.target.closest('[data-faq-picker-save]')) {
                this.settle([...this.selection.values()]);

                return;
            }

            const item = event.target.closest('[data-faq-picker-item]');

            if (item) {
                this.toggle(item);
            }
        });

        this.search.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.load(this.search.value.trim()), 250);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.resolver) {
                this.settle(null);
            }
        });

        return root;
    }

    toggle(item) {
        const id = Number(item.dataset.faqId);
        const data = this.items.get(id) ?? { id, question: item.dataset.faqQuestion, answer: '' };

        if (this.selection.has(id)) {
            this.selection.delete(id);
        } else {
            this.selection.set(id, { id, question: data.question });
        }

        item.outerHTML = faqRow(data, this.selection.has(id));
        this.updateCount();
    }

    updateCount() {
        const n = this.selection.size;
        this.count.textContent = n === 0 ? 'Hiç soru seçilmedi' : `${n} soru seçili`;
    }

    async load(search = '') {
        this.list.innerHTML = '<div class="py-[30px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';

        try {
            const { data } = await http.get(this.endpoint, { search, per_page: 100, sort: 'sort_order', direction: 'asc' });

            if (! data || data.length === 0) {
                this.list.innerHTML = '<div class="py-[30px] text-center text-gray-500 dark:text-gray-400">Soru bulunamadı.</div>';

                return;
            }

            this.items = new Map(data.map((item) => [item.id, item]));
            this.list.innerHTML = data.map((item) => faqRow(item, this.selection.has(item.id))).join('');
        } catch (error) {
            this.list.innerHTML = `<div class="py-[30px] text-center text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Sorular yüklenemedi.',
            )}</div>`;
        }
    }

    /**
     * @param {{id: number, question: string}[]} preselected
     * @param {string} endpoint
     * @returns {Promise<{id: number, question: string}[]|null>}
     */
    async open(preselected, endpoint) {
        this.root ??= this.build();
        this.endpoint = endpoint;
        this.selection = new Map(preselected.map((item) => [item.id, item]));

        this.search.value = '';
        this.updateCount();
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        this.load();

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }

    settle(result) {
        this.root.classList.remove('active');

        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }

        const resolve = this.resolver;
        this.resolver = null;
        resolve?.(result);
    }
}

const faqPicker = new FaqPicker();

/** Bir tekil <x-admin::form.faqs> alanı: mevcut seçimi gösterir, modalı açar. */
class FaqField {
    constructor(root) {
        this.root = root;
        this.name = root.dataset.faqName;
        this.endpoint = root.dataset.faqEndpoint;
        this.chips = root.querySelector('[data-faq-chips]');

        root.querySelector('[data-faq-open]').addEventListener('click', () => this.open());

        this.chips.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-faq-remove]');

            if (remove) {
                remove.closest('[data-faq-chip]').remove();
            }
        });
    }

    get selected() {
        return [...this.chips.querySelectorAll('[data-faq-chip]')].map((chip) => ({
            id: Number(chip.dataset.faqId),
            question: chip.querySelector('span').textContent,
        }));
    }

    async open() {
        const result = await faqPicker.open(this.selected, this.endpoint);

        if (result) {
            this.render(result);
        }
    }

    render(items) {
        this.chips.innerHTML = items.map((item) => `
            <span data-faq-chip data-faq-id="${item.id}"
                class="inline-flex items-center gap-[6px] py-[6px] px-[12px] rounded-md text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500 border border-primary-100 dark:border-[#172036] max-w-[280px]">
                <input type="hidden" name="${escapeHtml(this.name)}[]" value="${item.id}">
                <i class="material-symbols-outlined !text-[15px] shrink-0">help</i>
                <span class="truncate" title="${escapeHtml(item.question)}">${escapeHtml(item.question)}</span>
                <button type="button" data-faq-remove class="shrink-0 leading-none transition-all hover:text-danger-500">
                    <i class="ri-close-line"></i>
                </button>
            </span>`).join('');
    }
}

function init(root = document) {
    root.querySelectorAll('[data-faq-field]:not([data-faq-ready])').forEach((element) => {
        element.dataset.faqReady = '1';
        new FaqField(element);
    });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));
