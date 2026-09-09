/**
 * Medya tarayıcısının (dosya yöneticisi) davranışı. Markup'ı
 * resources/views/admin/pages/media/partials/browser.blade.php verir.
 *
 * Hem /admin/media sayfası hem de form içinden açılan seçici modal aynı
 * sınıfı kullanır; fark yalnızca `selectable` ve `manageable` bayraklarıdır
 * — ikisi de artık aynı yönetim yeteneklerine sahiptir (context menu,
 * sürükle-taşı, çoklu seçim), `selectable` sadece bir dosyayı "seçip"
 * sonucu döndürme davranışını ekler.
 *
 * Gezinme sidebar ağacı değil breadcrumb + çift tıklamayladır: klasörler
 * dosyalarla aynı ızgarada kart olarak durur, çift tıklanınca içine girilir.
 */

import { escapeHtml, http, HttpError } from './http.js';
import { toast } from './toast.js';
import { confirm } from './confirm.js';
import { promptText } from './prompt.js';
import { folderPicker } from './folder-picker.js';
import { mediaPreview } from './media-preview.js';
import { cropModal } from './cropper.js';

/**
 * Dosya uzantısı -> ikon + renk. Material Symbols bu panelde FILL ekseni 0'a
 * sabit yükleniyor (bkz. layout/partials/styles.blade.php), yani dolgulu
 * varyantı yok; dolgulu ikonlar yerel Remix Icon setinden alınıyor.
 */
const FILE_TYPES = [
    [/^(pdf)$/, 'ri-file-pdf-fill', 'text-danger-500'],
    [/^(docx?|odt|rtf)$/, 'ri-file-word-fill', 'text-secondary-500'],
    [/^(xlsx?|csv|ods)$/, 'ri-file-excel-fill', 'text-success-600'],
    [/^(pptx?|odp)$/, 'ri-file-ppt-fill', 'text-orange-500'],
    [/^(zip|rar|7z|tar|gz|bz2)$/, 'ri-file-zip-fill', 'text-warning-600'],
    [/^(mp4|mov|avi|mkv|webm)$/, 'ri-video-fill', 'text-purple-500'],
    [/^(mp3|wav|ogg|m4a|aac)$/, 'ri-music-fill', 'text-info-500'],
    [/^(json|xml|js|css|html|php|txt|md)$/, 'ri-file-code-fill', 'text-gray-500'],
];

/** @returns {{icon: string, color: string}} */
function fileType(media) {
    // Görseller küçük resimleriyle gösteriliyor; ikon da görsel ikonu olmalı.
    // Bu kontrol uzantı eşlemesinden ÖNCE gelir, aksi halde SVG hem küçük
    // resim hem "kod dosyası" ikonu alıp tutarsız görünürdü.
    if (media.is_image) {
        return { icon: 'ri-image-fill', color: 'text-secondary-500' };
    }

    const extension = String(media.extension ?? '').toLowerCase();
    const match = FILE_TYPES.find(([pattern]) => pattern.test(extension));

    return match
        ? { icon: match[1], color: match[2] }
        : { icon: 'ri-file-fill', color: 'text-gray-400' };
}

/** Kartların sağ üstündeki üç nokta — bağlam menüsünü açar. */
function menuButton() {
    return `<button type="button" data-item-menu
        class="absolute top-[6px] ltr:right-[6px] rtl:left-[6px] z-[2] w-[26px] h-[26px] rounded-[7px] inline-flex items-center justify-center
               bg-white/90 dark:bg-[#0c1427]/90 text-gray-500 dark:text-gray-400 opacity-0 transition-all
               group-hover:opacity-100 hover:text-primary-500 hover:bg-white dark:hover:bg-[#0c1427]">
        <i class="ri-more-fill text-[16px]"></i>
    </button>`;
}

/** Kartların sol üstündeki seçim kutusu — durumu renderSelection() yönetir. */
function checkBox() {
    return `<span data-item-check
        class="absolute top-[8px] ltr:left-[8px] rtl:right-[8px] z-[2] w-[20px] h-[20px] rounded-[6px] border inline-flex items-center justify-center transition-all
               opacity-0 bg-white/90 dark:bg-[#0c1427]/90 border-gray-300 dark:border-[#172036] text-transparent">
        <i class="ri-check-line text-[13px]"></i>
    </span>`;
}

export class MediaBrowser {
    /**
     * @param {HTMLElement} root  [data-media-browser] elemanı
     * @param {{onSelect?: (media: object) => void, onOpen?: (media: object) => void}} options
     */
    constructor(root, options = {}) {
        this.root = root;
        this.options = options;
        this.selectable = root.dataset.selectable === '1';
        this.manageable = root.dataset.manageable === '1';

        this.state = { search: '', type: '', page: 1, per_page: 30 };
        this.path = []; // [{id, name}, ...] — kök hariç, kökten bu yana gezilen klasörler
        this.mode = 'folder'; // 'folder' | 'recent' | 'unattached' — bkz. showRecent()/showUnattached()
        this.view = localStorage.getItem('media-browser-view') === 'list' ? 'list' : 'grid';
        this.folders = new Map(); // bu klasördeki alt klasörler: id -> folder
        this.items = new Map(); // bu klasördeki dosyalar: id -> media
        this.selection = new Set(); // 'folder:5' | 'media:12'
        this.lastSelectedKey = null;
        this.menu = null;
        this.dragDepth = 0;

        // [data-media-grid] artık ızgaranın kendisi değil, "Klasörler" ve
        // "Dosyalar" bölümlerini saran kapsayıcı. Görünüm anahtarı ve shift ile
        // aralık seçimi ona dayandığı için referans adı korunuyor.
        this.grid = root.querySelector('[data-media-grid]');
        this.folderSection = root.querySelector('[data-media-folders-section]');
        this.fileSection = root.querySelector('[data-media-files-section]');
        this.folderList = root.querySelector('[data-media-folders]');
        this.fileList = root.querySelector('[data-media-files]');
        this.list = root.querySelector('[data-media-list]');
        this.listBody = root.querySelector('[data-media-list-body]');
        this.viewToggle = root.querySelector('[data-media-view-toggle]');
        this.status = root.querySelector('[data-media-status]');
        this.pagination = root.querySelector('[data-media-pagination]');
        this.breadcrumb = root.querySelector('[data-media-breadcrumb]');
        this.bulkbar = root.querySelector('[data-media-bulkbar]');

        this.bind();
        this.applyView();
        this.load();
    }

    get currentFolderId() {
        return this.path.length ? this.path.at(-1).id : null;
    }

    bind() {
        let timer;
        this.root.querySelector('[data-media-search]')?.addEventListener('input', (event) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                this.state.search = event.target.value.trim();
                this.state.page = 1;
                this.load();
            }, 300);
        });

        this.root.querySelector('[data-media-type]')?.addEventListener('change', (event) => {
            this.state.type = event.target.value;
            this.state.page = 1;
            this.load();
        });

        this.root.querySelector('[data-media-upload-input]')?.addEventListener('change', (event) => {
            this.upload([...event.target.files]);
            event.target.value = '';
        });

        this.pagination.addEventListener('click', (event) => {
            const link = event.target.closest('[data-page]:not([data-disabled])');

            if (link) {
                this.state.page = Number(link.dataset.page);
                this.load();
            }
        });

        this.breadcrumb.addEventListener('click', (event) => {
            const crumb = event.target.closest('[data-crumb-index]');

            if (crumb) {
                this.mode = 'folder';
                this.path = this.path.slice(0, Number(crumb.dataset.crumbIndex));
                this.state.page = 1;
                this.clearSelection();
                this.load();
            }
        });

        this.viewToggle?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-media-view]');

            if (button) {
                this.view = button.dataset.mediaView;
                localStorage.setItem('media-browser-view', this.view);
                this.applyView();
            }
        });

        this.root.addEventListener('click', (event) => this.onClick(event));
        this.root.addEventListener('dblclick', (event) => this.onDoubleClick(event));
        this.root.addEventListener('contextmenu', (event) => this.onContextMenu(event));

        document.addEventListener('click', (event) => {
            if (this.menu && ! event.target.closest('[data-media-menu]')) {
                this.closeMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (! this.menu) {
                return;
            }

            if (event.key === 'Escape') {
                this.closeMenu();
            }
        });

        this.bindDrag();
        this.bindDropzone();
    }

    async onClick(event) {
        if (event.target.closest('[data-media-action="upload"]')) {
            this.root.querySelector('[data-media-upload-input]').click();

            return;
        }

        if (event.target.closest('[data-media-action="folder-create"]')) {
            this.createFolder();

            return;
        }

        if (event.target.closest('[data-media-action="bulk-move"]')) {
            this.moveSelection();

            return;
        }

        if (event.target.closest('[data-media-action="bulk-delete"]')) {
            this.deleteSelection();

            return;
        }

        if (event.target.closest('[data-media-action="bulk-clear"]')) {
            this.clearSelection();

            return;
        }

        if (event.target.closest('[data-media-action="bulk-select"]')) {
            this.pickSelection();

            return;
        }

        // Karttaki üç nokta: sağ tıkla aynı menüyü butonun altında açar.
        const trigger = event.target.closest('[data-item-menu]');

        if (trigger) {
            // Bu tıklama document'e ulaşırsa menüyü kapatan genel dinleyici
            // (bkz. bind()) menüyü açılır açılmaz kapatır. Sağ tık menüsü
            // 'contextmenu' olayını kullandığı için bu sorunu yaşamıyor.
            event.stopPropagation();

            const owner = trigger.closest('[data-item-key]');

            if (! this.selection.has(owner.dataset.itemKey)) {
                this.selection = new Set([owner.dataset.itemKey]);
                this.lastSelectedKey = owner.dataset.itemKey;
                this.renderSelection();
            }

            const rect = trigger.getBoundingClientRect();
            this.openMenu(rect.left, rect.bottom + 4, owner);

            return;
        }

        const card = event.target.closest('[data-item-key]');

        if (! card) {
            this.clearSelection();

            return;
        }

        // Seçim anında uygulanırsa toplu işlem çubuğu belirip ızgarayı aşağı
        // kaydırıyor; çift tıklamanın ikinci tıkı o zaman kaymış bir karta
        // düşüyor (klasöre girmek yerine yanlışlıkla seçiliyor). Seçimi kısa
        // bir gecikmeyle uygula — asıl çift tıklama gelirse bu bekleyen
        // seçim hiç işlenmeden iptal edilir.
        const { shiftKey, ctrlKey, metaKey } = event;

        clearTimeout(this.clickTimer);
        this.clickTimer = setTimeout(() => {
            this.selectCard(card, { shiftKey, ctrlKey, metaKey });
        }, 220);
    }

    async onDoubleClick(event) {
        clearTimeout(this.clickTimer);

        const card = event.target.closest('[data-item-key]');

        if (! card) {
            return;
        }

        if (card.dataset.itemType === 'folder') {
            this.openFolder(Number(card.dataset.itemId));

            return;
        }

        const media = this.items.get(Number(card.dataset.itemId));

        if (media) {
            this.openPreview(media);
        }
    }

    selectCard(card, event) {
        const key = card.dataset.itemKey;

        if (! this.manageable) {
            // Yönetim kapalıysa (kullanılmıyor artık — her iki sayfa da
            // manageable — ama savunma amaçlı) tek tık sadece seçer.
            this.selection = new Set([key]);
            this.renderSelection();

            return;
        }

        if (event.shiftKey && this.lastSelectedKey) {
            const keys = [...this.grid.querySelectorAll('[data-item-key]')].map((el) => el.dataset.itemKey);
            const from = keys.indexOf(this.lastSelectedKey);
            const to = keys.indexOf(key);

            if (from !== -1 && to !== -1) {
                const [start, end] = from < to ? [from, to] : [to, from];
                this.selection = new Set(keys.slice(start, end + 1));
            }
        } else if (event.ctrlKey || event.metaKey) {
            this.selection.has(key) ? this.selection.delete(key) : this.selection.add(key);
        } else {
            this.selection = this.selection.size === 1 && this.selection.has(key)
                ? new Set()
                : new Set([key]);
        }

        this.lastSelectedKey = key;
        this.renderSelection();
    }

    clearSelection() {
        this.selection.clear();
        this.lastSelectedKey = null;
        this.renderSelection();
    }

    /** Izgara/liste anahtarı — grid ve list her `load()`'da birlikte doldurulur, sadece görünürlük değişir. */
    applyView() {
        if (! this.viewToggle) {
            return;
        }

        this.grid.classList.toggle('hidden', this.view === 'list');
        this.list?.classList.toggle('hidden', this.view !== 'list');

        this.viewToggle.querySelectorAll('[data-media-view]').forEach((button) => {
            const active = button.dataset.mediaView === this.view;
            button.classList.toggle('bg-white', active);
            button.classList.toggle('dark:bg-[#0c1427]', active);
            button.classList.toggle('text-primary-500', active);
            button.classList.toggle('shadow-sm', active);
            button.classList.toggle('text-gray-500', ! active);
            button.classList.toggle('dark:text-gray-400', ! active);
        });
    }

    renderSelection() {
        this.root.querySelectorAll('[data-item-key]').forEach((card) => {
            const selected = this.selection.has(card.dataset.itemKey);
            card.classList.toggle('border-primary-500', selected);
            card.classList.toggle('bg-primary-50/60', selected);
            card.classList.toggle('dark:bg-[#15203c]', selected);
            card.classList.toggle('border-gray-100', ! selected);

            // Seçim kutusu: seçiliyken dolu ve her zaman görünür, boşken yalnızca
            // kartın üzerine gelindiğinde. Çakışan sınıflar (opacity-0 /
            // opacity-100) sıraya güvenmemek için karşılıklı kaldırılıyor.
            const check = card.querySelector('[data-item-check]');

            if (! check) {
                return;
            }

            check.classList.toggle('opacity-100', selected);
            check.classList.toggle('bg-primary-500', selected);
            check.classList.toggle('border-primary-500', selected);
            check.classList.toggle('text-white', selected);
            check.classList.toggle('opacity-0', ! selected);
            check.classList.toggle('group-hover:opacity-100', ! selected);
            check.classList.toggle('bg-white/90', ! selected);
            check.classList.toggle('dark:bg-[#0c1427]/90', ! selected);
            check.classList.toggle('border-gray-300', ! selected);
            check.classList.toggle('dark:border-[#172036]', ! selected);
            check.classList.toggle('text-transparent', ! selected);
        });

        if (! this.bulkbar) {
            return;
        }

        const count = this.selection.size;
        this.bulkbar.classList.toggle('hidden', count === 0);
        this.bulkbar.classList.toggle('flex', count > 0);

        if (count === 0) {
            return;
        }

        this.bulkbar.querySelector('[data-media-bulk-count]').textContent = `${count} öğe seçili`;

        const onlyOneFile = count === 1 && [...this.selection][0].startsWith('media:');
        const selectButton = this.bulkbar.querySelector('[data-media-action="bulk-select"]');

        if (this.selectable && onlyOneFile) {
            if (! selectButton) {
                this.bulkbar.querySelector('[data-media-bulk-count]').insertAdjacentHTML('afterend', `
                    <button type="button" data-media-action="bulk-select"
                        class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400">
                        <i class="material-symbols-outlined !text-[16px]">check</i> Bu Dosyayı Seç
                    </button>`);
            }
        } else {
            selectButton?.remove();
        }
    }

    pickSelection() {
        const key = [...this.selection][0];

        if (key?.startsWith('media:')) {
            this.options.onSelect?.(this.items.get(Number(key.split(':')[1])));
        }
    }

    openFolder(id) {
        const folder = this.folders.get(id);

        this.mode = 'folder';
        this.path.push({ id, name: folder?.name ?? '' });
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    /** Sidebar'ın (varsa) çağırdığı gezinme API'si — bkz. pages/media/index.js. */
    goToRoot() {
        this.mode = 'folder';
        this.path = [];
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    goToFolder(id, name) {
        this.mode = 'folder';
        this.path = [{ id, name }];
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    /**
     * goToFolder gibi ama TAM yolu alır. Sidebar klasör ağacı iç içe bir
     * klasöre atlarken bunu kullanır; aksi halde breadcrumb kökün hemen
     * altındaymış gibi tek seviye gösterirdi.
     *
     * @param {{id: number, name: string}[]} path
     */
    goToPath(path) {
        this.mode = 'folder';
        this.path = path.map(({ id, name }) => ({ id, name }));
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    showRecent() {
        this.mode = 'recent';
        this.path = [];
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    showUnattached() {
        this.mode = 'unattached';
        this.path = [];
        this.state.page = 1;
        this.clearSelection();
        this.load();
    }

    renderBreadcrumb() {
        if (this.mode !== 'folder') {
            const label = this.mode === 'recent' ? 'Son Eklenenler' : 'Bağlantısız Dosyalar';

            this.breadcrumb.innerHTML = `<span class="flex items-center gap-[2px]">
                <button type="button" data-crumb-index="0"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-500 py-[4px] px-[8px] rounded-[8px] hover:bg-gray-50 dark:hover:bg-[#15203c] transition-all">Medya</button>
                <i class="material-symbols-outlined !text-[17px] text-gray-300 dark:text-gray-600">chevron_right</i>
                <span class="text-md font-semibold text-black dark:text-white py-[4px] px-[8px]">${label}</span>
            </span>`;

            return;
        }

        const crumbs = [{ id: null, name: 'Medya' }, ...this.path];

        this.breadcrumb.innerHTML = crumbs.map((crumb, index) => {
            const isLast = index === crumbs.length - 1;

            return `<span class="flex items-center gap-[2px]">
                ${index > 0 ? '<i class="material-symbols-outlined !text-[17px] text-gray-300 dark:text-gray-600">chevron_right</i>' : ''}
                <button type="button" data-crumb-index="${index}" data-crumb-id="${crumb.id ?? ''}"
                    class="py-[4px] px-[8px] rounded-[8px] transition-all ${isLast
                        ? 'text-md font-semibold text-black dark:text-white cursor-default'
                        : 'text-sm text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-gray-50 dark:hover:bg-[#15203c]'}">
                    ${escapeHtml(crumb.name)}
                </button>
            </span>`;
        }).join('');
    }

    async load() {
        this.status.textContent = 'Yükleniyor...';
        this.status.classList.remove('hidden');
        this.renderBreadcrumb();

        // Konum değişti — sidebar (varsa) aktif öğesini buna göre günceller.
        // Yalnızca sidebar tıklamasında değil, breadcrumb ve çift tıklamayla
        // gezinmede de tetiklenir.
        this.options.onNavigate?.({ mode: this.mode, folderId: this.currentFolderId });

        const flat = this.mode !== 'folder';

        try {
            const [folders, filesResponse] = await Promise.all([
                flat || this.state.search ? Promise.resolve([]) : this.loadFolders(),
                http.get('/admin/media/datatable', {
                    ...this.state,
                    folder_id: flat ? '' : (this.currentFolderId ?? ''),
                    unattached: this.mode === 'unattached' ? 1 : '',
                    sort: this.mode === 'recent' ? 'created_at' : undefined,
                    direction: this.mode === 'recent' ? 'desc' : undefined,
                }),
            ]);

            this.folders.clear();
            folders.forEach((folder) => this.folders.set(folder.id, folder));

            this.items.clear();
            filesResponse.data.forEach((media) => this.items.set(media.id, media));

            // Izgara: klasörler ve dosyalar ayrı bölümlerde; boş olan bölüm gizlenir.
            this.folderList.innerHTML = folders.map((folder) => this.folderCard(folder)).join('');
            this.fileList.innerHTML = filesResponse.data.map((media) => this.fileCard(media)).join('');
            this.folderSection.classList.toggle('hidden', folders.length === 0);
            this.fileSection.classList.toggle('hidden', filesResponse.data.length === 0);

            // Liste görünümü tek tablo — klasörler yine üstte.
            this.listBody.innerHTML = folders.map((folder) => this.folderRow(folder)).join('')
                + filesResponse.data.map((media) => this.fileRow(media)).join('');

            const empty = folders.length === 0 && filesResponse.data.length === 0;
            this.status.classList.toggle('hidden', ! empty);

            if (empty) {
                this.renderEmpty();
            }
            this.renderPagination(filesResponse.meta);
            this.renderSelection();
        } catch (error) {
            this.folderList.innerHTML = '';
            this.fileList.innerHTML = '';
            this.folderSection.classList.add('hidden');
            this.fileSection.classList.add('hidden');
            this.listBody.innerHTML = '';
            this.status.classList.remove('hidden');
            this.status.textContent = error instanceof HttpError ? error.message : 'Yüklenemedi.';
        }
    }

    async loadFolders() {
        const { data } = await http.get('/admin/media/folders', { parent_id: this.currentFolderId ?? '' });

        return data;
    }

    /** Klasör kartı — Drive'daki gibi yatay satır, dosyaların üstünde ayrı bölümde. */
    folderCard(folder) {
        return `
            <div data-item-key="folder:${folder.id}" data-item-type="folder" data-item-id="${folder.id}" draggable="true"
                class="group relative flex items-center gap-[10px] py-[11px] ltr:pl-[13px] ltr:pr-[38px] rtl:pr-[13px] rtl:pl-[38px] rounded-[10px] border border-gray-100 dark:border-[#172036] cursor-pointer select-none transition-all hover:border-primary-300 hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="ri-folder-fill text-[21px] text-[#f2b544] shrink-0 leading-none"></i>
                <span class="text-sm text-black dark:text-white truncate grow" title="${escapeHtml(folder.name)}">${escapeHtml(folder.name)}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 shrink-0">${folder.media_count ?? 0}</span>
                ${this.manageable ? menuButton() : ''}
            </div>`;
    }

    /** Dosya kartı — büyük önizleme + üzerine gelince seçim kutusu ve menü. */
    fileCard(media) {
        const type = fileType(media);

        const thumb = media.is_image
            ? `<img src="${escapeHtml(media.thumb)}" alt="${escapeHtml(media.alt ?? '')}" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">`
            : `<i class="${type.icon} ${type.color} text-[40px] leading-none"></i>`;

        return `
            <div data-item-key="media:${media.id}" data-item-type="media" data-item-id="${media.id}" draggable="true"
                class="group relative rounded-[12px] border border-gray-100 dark:border-[#172036] overflow-hidden cursor-pointer select-none transition-all hover:border-primary-300 hover:shadow-[0_4px_14px_-4px_rgba(16,24,40,.14)]">
                ${checkBox()}
                ${this.manageable ? menuButton() : ''}
                <div class="aspect-[4/3] bg-gray-50 dark:bg-[#15203c] flex items-center justify-center overflow-hidden">${thumb}</div>
                <div class="flex items-center gap-[7px] px-[10px] py-[9px] border-t border-gray-100 dark:border-[#172036]">
                    <i class="${type.icon} ${type.color} text-[15px] shrink-0 leading-none"></i>
                    <p class="!mb-0 text-xs text-black dark:text-white truncate" title="${escapeHtml(media.name)}">${escapeHtml(media.name)}</p>
                </div>
            </div>`;
    }

    folderRow(folder) {
        return `
            <tr data-item-key="folder:${folder.id}" data-item-type="folder" data-item-id="${folder.id}" draggable="true"
                class="cursor-pointer select-none transition-all border-b border-gray-100 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <td class="px-[15px] py-[10px]">
                    <span class="flex items-center gap-[8px]">
                        <i class="ri-folder-fill text-[19px] text-[#f2b544] leading-none"></i>
                        ${escapeHtml(folder.name)}
                    </span>
                </td>
                <td class="px-[15px] py-[10px] text-gray-500 dark:text-gray-400 text-sm">${folder.created_at ?? '—'}</td>
                <td class="px-[15px] py-[10px] text-gray-500 dark:text-gray-400 text-sm">${folder.media_count ?? 0} dosya</td>
            </tr>`;
    }

    fileRow(media) {
        const type = fileType(media);
        const icon = media.is_image
            ? `<img src="${escapeHtml(media.thumb)}" alt="" class="w-[28px] h-[28px] rounded-[6px] object-cover">`
            : `<span class="w-[28px] h-[28px] rounded-[6px] bg-gray-50 dark:bg-[#15203c] inline-flex items-center justify-center">
                   <i class="${type.icon} ${type.color} text-[15px] leading-none"></i>
               </span>`;

        return `
            <tr data-item-key="media:${media.id}" data-item-type="media" data-item-id="${media.id}" draggable="true"
                class="cursor-pointer select-none transition-all border-b border-gray-100 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <td class="px-[15px] py-[8px]">
                    <span class="flex items-center gap-[8px]">
                        ${icon}
                        <span class="truncate max-w-[280px]" title="${escapeHtml(media.name)}">${escapeHtml(media.name)}</span>
                    </span>
                </td>
                <td class="px-[15px] py-[8px] text-gray-500 dark:text-gray-400 text-sm">${media.created_at ?? '—'}</td>
                <td class="px-[15px] py-[8px] text-gray-500 dark:text-gray-400 text-sm">${escapeHtml(media.human_size)}</td>
            </tr>`;
    }

    /** Boş klasör / sonuçsuz arama — düz yazı yerine ikonlu durum. */
    renderEmpty() {
        const searching = Boolean(this.state.search);

        this.status.innerHTML = `
            <div class="flex flex-col items-center gap-[10px] py-[20px]">
                <span class="w-[64px] h-[64px] rounded-full bg-gray-50 dark:bg-[#15203c] inline-flex items-center justify-center">
                    <i class="material-symbols-outlined !text-[30px] text-gray-400">${searching ? 'search_off' : 'folder_open'}</i>
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">${searching ? 'Sonuç bulunamadı' : 'Bu klasör boş'}</p>
                <p class="!mb-0 text-sm">${searching
                    ? 'Farklı bir arama terimi deneyin.'
                    : 'Dosyaları buraya sürükleyip bırakabilir ya da Yükle butonunu kullanabilirsiniz.'}</p>
            </div>`;
    }

    renderPagination(meta) {
        if (! meta || meta.last_page <= 1) {
            this.pagination.innerHTML = '';

            return;
        }

        const link = (label, page, { disabled = false, active = false } = {}) => `
            <li class="inline-block mx-[1px]">
                <span data-page="${page}"${disabled ? ' data-disabled' : ''}
                    class="w-[31px] h-[31px] block leading-[29px] text-center rounded-md border transition-all ${
                        active
                            ? 'bg-primary-500 text-white border-primary-500'
                            : `border-gray-100 dark:border-[#172036] ${disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer hover:bg-primary-500 hover:text-white hover:border-primary-500'}`
                    }">${label}</span>
            </li>`;

        const pages = [];
        const start = Math.max(1, meta.current_page - 2);

        for (let page = start; page <= Math.min(meta.last_page, start + 4); page++) {
            pages.push(link(page, page, { active: page === meta.current_page }));
        }

        this.pagination.innerHTML = `
            <div class="flex items-center justify-between flex-wrap gap-[10px]">
                <p class="!mb-0 text-sm">Toplam <strong>${meta.total}</strong> dosya</p>
                <ol class="flex items-center">
                    ${link('<i class="ri-arrow-left-s-line"></i>', meta.current_page - 1, { disabled: meta.current_page === 1 })}
                    ${pages.join('')}
                    ${link('<i class="ri-arrow-right-s-line"></i>', meta.current_page + 1, { disabled: meta.current_page === meta.last_page })}
                </ol>
            </div>`;
    }

    /* ---------------------------------------------------------------- *
     * Önizleme popup'ı
     * ---------------------------------------------------------------- */

    async openPreview(media) {
        const action = await mediaPreview.open(media, { selectable: this.selectable, manageable: this.manageable });

        if (action === 'select') {
            this.options.onSelect?.(media);
        } else if (action === 'edit') {
            this.options.onOpen?.(media);
        } else if (action === 'recrop') {
            this.recrop(media);
        } else if (action === 'delete') {
            this.deleteItems([media.id], []);
        }
    }

    /* ---------------------------------------------------------------- *
     * Context menu (sağ tık)
     * ---------------------------------------------------------------- */

    onContextMenu(event) {
        if (! this.manageable) {
            return;
        }

        event.preventDefault();

        const card = event.target.closest('[data-item-key]');

        if (card && ! this.selection.has(card.dataset.itemKey)) {
            this.selection = new Set([card.dataset.itemKey]);
            this.lastSelectedKey = card.dataset.itemKey;
            this.renderSelection();
        } else if (! card) {
            this.clearSelection();
        }

        this.openMenu(event.clientX, event.clientY, card);
    }

    menuItems(card) {
        if (! card) {
            return [
                { action: 'create-folder', icon: 'create_new_folder', label: 'Yeni Klasör' },
                { action: 'upload', icon: 'upload', label: 'Dosya Yükle' },
            ];
        }

        if (this.selection.size > 1) {
            return [
                { action: 'move', icon: 'drive_file_move', label: 'Taşı' },
                { action: 'delete', icon: 'delete', label: 'Sil', danger: true },
            ];
        }

        if (card.dataset.itemType === 'folder') {
            return [
                { action: 'open', icon: 'folder_open', label: 'Aç' },
                { action: 'rename', icon: 'edit', label: 'Yeniden Adlandır' },
                { action: 'move', icon: 'drive_file_move', label: 'Taşı' },
                { action: 'delete', icon: 'delete', label: 'Sil', danger: true },
            ];
        }

        const media = this.items.get(Number(card.dataset.itemId));

        return [
            { action: 'preview', icon: 'visibility', label: 'Önizle' },
            { action: 'edit', icon: 'edit', label: 'Düzenle' },
            { action: 'move', icon: 'drive_file_move', label: 'Taşı' },
            ...(media?.can_recrop ? [{ action: 'recrop', icon: 'crop', label: 'Yeniden Kırp' }] : []),
            { action: 'download', icon: 'download', label: 'İndir' },
            { action: 'delete', icon: 'delete', label: 'Sil', danger: true },
        ];
    }

    openMenu(x, y, card) {
        this.closeMenu();

        const menu = document.createElement('ul');
        menu.dataset.mediaMenu = '';
        menu.className = 'fixed z-[1403] min-w-[190px] py-[6px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] shadow-3xl';
        menu.innerHTML = this.menuItems(card).map((item) => `
            <li>
                <button type="button" data-menu-action="${item.action}"
                    class="w-full text-left flex items-center gap-[8px] px-[14px] py-[8px] text-sm transition-all ${item.danger ? 'text-danger-500 hover:bg-danger-100 dark:hover:bg-[#15203c]' : 'text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]'}">
                    <i class="material-symbols-outlined !text-[17px]">${item.icon}</i> ${item.label}
                </button>
            </li>`).join('');

        document.body.append(menu);

        const rect = menu.getBoundingClientRect();
        menu.style.left = `${Math.min(x, window.innerWidth - rect.width - 10)}px`;
        menu.style.top = `${Math.min(y, window.innerHeight - rect.height - 10)}px`;

        menu.addEventListener('click', (event) => {
            const action = event.target.closest('[data-menu-action]')?.dataset.menuAction;

            this.closeMenu();

            if (action) {
                this.runMenuAction(action, card);
            }
        });

        this.menu = menu;
    }

    closeMenu() {
        this.menu?.remove();
        this.menu = null;
    }

    runMenuAction(action, card) {
        const media = card?.dataset.itemType === 'media' ? this.items.get(Number(card.dataset.itemId)) : null;
        const folder = card?.dataset.itemType === 'folder' ? this.folders.get(Number(card.dataset.itemId)) : null;

        const actions = {
            'create-folder': () => this.createFolder(),
            upload: () => this.root.querySelector('[data-media-upload-input]').click(),
            open: () => this.openFolder(folder.id),
            preview: () => this.openPreview(media),
            edit: () => this.options.onOpen?.(media),
            recrop: () => this.recrop(media),
            download: () => window.open(media.url, '_blank'),
            rename: () => this.renameFolder(folder),
            move: () => this.moveSelection(),
            delete: () => this.deleteSelection(),
        };

        actions[action]?.();
    }

    /* ---------------------------------------------------------------- *
     * Sürükle-taşı (kart -> klasör kartı / breadcrumb)
     * ---------------------------------------------------------------- */

    bindDrag() {
        this.root.addEventListener('dragstart', (event) => {
            const card = event.target.closest('[data-item-key]');

            if (! card) {
                return;
            }

            if (! this.selection.has(card.dataset.itemKey)) {
                this.selection = new Set([card.dataset.itemKey]);
                this.renderSelection();
            }

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('application/x-media-items', JSON.stringify([...this.selection]));
        });

        const highlight = (target, on) => {
            target?.classList.toggle('!border-primary-500', on);
            target?.classList.toggle('!bg-primary-50', on);
        };

        this.root.addEventListener('dragover', (event) => {
            const folderCard = event.target.closest('[data-item-type="folder"]');
            const crumb = event.target.closest('[data-crumb-index]');

            if (folderCard || crumb) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            }
        });

        this.root.addEventListener('dragenter', (event) => {
            highlight(event.target.closest('[data-item-type="folder"]'), true);
        });

        this.root.addEventListener('dragleave', (event) => {
            highlight(event.target.closest('[data-item-type="folder"]'), false);
        });

        this.root.addEventListener('drop', (event) => {
            const folderCard = event.target.closest('[data-item-type="folder"]');
            const crumb = event.target.closest('[data-crumb-index]');
            const target = folderCard ?? crumb;

            if (! target) {
                return;
            }

            highlight(folderCard, false);

            const raw = event.dataTransfer.getData('application/x-media-items');

            if (! raw) {
                return; // OS dosyası — genel dropzone handler'ı yakalar.
            }

            event.preventDefault();
            event.stopPropagation();

            const targetId = folderCard ? Number(folderCard.dataset.itemId) : (crumb.dataset.crumbId ? Number(crumb.dataset.crumbId) : null);

            if (folderCard && this.selection.has(`folder:${targetId}`)) {
                return; // kendi üzerine bırakma
            }

            this.moveTo(JSON.parse(raw), targetId);
        });
    }

    bindDropzone() {
        const zone = this.root.querySelector('[data-media-dropzone]');
        const hint = this.root.querySelector('[data-media-drop-hint]');

        if (! zone) {
            return;
        }

        zone.addEventListener('dragenter', (event) => {
            if (! [...event.dataTransfer.types].includes('Files')) {
                return;
            }

            event.preventDefault();
            this.dragDepth++;
            hint.classList.remove('hidden');
        });

        zone.addEventListener('dragover', (event) => {
            if ([...event.dataTransfer.types].includes('Files')) {
                event.preventDefault();
            }
        });

        zone.addEventListener('dragleave', () => {
            if (--this.dragDepth <= 0) {
                this.dragDepth = 0;
                hint.classList.add('hidden');
            }
        });

        zone.addEventListener('drop', (event) => {
            if (! [...event.dataTransfer.types].includes('Files')) {
                return;
            }

            event.preventDefault();
            this.dragDepth = 0;
            hint.classList.add('hidden');

            // Bir klasör kartının üstüne bırakıldıysa doğrudan o klasöre yükle.
            const folderCard = event.target.closest('[data-item-type="folder"]');
            this.upload([...event.dataTransfer.files], folderCard ? Number(folderCard.dataset.itemId) : undefined);
        });
    }

    /* ---------------------------------------------------------------- *
     * Aksiyonlar
     * ---------------------------------------------------------------- */

    /** @param {File[]} files */
    async upload(files, folderId = undefined) {
        const accepted = files.filter((file) => file.type.startsWith('image/') || file.name.endsWith('.svg'));

        if (accepted.length === 0) {
            return;
        }

        this.status.classList.remove('hidden');

        const target = folderId !== undefined ? folderId : this.currentFolderId;

        for (const [index, file] of accepted.entries()) {
            this.status.textContent = `Yükleniyor... (${index + 1}/${accepted.length})`;

            const body = new FormData();
            body.append('file', file);

            if (target) {
                body.append('folder_id', target);
            }

            try {
                await http.post('/admin/media/upload', body);
            } catch (error) {
                toast.error(`${file.name}: ${error instanceof HttpError ? error.message : 'yüklenemedi'}`);
            }
        }

        toast.success(`${accepted.length} dosya yüklendi.`);
        this.load();
    }

    async recrop(media) {
        const preset = media.preset ?? null;
        const size = { width: media.width, height: media.height, label: 'Mevcut oran' };

        try {
            // 'original' saklanan orijinaldir — 'url' önceki kırpımın sonucudur,
            // kaynak olarak kullanılırsa her seferinde biraz daha fazla kırpar.
            const response = await fetch(media.original ?? media.url, { credentials: 'same-origin' });
            const blob = await response.blob();
            const crop = await cropModal.open(new File([blob], media.name, { type: blob.type }), { ...size, preset });

            if (! crop) {
                return;
            }

            const { message } = await http.post(`/admin/media/${media.id}/recrop`, { crop });
            toast.success(message);
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Yeniden kırpılamadı.');
        }
    }

    async createFolder() {
        const name = await promptText('Yeni klasör adı');

        if (! name) {
            return;
        }

        try {
            const { message } = await http.post('/admin/media/folders', {
                name,
                parent_id: this.currentFolderId,
            });
            toast.success(message);
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Klasör oluşturulamadı.');
        }
    }

    async renameFolder(folder) {
        const name = await promptText('Klasörü yeniden adlandır', { value: folder.name });

        if (! name || name === folder.name) {
            return;
        }

        try {
            const { message } = await http.put(`/admin/media/folders/${folder.id}`, { name });
            toast.success(message);
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Klasör güncellenemedi.');
        }
    }

    async moveSelection() {
        const { media, folders } = this.splitSelection();

        if (media.length === 0 && folders.length === 0) {
            return;
        }

        await this.pickAndMove(media, folders);
    }

    async moveTo(keys, targetFolderId) {
        const { media, folders } = this.splitSelection(keys);

        try {
            const { message } = await http.post('/admin/media/bulk-move', {
                media,
                folders,
                target_folder_id: targetFolderId,
            });
            toast.success(message);
            this.clearSelection();
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Taşınamadı.');
        }
    }

    async pickAndMove(media, folders) {
        try {
            const { data: tree } = await http.get('/admin/media/folders/tree');
            const targetId = await folderPicker.open(tree, { excludeIds: folders, currentId: this.currentFolderId });

            if (targetId === undefined) {
                return;
            }

            const { message } = await http.post('/admin/media/bulk-move', {
                media,
                folders,
                target_folder_id: targetId,
            });
            toast.success(message);
            this.clearSelection();
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Taşınamadı.');
        }
    }

    async deleteSelection() {
        const { media, folders } = this.splitSelection();

        await this.deleteItems(media, folders);
    }

    async deleteItems(media, folders) {
        if (media.length === 0 && folders.length === 0) {
            return;
        }

        const count = media.length + folders.length;

        if (! await confirm(`${count} öğe kalıcı olarak silinecek.`, { title: 'Öğeleri sil', accept: 'Evet, sil' })) {
            return;
        }

        try {
            const { message } = await http.post('/admin/media/bulk-delete', { media, folders });
            toast.success(message);
            this.clearSelection();
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
        }
    }

    splitSelection(keys = null) {
        const media = [];
        const folders = [];

        for (const key of keys ?? this.selection) {
            const [type, id] = key.split(':');
            (type === 'media' ? media : folders).push(Number(id));
        }

        return { media, folders };
    }
}
