/**
 * TinyMCE bağlama katmanı.
 *
 * <x-admin::form.editor name="content" /> bir <textarea data-editor> basar;
 * bu dosya sayfadaki tüm bu alanları editöre çevirir. Sayfa JS'i gerektirmez.
 *
 * Notlar:
 * - Görsel ekleme TinyMCE'nin kendi yükleyicisi yerine medya kütüphanesini
 *   açar; böylece editöre giren her görsel de kütüphanede kayıtlı olur.
 * - Panelin karanlık modu değiştiğinde editör skin'i canlı değiştirilemez,
 *   bu yüzden içerik korunarak yeniden kurulur.
 */

import { escapeHtml } from './http.js';
import { mediaPicker } from './media-picker.js';
import { toast } from './toast.js';

const BASE_URL = '/admin/assets/js/vendor/tinymce';

const PLUGINS = 'advlist autolink lists link table code codesample charmap '
    + 'searchreplace visualblocks fullscreen preview wordcount anchor importcss';

const TOOLBAR = 'undo redo | blocks | bold italic underline strikethrough | '
    + 'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | '
    + 'link medialibrary table blockquote codesample | removeformat searchreplace | '
    + 'visualblocks code preview fullscreen';

const isDark = () => document.documentElement.classList.contains('dark');

let darkAtInit = isDark();

function contentStyle() {
    return `body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.7; padding: 16px; }
        h1,h2,h3,h4 { font-weight: 600; }
        img { max-width: 100%; height: auto; }
        table { border-collapse: collapse; }
        table td, table th { border: 1px solid #d1d5db; padding: 6px 10px; }`;
}

function options(textarea) {
    const dark = isDark();

    return {
        target: textarea,
        // GPL self-host sürümü bu anahtarı ister; uzak bir servise istek atmaz.
        license_key: 'gpl',
        base_url: BASE_URL,
        suffix: '.min',
        language: 'tr',
        language_url: `${BASE_URL}/langs/tr.js`,

        height: Number(textarea.dataset.editorHeight) || 500,
        menubar: 'file edit view insert format tools table help',
        branding: false,
        promotion: false,
        statusbar: true,
        resize: true,

        plugins: PLUGINS,
        toolbar: TOOLBAR,
        block_formats: 'Paragraf=p; Başlık 2=h2; Başlık 3=h3; Başlık 4=h4; Alıntı=blockquote',

        skin: dark ? 'oxide-dark' : 'oxide',
        content_css: dark ? 'dark' : 'default',
        content_style: contentStyle(),

        // Göreli yola çevirirse ön yüzde kırık görsel oluşur.
        relative_urls: false,
        convert_urls: false,
        remove_script_host: false,

        setup(editor) {
            editor.ui.registry.addButton('medialibrary', {
                icon: 'image',
                tooltip: 'Medya kütüphanesinden görsel ekle',
                onAction: () => insertFromLibrary(editor),
            });

            // FormData textarea'yı okur; editör her değişimde oraya yazmalı.
            editor.on('change keyup setcontent undo redo', () => editor.save());
        },
    };
}

async function insertFromLibrary(editor) {
    const media = await mediaPicker.open();

    if (! media) {
        return;
    }

    if (! media.is_image) {
        toast.error('Yalnızca görsel eklenebilir.');

        return;
    }

    editor.insertContent(
        `<img src="${escapeHtml(media.url)}" alt="${escapeHtml(media.alt || media.name)}">`,
    );
}

export function initEditors(root = document) {
    if (! window.tinymce) {
        return;
    }

    root.querySelectorAll('[data-editor]:not([data-editor-ready])').forEach((textarea) => {
        textarea.dataset.editorReady = '1';
        window.tinymce.init(options(textarea));
    });
}

/** Karanlık mod değiştiğinde skin'i yenilemek için editörleri yeniden kurar. */
function rebuild() {
    // remove() içeriği textarea'ya yazar, bu yüzden veri kaybı olmaz.
    window.tinymce.remove();

    document.querySelectorAll('[data-editor][data-editor-ready]').forEach((textarea) => {
        delete textarea.dataset.editorReady;
    });

    initEditors();
}

function watchTheme() {
    new MutationObserver(() => {
        if (isDark() === darkAtInit) {
            return;
        }

        darkAtInit = isDark();
        rebuild();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

// Yakalama fazında: sayfa/modal submit işleyicisi FormData'yı okumadan önce
// editör içeriği textarea'ya yazılmış olmalı.
document.addEventListener('submit', () => window.tinymce?.triggerSave(), true);

document.addEventListener('DOMContentLoaded', () => {
    initEditors();
    watchTheme();
});
