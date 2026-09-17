{{--
    <x-captcha form="contact" />

    Google'ın "Ben robot değilim" kutusuyla aynı kalıp: küçük bir onay kutusu
    formun akışında durur, tıklanınca bulmaca AYRI bir popup'ta açılır. Böylece
    widget sayfada yer kaplamaz ve bulmaca yalnızca gerçekten gerektiğinde
    (kullanıcı kutuya tıklayınca) üretilir — sayfa ilk açıldığında hiçbir şey
    görünmez, "hazırlanıyor" metni de yalnızca popup açıkken kısaca görünür.

    Popup paneli JS tarafından <body>'ye taşınır: bu form bir açılır pencerenin
    (örn. bülten popup'ı) içinde olsa bile üstteki katmanı o kapsayıcı kırpmaz.
    Bileşen kendi css/js'ini de getirir (@once ile sayfada bir kez), layout'a
    bir şey eklemek gerekmez. Hata metni data-error="captcha" kutusuna basılır
    — projedeki form JS'leri bu sözleşmeyi zaten kullanıyor.
--}}
@once
    <link rel="stylesheet" href="{{ \App\Captcha\Support\Asset::url('captcha.css') }}">
    <script src="{{ \App\Captcha\Support\Asset::url('captcha.js') }}" defer></script>
@endonce

<div class="cap" data-captcha
    data-captcha-challenge="{{ route('captcha.challenge') }}"
    data-captcha-verify="{{ route('captcha.verify') }}">

    <input type="hidden" name="{{ $name }}" value="" data-captcha-input autocomplete="off">

    <button type="button" class="cap-check" data-captcha-toggle role="checkbox" aria-checked="false">
        <span class="cap-check-box" data-captcha-checkbox>
            <svg class="cap-check-mark" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="m4 12.5 5 5L20 6.5" />
            </svg>
            <span class="cap-check-spin" aria-hidden="true"></span>
        </span>

        <span class="cap-check-label">{{ $label }}</span>

        <span class="cap-check-divider" aria-hidden="true"></span>

        <span class="cap-check-badge" aria-hidden="true">
            <svg class="cap-check-badge-icon" viewBox="0 0 24 24" focusable="false">
                <path d="M12 3.2 5.5 5.8v5.1c0 4.7 2.8 8.1 6.5 9.9 3.7-1.8 6.5-5.2 6.5-9.9V5.8L12 3.2Z" />
                <path d="m9 12.3 2.1 2.1 4-4.2" />
            </svg>
            <span class="cap-check-badge-text">Güvenlik<br>Doğrulama</span>
        </span>
    </button>

    <div class="cap-modal" data-captcha-modal hidden>
        <div class="cap-modal-backdrop" data-captcha-backdrop></div>

        <div class="cap-modal-panel" role="dialog" aria-modal="true" aria-label="{{ $label }}">
            <div class="cap-modal-head">
                <span class="cap-modal-title">{{ $label }}</span>
                <div class="cap-modal-tools">
                    <button type="button" class="cap-tool" data-captcha-refresh aria-label="Yeni bulmaca getir" title="Yenile">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M20 11a8 8 0 1 0-2.3 6.3" />
                            <path d="M20 5v6h-6" />
                        </svg>
                    </button>
                    <button type="button" class="cap-tool" data-captcha-close aria-label="Kapat" title="Kapat">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="cap-modal-body">
                @include($driverView)
            </div>

            <p class="cap-status" data-captcha-status role="status" aria-live="polite"></p>
        </div>
    </div>

    <span class="cap-error" data-error="{{ $name }}" hidden></span>
</div>
