/**
 * Neler Yaptık (proje) formu.
 *
 * Gönderim AJAX ile yapılır: uzun bir anlatım doğrulama hatası yüzünden
 * kaybolmasın diye sayfa yenilenmez.
 */

import { aiGenerator } from '../../core/ai-generator.js';
import { AiProgress } from '../../core/ai-progress.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('project-form');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(`/admin/project/${id}`, new FormData(form))
            : await http.post('/admin/project', new FormData(form));

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
 * kaydetmeden önce düzenleyebilir. Modal "arka planda bırak"ılırsa üretim
 * kuyrukta sürer, ilerleme kartı formun üstünde görünür.
 */
const progress = new AiProgress(form, `project:${form.dataset.id ?? 'new'}`, applyOutput);

document.getElementById('project-ai')?.addEventListener('click', async () => {
    const result = await aiGenerator.open('project.content', {
        defaults: {
            title: form.querySelector('[name="title"]').value,
            // Müşteri ve sektör varsa üretim çok daha isabetli oluyor.
            notes: [
                form.querySelector('[name="client_name"]').value,
                form.querySelector('[name="sector"]').value,
            ].filter(Boolean).join(' · '),
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

    // Önizleme, sayaçlar ve canlı SEO skoru yeni değerleri görsün.
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

/**
 * Bu formda İKİ chip alanı var: etiketler ve teknolojiler. İkisi de aynı
 * JS'i (core/tag-input.js) paylaştığı için seçici `data-tag-endpoint` ile
 * daraltılır — öneri uç noktası olan tek alan etiketlerdir. Aksi halde
 * üretilen etiketler teknoloji listesine düşebilirdi.
 */
function setTags(tags) {
    if (! Array.isArray(tags) || tags.length === 0) {
        return;
    }

    const field = form.querySelector('[data-tag-input][data-tag-endpoint] [data-tag-field]');

    if (! field) {
        return;
    }

    tags.forEach((tag) => {
        field.value = tag;
        field.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
    });
}
