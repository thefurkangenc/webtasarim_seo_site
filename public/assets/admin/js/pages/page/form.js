/**
 * Sayfa formu.
 *
 * Gönderim AJAX ile yapılır: uzun bir içerik doğrulama hatası yüzünden
 * kaybolmasın diye sayfa yenilenmez.
 *
 * Ayrıca adres satırını canlı tutar — üst sayfa ya da kısa ad değiştikçe
 * sunucuya gitmeden sayfanın çözülecek tam adresini gösterir.
 */

import { aiGenerator } from '../../core/ai-generator.js';
import { AiProgress } from '../../core/ai-progress.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('page-form');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(`/admin/page/${id}`, new FormData(form))
            : await http.post('/admin/page', new FormData(form));

        toast.success(message);

        // Yeni kayıt düzenleme ekranına geçer; artık bir kimliği var.
        if (data?.redirect) {
            window.location.href = data.redirect;

            return;
        }

        // Kısa ad sunucuda değişmiş olabilir (boş bırakıldıysa başlıktan
        // türetilir, çakışırsa sonuna sayı eklenir) — alan gerçek değere çekilir.
        if (data?.slug) {
            slug.value = data.slug;
            renderUrl();
        }
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(form, error.errors);
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        }
    } finally {
        setLoading(button, false);
    }
});

/* --------------------------------------------------------------------------
 | Canlı adres satırı
 * -------------------------------------------------------------------------- */

const host = form.dataset.host ?? '';
const parentPaths = JSON.parse(form.dataset.parentPaths || '{}');
const urlLabel = form.querySelector('[data-page-url]');
const parentSelect = document.getElementById('parent_id');
const title = form.querySelector('[name="title"]');
const slug = form.querySelector('[name="slug"]');

/**
 * Sunucudaki App\Support\Slug ile aynı sonucu üretmeyi hedefleyen hafif bir
 * karşılık — yalnızca önizleme için. Gerçek slug her zaman PHP tarafında
 * üretilir, bu yüzden küçük farklar (çakışmada eklenen "-2" gibi) sorun değil.
 */
const slugify = (value) => {
    const tr = { ç: 'c', ğ: 'g', ı: 'i', İ: 'i', ö: 'o', ş: 's', ü: 'u' };

    return (value ?? '')
        .toString()
        .replace(/[çğıİöşü]/g, (char) => tr[char] ?? char)
        .toLowerCase()
        // Kalan aksanları (é, ü gibi) taban harfe indir: NFD ile ayrıştırıp
        // birleşen işaretleri (U+0300-U+036F) at.
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

function renderUrl() {
    if (! urlLabel) {
        return;
    }

    // Üst sayfa seçiliyse kök o sayfanın yolu (haritadan okunur), seçili
    // değilse sayfa site kökünde durur.
    const parentId = parentSelect?.value ?? '';
    const base = parentId ? (parentPaths[parentId] ?? '') : '';
    const segment = slugify(slug?.value) || slugify(title?.value);

    urlLabel.textContent = segment
        ? `${host}/${[base, segment].filter(Boolean).join('/')}`
        : `${host}/…`;
}

[title, slug].forEach((input) => input?.addEventListener('input', renderUrl));
parentSelect?.addEventListener('change', renderUrl);
renderUrl();

/* --------------------------------------------------------------------------
 | Şablon açıklaması
 * -------------------------------------------------------------------------- */

const templateSelect = document.getElementById('template');
const templateHint = form.querySelector('[data-template-hint]');
const templateDescriptions = JSON.parse(templateHint?.dataset.descriptions || '{}');

function renderTemplateHint() {
    if (templateHint) {
        templateHint.textContent = templateDescriptions[templateSelect?.value] ?? '';
    }
}

templateSelect?.addEventListener('change', renderTemplateHint);
renderTemplateHint();

/* --------------------------------------------------------------------------
 | Yapay zeka üretimi
 * -------------------------------------------------------------------------- */

/*
 * Sonuç doğrudan form alanlarına yazılır, kullanıcı kaydetmeden önce
 * düzenleyebilir. Kullanıcı modalı "arka planda bırak"ırsa üretim kuyrukta
 * devam eder — sayfa başlığının altında beliren kart ilerlemeyi (ve varsa
 * hatayı) gösterir (bkz. core/ai-progress.js).
 */
const progress = new AiProgress(form, `page:${form.dataset.id ?? 'new'}`, applyOutput);

document.getElementById('page-ai')?.addEventListener('click', async () => {
    const result = await aiGenerator.open('page.content', {
        defaults: { title: title.value },
    });

    if (! result) {
        return;
    }

    if (result.background) {
        progress.track(result.id);

        return;
    }

    applyOutput(result);
});

function applyOutput(output) {
    fill('title', output.title);
    fill('excerpt', output.excerpt);
    fill('seo[meta_description]', output.meta_description);
    fill('seo[meta_keywords]', output.meta_keywords);

    setContent(output.content);
    setTags(output.tags);

    // Önizleme, sayaçlar ve adres satırı yeni değerleri görsün.
    form.querySelectorAll('[data-seo-input], [name="title"], [name="excerpt"]')
        .forEach((input) => input.dispatchEvent(new Event('input', { bubbles: true })));

    toast.success('İçerik forma yazıldı. Kaydetmeden önce gözden geçirin.');
}

function fill(name, value) {
    const input = form.querySelector(`[name="${name}"]`);

    if (input && value) {
        input.value = value;
    }
}

function setContent(html) {
    if (! html) {
        return;
    }

    const editor = window.tinymce?.get('content');

    if (editor) {
        editor.setContent(html);
        editor.save();

        return;
    }

    // Editör henüz kurulmadıysa textarea'ya yaz; kurulunca oradan okunur.
    form.querySelector('[name="content"]').value = html;
}

function setTags(tags) {
    if (! Array.isArray(tags) || tags.length === 0) {
        return;
    }

    const field = form.querySelector('[data-tag-field]');

    if (! field) {
        return;
    }

    tags.forEach((tag) => {
        field.value = tag;
        field.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
    });
}
