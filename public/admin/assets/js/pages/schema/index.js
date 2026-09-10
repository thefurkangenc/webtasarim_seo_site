/**
 * Schema.org doğrulama ekranı: seçilen sayfa için üretilen @graph'ı çeker,
 * kural denetimini gösterir ve harici doğrulayıcılara bağlantı kurar.
 */

import { escapeHtml, http, HttpError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const targetSelect = document.getElementById('schema-target');
const urlInput = document.getElementById('schema-url');
const runButton = document.getElementById('schema-run');
const result = document.getElementById('schema-result');
const jsonBox = document.getElementById('schema-json');

// Tailwind'in tarayıcısı ${} birleştirmesini göremez; sınıflar tam yazılır.
const SECTIONS = {
    errors: {
        title: 'Hatalar',
        icon: 'error',
        wrap: 'rounded-md border border-danger-200 dark:border-[#172036] overflow-hidden',
        head: 'px-[14px] py-[8px] bg-danger-50 dark:bg-[#15203c] text-danger-600 text-xs font-medium flex items-center gap-[6px]',
        card: 'p-[14px] rounded-md bg-danger-100 dark:bg-[#15203c] text-center',
        num: 'block text-xl font-bold text-danger-600',
    },
    warnings: {
        title: 'Uyarılar',
        icon: 'warning',
        wrap: 'rounded-md border border-warning-200 dark:border-[#172036] overflow-hidden',
        head: 'px-[14px] py-[8px] bg-warning-50 dark:bg-[#15203c] text-warning-600 text-xs font-medium flex items-center gap-[6px]',
        card: 'p-[14px] rounded-md bg-warning-100 dark:bg-[#15203c] text-center',
        num: 'block text-xl font-bold text-warning-600',
    },
    passed: {
        title: 'Eksiksiz düğümler',
        icon: 'check_circle',
        wrap: 'rounded-md border border-success-200 dark:border-[#172036] overflow-hidden',
        head: 'px-[14px] py-[8px] bg-success-50 dark:bg-[#15203c] text-success-600 text-xs font-medium flex items-center gap-[6px]',
        card: 'p-[14px] rounded-md bg-success-100 dark:bg-[#15203c] text-center',
        num: 'block text-xl font-bold text-success-600',
    },
};

let lastJson = '';

runButton.addEventListener('click', run);
urlInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        run();
    }
});
targetSelect.addEventListener('change', () => {
    if (targetSelect.value) {
        urlInput.value = '';
        run();
    }
});

document.getElementById('schema-copy').addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(lastJson);
        toast.success('JSON-LD panoya kopyalandı.');
    } catch {
        toast.error('Kopyalanamadı.');
    }
});

async function run() {
    const url = (urlInput.value.trim() || targetSelect.value).trim();

    if (! url) {
        toast.error('Bir sayfa seçin ya da adres girin.');
        return;
    }

    runButton.disabled = true;
    runButton.classList.add('opacity-60');

    try {
        const { data } = await http.get(`/admin/schema/preview?url=${encodeURIComponent(url)}`);
        render(data);
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Çıktı üretilemedi.');
    } finally {
        runButton.disabled = false;
        runButton.classList.remove('opacity-60');
    }
}

function render(data) {
    const { target, json, lint } = data;

    lastJson = json;
    result.hidden = false;

    document.querySelector('[data-target-url]').textContent = target.url;
    document.getElementById('schema-open').href = target.url;
    document.querySelector('[data-target-kind]').textContent = target.kind;
    document.querySelector('[data-target-unresolved]').hidden = target.resolved;

    document.getElementById('schema-google').href =
        `https://search.google.com/test/rich-results?url=${encodeURIComponent(target.url)}`;
    document.getElementById('schema-validator').href =
        `https://validator.schema.org/#url=${encodeURIComponent(target.url)}`;

    renderLint(lint);
    jsonBox.innerHTML = highlight(json);
}

function renderLint(lint) {
    const cards = [
        [SECTIONS.errors, lint.counts.errors, 'Hata'],
        [SECTIONS.warnings, lint.counts.warnings, 'Uyarı'],
        [SECTIONS.passed, lint.passed.length, 'Geçen düğüm'],
    ];

    document.getElementById('schema-lint').innerHTML = cards.map(([tone, value, label]) => `
        <div class="${tone.card}">
            <span class="${tone.num}">${value}</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400">${label}</span>
        </div>
    `).join('');

    const block = (tone, items) => items.length === 0 ? '' : `
        <div class="${tone.wrap}">
            <div class="${tone.head}">
                <i class="material-symbols-outlined !text-[16px]">${tone.icon}</i> ${tone.title} (${items.length})
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-[#172036]">
                ${items.map((line) => `<li class="px-[14px] py-[7px] text-xs text-black dark:text-gray-200">${escapeHtml(line)}</li>`).join('')}
            </ul>
        </div>
    `;

    document.getElementById('schema-lint-detail').innerHTML =
        block(SECTIONS.errors, lint.errors) +
        block(SECTIONS.warnings, lint.warnings) +
        block(SECTIONS.passed, lint.passed);
}

/** Kaba JSON renklendirme — bağımlılık yok. */
function highlight(json) {
    return escapeHtml(json).replace(
        /(&quot;(?:\\.|[^&\\])*?&quot;)(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?)/g,
        (match, str, colon, literal, number) => {
            if (str) {
                return `<span class="${colon ? 'text-primary-500' : 'text-success-600'}">${match}</span>`;
            }
            if (literal) {
                return `<span class="text-purple-500">${match}</span>`;
            }
            if (number) {
                return `<span class="text-orange-500">${match}</span>`;
            }
            return match;
        },
    );
}
