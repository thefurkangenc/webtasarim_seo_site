/**
 * Form yardımcıları: serileştirme, hata boyama, yükleniyor durumu.
 *
 * Her input'un altında <span data-error="<alan_adi>"></span> bulunmalıdır;
 * doğrulama mesajları oraya basılır.
 */

const ERROR_BORDER = '!border-danger-500';

/** Laravel'in nokta notasyonunu HTML name attribute'una çevirir: a.0.b -> a[0][b] */
function toFieldName(key) {
    const [first, ...rest] = key.split('.');

    return rest.length ? `${first}${rest.map((part) => `[${part}]`).join('')}` : first;
}

function inputsFor(form, key) {
    const name = toFieldName(key);

    return form.querySelectorAll(`[name="${name}"], [name="${name}[]"]`);
}

export function serialize(form) {
    return new FormData(form);
}

export function clearErrors(form) {
    form.querySelectorAll('[data-error]').forEach((element) => {
        element.textContent = '';
    });

    form.querySelectorAll(`.${CSS.escape(ERROR_BORDER)}`).forEach((element) => {
        element.classList.remove(ERROR_BORDER);
    });
}

/**
 * @param {HTMLFormElement} form
 * @param {Record<string, string[]>} errors Laravel'in 422 yanıtındaki `errors`
 */
export function showErrors(form, errors) {
    clearErrors(form);

    let firstInvalid = null;

    Object.entries(errors).forEach(([key, messages]) => {
        const slot = form.querySelector(`[data-error="${CSS.escape(key)}"]`);

        if (slot) {
            slot.textContent = messages[0];
        }

        inputsFor(form, key).forEach((input) => {
            input.classList.add(ERROR_BORDER);
            firstInvalid ??= input;
        });
    });

    firstInvalid?.focus();
    firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

export function setLoading(button, isLoading) {
    if (! button) {
        return;
    }

    if (isLoading) {
        button.dataset.originalHtml ??= button.innerHTML;
        button.disabled = true;
        button.classList.add('opacity-60', 'pointer-events-none');
        button.innerHTML = '<span class="flex items-center justify-center gap-[5px]">'
            + '<i class="material-symbols-outlined animate-spin !text-[18px]">progress_activity</i>'
            + 'Kaydediliyor...</span>';

        return;
    }

    button.disabled = false;
    button.classList.remove('opacity-60', 'pointer-events-none');

    if (button.dataset.originalHtml !== undefined) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
    }
}
