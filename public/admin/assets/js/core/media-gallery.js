/**
 * <x-admin::form.image multiple> — çoklu görsel (galeri) modu.
 *
 * Tekil moddan farkı: alan bir liste tutar. Üstteki büyük çerçeve o an seçili
 * görseli gösterir, altındaki şeritte tüm görsellerin küçük önizlemeleri yan
 * yana kayar. Her küçük önizlemede kapak yapma ve kaldırma düğmesi vardır.
 *
 * Forma iki şey gider:
 *   name[]      -> sıralı medya id'leri
 *   name_cover  -> kapak olarak işaretlenen medya id'si (boş olabilir)
 *
 * Sunucuda `$model->syncMedia($ids, 'gallery', $coverId)` bunları karşılar;
 * kapak bilgisi `mediables.is_cover` kolonunda saklanır.
 *
 * Tekil alanlar core/media-field.js'e aittir — buradaki dinleyiciler
 * `data-media-multiple` taşımayan alanları görmezden gelir.
 */

import { confirm } from './confirm.js';
import { mediaPicker } from './media-picker.js';
import { uploadWithCrop } from './media-upload.js';

/** Sadece çoklu alanlar; tekil alanlar core/media-field.js'e ait. */
function field(element) {
    const root = element.closest('[data-media-field]');

    return root?.dataset.mediaMultiple ? root : null;
}

function parts(root) {
    return {
        inputs: root.querySelector('[data-media-inputs]'),
        cover: root.querySelector('[data-media-cover-input]'),
        file: root.querySelector('[data-media-file]'),
        image: root.querySelector('[data-media-image]'),
        preview: root.querySelector('[data-media-preview]'),
        empty: root.querySelector('[data-media-empty]'),
        info: root.querySelector('[data-media-info]'),
        strip: root.querySelector('[data-media-strip]'),
        thumbs: root.querySelector('[data-media-thumbs]'),
        remove: root.querySelector('[data-media-action="remove"]'),
        selectLabel: root.querySelector('[data-media-select-label]'),
    };
}

/*
 * Alanın durumu DOM'da değil burada tutulur: her alan kökü için görsel listesi
 * ve seçili/kapak id'leri. Modal her açıldığında yeni bir kök geldiği için
 * WeakMap kullanılıyor — kök DOM'dan düşünce kayıt da düşer.
 */
const states = new WeakMap();

function state(root) {
    if (! states.has(root)) {
        states.set(root, {
            items: JSON.parse(root.dataset.mediaItems || '[]'),
            coverId: Number(root.dataset.mediaCover) || null,
            activeId: null,
        });
    }

    return states.get(root);
}

function render(root) {
    const s = state(root);
    const { inputs, cover, image, preview, empty, info, strip, thumbs, remove, selectLabel } = parts(root);

    // Aktif görsel silinmiş olabilir; listedeki ilkine düş.
    const active = s.items.find((item) => item.id === s.activeId) ?? s.items[0] ?? null;
    s.activeId = active?.id ?? null;

    inputs.innerHTML = s.items
        .map((item) => `<input type="hidden" name="${root.dataset.mediaName}[]" value="${item.id}">`)
        .join('');

    cover.value = s.items.some((item) => item.id === s.coverId) ? s.coverId : '';

    preview.classList.toggle('hidden', ! active);
    empty.classList.toggle('hidden', Boolean(active));
    remove?.classList.toggle('hidden', ! active);
    strip.classList.toggle('hidden', s.items.length === 0);
    info.classList.toggle('hidden', ! active);

    if (selectLabel) {
        selectLabel.textContent = s.items.length ? 'Görsel Ekle' : 'Dosya Seç';
    }

    if (active) {
        image.src = active.medium ?? active.url;
        image.alt = active.alt ?? active.name ?? '';
        info.textContent = active.width
            ? `${active.name} · ${active.width}×${active.height} · ${active.human_size}`
            : `${active.name} · ${active.human_size}`;
    }

    thumbs.innerHTML = s.items.map((item) => thumb(item, s)).join('');
}

/** Şeritteki tek bir küçük önizleme. */
function thumb(item, s) {
    const isActive = item.id === s.activeId;
    const isCover = item.id === s.coverId;

    return `
        <div data-media-thumb="${item.id}"
            class="relative shrink-0 w-[76px] h-[76px] rounded-md overflow-hidden border-2 cursor-pointer transition-all ${isActive ? 'border-primary-500' : 'border-gray-200 dark:border-[#172036] hover:border-primary-300'}">
            <img src="${item.thumb ?? item.url}" alt="${item.alt ?? ''}" class="w-full h-full object-cover">

            ${isCover ? `
                <span class="absolute top-0 left-0 py-[1px] px-[5px] text-[10px] font-medium bg-primary-500 text-white rounded-br-md">
                    Kapak
                </span>` : ''}

            <button type="button" data-media-thumb-action="cover" title="Kapak yap"
                class="absolute bottom-[3px] left-[3px] w-[20px] h-[20px] flex items-center justify-center rounded-sm bg-black/50 text-white transition-all hover:bg-primary-500">
                <i class="material-symbols-outlined !text-[13px]">${isCover ? 'star' : 'star_outline'}</i>
            </button>

            <button type="button" data-media-thumb-action="remove" title="Kaldır"
                class="absolute bottom-[3px] right-[3px] w-[20px] h-[20px] flex items-center justify-center rounded-sm bg-black/50 text-white transition-all hover:bg-danger-500">
                <i class="material-symbols-outlined !text-[13px]">delete</i>
            </button>
        </div>`;
}

function addMedia(root, media) {
    const s = state(root);

    if (! media || s.items.some((item) => item.id === media.id)) {
        return;
    }

    s.items.push(media);
    s.activeId = media.id;

    // İlk görsel kendiliğinden kapak olur; kullanıcı sonra değiştirebilir.
    s.coverId ??= media.id;

    render(root);
}

async function addFiles(root, files) {
    for (const file of files) {
        // Sırayla yükleniyor: kırpma modalı açılacaksa aynı anda iki tanesi açılamaz.
        addMedia(root, await uploadWithCrop(root, file));
    }

    parts(root).file.value = '';
}

document.addEventListener('change', (event) => {
    const fileInput = event.target.closest('[data-media-file]');
    const root = fileInput ? field(fileInput) : null;

    if (root && fileInput.files?.length) {
        addFiles(root, [...fileInput.files]);
    }
});

document.addEventListener('click', async (event) => {
    const thumbButton = event.target.closest('[data-media-thumb-action]');
    const thumbBox = event.target.closest('[data-media-thumb]');
    const actionButton = event.target.closest('[data-media-action]');
    const root = field(event.target);

    if (! root) {
        return;
    }

    const s = state(root);

    if (thumbButton) {
        const id = Number(thumbBox.dataset.mediaThumb);

        if (thumbButton.dataset.mediaThumbAction === 'cover') {
            s.coverId = s.coverId === id ? null : id;
            render(root);
        }

        if (thumbButton.dataset.mediaThumbAction === 'remove') {
            s.items = s.items.filter((item) => item.id !== id);

            if (s.coverId === id) {
                s.coverId = s.items[0]?.id ?? null;
            }

            render(root);
        }

        return;
    }

    // Küçük önizlemeye tıklamak onu üstteki büyük çerçeveye taşır.
    if (thumbBox) {
        s.activeId = Number(thumbBox.dataset.mediaThumb);
        render(root);

        return;
    }

    if (! actionButton) {
        return;
    }

    const action = actionButton.dataset.mediaAction;

    if (action === 'select') {
        parts(root).file.click();
    }

    if (action === 'library') {
        addMedia(root, await mediaPicker.open());
    }

    if (action === 'remove' && await confirm('Tüm görseller alandan kaldırılacak. Dosyalar medya kütüphanesinde kalır.', {
        title: 'Görselleri kaldır', accept: 'Kaldır',
    })) {
        s.items = [];
        s.coverId = null;
        s.activeId = null;
        render(root);
    }
});

document.addEventListener('drop', (event) => {
    const drop = event.target.closest('[data-media-drop]');
    const root = drop ? field(drop) : null;

    if (! root || ! [...(event.dataTransfer?.types ?? [])].includes('Files')) {
        return;
    }

    event.preventDefault();

    const files = [...(event.dataTransfer.files ?? [])];

    if (files.length) {
        addFiles(root, files);
    }
});

/** Sunucu tarafında basılan görselleri ilk açılışta şeride yazar. */
function init(root = document) {
    root.querySelectorAll('[data-media-field][data-media-multiple]:not([data-media-ready])').forEach((element) => {
        element.dataset.mediaReady = '1';
        render(element);
    });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));
