/**
 * Blog yazısı formu.
 *
 * Gönderim AJAX ile yapılır: uzun bir yazı doğrulama hatası yüzünden
 * kaybolmasın diye sayfa yenilenmez.
 */

import { aiGenerator } from '../../core/ai-generator.js';
import { AiProgress } from '../../core/ai-progress.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('blog-form');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(`/admin/blog/${id}`, new FormData(form))
            : await http.post('/admin/blog', new FormData(form));

        toast.success(message);

        // Yeni kayıt düzenleme ekranına geçer; artık bir kimliği var.
        if (data?.redirect) {
            window.location.href = data.redirect;
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

/*
 * Yapay zeka üretimi: sonuç doğrudan form alanlarına yazılır, kullanıcı
 * kaydetmeden önce düzenleyebilir. Kullanıcı modalı "arka planda bırak"ırsa
 * üretim kuyrukta devam eder — sayfa başlığının altında beliren bu kart
 * ilerlemeyi (ve varsa hatayı) gösterir, sayfa yenilense bile takip kaydı
 * kaybolmaz (bkz. core/ai-progress.js).
 */
const progress = new AiProgress(form, `blog:${form.dataset.id ?? 'new'}`, applyOutput);

document.getElementById('blog-ai')?.addEventListener('click', async () => {
    const result = await aiGenerator.open('blog.content', {
        defaults: {
            title: form.querySelector('[name="title"]').value,
            category: form.querySelector('[name="blog_category_id"]')?.selectedOptions[0]?.text ?? '',
        },
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

    // Önizleme ve sayaçlar yeni değerleri görsün.
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
