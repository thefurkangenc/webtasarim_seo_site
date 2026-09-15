/**
 * <x-admin::form.repeater> davranışı — tekrarlanabilir form satırları.
 *
 * Satırlar gizli bir <template> içindeki iskeletten çoğaltılır; alan adları
 * `alan[İNDİS][kolon]` biçimindedir. İndis yalnızca ARTAN bir sayaçtır,
 * silmede yeniden numaralandırılmaz: PHP boşluklu indisleri de dizi olarak
 * okur ve sunucu tarafı listeyi `values()` ile yeniden indisler. Satır sırası
 * DOM sırasıdır — tarayıcı FormData'yı DOM sırasına göre gönderir, bu yüzden
 * sürükleyip bırakmak sıranın kendisini değiştirir, ek bir alan gerekmez.
 */

import { toast } from './toast.js';

class Repeater {
    constructor(root) {
        this.root = root;
        this.list = root.querySelector('[data-repeater-list]');
        this.template = root.querySelector('[data-repeater-template]');
        this.empty = root.querySelector('[data-repeater-empty]');
        this.max = Number(root.dataset.repeaterMax) || 0;
        // Sunucu render'ı 0..n-1 kullandığı için sayaç oradan devam eder.
        this.index = this.list.querySelectorAll('[data-repeater-row]').length;

        this.bind();
        this.enableSortable();
        this.refresh();
    }

    get rowCount() {
        return this.list.querySelectorAll('[data-repeater-row]').length;
    }

    bind() {
        this.root.addEventListener('click', (event) => {
            if (event.target.closest('[data-repeater-add]')) {
                this.add();

                return;
            }

            const remove = event.target.closest('[data-repeater-remove]');

            if (remove) {
                remove.closest('[data-repeater-row]').remove();
                this.refresh();
            }
        });
    }

    add() {
        if (this.max > 0 && this.rowCount >= this.max) {
            toast.error(`En fazla ${this.max} satır ekleyebilirsiniz.`);

            return;
        }

        // __index__ yer tutucusu, alan adlarının çakışmaması için benzersiz
        // bir sayıyla değişir.
        this.list.insertAdjacentHTML(
            'beforeend',
            this.template.innerHTML.replaceAll('__index__', String(this.index++)),
        );

        this.refresh();
        this.list.querySelector('[data-repeater-row]:last-of-type input, [data-repeater-row]:last-of-type select')?.focus();
    }

    /** Boş durum yazısı ve tutamaçların görünürlüğü satır sayısına bağlı. */
    refresh() {
        const count = this.rowCount;

        this.empty?.classList.toggle('hidden', count > 0);
        // Tek satırda sürükleyecek bir şey yok; tutamaç kafa karıştırır.
        this.list.querySelectorAll('[data-repeater-handle]').forEach((handle) => {
            handle.classList.toggle('opacity-0', count < 2);
            handle.classList.toggle('pointer-events-none', count < 2);
        });
    }

    async enableSortable() {
        const { default: Sortable } = await import('../vendor/sortablejs/sortable.esm.js');

        Sortable.create(this.list, {
            handle: '[data-repeater-handle]',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
        });
    }
}

export function initRepeaters(root = document) {
    root.querySelectorAll('[data-repeater]:not([data-repeater-ready])').forEach((element) => {
        element.dataset.repeaterReady = '1';
        new Repeater(element);
    });
}

document.addEventListener('DOMContentLoaded', () => initRepeaters());
document.addEventListener('admin:content-loaded', (event) => initRepeaters(event.target));
