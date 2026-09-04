/**
 * Promise döndüren klasör ağacı seçici — dosya yöneticisinin "Taşı" diyaloğu.
 * `/admin/media/folders/tree` uç noktasının döndürdüğü iç içe klasör
 * dizisini render eder; taşınan öğelerin kendisi (ve klasörse alt ağacı)
 * hedef olarak seçilemez şekilde devre dışı bırakılır.
 *
 *   const folderId = await folderPicker.open(tree, { excludeIds: [5], currentId: 3 });
 *   // folderId === undefined -> vazgeçildi
 *   // folderId === null | number -> seçilen klasör (null = kök)
 */

import { escapeHtml } from './http.js';

class FolderPicker {
    constructor() {
        this.root = null;
        this.resolver = null;
        this.selected = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'folder-picker';
        root.className = 'add-new-popup z-[1004] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[420px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <h5 class="!mb-[15px]">Klasöre taşı</h5>
                    <ul data-picker-tree class="max-h-[320px] overflow-y-auto rounded-md border border-gray-100 dark:border-[#172036] p-[8px]"></ul>
                    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] md:mt-[25px] border-t border-gray-100 dark:border-[#172036]">
                        <button type="button" data-picker-cancel
                            class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            Vazgeç
                        </button>
                        <button type="button" data-picker-accept
                            class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            Buraya Taşı
                        </button>
                    </div>
                </div>
            </div>`;

        document.body.append(root);

        root.querySelector('[data-picker-tree]').addEventListener('click', (event) => {
            const row = event.target.closest('[data-picker-row]:not([data-picker-disabled])');

            if (! row) {
                return;
            }

            root.querySelectorAll('[data-picker-row]').forEach((el) => el.classList.remove('bg-primary-50', 'dark:bg-[#15203c]', 'text-primary-500'));
            row.classList.add('bg-primary-50', 'dark:bg-[#15203c]', 'text-primary-500');
            this.selected = row.dataset.pickerId ? Number(row.dataset.pickerId) : null;
        });

        root.querySelector('[data-picker-cancel]').addEventListener('click', () => this.settle(undefined));
        root.querySelector('[data-picker-accept]').addEventListener('click', () => this.settle(this.selected));
        root.addEventListener('click', (event) => {
            if (event.target === root) {
                this.settle(undefined);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.resolver) {
                this.settle(undefined);
            }
        });

        return root;
    }

    row(id, name, depth, disabled, icon = 'folder') {
        const base = 'flex items-center gap-[7px] py-[7px] rounded-md transition-all text-sm';
        const style = `padding-inline-start: ${10 + depth * 16}px; padding-inline-end: 10px;`;

        if (disabled) {
            return `<li data-picker-row data-picker-disabled class="${base} opacity-40 cursor-not-allowed" style="${style}">
                <i class="material-symbols-outlined !text-[17px]">${icon}</i> ${escapeHtml(name)}
            </li>`;
        }

        return `<li data-picker-row data-picker-id="${id ?? ''}" class="${base} cursor-pointer hover:bg-gray-50 dark:hover:bg-[#15203c]" style="${style}">
            <i class="material-symbols-outlined !text-[17px]">${icon}</i> ${escapeHtml(name)}
        </li>`;
    }

    renderTree(folders, excludeIds, depth = 0) {
        return folders.map((folder) => {
            // Bir klasör kendi altına (ya da kendi alt ağacına) taşınamaz —
            // bu dal ve tüm çocukları devre dışı render edilir.
            const disabled = excludeIds.includes(folder.id);

            return this.row(folder.id, folder.name, depth, disabled)
                + (folder.children?.length ? this.renderTree(folder.children, excludeIds, depth + 1) : '');
        }).join('');
    }

    settle(result) {
        if (! this.resolver) {
            return;
        }

        this.root.classList.remove('active');
        document.body.classList.remove('overflow-hidden');

        const resolve = this.resolver;
        this.resolver = null;
        resolve(result);
    }

    /**
     * @param {Array<object>} tree  `/admin/media/folders/tree` yanıtı
     * @param {{excludeIds?: number[], currentId?: number|null}} options
     * @returns {Promise<number|null|undefined>}
     */
    open(tree, options = {}) {
        this.root ??= this.build();
        this.selected = options.currentId ?? null;

        const excludeIds = options.excludeIds ?? [];
        const list = this.root.querySelector('[data-picker-tree]');

        list.innerHTML = this.row(null, 'Kök klasör', 0, false, 'inbox')
            + this.renderTree(tree, excludeIds);

        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        return new Promise((resolve) => {
            this.resolver = resolve;
        });
    }
}

export const folderPicker = new FolderPicker();
