/**
 * Site Haritası ekranı — üç sekme: site haritası, hızlı indeksleme (IndexNow)
 * ve robots.txt. Hepsi "arama motoruna sitemi bildir" işinin parçası olduğu
 * için tek sayfada toplandı; IndexNow'ın ayrı bir menü öğesi yok.
 *
 * Ayar formları POST + _method=PUT ile gider — FormData taşıyan gerçek PUT
 * gövdesini PHP ayrıştırmıyor (setting/analytics.js ile aynı kalıp).
 */

import { confirm } from '../../core/confirm.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-indexing-page]');

if (root) {
    /* ---------------------------------------------------------- sekmeler */

    const ACTIVE = 'bg-primary-500 text-white border-primary-500';
    const IDLE = 'text-black dark:text-white border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]';
    const STORAGE_KEY = 'admin.sitemap.tab';

    const buttons = [...root.querySelectorAll('[data-tab]')];
    const panes = [...root.querySelectorAll('[data-pane]')];

    function showTab(name) {
        const known = buttons.some((button) => button.dataset.tab === name) ? name : 'sitemap';

        buttons.forEach((button) => {
            const on = button.dataset.tab === known;
            button.className = button.className
                .replace(ACTIVE, '').replace(IDLE, '').trim()
                + ' border ' + (on ? ACTIVE : IDLE);
        });

        panes.forEach((pane) => {
            pane.hidden = pane.dataset.pane !== known;
        });

        try {
            // Kaydettikten sonra sayfa yenilenince kullanıcı aynı sekmede kalsın.
            localStorage.setItem(STORAGE_KEY, known);
        } catch {
            // Gizli sekmede localStorage kapalı olabilir; sekme yine çalışır.
        }
    }

    root.querySelector('[data-tabs]').addEventListener('click', (event) => {
        const button = event.target.closest('[data-tab]');
        if (button) showTab(button.dataset.tab);
    });

    // Üstteki özet kartları da ilgili sekmeye atlar.
    root.addEventListener('click', (event) => {
        const jump = event.target.closest('[data-tab-jump]');

        if (jump) {
            showTab(jump.dataset.tabJump);
            root.querySelector('[data-tabs]').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    let initial = 'sitemap';

    try {
        initial = localStorage.getItem(STORAGE_KEY) ?? 'sitemap';
    } catch {
        // yoksay
    }

    showTab(initial);

    /* ------------------------------------------------- ortak form gönderimi */

    async function submitForm(form, { reload = true } = {}) {
        const button = form.querySelector('[type=submit]');
        clearErrors(form);
        setLoading(button, true);

        try {
            const { message } = await http.post(form.action, new FormData(form));
            toast.success(message);

            if (reload) {
                setTimeout(() => window.location.reload(), 700);
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
    }

    // Site haritası + robots.txt aynı formda (ikisi de aynı ayar grubuna yazılır).
    document.getElementById('sitemap-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitForm(event.currentTarget);
    });

    document.getElementById('indexnow-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitForm(event.currentTarget, { reload: false });
    });

    /* -------------------------------------------------- site haritası üret */

    document.getElementById('sitemap-generate')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.generateEndpoint);
            toast.success(message);
            // Üretim kuyrukta çalışıyor; rapor birkaç saniye sonra güncellenir.
            setTimeout(() => window.location.reload(), 4000);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Kuyruğa alınamadı.');
        } finally {
            setLoading(button, false);
        }
    });

    /* ------------------------------------------------------------ IndexNow */

    document.getElementById('indexnow-new-key')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;

        const ok = await confirm(
            'Eski anahtar anında geçersiz olur. Arama motorları sonraki bildirimde yeni anahtarı doğrular; başka bir şey yapmanız gerekmez.',
            { title: 'Anahtar yenilensin mi?', accept: 'Yenile' },
        );

        if (! ok) return;

        setLoading(button, true);

        try {
            const { message, data } = await http.post(root.dataset.indexnowKey);
            root.querySelector('[data-indexnow-key-text]').textContent = data.key;
            root.querySelector('[data-indexnow-key-url]').href = `${window.location.origin}/${data.key}.txt`;
            toast.success(message);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Anahtar yenilenemedi.');
        } finally {
            setLoading(button, false);
        }
    });

    const urlsField = root.querySelector('[name=urls]');

    document.getElementById('indexnow-submit')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const errorSlot = root.querySelector('[data-error="urls"]');

        if (errorSlot) errorSlot.textContent = '';
        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.indexnowSubmit, { urls: urlsField.value });
            toast.success(message);
            urlsField.value = '';
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            if (error instanceof ValidationError && errorSlot) {
                errorSlot.textContent = error.errors.urls?.[0] ?? 'Adresleri kontrol edin.';
            } else {
                toast.error(error instanceof HttpError ? error.message : 'Bildirilemedi.');
            }
        } finally {
            setLoading(button, false);
        }
    });

    document.getElementById('indexnow-submit-all')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;

        const ok = await confirm(
            'Site haritasındaki bütün adresler arama motorlarına bildirilecek. Bunu sık tekrarlamanın faydası yoktur — normalde içerik kaydedildikçe tek tek bildirilir.',
            { title: 'Tüm adresler bildirilsin mi?', accept: 'Gönder' },
        );

        if (! ok) return;

        setLoading(button, true);

        try {
            const { message } = await http.post(root.dataset.indexnowSubmitAll);
            toast.success(message);
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Gönderilemedi.');
        } finally {
            setLoading(button, false);
        }
    });
}
