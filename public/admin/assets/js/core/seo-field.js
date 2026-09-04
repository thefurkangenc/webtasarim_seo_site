/**
 * <x-admin::form.seo> davranışı: karakter sayaçları, canlı arama sonucu
 * önizlemesi ve kaynak alanlardan otomatik doldurma.
 *
 * Otomatik doldurma: meta başlık/açıklama/paylaşım görseli, ilgili kaynak
 * alan (örn. blog başlığı) değiştikçe eşzamanlı güncellenir — ama SADECE
 * kullanıcı o meta alana daha önce hiç dokunmadıysa. Elle bir değer
 * yazıldığı (ya da mevcut kayıtta zaten doluysa) andan itibaren senkron
 * durur, kullanıcının girdisi ezilmez.
 */

import { setFieldMedia } from './media-field.js';

const TR_MAP = { ç: 'c', ğ: 'g', ı: 'i', ö: 'o', ş: 's', ü: 'u', İ: 'i', I: 'i' };

function slugify(value) {
    return value
        .replace(/[çğıöşüİI]/g, (char) => TR_MAP[char] ?? char)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function truncate(value, limit) {
    return value.length > limit ? `${value.slice(0, limit - 1).trimEnd()}…` : value;
}

class SeoField {
    constructor(root) {
        this.root = root;
        this.form = root.closest('form') ?? document;
        this.host = root.dataset.seoHost;
        this.path = root.dataset.seoPath;

        this.metaTitle = root.querySelector('[data-seo-input="meta_title"]');
        this.metaDescription = root.querySelector('[data-seo-input="meta_description"]');

        this.preview = {
            url: root.querySelector('[data-seo-preview-url]'),
            title: root.querySelector('[data-seo-preview-title]'),
            description: root.querySelector('[data-seo-preview-description]'),
        };

        this.sources = {
            title: this.field(root.dataset.seoTitleSource),
            description: this.field(root.dataset.seoDescriptionSource),
            slug: this.field(root.dataset.seoSlugSource),
        };

        // Zaten dolu bir alana (düzenleme ekranında elle girilmiş bir meta gibi)
        // otomatik doldurma dokunmaz — kullanıcı bilinçli olarak özelleştirmiş sayılır.
        this.touched = {
            title: this.value(this.metaTitle) !== '',
            description: this.value(this.metaDescription) !== '',
            image: false,
        };

        this.bindText();
        this.bindImage(root.dataset.seoImageSource);
        this.render();
    }

    /** Kaynak alanlar bu bileşenin dışında, formun başka yerindedir. */
    field(name) {
        return name ? this.form.querySelector(`[name="${CSS.escape(name)}"]`) : null;
    }

    bindText() {
        // Meta alana elle yazıldığında senkron o alan için biter.
        this.metaTitle?.addEventListener('input', () => {
            this.touched.title = true;
            this.render();
        });

        this.metaDescription?.addEventListener('input', () => {
            this.touched.description = true;
            this.render();
        });

        // Kaynak değiştikçe dokunulmamış meta alana kopyalanır.
        this.sources.title?.addEventListener('input', () => {
            if (! this.touched.title && this.metaTitle) {
                this.metaTitle.value = this.sources.title.value;
            }

            this.render();
        });

        this.sources.description?.addEventListener('input', () => {
            if (! this.touched.description && this.metaDescription) {
                this.metaDescription.value = this.sources.description.value;
            }

            this.render();
        });

        this.sources.slug?.addEventListener('input', () => this.render());
    }

    /**
     * Kapak görseli değiştikçe paylaşım görselini eşzamanlı doldurur.
     * imageSource verilmemişse (modülde kapak görseli yoksa) hiçbir şey yapmaz.
     */
    bindImage(sourceFieldName) {
        this.imageTarget = this.root.querySelector('[data-media-field]');

        if (! sourceFieldName || ! this.imageTarget) {
            return;
        }

        this.imageSource = this.form
            .querySelector(`[data-media-input][name="${CSS.escape(sourceFieldName)}"]`)
            ?.closest('[data-media-field]');

        if (! this.imageSource) {
            return;
        }

        this.touched.image = Boolean(
            this.imageTarget.querySelector('[data-media-input]')?.value,
        );

        // Hedef alanda kullanıcı doğrudan bir işlem yaparsa (seç/kütüphane/kaldır/
        // yeniden kırp) senkron o andan itibaren biter.
        this.imageTarget.addEventListener('click', (event) => {
            if (event.target.closest('[data-media-action]')) {
                this.touched.image = true;
            }
        });

        this.imageSource.addEventListener('media:change', (event) => {
            if (! this.touched.image) {
                setFieldMedia(this.imageTarget, event.detail);
            }
        });
    }

    render() {
        this.count(this.metaTitle, 'meta_title');
        this.count(this.metaDescription, 'meta_description');

        const title = this.value(this.metaTitle) || this.value(this.sources.title) || 'Sayfa başlığı';
        const description = this.value(this.metaDescription)
            || this.value(this.sources.description)
            || 'Sayfa açıklaması arama sonuçlarında burada görünür.';

        const slug = this.value(this.sources.slug) || slugify(this.value(this.sources.title));
        const segments = [this.host, this.path, slug].filter(Boolean);

        this.preview.url.textContent = segments.join(' › ');
        this.preview.title.textContent = truncate(title, 60);
        this.preview.description.textContent = truncate(description, 160);
    }

    value(input) {
        return input?.value.trim() ?? '';
    }

    count(input, key) {
        const counter = this.root.querySelector(`[data-seo-counter="${key}"]`);

        if (! input || ! counter) {
            return;
        }

        const limit = Number(input.dataset.seoLimit);
        const length = input.value.trim().length;

        counter.textContent = `${length} / ${limit}`;
        counter.className = 'text-xs shrink-0 '.concat(
            length > limit ? 'text-danger-500'
                : length > limit * 0.9 ? 'text-warning-500'
                    : 'text-gray-500 dark:text-gray-400',
        );
    }
}

export function initSeoFields(root = document) {
    root.querySelectorAll('[data-seo]:not([data-seo-ready])').forEach((element) => {
        element.dataset.seoReady = '1';
        new SeoField(element);
    });
}

document.addEventListener('DOMContentLoaded', () => initSeoFields());
document.addEventListener('admin:content-loaded', (event) => initSeoFields(event.target));
