/**
 * AJAX veri tablosu: yükleme, arama, filtre, sıralama, sayfalama.
 *
 * Sunucu tarafı `RespondsWithJson::success()` ile bir paginator döndürmelidir:
 *   { success, message, data: [...], meta: { current_page, last_page, per_page, total, from, to } }
 *
 * Gönderilen parametreler: search, sort, direction, page, per_page + filtreler.
 * İlgili FilterRequest bunları kabul etmelidir, aksi halde sessizce yok sayılır.
 */

import { http, HttpError } from './http.js';

const CELL = 'ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]';

export class DataTable {
    /**
     * @param {{
     *   endpoint: string,
     *   body: HTMLElement,
     *   row: (item: object, index: number) => string,
     *   search?: HTMLInputElement,
     *   filters?: Record<string, HTMLElement>,
     *   pagination?: HTMLElement,
     *   perPage?: number,
     *   sort?: string,
     *   direction?: 'asc'|'desc',
     *   empty?: string,
     * }} options
     */
    constructor(options) {
        this.options = options;
        this.body = options.body;
        this.table = this.body.closest('table');
        this.filters = options.filters ?? {};
        this.pagination = options.pagination ?? this.createPagination();

        this.state = {
            search: '',
            sort: options.sort ?? null,
            direction: options.direction ?? 'desc',
            page: 1,
            per_page: options.perPage ?? 15,
        };

        this.bind();
    }

    get columnCount() {
        return this.table?.querySelectorAll('thead th').length ?? 1;
    }

    createPagination() {
        const element = document.createElement('div');
        element.className = 'mt-[20px]';
        (this.table?.parentElement ?? this.body).after(element);

        return element;
    }

    bind() {
        if (this.options.search) {
            let timer;
            this.options.search.addEventListener('input', (event) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    this.state.search = event.target.value.trim();
                    this.state.page = 1;
                    this.load();
                }, 300);
            });
        }

        Object.values(this.filters).forEach((element) => {
            element.addEventListener('change', () => {
                this.state.page = 1;
                this.load();
            });
        });

        this.table?.querySelectorAll('thead [data-column]').forEach((header) => {
            header.addEventListener('click', () => {
                const column = header.dataset.column;

                this.state.direction = this.state.sort === column && this.state.direction === 'asc' ? 'desc' : 'asc';
                this.state.sort = column;
                this.state.page = 1;
                this.load();
            });
        });

        this.pagination.addEventListener('click', (event) => {
            const link = event.target.closest('[data-page]');

            if (link && ! link.hasAttribute('data-disabled')) {
                this.state.page = Number(link.dataset.page);
                this.load();
            }
        });
    }

    params() {
        const params = { ...this.state };

        Object.entries(this.filters).forEach(([name, element]) => {
            params[name] = element.value;
        });

        return params;
    }

    /** Filtreleri ve sayfayı sıfırlamadan mevcut görünümü tazeler. */
    reload() {
        return this.load();
    }

    async load() {
        this.setMessage('Yükleniyor...');

        try {
            const { data, meta } = await http.get(this.options.endpoint, this.params());

            if (! data || data.length === 0) {
                this.setMessage(this.options.empty ?? 'Kayıt bulunamadı.');
                this.pagination.innerHTML = '';

                return;
            }

            this.body.innerHTML = data.map((item, index) => this.options.row(item, index)).join('');
            this.renderPagination(meta);
            this.markSortedColumn();
        } catch (error) {
            this.setMessage(
                error instanceof HttpError ? error.message : 'Liste yüklenemedi.',
                'text-danger-500',
            );
            this.pagination.innerHTML = '';
        }
    }

    setMessage(message, extraClass = 'text-gray-500 dark:text-gray-400') {
        this.body.innerHTML = `<tr><td colspan="${this.columnCount}" class="${CELL} !text-center ${extraClass}">${message}</td></tr>`;
    }

    markSortedColumn() {
        this.table?.querySelectorAll('thead [data-column] i').forEach((icon) => {
            const isSorted = icon.closest('[data-column]').dataset.column === this.state.sort;

            icon.className = isSorted
                ? `ri-arrow-${this.state.direction === 'asc' ? 'up' : 'down'}-line text-primary-500`
                : 'ri-expand-up-down-fill text-gray-500 dark:text-gray-400';
        });
    }

    renderPagination(meta) {
        if (! meta || meta.last_page <= 1) {
            this.pagination.innerHTML = '';

            return;
        }

        const link = (label, page, { disabled = false, active = false } = {}) => {
            const base = 'w-[31px] h-[31px] block leading-[29px] text-center rounded-md border transition-all';
            const style = active
                ? 'bg-primary-500 text-white border-primary-500'
                : `border-gray-100 dark:border-[#172036] ${disabled ? 'opacity-40 cursor-not-allowed' : 'hover:bg-primary-500 hover:text-white hover:border-primary-500 cursor-pointer'}`;

            return `<li class="inline-block mx-[1px]">
                <span class="${base} ${style}" data-page="${page}"${disabled ? ' data-disabled' : ''}>${label}</span>
            </li>`;
        };

        const pages = [];
        const start = Math.max(1, meta.current_page - 2);
        const end = Math.min(meta.last_page, start + 4);

        for (let page = start; page <= end; page++) {
            pages.push(link(page, page, { active: page === meta.current_page }));
        }

        this.pagination.innerHTML = `
            <div class="flex items-center justify-between flex-wrap gap-[10px]">
                <p class="!mb-0 text-sm">Toplam <strong>${meta.total}</strong> kayıttan ${meta.from}–${meta.to} arası</p>
                <ol class="flex items-center">
                    ${link('<i class="ri-arrow-left-s-line"></i>', meta.current_page - 1, { disabled: meta.current_page === 1 })}
                    ${pages.join('')}
                    ${link('<i class="ri-arrow-right-s-line"></i>', meta.current_page + 1, { disabled: meta.current_page === meta.last_page })}
                </ol>
            </div>`;
    }
}

/** Satır render'ında hücre yazmayı kısaltır. */
export const cell = (content, extra = '') => `<td class="${CELL} ${extra}">${content}</td>`;
