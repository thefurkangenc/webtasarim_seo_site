/**
 * İlk kurulum sihirbazı. Adımlar oturumda birikir; "Kurulumu başlat"
 * görevleri sırayla POST eder. jQuery yok.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { escapeHtml, http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-setup]');
const wizard = root?.querySelector('[data-setup-wizard]');

if (root && wizard) {
    const install = root.querySelector('[data-setup-install]');
    const progress = root.querySelector('[data-setup-progress]');
    const steps = [...root.querySelectorAll('[data-step]')].map((el) => el.dataset.step);
    const tasks = JSON.parse(root.dataset.tasks || '[]');
    const payload = JSON.parse(root.dataset.payload || '{}');
    let running = false;

    let current = Math.max(0, steps.findIndex((key) => key !== 'summary' && ! payload[key]));

    if (payload.admin && payload.company && payload.modules && payload.contact && payload.mail && payload.legal) {
        current = steps.indexOf('summary');
    }

    function paintNav() {
        const ratio = ((current + 1) / steps.length) * 100;
        progress.style.width = `${ratio}%`;

        root.querySelectorAll('[data-nav]').forEach((item, index) => {
            const dot = item.querySelector('[data-nav-dot]');
            dot.className = 'mx-auto mb-[8px] w-[32px] h-[32px] rounded-full flex items-center justify-center text-xs font-semibold';

            if (index < current) {
                dot.classList.add('bg-success-500', 'text-white');
                dot.innerHTML = '<i class="material-symbols-outlined !text-[16px]">check</i>';
            } else if (index === current) {
                dot.classList.add('bg-primary-500', 'text-white');
                dot.textContent = String(index + 1);
            } else {
                dot.classList.add('bg-gray-200', 'dark:bg-[#15203c]', 'text-gray-500', 'dark:text-gray-400');
                dot.textContent = String(index + 1);
            }
        });
    }

    function show(index) {
        current = index;
        root.querySelectorAll('[data-step]').forEach((panel, i) => {
            panel.classList.toggle('hidden', i !== index);
        });
        paintNav();

        if (steps[index] === 'summary') {
            renderSummary();
        }
    }

    function renderSummary() {
        const rows = [
            payload.admin?.name ? `${payload.admin.name} · ${payload.admin.email}` : 'Yönetici',
            payload.company?.name || 'Firma',
            'Seçilen modüller kaydedilecek',
            payload.contact?.skipped ? 'İletişim formu atlandı (varsayılan)' : 'İletişim formu',
            payload.mail?.skipped ? 'E-posta atlandı' : 'SMTP kaydedilecek',
            payload.legal?.skipped ? 'Yasal sayfalar atlandı' : 'KVKK ve çerez taslağı',
        ];

        root.querySelector('[data-summary-list]').innerHTML = rows
            .map((row) => `<li class="flex items-center gap-[8px] text-black dark:text-white"><i class="material-symbols-outlined !text-[18px] text-success-500">check_circle</i>${escapeHtml(row)}</li>`)
            .join('');
    }

    function bodyFromForm(form) {
        const data = {};

        new FormData(form).forEach((value, key) => {
            const path = key.replace(/\]/g, '').split('[');
            if (path.length === 1) {
                data[key] = value;

                return;
            }

            data[path[0]] ??= {};
            data[path[0]][path[1]] = value === '1' || value === 'on' ? true : value;
        });

        form.querySelectorAll('input[type=checkbox][name^="modules["]').forEach((box) => {
            const key = box.name.slice(8, -1);
            data.modules ??= {};
            data.modules[key] = box.checked;
        });

        return data;
    }

    function remember(step, data) {
        payload[step] = { ...payload[step], ...data };
        delete payload[step].password;
        delete payload[step].password_confirmation;
    }

    async function save(form, extra = {}) {
        const button = form.querySelector('[type=submit]');
        clearErrors(form);
        setLoading(button, true);

        try {
            const body = { ...bodyFromForm(form), ...extra };
            await http.post(form.dataset.endpoint, body);
            remember(form.dataset.step, body);
            show(current + 1);
        } catch (error) {
            if (error instanceof ValidationError) {
                showErrors(form, error.errors);
            } else if (error instanceof HttpError) {
                toast.error(error.message);
            }
        } finally {
            setLoading(button, false);
        }
    }

    async function skip(form) {
        try {
            await http.post(form.dataset.endpoint, { skipped: true });
            remember(form.dataset.step, { skipped: true });
            show(current + 1);
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Atlanamadı.');
        }
    }

    wizard.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-step]');
        if (! form) return;

        event.preventDefault();
        save(form, form.dataset.step === 'legal' ? { skipped: false } : {});
    });

    wizard.addEventListener('click', (event) => {
        if (event.target.closest('[data-back]')) {
            show(Math.max(0, current - 1));
        }

        if (event.target.closest('[data-skip]')) {
            const form = event.target.closest('form[data-step]');
            if (form) skip(form);
        }

        if (event.target.closest('[data-start]')) {
            startInstall();
        }
    });

    install.querySelector('[data-install-retry]')?.addEventListener('click', () => {
        startInstall();
    });

    const logoInput = root.querySelector('[data-logo-input]');
    logoInput?.addEventListener('change', async () => {
        const file = logoInput.files?.[0];
        if (! file) return;

        const body = new FormData();
        body.append('logo', file);

        try {
            const { data } = await http.post(root.dataset.logo, body);
            const hidden = root.querySelector('[name=logo_media_id]');
            hidden.value = data.id;
            const wrap = root.querySelector('[data-logo-preview]');
            wrap.classList.remove('hidden');
            wrap.querySelector('img').src = URL.createObjectURL(file);
            payload.company = { ...payload.company, logo_media_id: data.id };
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'Logo yüklenemedi.');
        }
    });

    function taskRow(task, state) {
        const icon = state === 'done'
            ? 'check'
            : state === 'error'
                ? 'close'
                : state === 'warn'
                    ? 'info'
                    : 'progress_activity';
        const spin = state === 'active' ? ' animate-spin' : '';
        const color = state === 'done'
            ? 'text-success-500'
            : state === 'error'
                ? 'text-danger-500'
                : state === 'warn'
                    ? 'text-warning-500'
                    : state === 'active'
                        ? 'text-primary-500'
                        : 'text-gray-400';

        return `<li data-task="${task.key}" class="flex items-center gap-[10px] text-sm text-black dark:text-white">
            <i class="material-symbols-outlined !text-[20px] ${color}${spin}">${icon}</i>
            <span>${escapeHtml(task.label)}</span>
        </li>`;
    }

    async function startInstall() {
        if (running) {
            return;
        }

        running = true;
        wizard.classList.add('hidden');
        install.classList.remove('hidden');
        install.querySelector('[data-install-retry]').classList.add('hidden');
        install.querySelector('[data-install-done]').classList.add('hidden');
        install.querySelector('[data-install-orb]').classList.remove('hidden');
        install.querySelector('[data-install-title]').textContent = 'Panel kuruluyor';
        install.querySelector('[data-install-subtitle]').textContent = 'Bu işlem bir dakikadan kısa sürer.';

        const list = install.querySelector('[data-install-list]');
        list.innerHTML = tasks.map((task) => taskRow(task, 'idle')).join('');

        for (const task of tasks) {
            list.querySelector(`[data-task="${task.key}"]`).outerHTML = taskRow(task, 'active');

            try {
                const { data } = await http.post(root.dataset.run, { task: task.key });
                const payloadData = data ?? {};
                const warn = payloadData.warning || (task.key === 'ffmpeg' && payloadData.installed === false);

                list.querySelector(`[data-task="${task.key}"]`).outerHTML = taskRow(task, warn ? 'warn' : 'done');

                if (warn && payloadData.command) {
                    const hint = install.querySelector('[data-ffmpeg-hint]');
                    hint.classList.remove('hidden');
                    install.querySelector('[data-ffmpeg-command]').textContent = payloadData.command;
                }

                if (task.key === 'finalize' && payloadData.redirect) {
                    running = false;
                    finish(payloadData.redirect);
                    return;
                }
            } catch (error) {
                list.querySelector(`[data-task="${task.key}"]`).outerHTML = taskRow(task, 'error');
                install.querySelector('[data-install-title]').textContent = 'Kurulum durdu';
                install.querySelector('[data-install-subtitle]').textContent = error instanceof HttpError
                    ? error.message
                    : 'Bir adım tamamlanamadı.';
                install.querySelector('[data-install-orb]').classList.add('hidden');
                install.querySelector('[data-install-retry]').classList.remove('hidden');
                running = false;

                return;
            }
        }

        running = false;
    }

    function finish(url) {
        install.querySelector('[data-install-title]').textContent = 'Kurulum tamam';
        install.querySelector('[data-install-subtitle]').textContent = 'Panele yönlendiriliyorsunuz.';
        install.querySelector('[data-install-orb]').classList.add('hidden');
        const done = install.querySelector('[data-install-done]');
        done.classList.remove('hidden');
        const link = install.querySelector('[data-install-link]');
        link.href = url;
        window.setTimeout(() => {
            window.location.href = url;
        }, 900);
    }

    show(current);
}
