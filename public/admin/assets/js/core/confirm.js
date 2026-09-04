/** Promise döndüren onay kutusu: `if (await confirm('...')) { ... }` */

let dialog = null;
let resolver = null;

function build() {
    const element = document.createElement('div');
    element.id = 'admin-confirm';
    element.className = 'add-new-popup z-[1004] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
    element.innerHTML = `
        <div class="popup-dialog flex transition-all max-w-[420px] min-h-full items-center mx-auto">
            <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-content text-center">
                    <i class="material-symbols-outlined !text-[48px] text-danger-500">warning</i>
                    <h5 class="!mb-[8px] mt-[10px]" data-confirm-title></h5>
                    <p class="text-gray-500 dark:text-gray-400" data-confirm-message></p>
                </div>
                <div class="trezo-card-footer flex items-center justify-center gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] md:mt-[25px] border-t border-gray-100 dark:border-[#172036]">
                    <button type="button" data-confirm-cancel
                        class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        Vazgeç
                    </button>
                    <button type="button" data-confirm-accept
                        class="inline-block py-[10px] px-[30px] bg-danger-500 text-white transition-all hover:bg-danger-400 rounded-md border border-danger-500 hover:border-danger-400">
                        Evet, sil
                    </button>
                </div>
            </div>
        </div>`;

    document.body.append(element);

    element.querySelector('[data-confirm-cancel]').addEventListener('click', () => settle(false));
    element.querySelector('[data-confirm-accept]').addEventListener('click', () => settle(true));
    element.addEventListener('click', (event) => {
        if (event.target === element) {
            settle(false);
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
        settle(false);
    }
});

/**
 * @param {string} message
 * @param {{title?: string, accept?: string}} options
 * @returns {Promise<boolean>}
 */
export function confirm(message, options = {}) {
    dialog ??= build();

    dialog.querySelector('[data-confirm-title]').textContent = options.title ?? 'Emin misiniz?';
    dialog.querySelector('[data-confirm-message]').textContent = message;
    dialog.querySelector('[data-confirm-accept]').textContent = options.accept ?? 'Evet, sil';

    dialog.classList.add('active');
    document.body.classList.add('overflow-hidden');

    return new Promise((resolve) => {
        resolver = resolve;
    });
}
