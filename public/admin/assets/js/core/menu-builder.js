/**
 * Menü yöneticisi ağacı — iç içe sürükle-bırak, öğe kartları, ekle/düzenle/sil.
 *
 * Tek sayfa, modal yok (öğe ekleme/bağlantı değiştirme için ayrı bir inline
 * modal var — o da bu dosyadan yönetilir). Ağaç DOM'da tutulur; her sürükle
 * bırakışında tüm yapı serialize edilip tek PUT ile kaydedilir.
 *
 * Sunucu sözleşmesi:
 *   POST   /admin/menu/{id}/items      yeni öğe
 *   PUT    /admin/menu-item/{id}       öğe güncelle
 *   DELETE /admin/menu-item/{id}       öğe sil (alt öğeler DB'de cascade)
 *   PUT    /admin/menu/{id}/tree       { nodes: [{id, children:[...]}] }
 */

import { confirm } from './confirm.js';
import { escapeHtml, http, HttpError, ValidationError } from './http.js';
import { toast } from './toast.js';

const TYPE_LABELS = {
    url: 'Özel bağlantı',
    route: 'Hazır bağlantı',
    linkable: 'Kayda bağlı',
};

export class MenuBuilder {
    constructor(root, workspace) {
        this.root = root;
        this.workspace = workspace;
        this.menuId = workspace.menu.id;
        this.maxDepth = workspace.maxDepth;

        this.treeHost = root.querySelector('[data-menu-tree]');
        this.emptyState = root.querySelector('[data-menu-empty]');
        this.savingHint = root.querySelector('[data-menu-saving]');

        // id -> düğüm verisi (kartların link bilgisini elde tutar; inline
        // düzenlemede güncelleme isteği tüm link alanlarını göndermek zorunda).
        this.nodes = new Map();
        this.sortables = [];

        this.modal = new ItemModal(root, workspace, (payload, itemId) => this.persistItem(payload, itemId));

        this.render(workspace.tree);
        this.bindToolbar();
    }

    /* ------------------------------------------------------------------ *
     | Render
     * ------------------------------------------------------------------ */

    render(tree) {
        this.nodes.clear();
        this.indexNodes(tree);

        this.treeHost.innerHTML = '<div class="menu-list" data-list></div>';
        this.renderInto(this.treeHost.firstElementChild, tree);

        this.emptyState.classList.toggle('hidden', tree.length > 0);
        this.treeHost.classList.toggle('hidden', tree.length === 0);

        this.initSortables();
    }

    indexNodes(nodes) {
        nodes.forEach((node) => {
            this.nodes.set(node.id, node);
            this.indexNodes(node.children ?? []);
        });
    }

    renderInto(listEl, nodes) {
        nodes.forEach((node) => listEl.appendChild(this.nodeElement(node)));
    }

    nodeElement(node) {
        const el = document.createElement('div');
        el.className = 'menu-node';
        el.dataset.node = '';
        el.dataset.id = node.id;

        el.innerHTML = `
            <div class="menu-node-head flex items-center gap-[10px] p-[12px] rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50/60 dark:bg-[#15203c]">
                <button type="button" data-drag class="menu-drag cursor-grab text-gray-400 hover:text-primary-500 leading-none shrink-0" title="Taşı">
                    <i class="material-symbols-outlined !text-[20px]">drag_indicator</i>
                </button>

                <div class="min-w-0 grow">
                    <span class="font-medium text-sm text-black dark:text-white block truncate" data-node-label>${escapeHtml(node.resolved_label || '(adsız)')}</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block truncate">
                        ${escapeHtml(TYPE_LABELS[node.link_type] ?? node.link_type)} · ${escapeHtml(node.target_summary || '—')}
                    </span>
                </div>

                ${node.status ? '' : '<span class="text-[10px] font-medium py-[1px] px-[8px] text-danger-500 bg-danger-100 dark:bg-[#ffffff14] rounded-sm shrink-0">Gizli</span>'}
                ${node.target === '_blank' ? '<i class="material-symbols-outlined !text-[16px] text-gray-400 shrink-0" title="Yeni sekmede açılır">open_in_new</i>' : ''}

                <button type="button" data-toggle class="text-gray-400 hover:text-primary-500 leading-none shrink-0" title="Düzenle">
                    <i class="material-symbols-outlined !text-[20px]">expand_more</i>
                </button>
                <button type="button" data-remove class="text-gray-400 hover:text-danger-500 leading-none shrink-0" title="Sil">
                    <i class="material-symbols-outlined !text-[20px]">delete</i>
                </button>
            </div>

            <div class="menu-node-body hidden pt-[12px] pb-[4px] px-[4px]">
                ${this.bodyMarkup(node)}
            </div>

            <div class="menu-list menu-list-nested" data-list></div>
        `;

        // Gerçek derinlik listDepth() ile DOM'dan hesaplanır; öznitelik tutulmaz.
        this.renderInto(el.querySelector('[data-list]'), node.children ?? []);

        return el;
    }

    bodyMarkup(node) {
        return `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px]">
                <div>
                    <label class="text-xs font-medium text-black dark:text-white mb-[6px] block">Menüde Görünen Ad</label>
                    <input type="text" data-field="label" value="${escapeHtml(node.label ?? '')}"
                        placeholder="${escapeHtml(node.link_type === 'linkable' ? 'Boş = kaydın adı' : '')}"
                        class="h-[38px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 transition-all focus:border-primary-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-black dark:text-white mb-[6px] block">Açılış Şekli</label>
                    <select data-field="target"
                        class="h-[38px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[10px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500">
                        <option value="_self" ${node.target === '_self' ? 'selected' : ''}>Aynı sekmede</option>
                        <option value="_blank" ${node.target === '_blank' ? 'selected' : ''}>Yeni sekmede</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center flex-wrap gap-[12px] mt-[14px]">
                <label class="flex items-center gap-[8px] cursor-pointer select-none text-sm text-black dark:text-white">
                    <input type="checkbox" data-field="status" ${node.status ? 'checked' : ''} class="w-[16px] h-[16px] accent-primary-500">
                    Menüde göster
                </label>

                <button type="button" data-edit-link class="inline-flex items-center gap-[5px] text-xs text-gray-600 dark:text-gray-300 py-[7px] px-[12px] rounded-md border border-gray-200 dark:border-[#172036] hover:border-primary-500 transition-all">
                    <i class="material-symbols-outlined !text-[15px]">link</i> Bağlantıyı Değiştir
                </button>

                <button type="button" data-save-item class="ltr:ml-auto rtl:mr-auto inline-flex items-center gap-[5px] text-xs text-white py-[7px] px-[14px] rounded-md bg-primary-500 hover:bg-primary-400 transition-all">
                    <i class="material-symbols-outlined !text-[15px]">check</i> Kaydet
                </button>
            </div>
        `;
    }

    /* ------------------------------------------------------------------ *
     | Sortable
     * ------------------------------------------------------------------ */

    async initSortables() {
        this.sortables.forEach((s) => s.destroy());
        this.sortables = [];

        const { default: Sortable } = await import('../vendor/sortablejs/sortable.esm.js');

        this.root.querySelectorAll('[data-list]').forEach((list) => {
            this.sortables.push(Sortable.create(list, {
                group: 'menu-items',
                handle: '[data-drag]',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.6,
                ghostClass: 'menu-node-ghost',
                onMove: (evt) => this.canDrop(evt),
                onEnd: () => this.saveTree(),
            }));
        });
    }

    /** Bırakılan yerin derinliği + taşınan dalın yüksekliği sınırı aşmasın. */
    canDrop(evt) {
        const targetDepth = this.listDepth(evt.to);
        const draggedHeight = this.subtreeHeight(evt.dragged);

        return targetDepth + 1 + draggedHeight <= this.maxDepth;
    }

    /** Bir listenin kök listeye göre iç içe geçme derinliği (kök = 0). */
    listDepth(list) {
        let depth = 0;
        let node = list.parentElement?.closest('[data-node]');

        while (node) {
            depth += 1;
            node = node.parentElement?.closest('[data-node]');
        }

        return depth;
    }

    /** Bir düğümün altındaki en derin çocuğun kaç kat aşağıda olduğu. */
    subtreeHeight(nodeEl) {
        const childNodes = nodeEl.querySelectorAll(':scope > [data-list] > [data-node]');

        if (childNodes.length === 0) {
            return 0;
        }

        return 1 + Math.max(...[...childNodes].map((child) => this.subtreeHeight(child)));
    }

    /* ------------------------------------------------------------------ *
     | Persist
     * ------------------------------------------------------------------ */

    serialize(listEl = this.treeHost.querySelector('[data-list]')) {
        return [...listEl.querySelectorAll(':scope > [data-node]')].map((node) => ({
            id: Number(node.dataset.id),
            children: this.serialize(node.querySelector(':scope > [data-list]')),
        }));
    }

    async saveTree() {
        this.savingHint.classList.remove('hidden');
        this.savingHint.classList.add('flex');

        try {
            const { data } = await http.put(`/admin/menu/${this.menuId}/tree`, { nodes: this.serialize() });
            // Sunucu temizlenmiş ağacı döndürür; state'i tazele (kart görünümü
            // değişmez, yalnızca nodes haritası güncel kalsın).
            this.nodes.clear();
            this.indexNodes(data);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Sıralama kaydedilemedi.');
            // Reddedilen bir taşımada sunucudaki gerçek ağaca geri dön.
            await this.reload();
        } finally {
            this.savingHint.classList.add('hidden');
            this.savingHint.classList.remove('flex');
        }
    }

    async reload() {
        const { data } = await http.get(`/admin/menu/${this.menuId}/tree`);
        this.render(data);
    }

    /** Modal'dan gelir: itemId varsa güncelle, yoksa yeni öğe. */
    async persistItem(payload, itemId) {
        if (itemId) {
            const { data } = await http.put(`/admin/menu-item/${itemId}`, payload);
            this.replaceNode(itemId, data);
            toast.success('Menü öğesi güncellendi.');

            return;
        }

        const { data } = await http.post(`/admin/menu/${this.menuId}/items`, payload);
        this.appendNode(data);
        toast.success('Menü öğesi eklendi.');
    }

    appendNode(node) {
        node.children = node.children ?? [];
        this.nodes.set(node.id, node);

        const list = this.treeHost.querySelector('[data-list]')
            ?? (this.render([]), this.treeHost.querySelector('[data-list]'));

        list.appendChild(this.nodeElement(node));
        this.emptyState.classList.add('hidden');
        this.treeHost.classList.remove('hidden');
        this.initSortables();
    }

    replaceNode(id, node) {
        const el = this.root.querySelector(`[data-node][data-id="${id}"]`);

        if (! el) {
            return this.reload();
        }

        node.children = this.nodes.get(id)?.children ?? [];
        this.nodes.set(id, node);

        const fresh = this.nodeElement(node);
        // Alt öğeleri koru — nodeElement onları state'ten yeniden basıyor.
        el.replaceWith(fresh);
        this.initSortables();
    }

    /* ------------------------------------------------------------------ *
     | Toolbar + kart olayları
     * ------------------------------------------------------------------ */

    bindToolbar() {
        this.root.querySelector('[data-menu-add]')?.addEventListener('click', () => this.modal.open(null));

        this.treeHost.addEventListener('click', (event) => this.onTreeClick(event));

        this.root.querySelector('[data-menu-settings]')?.addEventListener('submit', (event) => this.saveSettings(event));
    }

    async onTreeClick(event) {
        const nodeEl = event.target.closest('[data-node]');

        if (! nodeEl) {
            return;
        }

        const id = Number(nodeEl.dataset.id);

        if (event.target.closest('[data-toggle]')) {
            const body = nodeEl.querySelector(':scope > .menu-node-body');
            const open = body.classList.toggle('hidden');
            event.target.closest('[data-toggle]').querySelector('i').textContent = open ? 'expand_more' : 'expand_less';

            return;
        }

        if (event.target.closest('[data-remove]')) {
            return this.removeNode(id, nodeEl);
        }

        if (event.target.closest('[data-edit-link]')) {
            return this.modal.open(this.nodes.get(id));
        }

        if (event.target.closest('[data-save-item]')) {
            return this.saveInline(id, nodeEl, event.target.closest('[data-save-item]'));
        }
    }

    async removeNode(id, nodeEl) {
        const childCount = nodeEl.querySelectorAll(':scope > [data-list] [data-node]').length;
        const message = childCount > 0
            ? `Bu öğe ve altındaki ${childCount} öğe silinsin mi?`
            : 'Bu menü öğesi silinsin mi?';

        if (! await confirm(message, { title: 'Menü öğesini sil', accept: 'Evet, sil' })) {
            return;
        }

        try {
            await http.delete(`/admin/menu-item/${id}`);
            nodeEl.remove();
            this.nodes.delete(id);

            const empty = this.treeHost.querySelectorAll('[data-node]').length === 0;
            this.emptyState.classList.toggle('hidden', ! empty);
            this.treeHost.classList.toggle('hidden', empty);

            toast.success('Menü öğesi silindi.');
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
        }
    }

    /** Kart içi hızlı düzenleme: yalnızca ad/hedef/görünürlük. Link alanları
     *  state'ten alınır (güncelleme isteği hepsini bekliyor). */
    async saveInline(id, nodeEl, button) {
        const node = this.nodes.get(id);
        const body = nodeEl.querySelector(':scope > .menu-node-body');

        const payload = {
            ...this.linkFields(node),
            label: body.querySelector('[data-field="label"]').value.trim() || null,
            target: body.querySelector('[data-field="target"]').value,
            status: body.querySelector('[data-field="status"]').checked,
        };

        button.disabled = true;

        try {
            const { data } = await http.put(`/admin/menu-item/${id}`, payload);
            this.replaceNode(id, data);
            toast.success('Menü öğesi güncellendi.');
        } catch (error) {
            if (error instanceof ValidationError) {
                toast.error(Object.values(error.errors)[0][0]);
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
            }
        } finally {
            button.disabled = false;
        }
    }

    linkFields(node) {
        return {
            link_type: node.link_type,
            url: node.url,
            route_name: node.route_name,
            linkable_type: node.linkable_type,
            linkable_id: node.linkable_id,
        };
    }

    async saveSettings(event) {
        event.preventDefault();
        const form = event.target;
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;

        try {
            await http.put(`/admin/menu/${this.menuId}`, { title: form.querySelector('[name="title"]').value });
            toast.success('Menü başlığı güncellendi.');
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        } finally {
            button.disabled = false;
        }
    }
}

/* ====================================================================== *
 | Öğe ekle / bağlantı değiştir modalı
 * ====================================================================== */

class ItemModal {
    constructor(root, workspace, onSubmit) {
        this.el = root.querySelector('[data-menu-item-modal]');
        this.form = this.el.querySelector('[data-menu-item-form]');
        this.title = this.el.querySelector('[data-menu-item-modal-title]');
        this.workspace = workspace;
        this.onSubmit = onSubmit;
        this.editingId = null;

        this.bind();
    }

    bind() {
        this.el.addEventListener('click', (event) => {
            if (event.target === this.el || event.target.closest('[data-modal-close]')) {
                this.close();
            }
        });

        this.el.querySelectorAll('[data-menu-type]').forEach((button) => {
            button.addEventListener('click', () => this.setType(button.dataset.menuType));
        });

        this.form.addEventListener('submit', (event) => this.submit(event));
    }

    open(node) {
        this.editingId = node?.id ?? null;
        this.title.textContent = node ? 'Menü Öğesini Düzenle' : 'Yeni Menü Öğesi';

        this.reset();

        if (node) {
            this.fill(node);
        } else {
            this.setType('url');
        }

        this.el.classList.add('active');
        document.body.classList.add('overflow-hidden');
    }

    close() {
        this.el.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    }

    reset() {
        this.form.reset();
        this.form.querySelectorAll('[data-error]').forEach((span) => (span.textContent = ''));
        this.syncChoice('menu-item-route', '');
        this.syncChoice('menu-item-linkable', '');
        this.syncChoice('menu-item-target', '_self');
    }

    fill(node) {
        this.setType(node.link_type);
        this.form.querySelector('[name="label"]').value = node.label ?? '';
        this.syncChoice('menu-item-target', node.target ?? '_self');

        if (node.link_type === 'url') {
            this.form.querySelector('[name="url"]').value = node.url ?? '';
        } else if (node.link_type === 'route') {
            this.syncChoice('menu-item-route', node.route_name ?? '');
        } else if (node.link_type === 'linkable') {
            const key = this.keyForModel(node.linkable_type);
            this.syncChoice('menu-item-linkable', key ? `${key}:${node.linkable_id}` : '');
        }
    }

    setType(type) {
        this.form.querySelector('[name="link_type"]').value = type;

        this.el.querySelectorAll('[data-menu-type]').forEach((button) => {
            const on = button.dataset.menuType === type;
            button.classList.toggle('border-primary-500', on);
            button.classList.toggle('bg-primary-50', on);
            button.classList.toggle('dark:bg-[#15203c]', on);
        });

        this.el.querySelectorAll('[data-when]').forEach((section) => {
            section.hidden = section.dataset.when !== type;
        });
    }

    async submit(event) {
        event.preventDefault();

        const button = this.form.querySelector('[type="submit"]');
        const payload = this.payload();

        this.form.querySelectorAll('[data-error]').forEach((span) => (span.textContent = ''));
        button.disabled = true;

        try {
            await this.onSubmit(payload, this.editingId);
            this.close();
        } catch (error) {
            if (error instanceof ValidationError) {
                Object.entries(error.errors).forEach(([field, messages]) => {
                    const span = this.form.querySelector(`[data-error="${field}"]`);

                    if (span) {
                        span.textContent = messages[0];
                    }
                });
                toast.error('Girilen bilgileri kontrol edin.');
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
            }
        } finally {
            button.disabled = false;
        }
    }

    payload() {
        const type = this.form.querySelector('[name="link_type"]').value;
        const data = {
            link_type: type,
            label: this.form.querySelector('[name="label"]').value.trim() || null,
            target: this.form.querySelector('[name="target"]').value,
            status: true,
        };

        if (type === 'url') {
            data.url = this.form.querySelector('[name="url"]').value.trim();
        } else if (type === 'route') {
            data.route_name = this.form.querySelector('[name="route_name"]').value;
        } else if (type === 'linkable') {
            const [key, id] = (this.form.querySelector('[data-menu-linkable]').value || '').split(':');
            data.linkable_type = key ? this.workspaceModel(key) : null;
            data.linkable_id = id ? Number(id) : null;
        }

        return data;
    }

    /* Choices.js örneği select üzerinde saklanır; native select'e yazmak
       arayüzü tazelemez, API üzerinden set edilir. */
    syncChoice(id, value) {
        const select = this.form.querySelector(`#${id}`);

        if (! select) {
            return;
        }

        if (select.choicesInstance) {
            select.choicesInstance.setChoiceByValue(value || '');
        } else {
            select.value = value;
        }
    }

    workspaceModel(key) {
        return this.workspace.linkables?.[key]?.model ?? null;
    }

    keyForModel(model) {
        return Object.entries(this.workspace.linkables ?? {})
            .find(([, group]) => group.model === model)?.[0] ?? null;
    }
}
