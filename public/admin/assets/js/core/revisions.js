/**
 * Revizyon geçmişi arayüzünün paylaşılan parçaları.
 *
 * Üç yerde kullanılır:
 *   1. /admin/revision merkezi sayfası (pages/revision/index.js)
 *   2. Düzenleme formlarındaki "Revizyonlar" butonu
 *   3. Liste satırlarındaki geri sarma ikonu -> revisionButton()
 *
 * Satır şablonu, karşılaştırma modalı ve geri yükleme onayı tek yerde
 * durur; üç kullanım da aynı görünümü paylaşır.
 */

import { confirm } from './confirm.js';
import { escapeHtml, http, HttpError } from './http.js';
import { toast } from './toast.js';

const CELL = 'ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036] align-top';

const chip = (text) =>
    `<span class="inline-block text-[10px] py-[2px] px-[7px] rounded-[6px] bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-300 ltr:mr-[4px] rtl:ml-[4px] mb-[3px]">${escapeHtml(text)}</span>`;

/** Değişen alan rozetleri; ilk sürümde değişiklik listesi olmaz. */
function changedCell(item) {
    if (! item.changed || item.changed.length === 0) {
        return '<span class="text-xs text-gray-500 dark:text-gray-400">İlk kayıt hali</span>';
    }

    const shown = item.changed.slice(0, 4).map(chip).join('');
    const rest = item.changed.length > 4
        ? `<span class="text-xs text-gray-500 dark:text-gray-400">+${item.changed.length - 4} alan</span>`
        : '';

    return `<div class="max-w-[320px]">${shown}${rest}</div>`;
}

/** Revizyon listesinin tek satırı — merkezi sayfa ve modal bunu paylaşır. */
export function revisionRow(item, { showModule = true } = {}) {
    const subject = `<span class="block text-sm truncate max-w-[240px]" title="${escapeHtml(item.label ?? '')}">${escapeHtml(item.label ?? '—')}</span>`;

    const module = showModule
        ? `<span class="inline-flex items-center gap-[4px] text-xs text-gray-500 dark:text-gray-400 mt-[2px]">
               <i class="material-symbols-outlined !text-[14px]">${escapeHtml(item.module?.icon ?? 'history')}</i>
               ${escapeHtml(item.module?.label ?? '')}
           </span>`
        : '';

    const deleted = item.subject_exists
        ? ''
        : '<span class="block text-[11px] text-danger-500 mt-[2px]">Kayıt silinmiş</span>';

    return `<tr>
        <td class="${CELL} whitespace-nowrap">
            <span class="block text-sm">${escapeHtml(item.created_at ?? '')}</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.created_for_humans ?? '')}</span>
        </td>
        <td class="${CELL}"><div class="flex flex-col">${subject}${module}${deleted}</div></td>
        <td class="${CELL}">${changedCell(item)}</td>
        <td class="${CELL} whitespace-nowrap text-sm">${escapeHtml(item.user ?? 'Sistem')}</td>
        <td class="${CELL} whitespace-nowrap">
            <button type="button" data-revision-compare="${item.id}" title="Karşılaştır"
                class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-[20px]">difference</i>
            </button>
        </td>
    </tr>`;
}

/**
 * Liste satırlarına eklenen "Revizyonlar" düğmesi:
 *
 *   ${revisionButton('App\\Models\\Blog\\Blog', item.id)}
 *
 * Tıklama bu dosyanın kendi delegasyonuyla yakalanır.
 */
export function revisionButton(subjectType, id) {
    return `<button type="button" data-revision-history="${escapeHtml(subjectType)}" data-revision-id="${id}"
        title="Revizyonlar" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
        <i class="material-symbols-outlined !text-md">settings_backup_restore</i>
    </button>`;
}

/* ------------------------------------------------------------------ *
 * Karşılaştırma modalı
 * ------------------------------------------------------------------ */

class CompareModal {
    constructor() {
        this.root = null;
        this.id = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'revision-compare';
        root.className = 'add-new-popup z-[1405] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[900px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[16px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                        <div class="trezo-card-title"><h5 class="!mb-0">Sürüm Karşılaştırma</h5></div>
                        <button type="button" data-compare-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                            <i class="ri-close-fill"></i>
                        </button>
                    </div>
                    <div data-compare-body class="max-h-[70vh] overflow-y-auto"></div>
                </div>
            </div>`;
        document.body.append(root);

        this.body = root.querySelector('[data-compare-body]');

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-compare-close]')) {
                this.close();

                return;
            }

            if (event.target.closest('[data-compare-restore]')) {
                this.restore();
            }
        });

        return root;
    }

    async open(id) {
        this.root ??= this.build();
        this.id = id;
        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        try {
            const { data } = await http.get(`/admin/revision/${id}`);
            this.render(data);
        } catch (error) {
            this.body.innerHTML = `<div class="py-[40px] text-center text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Sürüm yüklenemedi.',
            )}</div>`;
        }
    }

    close() {
        this.root?.classList.remove('active');

        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    async restore() {
        if (! await confirm('Kayıt bu sürüme döndürülsün mü? Şu anki hali de geçmişe eklenir, istersen geri alabilirsin.', {
            title: 'Sürümü geri yükle',
            accept: 'Evet, geri yükle',
        })) {
            return;
        }

        try {
            const { message } = await http.post(`/admin/revision/${this.id}/restore`);
            toast.success(message);
            this.close();
            document.dispatchEvent(new CustomEvent('revision:restored'));
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Geri yüklenemedi.');
        }
    }

    render(item) {
        const value = (text) => text === null || text === undefined || text === ''
            ? '<span class="text-gray-400 italic">boş</span>'
            : escapeHtml(text);

        const rows = item.fields.map((field) => `
            <tr>
                <td class="px-[12px] py-[9px] align-top border-b border-gray-100 dark:border-[#172036] text-xs font-medium whitespace-nowrap">${escapeHtml(field.label)}</td>
                <td class="px-[12px] py-[9px] align-top border-b border-gray-100 dark:border-[#172036] text-xs text-warning-700 dark:text-warning-500 break-words">${value(field.old)}</td>
                <td class="px-[12px] py-[9px] align-top border-b border-gray-100 dark:border-[#172036] text-xs text-black dark:text-white break-words">${value(field.new)}</td>
            </tr>`).join('');

        const table = item.fields.length > 0
            ? `<div class="overflow-x-auto rounded-[10px] border border-gray-100 dark:border-[#172036]">
                   <table class="w-full">
                       <thead>
                           <tr class="text-xs text-gray-500 dark:text-gray-400">
                               <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Alan</th>
                               <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Bu sürüm</th>
                               <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Şu anki hali</th>
                           </tr>
                       </thead>
                       <tbody>${rows}</tbody>
                   </table>
               </div>`
            : `<div class="py-[30px] text-center text-gray-500 dark:text-gray-400">
                   Bu sürüm kaydın şu anki haliyle birebir aynı.
               </div>`;

        const action = item.restorable
            ? `<button type="button" data-compare-restore
                   class="inline-flex items-center gap-[6px] py-[10px] px-[20px] bg-primary-500 text-white rounded-md transition-all hover:bg-primary-400">
                   <i class="material-symbols-outlined !text-[19px]">settings_backup_restore</i> Bu sürüme dön
               </button>`
            : '<span class="text-sm text-danger-500">Kayıt silinmiş; bu sürüm geri yüklenemez.</span>';

        this.body.innerHTML = `
            <div class="flex items-start justify-between gap-[16px] flex-wrap mb-[18px]">
                <div class="min-w-0">
                    <span class="block font-medium text-black dark:text-white">${escapeHtml(item.label ?? '')}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[3px]">
                        ${escapeHtml(item.module?.label ?? '')} · ${escapeHtml(item.created_at ?? '')}
                        (${escapeHtml(item.created_for_humans ?? '')}) · ${escapeHtml(item.user ?? 'Sistem')}
                    </span>
                </div>
                ${item.edit_url ? `<a href="${escapeHtml(item.edit_url)}" class="text-sm text-primary-500 hover:underline whitespace-nowrap">Kaydı düzenle</a>` : ''}
            </div>

            ${table}

            <div class="mt-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                <span class="text-xs text-gray-500 dark:text-gray-400 max-w-[420px]">
                    Geri yükleme içeriğin yanı sıra SEO alanlarını, etiketleri ve görsel bağlarını da bu sürüme döndürür.
                </span>
                ${action}
            </div>`;
    }
}

const compareModal = new CompareModal();

export function openRevisionCompare(id) {
    return compareModal.open(id);
}

/* ------------------------------------------------------------------ *
 * Liste modalı — kayıt bazlı geçmiş
 * ------------------------------------------------------------------ */

class RevisionListModal {
    constructor() {
        this.root = null;
        this.page = 1;
        this.params = {};
    }

    build() {
        const root = document.createElement('div');
        root.id = 'revision-list';
        root.className = 'add-new-popup z-[1404] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[1000px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[16px] flex items-center justify-between gap-[12px] -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                        <div class="trezo-card-title"><h5 class="!mb-0" data-list-title>Revizyon Geçmişi</h5></div>
                        <div class="flex items-center gap-[12px]">
                            <a href="/admin/revision" class="text-sm text-primary-500 transition-all hover:underline whitespace-nowrap">Tümünü gör</a>
                            <button type="button" data-list-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                                <i class="ri-close-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div data-list-body class="max-h-[65vh] overflow-y-auto -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px]"></div>
                    <div data-list-more class="pt-[16px] mt-[16px] border-t border-gray-100 dark:border-[#172036] text-center"></div>
                </div>
            </div>`;
        document.body.append(root);

        this.body = root.querySelector('[data-list-body]');
        this.more = root.querySelector('[data-list-more]');
        this.title = root.querySelector('[data-list-title]');

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-list-close]')) {
                this.close();

                return;
            }

            const compare = event.target.closest('[data-revision-compare]');

            if (compare) {
                openRevisionCompare(compare.dataset.revisionCompare);

                return;
            }

            if (event.target.closest('[data-list-load-more]')) {
                this.page++;
                this.load(true);
            }
        });

        // Geri yükleme sonrası liste tazelenir: yeni bir sürüm daha eklenmiştir.
        document.addEventListener('revision:restored', () => {
            if (this.root?.classList.contains('active')) {
                this.page = 1;
                this.load();
            }
        });

        return root;
    }

    async open(options = {}) {
        this.root ??= this.build();

        this.params = {
            subject_type: options.subjectType ?? '',
            subject_id: options.subjectId ?? '',
            per_page: 15,
        };
        this.page = 1;

        this.title.textContent = options.title ?? 'Revizyon Geçmişi';
        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.more.innerHTML = '';
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        this.load();
    }

    close() {
        this.root?.classList.remove('active');

        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    async load(append = false) {
        try {
            const { data, meta } = await http.get('/admin/revision/datatable', { ...this.params, page: this.page });

            if (! data || data.length === 0) {
                if (! append) {
                    this.body.innerHTML = `
                        <div class="py-[50px] text-center">
                            <span class="w-[56px] h-[56px] rounded-full bg-gray-50 dark:bg-[#15203c] inline-flex items-center justify-center mb-[10px]">
                                <i class="material-symbols-outlined !text-[26px] text-gray-400">settings_backup_restore</i>
                            </span>
                            <p class="!mb-0 text-gray-500 dark:text-gray-400">Bu kaydın henüz eski bir sürümü yok.</p>
                            <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400 mt-[4px]">İlk düzenlemeden sonra eski hali burada birikmeye başlar.</p>
                        </div>`;
                }

                this.more.innerHTML = '';

                return;
            }

            const rows = data.map((item) => revisionRow(item, { showModule: false })).join('');

            if (append) {
                this.body.querySelector('tbody').insertAdjacentHTML('beforeend', rows);
            } else {
                this.body.innerHTML = `
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full">
                            <tbody class="text-black dark:text-white">${rows}</tbody>
                        </table>
                    </div>`;
            }

            this.more.innerHTML = meta && meta.current_page < meta.last_page
                ? `<button type="button" data-list-load-more
                       class="inline-flex items-center gap-[6px] py-[9px] px-[20px] text-sm text-black dark:text-white transition-all rounded-[10px] border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                       <i class="material-symbols-outlined !text-[18px]">expand_more</i> Daha fazla göster
                   </button>`
                : `<span class="text-xs text-gray-500 dark:text-gray-400">Toplam ${meta?.total ?? data.length} sürüm</span>`;
        } catch (error) {
            this.body.innerHTML = `<div class="py-[40px] text-center text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Sürümler yüklenemedi.',
            )}</div>`;
        }
    }
}

const listModal = new RevisionListModal();

export function openRevisionList(options) {
    return listModal.open(options);
}

/**
 * Satırlardaki ve form başlıklarındaki düğmeleri kendiliğinden bağlar:
 *
 *   <button data-revision-history="App\Models\Blog\Blog" data-revision-id="12">
 */
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-revision-history]');

        if (button) {
            openRevisionList({
                subjectType: button.dataset.revisionHistory,
                subjectId: button.dataset.revisionId,
                title: button.dataset.revisionTitle ?? 'Revizyon Geçmişi',
            });
        }
    });
});
