{{--
    Site genelinde sağ altta duran dönüşüm bloğu + teklif penceresi.

    Tasarım kararları:
    - Etiketler HER ZAMAN görünür. İkonun üstüne gelince açılan buton masaüstünde
      "keşfet" gerektirir, mobilde hover yoktur ve ziyaretçi ikonu tahmin etmek
      zorunda kalır — dönüşüm için en kötü hâli budur.
    - Teklif butonu birincil: daha büyük, koyu, faydayı yazan alt satırıyla
      ("2 saat içinde dönüş"). WhatsApp ikincil: marka yeşili, tanınır.
    - Blok sayfa açılır açılmaz değil, ziyaretçi biraz kaydırdıktan sonra girer
      (JS `is-visible`); ilk ekranı kapatmaz, girişi de animasyonun kendisidir.

    Numara panelden gelir (Ayarlar → Firma Bilgileri). WhatsApp alanı boşsa
    telefon denenir, ikisi de boşsa o buton hiç basılmaz.
--}}
@php
    $ctaCompany = \App\Support\Settings::group('company');
    $ctaPhone = $ctaCompany['phone'] ?? null;
    $ctaPhoneHref = \App\Support\Phone::href($ctaPhone);
    $ctaWhatsappNumber = ($ctaCompany['whatsapp'] ?? null) ?: $ctaPhone;
    $ctaSiteName = ($ctaCompany['name'] ?? null) ?: config('app.name');
    $ctaWhatsapp = \App\Support\Phone::whatsapp(
        $ctaWhatsappNumber,
        'Merhaba, ' . $ctaSiteName . ' hakkında bilgi almak istiyorum.',
    );
@endphp

<div class="floating-cta" data-floating-cta>
    @if ($ctaPhoneHref)
        <div class="floating-cta__call-wrap">
            <a class="floating-cta__call" href="{{ $ctaPhoneHref }}"
                aria-label="Tıkla, hemen ara: {{ $ctaPhone }}">
                <span class="floating-cta__call-icon" aria-hidden="true">
                    <i class="fa-solid fa-phone"></i>
                </span>
                <span class="floating-cta__call-text">
                    <small>Tıkla, Hemen Ara</small>
                    <strong>{{ $ctaPhone }}</strong>
                </span>
            </a>
        </div>
    @endif

    @if ($ctaWhatsapp)
        <a class="floating-cta__btn floating-cta__btn--whatsapp" href="{{ $ctaWhatsapp }}"
            target="_blank" rel="noopener noreferrer">
            <span class="floating-cta__icon" aria-hidden="true">
                <img src="{{ asset('assets/img/whatsapp.png') }}" alt="WhatsApp'tan Yaz">
            </span>
            <span class="floating-cta__text">
                <strong>WhatsApp'tan Yaz</strong>
                <small>Anında cevap</small>
            </span>
        </a>
    @endif

    <button type="button" class="floating-cta__btn floating-cta__btn--quote" data-quote-open
        aria-haspopup="dialog">
        <span class="floating-cta__icon" aria-hidden="true">
            <img src="{{ asset('assets/img/teklif.png') }}" alt="Ücretsiz Teklif Al">
        </span>
        <span class="floating-cta__text">
            <strong>Ücretsiz Teklif Al</strong>
            <small>2 saat içinde dönüş</small>
        </span>
    </button>
</div>

<div class="quote-modal" data-quote-modal hidden role="dialog" aria-modal="true"
    aria-labelledby="quote-modal-title">
    <div class="quote-modal__backdrop" data-quote-close></div>

    <div class="quote-modal__dialog" role="document">
        <button type="button" class="quote-modal__close" data-quote-close aria-label="Kapat">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <h2 id="quote-modal-title" class="visually-hidden">Ücretsiz teklif formu</h2>

        <x-site.quote-form compact
            heading="Ücretsiz teklif alın"
            intro="İki kısa adım — uzman ekibimiz ihtiyacınıza uygun teklifi hazırlayıp sizi arasın." />
    </div>
</div>
