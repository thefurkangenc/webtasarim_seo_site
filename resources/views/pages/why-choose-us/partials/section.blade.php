{{--
    "Neden Biz" bölümü — anasayfa ve iletişim sayfası ortak kullanır.

    Veriyi kendi çeker: çağıran sayfanın servis bilmesi gerekmez, bir
    @include yeter. CSS'i de kendi yükler (@once ile, kaç kez include
    edilirse edilsin tek satır basılır) — böylece yeni bir sayfaya
    eklenirken stil dosyasını unutmak mümkün değil.

    $bg  -> bölüm arka planı için tema class'ı (örn. 'sec-bg2'), isteğe bağlı.
--}}
@once
    @push('css')
        <link rel="stylesheet" href="{{ asset('assets/css/sections/why-choose-us.css') }}">
    @endpush
@endonce

@php
    $whyReasons = app(\App\Services\WhyChooseUs\WhyChooseUsService::class)->active();
    $whyHeading = \App\Support\Settings::group('why_choose_us');
    $whyBg = $bg ?? '';
@endphp

<section class="why-us sp {{ $whyBg }}" aria-labelledby="why-us-title">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <div class="why-us__visual" data-aos="fade-right" data-aos-duration="900">
                    <img src="{{ asset('assets/img/neden-biz.jpg') }}"
                        alt="Web tasarım, yazılım ve dijital pazarlama çözümleri"
                        width="640" height="520" loading="lazy">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="why-us__content heading14">
                    <span class="sub-title">
                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                            src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Neden Biz?">
                        Neden Biz?
                    </span>

                    <h2 id="why-us-title" class="text-anime-style-3">
                        {{ $whyHeading['title'] }}
                    </h2>

                    <p class="why-us__lead">
                        {{ $whyHeading['description'] }}
                    </p>

                    @if ($whyReasons->isNotEmpty())
                        <ul class="why-us__points">
                            @foreach ($whyReasons as $reason)
                                <li>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    <span>
                                        <strong>{{ $reason->title }}</strong>
                                        @if (filled($reason->description))
                                            — {{ $reason->description }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="why-us__actions">
                        <a href="{{ route('iletisim') }}" class="ui-btn ui-btn--solid">Ücretsiz Görüşme Planlayın</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
