/**
 * <x-admin::form.phone> davranışı.
 *
 * Sol taraftaki ülke düğmesi Trezo dropdown kalıbını kullanır (Choices.js
 * değil). Sağdaki görünür input yazıldıkça maskelenir; forma giden değerler
 * gizli `country_id` ve yalnızca rakamlardan oluşan `phone` alanlarıdır.
 */

function digits(value) {
    return String(value ?? '').replace(/\D+/g, '');
}

function format(raw, mask) {
    const nums = digits(raw);
    let out = '';
    let index = 0;

    for (const char of mask) {
        if (char === '0') {
            if (index >= nums.length) {
                break;
            }

            out += nums[index++];

            continue;
        }

        if (index >= nums.length) {
            break;
        }

        out += char;
    }

    return out;
}

function normalize(raw, country) {
    let nums = digits(raw);

    if (country.strip_leading_zero && nums.startsWith('0')) {
        nums = nums.slice(1);
    }

    return nums.slice(0, country.digit_count);
}

function placeholder(country) {
    return String(country.mask).replaceAll('0', '5');
}

class PhoneField {
    constructor(root) {
        this.root = root;
        this.countryInput = root.querySelector('[data-phone-country]');
        this.phoneInput = root.querySelector('[data-phone-value]');
        this.display = root.querySelector('[data-phone-display]');
        this.toggle = root.querySelector('[data-phone-toggle]');
        this.menu = root.querySelector('[data-phone-menu]');
        this.flag = root.querySelector('[data-phone-flag]');
        this.dial = root.querySelector('[data-phone-dial]');
        this.iso = root.querySelector('[data-phone-iso]');
        this.countries = JSON.parse(root.dataset.phoneCountries || '[]');
        this.country = this.countries.find((item) => String(item.id) === String(this.countryInput.value))
            ?? this.countries[0]
            ?? null;

        this.onDocumentClick = (event) => {
            if (! this.root.contains(event.target)) {
                this.setOpen(false);
            }
        };

        this.bind();

        if (this.country) {
            this.renderCountry();
            this.sync(this.phoneInput.value);
        }
    }

    bind() {
        this.toggle?.addEventListener('click', (event) => {
            event.preventDefault();
            this.setOpen(this.menu.hidden);
        });

        this.menu?.addEventListener('click', (event) => {
            const option = event.target.closest('[data-phone-option]');

            if (! option) {
                return;
            }

            const country = this.countries.find((item) => String(item.id) === option.dataset.phoneOption);

            if (country) {
                this.country = country;
                this.renderCountry();
                this.sync(this.phoneInput.value);
            }

            this.setOpen(false);
        });

        this.display?.addEventListener('input', () => this.sync(this.display.value));

        this.display?.addEventListener('paste', (event) => {
            event.preventDefault();
            this.sync((event.clipboardData || window.clipboardData).getData('text'));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.setOpen(false);
            }
        });
    }

    setOpen(open) {
        if (! this.menu) {
            return;
        }

        this.menu.hidden = ! open;
        this.toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open) {
            document.addEventListener('click', this.onDocumentClick);
        } else {
            document.removeEventListener('click', this.onDocumentClick);
        }
    }

    renderCountry() {
        this.countryInput.value = this.country.id;
        this.flag.textContent = this.country.flag;
        this.dial.textContent = `+${this.country.dial_code}`;
        this.iso.textContent = this.country.iso2;
        this.display.placeholder = placeholder(this.country);
    }

    sync(raw) {
        const nums = normalize(raw, this.country);

        this.phoneInput.value = nums;
        this.display.value = format(nums, this.country.mask);

        const cursor = this.display.value.length;
        this.display.setSelectionRange(cursor, cursor);
    }
}

function boot(root = document) {
    root.querySelectorAll('[data-phone-field]').forEach((element) => {
        if (element.dataset.phoneReady) {
            return;
        }

        element.dataset.phoneReady = '1';
        new PhoneField(element);
    });
}

boot();
