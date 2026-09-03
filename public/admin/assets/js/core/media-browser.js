/**
 * Medya tarayıcısının davranışı. Markup'ı
 * resources/views/admin/pages/media/partials/browser.blade.php verir.
 *
 * Hem /admin/media sayfası hem de form içinden açılan seçici modal aynı
 * sınıfı kullanır; fark yalnızca `selectable` ve `manageable` bayraklarıdır.
 */

import { escapeHtml, http, HttpError } from './http.js';
import { toast } from './toast.js';
import { confirm } from './confirm.js';
import { cropModal } from './cropper.js';

const ACTIVE_FOLDER = ['bg-primary-50', 'dark:bg-[#15203c]', '!text-primary-500'];

export class MediaBrowser {
    /**
     * @param {HTMLElement} root  [data-media-browser] elemanı
     * @param {{onSelect?: (media: object) => void}} options
     */
    constructor(root, options = {}) {
        this.root = root;
        this.options = options;
        this.selectable = root.dataset.selectable === '1';
        this.manageable = root.dataset.manageable === '1';
        this.items = new Map();

        this.state = { search: '', folder_id: '', type: '', unattached: false, page: 1, per_page: 30 };

        this.grid = root.querySelector('[data-media-grid]');
        this.status = root.querySelector('[data-media-status]');
        this.pagination = root.querySelector('[data-media-pagination]');

        this.bind();
        this.load();
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

        this.root.addEventListener('click', (event) => this.onClick(event));

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

        this.bindDropzone();
    }

    bindDropzone() {
        const zone = this.root.querySelector('[data-media-dropzone]');
        const hint = this.root.querySelector('[data-media-drop-hint]');

        if (! zone) {
            return;
        }

        let depth = 0;

        zone.addEventListener('dragenter', (event) => {
            event.preventDefault();
            depth++;
            hint.classList.remove('hidden');
        });

        zone.addEventListener('dragover', (event) => event.preventDefault());

        zone.addEventListener('dragleave', () => {
            if (--depth <= 0) {
                depth = 0;
                hint.classList.add('hidden');
            }
        });

        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            depth = 0;
            hint.classList.add('hidden');
            this.upload([...event.dataTransfer.files]);
        });
    }

    async onClick(event) {
        const folder = event.target.closest('[data-folder-id]');

        if (folder) {
            this.selectFolder(folder);

            return;
        }

        if (event.target.closest('[data-media-filter="unattached"]')) {
            this.toggleUnattached(event.target.closest('[data-media-filter]'));

            return;
        }

        if (event.target.closest('[data-media-action="upload"]')) {
            this.root.querySelector('[data-media-upload-input]').click();

            return;
        }

        if (event.target.closest('[data-media-action="folder-create"]')) {
            this.createFolder();

            return;
        }

        const card = event.target.closest('[data-media-id]');

        if (! card) {
            return;
        }

        const media = this.items.get(Number(card.dataset.mediaId));
        const action = event.target.closest('[data-card-action]')?.dataset.cardAction;

        if (action === 'delete') {
            this.remove(media);
        } else if (action === 'recrop') {
            this.recrop(media);
        } else if (this.selectable) {
            this.options.onSelect?.(media);
        } else {
            this.options.onOpen?.(media);
        }
    }

    selectFolder(button) {
        this.root.querySelectorAll('.media-folder').forEach((element) => element.classList.remove(...ACTIVE_FOLDER));
        button.classList.add(...ACTIVE_FOLDER);

        this.state.folder_id = button.dataset.folderId ?? '';
        this.state.unattached = false;
        this.state.page = 1;
        this.load();
    }

    toggleUnattached(button) {
        this.root.querySelectorAll('.media-folder').forEach((element) => element.classList.remove(...ACTIVE_FOLDER));
        button.classList.add(...ACTIVE_FOLDER);

        this.state.unattached = true;
        this.state.folder_id = '';
        this.state.page = 1;
        this.load();
    }

    async load() {
        this.status.textContent = 'Yükleniyor...';
        this.status.classList.remove('hidden');

        try {
            const { data, meta } = await http.get('/admin/media/datatable', {
                ...this.state,
                unattached: this.state.unattached ? 1 : '',
            });

            this.items.clear();
            data.forEach((media) => this.items.set(media.id, media));

            this.grid.innerHTML = data.map((media) => this.card(media)).join('');
            this.status.classList.toggle('hidden', data.length > 0);
            this.status.textContent = 'Bu klasörde dosya yok.';
            this.renderPagination(meta);
        } catch (error) {
            this.grid.innerHTML = '';
            this.status.classList.remove('hidden');
            this.status.textContent = error instanceof HttpError ? error.message : 'Dosyalar yüklenemedi.';
        }
    }

    card(media) {
        const thumb = media.is_image
            ? `<img src="${escapeHtml(media.thumb)}" alt="${escapeHtml(media.alt ?? '')}" loading="lazy" class="w-full h-full object-cover">`
            : `<div class="w-full h-full flex items-center justify-center text-gray-400">
                   <i class="material-symbols-outlined !text-[34px]">draft</i>
               </div>`;

        const tools = this.manageable ? `
            <div class="absolute top-[6px] ltr:right-[6px] rtl:left-[6px] flex gap-[4px] opacity-0 group-hover:opacity-100 transition-all">
                ${media.can_recrop ? `<button type="button" data-card-action="recrop" title="Yeniden kırp"
                    class="w-[26px] h-[26px] rounded-md bg-white/90 dark:bg-[#0c1427]/90 text-black dark:text-white inline-flex items-center justify-center hover:bg-primary-500 hover:text-white">
                    <i class="material-symbols-outlined !text-[15px]">crop</i></button>` : ''}
                <button type="button" data-card-action="delete" title="Sil"
                    class="w-[26px] h-[26px] rounded-md bg-white/90 dark:bg-[#0c1427]/90 text-black dark:text-white inline-flex items-center justify-center hover:bg-danger-500 hover:text-white">
                    <i class="material-symbols-outlined !text-[15px]">delete</i></button>
            </div>` : '';

        return `
            <div data-media-id="${media.id}"
                class="group relative rounded-md border border-gray-100 dark:border-[#172036] overflow-hidden cursor-pointer transition-all hover:border-primary-500">
                <div class="aspect-square bg-gray-50 dark:bg-[#15203c]">${thumb}</div>
                ${tools}
                <div class="p-[8px]">
                    <p class="!mb-0 text-xs text-black dark:text-white truncate" title="${escapeHtml(media.name)}">${escapeHtml(media.name)}</p>
                    <p class="!mb-0 text-[11px] text-gray-500 dark:text-gray-400">
                        ${media.width ? `${media.width}×${media.height} · ` : ''}${escapeHtml(media.human_size)}
                    </p>
                </div>
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

    /** @param {File[]} files */
    async upload(files) {
        const accepted = files.filter((file) => file.type.startsWith('image/') || file.name.endsWith('.svg'));

        if (accepted.length === 0) {
            return;
        }

        this.status.classList.remove('hidden');

        for (const [index, file] of accepted.entries()) {
            this.status.textContent = `Yükleniyor... (${index + 1}/${accepted.length})`;

            const body = new FormData();
            body.append('file', file);

            if (this.state.folder_id) {
                body.append('folder_id', this.state.folder_id);
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

    async remove(media) {
        if (! await confirm(`"${media.name}" kalıcı olarak silinecek.`)) {
            return;
        }

        try {
            const { message } = await http.delete(`/admin/media/${media.id}`);
            toast.success(message);
            this.load();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Dosya silinemedi.');
        }
    }

    async recrop(media) {
        const preset = media.preset ?? null;
        const size = { width: media.width, height: media.height, label: 'Mevcut oran' };

        try {
            const response = await fetch(media.url, { credentials: 'same-origin' });
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
        const name = window.prompt('Klasör adı');

        if (! name?.trim()) {
            return;
        }

        try {
            const { message } = await http.post('/admin/media/folders', {
                name: name.trim(),
                parent_id: this.state.folder_id || null,
            });
            toast.success(message);
            window.location.reload();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Klasör oluşturulamadı.');
        }
    }
}
