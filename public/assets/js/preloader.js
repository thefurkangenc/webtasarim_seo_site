(function () {
    const preloader = document.getElementById('site-preloader');
    if (!preloader) {
        return;
    }

    const bar = preloader.querySelector('.site-preloader__bar');
    let progress = 0;
    let done = false;

    const tick = () => {
        if (done) {
            return;
        }

        progress = Math.min(progress + Math.random() * 12, 92);
        if (bar) {
            bar.style.width = progress + '%';
        }
    };

    const interval = window.setInterval(tick, 180);

    const finish = () => {
        if (done) {
            return;
        }

        done = true;
        window.clearInterval(interval);
        window.clearTimeout(failsafe);

        if (bar) {
            bar.style.width = '100%';
        }

        preloader.classList.add('is-done');

        window.setTimeout(() => {
            preloader.remove();
            document.documentElement.classList.remove('is-preloading');
        }, 650);
    };

    // Yavaş/asılı kalan bir üçüncü taraf isteği (analytics, font, reklam)
    // "load" olayını çok geciktirebilir; ekran süresiz beyaz kalmasın diye
    // ne olursa olsun 4 saniye sonra kapatılır.
    const failsafe = window.setTimeout(finish, 4000);

    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish, { once: true });
    }
})();
