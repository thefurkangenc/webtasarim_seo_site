{{--
    İki adımlı teklif formu — hizmet detayının kenar çubuğunda ve site
    genelindeki açılır pencerede AYNI bileşen kullanılır.

    Sayfada birden çok kopya bulunabildiği için her alan kimliği örneğe özel
    bir önekle basılır ($uid); JS de kimliğe değil `[data-quote-widget]`
    kapsayıcısına bağlanır, böylece iki form birbirini ezmez.

    Props
      service  -> sayfanın hizmeti; seçim kutusunda seçili gelir (opsiyonel)
      region   -> bölge sayfasıysa talebe işlenir (opsiyonel)
      heading  -> başlık; verilmezse genel metin
      intro    -> başlık altı açıklama
      compact  -> açılır pencere için: güven listesi ve telefon satırı basılmaz
--}}
@props([
    'service' => null,
    'region' => null,
    'heading' => null,
    'intro' => null,
    'compact' => false,
])

{{-- CSS ve JS layout/partials'ta kuresel yuklenir (bkz. css.blade.php,
     scripts.blade.php); bileşen bu yüzden kendi varlığını push etmez. --}}

@php
    $uid = 'quote-' . \Illuminate\Support\Str::random(6);
    $quoteServices = app(\App\Services\Quote\QuoteService::class)->serviceOptions();
    $quoteCompany = \App\Support\Settings::group('company');
    $quoteHeading = $heading ?: 'Ücretsiz teklif alın!';
    $quoteIntro = $intro ?: 'İki kısa adımı doldurun, uzman ekibimiz ihtiyacınıza uygun teklifi hazırlasın.';
    $quoteCompact = (bool) $compact;
@endphp

<div class="quote-widget {{ $quoteCompact ? 'quote-widget--compact' : '' }}" data-quote-widget data-quote-step="1">
    <div class="quote-head">
    <span class="quote-eyebrow"><span class="quote-eyebrow-dot"></span> Ücretsiz Teklif</span>
        <h3>{{ $quoteHeading }}</h3>
        <p>{{ $quoteIntro }}</p>
        <ul class="quote-trust">
            <li><i class="fa-solid fa-circle-check"></i> 2 saat içinde dönüş</li>
            <li><i class="fa-solid fa-circle-check"></i> Ücretsiz ön analiz</li>
            <li><i class="fa-solid fa-circle-check"></i> Bağlayıcı değildir</li>
        </ul>
    </div>

    <div class="quote-body">
        <div class="quote-step-form-wrap">
            <form class="quote-step-form" action="{{ route('teklif.store') }}" method="POST" novalidate>
                {{-- JS token'ı başlıkta da yollar; bu alan JS devre dışıyken
                     formun 419 ile ölmemesi için duruyor. --}}
                @csrf

                @if ($region)
                    <input type="hidden" name="region_id" value="{{ $region->id }}">
                @endif

                {{-- Honeypot: ziyaretçi görmez, bot doldurursa kayıt açılmaz. --}}
                <input type="text" name="website" class="quote-hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="quote-stepper" aria-hidden="true">
                    <div class="quote-stepper-item" data-stepper="1">
                        <span class="quote-stepper-dot"><span>1</span><i class="fa-solid fa-check"></i></span>
                        <span class="quote-stepper-label">Proje</span>
                    </div>
                    <div class="quote-progress">
                        <span class="quote-progress-fill" data-quote-progress></span>
                    </div>
                    <div class="quote-stepper-item" data-stepper="2">
                        <span class="quote-stepper-dot"><span>2</span><i class="fa-solid fa-check"></i></span>
                        <span class="quote-stepper-label">İletişim</span>
                    </div>
                </div>
                <p class="visually-hidden">Adım <span data-quote-current>1</span> / 2</p>

                <div class="quote-steps-viewport">
                    <div class="quote-steps-track" data-quote-track>
                        <div class="quote-step is-active" data-step="1">
                            <div class="quote-field">
                                <label for="{{ $uid }}-company">Firma adınız</label>
                                <div class="quote-input">
                                    <i class="fa-regular fa-building"></i>
                                    <input type="text" name="company" id="{{ $uid }}-company" data-quote-company
                                        placeholder="Örn. ABC İşletmesi" autocomplete="organization" required>
                                </div>
                                <p class="quote-error" data-error-for="company" hidden>Firma adını yazın.</p>
                            </div>
                            <div class="quote-field">
                                <label for="{{ $uid }}-service">İlgilendiğiniz hizmet</label>
                                <div class="quote-input">
                                    <i class="fa-solid fa-layer-group"></i>
                                    <select class="quote-step-select" name="service_id" id="{{ $uid }}-service"
                                        data-quote-service required>
                                        <option value="" disabled @selected(! $service || ! isset($quoteServices[$service->id]))>Hizmet seçin</option>
                                        @foreach ($quoteServices as $quoteServiceId => $quoteServiceTitle)
                                            <option value="{{ $quoteServiceId }}" @selected($service && $quoteServiceId === $service->id)>{{ $quoteServiceTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="quote-error" data-error-for="service_id" hidden>Bir hizmet seçin.</p>
                            </div>
                        </div>

                        <div class="quote-step" data-step="2">
                            <div class="quote-field">
                                <label for="{{ $uid }}-phone">Telefon numaranız</label>
                                <div class="quote-input">
                                    <i class="fa-solid fa-phone"></i>
                                    <input type="tel" name="phone" id="{{ $uid }}-phone" data-quote-phone
                                        inputmode="numeric" placeholder="0 (___) ___ __ __"
                                        autocomplete="tel" maxlength="19" required>
                                </div>
                                <p class="quote-error" data-error-for="phone" hidden>Geçerli bir telefon numarası yazın.</p>
                            </div>
                            <div class="quote-field">
                                <label for="{{ $uid }}-notes">Projeniz hakkında <span class="quote-optional">(isteğe bağlı)</span></label>
                                <textarea name="notes" id="{{ $uid }}-notes" rows="3"
                                    placeholder="İhtiyacınız hakkında kısa bir açıklama yazabilirsiniz."></textarea>
                            </div>
                            <div class="quote-field">
                                <x-captcha form="quote" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quote-nav" data-quote-nav>
                    <button type="button" class="ui-btn ui-btn--outline ui-btn--icon" data-quote-back aria-label="Geri" hidden>
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <button type="button" class="ui-btn ui-btn--solid" data-quote-next>
                        Devam Et <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="ui-btn ui-btn--solid" data-quote-submit hidden>
                        Ücretsiz Teklif Al <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>

                <p class="quote-error quote-form-error" data-quote-form-error hidden></p>

                <p class="quote-privacy">
                    <i class="fa-solid fa-lock"></i> Bilgileriniz gizli tutulur, üçüncü kişilerle paylaşılmaz.
                </p>
            </form>

            <div class="quote-success" data-quote-success hidden role="status" aria-live="polite">
                <div class="quote-success-icon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h4>Talebiniz alındı</h4>
                <p>Uzmanımız en kısa sürede sizi arayacak.</p>
            </div>
        </div>
    </div>

    @if (! $quoteCompact && filled($quoteCompany['phone'] ?? null))
        <a class="quote-call" href="{{ \App\Support\Phone::href($quoteCompany['phone']) }}">
            <span class="quote-call-icon"><i class="fa-solid fa-headset"></i></span>
            <span class="quote-call-text">
                <small>Beklemek istemiyor musunuz?</small>
                <strong>{{ $quoteCompany['phone'] }}</strong>
            </span>
        </a>
    @endif
</div>
