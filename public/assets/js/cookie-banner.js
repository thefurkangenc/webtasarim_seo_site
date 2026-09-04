(() => {
    const root = document.querySelector('[data-cookie-banner]');
    const configEl = document.getElementById('cookie-banner-config');

    if (! root || ! configEl) {
        return;
    }

    const config = JSON.parse(configEl.textContent || '{}');
    const panel = root.querySelector('[data-cookie-panel]');
    const openBtn = document.querySelector('[data-cookie-open]');
    const name = config.cookie || 'cookie_consent';
    const version = Number(config.version || 1);
    const days = Number(config.days || 180);

    root.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
        persist({ functional: true, analytics: true, marketing: true });
    });

    root.querySelector('[data-cookie-reject]')?.addEventListener('click', () => {
        persist({ functional: false, analytics: false, marketing: false });
    });

    root.querySelector('[data-cookie-customize]')?.addEventListener('click', () => {
        if (panel) {
            panel.hidden = ! panel.hidden;
        }
    });

    root.querySelector('[data-cookie-save]')?.addEventListener('click', () => {
        persist(readCategories());
    });

    openBtn?.addEventListener('click', () => {
        root.hidden = false;
        if (panel) {
            panel.hidden = false;
        }
        openBtn.hidden = true;
        hydrate();
    });

    function readCategories() {
        return {
            functional: Boolean(root.querySelector('[data-cookie-cat="functional"]')?.checked),
            analytics: Boolean(root.querySelector('[data-cookie-cat="analytics"]')?.checked),
            marketing: Boolean(root.querySelector('[data-cookie-cat="marketing"]')?.checked),
        };
    }

    function hydrate() {
        const current = readCookie();

        if (! current) {
            return;
        }

        root.querySelectorAll('[data-cookie-cat]').forEach((input) => {
            input.checked = Boolean(current[input.dataset.cookieCat]);
        });
    }

    function persist(categories) {
        const payload = {
            v: version,
            functional: Boolean(categories.functional),
            analytics: Boolean(categories.analytics),
            marketing: Boolean(categories.marketing),
            ts: new Date().toISOString(),
        };

        const maxAge = days * 24 * 60 * 60;
        let cookie = `${name}=${encodeURIComponent(JSON.stringify(payload))}; Path=/; Max-Age=${maxAge}; SameSite=Lax`;

        if (location.protocol === 'https:') {
            cookie += '; Secure';
        }

        document.cookie = cookie;
        location.reload();
    }

    function readCookie() {
        const row = document.cookie.split('; ').find((part) => part.startsWith(`${name}=`));

        if (! row) {
            return null;
        }

        try {
            return JSON.parse(decodeURIComponent(row.slice(name.length + 1)));
        } catch {
            return null;
        }
    }
})();
