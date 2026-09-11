/**
 * <x-admin::form.tags> ve <x-admin::form.chips> davranışı.
 *
 * Değerler gizli input olarak tutulur (name="tags[]"), böylece form normal
 * FormData ile gönderilir; ayrı bir serileştirme gerekmez.
 *
 * Enter ya da virgül değeri ekler; boş alanda Backspace sonuncuyu siler.
 *
 * İki kip aynı sınıfla çalışır; fark yalnızca önerilerdedir:
 *   data-tag-endpoint VAR  -> etiket kipi, yazarken /admin/tags/search'e sorar
 *   data-tag-endpoint YOK  -> serbest liste kipi (teknolojiler gibi), öneri yok
 * Uzunluk sınırı data-tag-max ile alan başına değiştirilebilir.
 */

import { escapeHtml, http } from './http.js';

const MAX_LENGTH = 50;

class TagInput {
    constructor(root) {
        this.root = root;
        this.name = root.dataset.tagName;
        this.endpoint = root.dataset.tagEndpoint || null;
        this.maxLength = Number(root.dataset.tagMax) || MAX_LENGTH;
        this.chips = root.querySelector('[data-tag-chips]');
        this.field = root.querySelector('[data-tag-field]');
        this.suggestions = root.querySelector('[data-tag-suggestions]');
        this.searchTimer = null;

        this.bind();
    }

    get names() {
        return [...this.chips.querySelectorAll('input')].map((input) => input.value);
    }

    bind() {
        // Kutunun boş yerine tıklamak da alana odaklanmalı.
        this.root.addEventListener('mousedown', (event) => {
            if (event.target === this.root || event.target === this.chips) {
                event.preventDefault();
                this.field.focus();
            }
        });

        this.root.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-tag-remove]');

            if (remove) {
                remove.closest('[data-tag-chip]').remove();
            }
        });

        this.field.addEventListener('keydown', (event) => this.onKeydown(event));
        this.field.addEventListener('input', () => this.scheduleSearch());
        this.field.addEventListener('blur', () => {
            // Öneriye tıklanıyorsa kapatma; mousedown önce çalışır.
            setTimeout(() => this.hideSuggestions(), 150);
            this.add(this.field.value);
        });

        this.suggestions?.addEventListener('mousedown', (event) => {
            const option = event.target.closest('[data-tag-option]');

            if (option) {
                event.preventDefault();
                this.add(option.dataset.tagOption);
                this.field.focus();
            }
        });
    }

    onKeydown(event) {
        if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();
            this.add(this.field.value);

            return;
        }

        if (event.key === 'Backspace' && this.field.value === '') {
            this.chips.querySelector('[data-tag-chip]:last-of-type')?.remove();

            return;
        }

        if (event.key === 'Escape') {
            this.hideSuggestions();
        }
    }

    add(value) {
        const name = value.trim().replace(/\s+/g, ' ').slice(0, this.maxLength);

        this.field.value = '';
        this.hideSuggestions();

        if (name === '' || this.names.some((existing) => existing.toLocaleLowerCase('tr') === name.toLocaleLowerCase('tr'))) {
            return;
        }

        const chip = document.createElement('span');
        chip.dataset.tagChip = '';
        chip.className = 'inline-flex items-center gap-[5px] py-[5px] px-[10px] rounded-md text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500 border border-primary-100 dark:border-[#172036]';
        chip.innerHTML = `<input type="hidden" name="${escapeHtml(this.name)}[]" value="${escapeHtml(name)}">
            <span>${escapeHtml(name)}</span>
            <button type="button" data-tag-remove class="leading-none transition-all hover:text-danger-500"><i class="ri-close-line"></i></button>`;

        this.chips.append(chip);
    }

    scheduleSearch() {
        // Serbest liste kipinde önerilecek bir havuz yok.
        if (! this.endpoint) {
            return;
        }

        clearTimeout(this.searchTimer);

        const term = this.field.value.trim();

        if (term.length < 2) {
            this.hideSuggestions();

            return;
        }

        this.searchTimer = setTimeout(() => this.search(term), 250);
    }

    async search(term) {
        try {
            const { data } = await http.get(this.endpoint, { q: term });
            const chosen = this.names.map((name) => name.toLocaleLowerCase('tr'));
            const options = (data ?? []).filter((tag) => ! chosen.includes(tag.name.toLocaleLowerCase('tr')));

            if (options.length === 0) {
                this.hideSuggestions();

                return;
            }

            this.suggestions.innerHTML = options.map((tag) => `
                <li data-tag-option="${escapeHtml(tag.name)}"
                    class="px-[15px] py-[7px] text-sm cursor-pointer transition-all text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    ${escapeHtml(tag.name)}
                </li>`).join('');

            this.suggestions.classList.remove('hidden');
        } catch {
            // Öneri kozmetiktir; hata durumunda alan yazmaya devam edebilmeli.
            this.hideSuggestions();
        }
    }

    hideSuggestions() {
        if (! this.suggestions) {
            return;
        }

        this.suggestions.classList.add('hidden');
        this.suggestions.innerHTML = '';
    }
}

export function initTagInputs(root = document) {
    root.querySelectorAll('[data-tag-input]:not([data-tag-ready])').forEach((element) => {
        element.dataset.tagReady = '1';
        new TagInput(element);
    });
}

document.addEventListener('DOMContentLoaded', () => initTagInputs());
document.addEventListener('admin:content-loaded', (event) => initTagInputs(event.target));
