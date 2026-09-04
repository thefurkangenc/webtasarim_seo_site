/**
 * Promise döndüren tek satır metin girişi kutusu — `confirm.js` ile aynı
 * kalıp. Klasör oluşturma/yeniden adlandırma gibi tek alanlık istekler için;
 * tarayıcının native `prompt()`'u yerine panelin kendi görünümünü kullanır.
 *
 *   const name = await promptText('Klasör adı', { value: 'Eski ad' });
 *   // name === null  -> vazgeçildi
 *   // name === 'Yeni ad'
 */

let dialog = null;
let resolver = null;

function build() {
    const element = document.createElement('div');
    element.id = 'admin-prompt';
    element.className = 'add-new-popup z-[1004] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
    element.innerHTML = `
        <div class="popup-dialog flex transition-all max-w-[420px] min-h-full items-center mx-auto">
            <form data-prompt-form class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <h5 class="!mb-[15px]" data-prompt-title></h5>
                <input type="text" data-prompt-input maxlength="255" autocomplete="off"
                    class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all focus:border-primary-500">
                <p data-prompt-error class="hidden text-danger-500 text-xs mt-[6px]"></p>
                <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] md:mt-[25px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="button" data-prompt-cancel
                        class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        Vazgeç
                    </button>
                    <button type="submit" data-prompt-accept
                        class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>`;

    document.body.append(element);

    const input = element.querySelector('[data-prompt-input]');

    element.querySelector('[data-prompt-form]').addEventListener('submit', (event) => {
        event.preventDefault();

        const value = input.value.trim();

        if (value === '') {
            const error = element.querySelector('[data-prompt-error]');
            error.textContent = 'Bu alan zorunludur.';
            error.classList.remove('hidden');

            return;
        }

        settle(value);
    });

    element.querySelector('[data-prompt-cancel]').addEventListener('click', () => settle(null));
    element.addEventListener('click', (event) => {
        if (event.target === element) {
            settle(null);
        }
    });

    return element;
}

function settle(result) {
    if (! resolver) {
        return;
    }

    dialog.classList.remove('active');
    document.body.classList.remove('overflow-hidden');
    resolver(result);
    resolver = null;
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && resolver) {
        settle(null);
    }
});

/**
 * @param {string} title
 * @param {{value?: string, accept?: string}} options
 * @returns {Promise<string|null>}
 */
export function promptText(title, options = {}) {
    dialog ??= build();

    const input = dialog.querySelector('[data-prompt-input]');

    dialog.querySelector('[data-prompt-title]').textContent = title;
    dialog.querySelector('[data-prompt-accept]').textContent = options.accept ?? 'Kaydet';
    dialog.querySelector('[data-prompt-error]').classList.add('hidden');
    input.value = options.value ?? '';

    dialog.classList.add('active');
    document.body.classList.add('overflow-hidden');

    // Modal geçiş animasyonu bitmeden focus verilirse tarayıcı bazen kaçırır.
    setTimeout(() => input.focus(), 50);

    return new Promise((resolve) => {
        resolver = resolve;
    });
}
