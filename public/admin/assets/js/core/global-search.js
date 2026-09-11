/**
 * Header'daki global arama.
 *
 * Sonuçlar modüllere göre gruplanır ve klavyeyle gezilebilir: ↑ ↓ ile satır
 * seçilir, Enter açar, Esc kapatır. Ctrl/⌘+K her yerden arama kutusuna odaklanır.
 *
 * Yetki filtresi SUNUCUDA: uç nokta yetki ara katmanından muaf tutulduğu için
 * hangi modülün sonucunun döneceğine GlobalSearchService karar verir.
 */

import { escapeHtml, http } from './http.js';

const MIN_LENGTH = 2;
const DEBOUNCE = 250;
const ROW = 'data-search-row';

class GlobalSearch {
    constructor(root) {
        this.root = root;
        this.input = root.querySelector('[data-search-input]');
        this.panel = root.querySelector('[data-search-results]');
        this.icon = root.querySelector('[data-search-icon]');
        this.timer = null;
        this.active = -1;
        // Kullanıcı hızlı yazarken gecikmiş bir yanıt yeni sonucu ezebilir;
        // her istek numaralanır ve yalnızca en sonuncusu basılır.
        this.request = 0;

        this.bind();
    }

    get rows() {
        return [...this.panel.querySelectorAll(`[${ROW}]`)];
    }

    bind() {
        this.input.addEventListener('input', () => this.schedule());
        this.input.addEventListener('keydown', (event) => this.onKeydown(event));
        this.input.addEventListener('focus', () => {
            if (this.input.value.trim().length >= MIN_LENGTH) {
                this.panel.hidden = false;
            }
        });

        // Panel dışına tıklanınca kapanır; panel içindeki tıklama bağlantıdır.
        document.addEventListener('click', (event) => {
            if (! this.root.contains(event.target)) {
                this.close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.input.focus();
                this.input.select();
            }
        });
    }

    onKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
            this.input.blur();

            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            this.move(event.key === 'ArrowDown' ? 1 : -1);

            return;
        }

        if (event.key === 'Enter') {
            const row = this.rows[this.active];

            if (row) {
                event.preventDefault();
                window.location.href = row.getAttribute('href');
            }
        }
    }

    move(step) {
        const rows = this.rows;

        if (rows.length === 0) {
            return;
        }

        // Listenin iki ucunda döner; en alttayken ↓ başa gider.
        this.active = (this.active + step + rows.length) % rows.length;

        rows.forEach((row, index) => {
            row.classList.toggle('bg-gray-50', index === this.active);
            row.classList.toggle('dark:bg-[#15203c]', index === this.active);
        });

        rows[this.active].scrollIntoView({ block: 'nearest' });
    }

    schedule() {
        clearTimeout(this.timer);

        const term = this.input.value.trim();

        if (term.length < MIN_LENGTH) {
            this.close();

            return;
        }

        this.timer = setTimeout(() => this.search(term), DEBOUNCE);
    }

    async search(term) {
        const ticket = ++this.request;

        this.busy(true);

        try {
            const { data } = await http.get('/admin/search', { q: term });

            if (ticket !== this.request) {
                return;
            }

            this.render(data);
        } catch {
            if (ticket === this.request) {
                this.panel.innerHTML = this.message('Arama yapılamadı.');
                this.panel.hidden = false;
            }
        } finally {
            if (ticket === this.request) {
                this.busy(false);
            }
        }
    }

    render(data) {
        this.active = -1;

        if (data.total === 0) {
            this.panel.innerHTML = this.message(`“${escapeHtml(data.term)}” için sonuç bulunamadı.`);
            this.panel.hidden = false;

            return;
        }

        this.panel.innerHTML = data.groups.map((group) => `
            <div class="px-[14px] pt-[10px] pb-[4px] flex items-center gap-[6px]">
                <i class="material-symbols-outlined !text-[14px] text-gray-400">${group.icon}</i>
                <span class="text-[10px] font-medium uppercase tracking-[.5px] text-gray-400">${escapeHtml(group.label)}</span>
            </div>
            ${group.items.map((item) => `
                <a href="${escapeHtml(item.url)}" ${ROW}
                    class="block px-[14px] py-[8px] transition-all hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <span class="block text-sm text-black dark:text-white truncate">${escapeHtml(item.title)}</span>
                    ${item.subtitle ? `<span class="block text-xs text-gray-500 dark:text-gray-400 truncate">${escapeHtml(item.subtitle)}</span>` : ''}
                </a>`).join('')}
        `).join('');

        this.panel.hidden = false;
    }

    message(text) {
        return `<p class="!mb-0 px-[14px] py-[22px] text-center text-sm text-gray-500 dark:text-gray-400">${text}</p>`;
    }

    busy(state) {
        this.icon.textContent = state ? 'progress_activity' : 'search';
        this.icon.classList.toggle('animate-spin', state);
    }

    close() {
        this.panel.hidden = true;
        this.active = -1;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-global-search]');

    if (root) {
        new GlobalSearch(root);
    }
});
