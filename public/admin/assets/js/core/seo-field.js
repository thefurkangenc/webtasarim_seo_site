/**
 * <x-admin::form.seo> davranışı: karakter sayaçları ve canlı arama sonucu
 * önizlemesi.
 *
 * Meta alanları boşken önizleme, formdaki kaynak alanlara düşer — panelde
 * görülen şey, ön yüzde HasSeo::seoMeta()'nın üreteceği şeyle aynı olsun diye.
 */

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
            title: this.source(root.dataset.seoTitleSource),
            description: this.source(root.dataset.seoDescriptionSource),
            slug: this.source(root.dataset.seoSlugSource),
        };

        this.bind();
        this.render();
    }

    /** Kaynak alanlar bu bileşenin dışında, formun başka yerindedir. */
    source(name) {
        return name ? this.form.querySelector(`[name="${CSS.escape(name)}"]`) : null;
    }

    bind() {
        [this.metaTitle, this.metaDescription, ...Object.values(this.sources)]
            .filter(Boolean)
            .forEach((input) => input.addEventListener('input', () => this.render()));
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
