/**
 * AJAX veri tablosu: yükleme, arama, filtre, sıralama, sayfalama.
 *
 * Sunucu tarafı `RespondsWithJson::success()` ile bir paginator döndürmelidir:
 *   { success, message, data: [...], meta: { current_page, last_page, per_page, total, from, to } }
 *
 * Gönderilen parametreler: search, sort, direction, page, per_page + filtreler.
 * İlgili FilterRequest bunları kabul etmelidir, aksi halde sessizce yok sayılır.
 *
 * Sürükle-bırak sıralama (opsiyonel): `reorder: { button, endpoint }` verilirse
 * satır şablonuna `<tr data-id="...">` ve gizli bir tutamaç hücresi
 * (`data-reorder-column` + içinde `data-reorder-handle`) eklemek yeterli —
 * bkz. trezo-ui skill'i "Sürükle-bırak sıralama" bölümü.
 */

import { http, HttpError } from './http.js';
import { toast } from './toast.js';

const CELL = 'ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]';

const REORDER_ICON_ON = 'checklist';
const REORDER_ICON_OFF = 'drag_indicator';

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
     *   reorder?: { button: HTMLElement, endpoint: string },
     *   onLoaded?: (items: object[]) => void,
     * }} options
     */
    constructor(options) {
        this.options = options;
        this.body = options.body;
        this.table = this.body.closest('table');
        this.filters = options.filters ?? {};
        this.pagination = options.pagination ?? this.createPagination();
        this.reorderConfig = options.reorder ?? null;
        this.reordering = false;
        this.sortable = null;

        this.state = {
            search: '',
            sort: options.sort ?? null,
            direction: options.direction ?? 'desc',
            page: 1,
            per_page: options.perPage ?? 15,
        };

        this.bind();

        if (this.reorderConfig) {
            this.bindReorder();
        }
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
                if (this.reordering) {
                    return;
                }

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

    /**
     * Sürükle-bırak sıralama modu. Açıkken arama/filtre/sayfalama/sütun
     * sıralaması devre dışı kalır, tüm kayıtlar sort_order'a göre tek
     * sayfada gösterilir; her bırakışta sıra kaydedilir.
     */
    bindReorder() {
        this.reorderConfig.button.addEventListener('click', () => this.toggleReorder());
    }

    async toggleReorder() {
        this.reordering = ! this.reordering;
        this.setReorderButton();
        this.setControlsDisabled(this.reordering);

        if (this.reordering) {
            await this.loadForReorder();
            await this.enableSortable();

            return;
        }

        this.disableSortable();
        this.state.page = 1;
        this.load();
    }

    setReorderButton() {
        const icon = this.reordering ? REORDER_ICON_ON : REORDER_ICON_OFF;
        const label = this.reordering ? 'Sıralamayı Bitir' : 'Sıralama Modu';

        this.reorderConfig.button.innerHTML =
            `<i class="material-symbols-outlined !text-[19px]">${icon}</i> ${label}`;
    }

    setControlsDisabled(disabled) {
        if (this.options.search) {
            this.options.search.disabled = disabled;
        }

        Object.values(this.filters).forEach((element) => {
            element.disabled = disabled;
        });

        this.pagination.classList.toggle('hidden', disabled);

        this.table?.querySelectorAll('thead [data-column]').forEach((header) => {
            header.classList.toggle('pointer-events-none', disabled);
            header.classList.toggle('opacity-50', disabled);
        });

        this.table?.querySelectorAll('[data-reorder-column]').forEach((element) => {
            element.classList.toggle('hidden', ! disabled);
        });
    }

    /**
     * Sıralama modunda tüm kayıtlar tek seferde, sort_order'a göre gelir.
     *
     * Filtreler varsayılan olarak yok sayılır — filtrelenmiş bir alt kümeyi
     * sıralamak, dışarıda kalan kayıtların sırasıyla iç içe geçerdi. Sıralama
     * gerçekten bir kapsam içinde tekilse (ağaç seviyesi gibi) o kapsamı
     * belirleyen filtreler adlarıyla verilir:
     * `reorder: { ..., withFilters: ['parent_id'] }`.
     */
    async loadForReorder() {
        const scope = Object.fromEntries(
            (this.reorderConfig.withFilters ?? [])
                .filter((name) => this.filters[name])
                .map((name) => [name, this.filters[name].value]),
        );

        try {
            const { data } = await http.get(this.options.endpoint, { ...scope, per_page: 1000, sort: 'sort_order', direction: 'asc' });

            this.body.innerHTML = (data ?? []).map((item, index) => this.options.row(item, index)).join('');
            this.options.onLoaded?.(data ?? []);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Liste yüklenemedi.');
        }
    }

    async enableSortable() {
        const { default: Sortable } = await import('../vendor/sortablejs/sortable.esm.js');

        this.sortable = Sortable.create(this.body, {
            handle: '[data-reorder-handle]',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: () => this.saveOrder(),
        });
    }

    disableSortable() {
        this.sortable?.destroy();
        this.sortable = null;
    }

    async saveOrder() {
        const ids = [...this.body.querySelectorAll('[data-id]')].map((row) => Number(row.dataset.id));

        try {
            await http.put(this.reorderConfig.endpoint, { ids });
            toast.success('Sıralama güncellendi.');
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Sıralama kaydedilemedi.');
        }
    }

    params() {
        const params = { ...this.state };

        Object.entries(this.filters).forEach(([name, element]) => {
            // Checkbox'ta `.value` işaretli olmasa bile "on" döner; ham haliyle
            // gönderilirse filtre hep açıkmış gibi davranır ve sunucu tarafı
            // doğrulaması ("0/1 olmalı") patlar. İşaretsiz = boş, yani gönderilmez.
            params[name] = element.type === 'checkbox'
                ? (element.checked ? (element.value === 'on' ? '1' : element.value) : '')
                : element.value;
        });

        return params;
    }

    /**
     * Filtreleri ve sayfayı sıfırlamadan mevcut görünümü tazeler. Sıralama
     * modu açıkken de o görünümde kalır — örn. modaldan yeni kayıt eklenirse
     * paginasyona düşmez, listenin sonunda görünür.
     */
    reload() {
        return this.reordering ? this.loadForReorder() : this.load();
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

            // Satırlar basıldıktan sonra çalışan işler (ör. görüntüleme
            // sayılarının ayrı bir istekle doldurulması) için.
            this.options.onLoaded?.(data);
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

/**
 * `reorder` seçeneğini kullanan tablolarda satırın ilk hücresi bu olmalı.
 * Sıralama modu kapalıyken gizli durur (`data-reorder-column`); `core/table.js`
 * moda girince gösterir. Tutamaç ikonu `data-reorder-handle` — SortableJS
 * sürüklemeyi yalnızca bu ikondan başlatır, satırın geri kalanı tıklanabilir kalır.
 */
export const reorderHandle = () => `<td data-reorder-column class="hidden w-[36px] ${CELL}">
    <i data-reorder-handle class="material-symbols-outlined !text-[19px] text-gray-400 cursor-grab active:cursor-grabbing">drag_indicator</i>
</td>`;
