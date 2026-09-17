@extends('layout.app')
{{-- Sekme başlığı SEO alanından gelir; boşsa hizmetin kendi başlığına düşer.
     Bölge sayfasında bu değer bölge adıyla nitelenmiş olarak gelir (bkz.
     Service::renderFor), böylece her bölge adresi kendi başlığını alır. --}}
@section('title', $rendered['seo']['title'] ?: $rendered['title'])
@section('meta_description', (string) $rendered['seo']['description'])
@section('meta_keywords', (string) $rendered['seo']['keywords'])
@section('meta_image', (string) $rendered['seo']['image'])
@section('content')
    @php
        // Bölge sayfasında üst kırılım "hizmetin genel adı" olarak bölgesiz
        // (yer tutucusuz) başlığı gösterir — "Gaziantep Web Tasarım > Gaziantep"
        // gibi tekrarlı görünmesin diye burada ayrıca çözülür.
        $genericTitle = $service->renderGeneric()['title'];

        // Kırılımın bölge basamakları adresle aynı sırayı izler (il > ilçe).
        // Hizmete bağlı olmayan bir üst bölgenin sayfası yoktur, o yüzden
        // linksiz basılır.
        $regionTrail = $region
            ? $region->ancestorsAndSelf()->map(fn ($step) => [
                'name' => $step->name,
                'url' => $step->is($region) || $service->coveredRegions()->contains($step)
                    ? route('hizmetler.show-region', [$service->slug, $step->slug_path])
                    : null,
            ])
            : collect();
    @endphp

    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $rendered['title'] }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('hizmetler') }}">Hizmetler</a></li>
                                @if ($region)
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li><a href="{{ route('hizmetler.show', $service->slug) }}">{{ $genericTitle }}</a></li>
                                    @foreach ($regionTrail as $step)
                                        <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                        <li>
                                            @if ($step['url'] && ! $loop->last)
                                                <a href="{{ $step['url'] }}">{{ $step['name'] }}</a>
                                            @else
                                                {{ $step['name'] }}
                                            @endif
                                        </li>
                                    @endforeach
                                @else
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $rendered['title'] }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--===== BLOG DETAILS AREA START =====-->

    <div class="blog-details-area sp">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="sidebar-area">

                        @php($quoteCompany = \App\Support\Settings::group('company'))

                        <div class="quote-widget" data-quote-widget data-quote-step="1">
                            <div class="quote-head">
                                <span class="quote-eyebrow"><span class="quote-eyebrow-dot"></span> Ücretsiz Teklif</span>
                                <h3>Projenize özel fiyat alın</h3>
                                <p>İki kısa adımı doldurun, uzman ekibimiz ihtiyacınıza uygun teklifi hazırlasın.</p>
                                <ul class="quote-trust">
                                    <li><i class="fa-solid fa-circle-check"></i> 24 saat içinde dönüş</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Ücretsiz ön analiz</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Bağlayıcı değildir</li>
                                </ul>
                            </div>

                            <div class="quote-body">
                                <div class="quote-step-form-wrap">
                                    <form class="quote-step-form" id="quote-step-form" action="{{ route('teklif.store') }}" method="POST" novalidate>
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
                                                        <label for="quote-company">Firma adınız</label>
                                                        <div class="quote-input">
                                                            <i class="fa-regular fa-building"></i>
                                                            <input type="text" name="company" id="quote-company"
                                                                placeholder="Örn. Umay Dijital" autocomplete="organization"
                                                                required>
                                                        </div>
                                                        <p class="quote-error" data-error-for="company" hidden>Firma adını yazın.</p>
                                                    </div>
                                                    <div class="quote-field">
                                                        <label for="quote-service">İlgilendiğiniz hizmet</label>
                                                        <div class="quote-input">
                                                            <i class="fa-solid fa-layer-group"></i>
                                                            <select class="quote-step-select" name="service_id" id="quote-service"
                                                                required>
                                                                <option value="" disabled @selected(! isset($quoteServices[$service->id]))>Hizmet seçin</option>
                                                                @foreach ($quoteServices as $quoteServiceId => $quoteServiceTitle)
                                                                    <option value="{{ $quoteServiceId }}" @selected($quoteServiceId === $service->id)>{{ $quoteServiceTitle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <p class="quote-error" data-error-for="service_id" hidden>Bir hizmet seçin.</p>
                                                    </div>
                                                </div>

                                                <div class="quote-step" data-step="2">
                                                    <div class="quote-field">
                                                        <label for="quote-phone">Telefon numaranız</label>
                                                        <div class="quote-input">
                                                            <i class="fa-solid fa-phone"></i>
                                                            <input type="tel" name="phone" id="quote-phone"
                                                                inputmode="numeric" placeholder="0 (___) ___ __ __"
                                                                autocomplete="tel" maxlength="19" required>
                                                        </div>
                                                        <p class="quote-error" data-error-for="phone" hidden>Geçerli bir telefon numarası yazın.</p>
                                                    </div>
                                                    <div class="quote-field">
                                                        <label for="quote-notes">Projeniz hakkında <span class="quote-optional">(isteğe bağlı)</span></label>
                                                        <textarea name="notes" id="quote-notes" rows="3" placeholder="Hedefiniz, bütçeniz, zamanlamanız…"></textarea>
                                                    </div>
                                                    <div class="quote-field">
                                                        <x-captcha form="quote" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="quote-nav" data-quote-nav>
                                            <button type="button" class="quote-btn-back" data-quote-back aria-label="Geri" hidden>
                                                <i class="fa-solid fa-arrow-left"></i>
                                            </button>
                                            <button type="button" class="quote-btn-primary" data-quote-next>
                                                Devam Et <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                            <button type="submit" class="quote-btn-primary" data-quote-submit hidden>
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

                            @if (filled($quoteCompany['phone'] ?? null))
                                <a class="quote-call" href="{{ \App\Support\Phone::href($quoteCompany['phone']) }}">
                                    <span class="quote-call-icon"><i class="fa-solid fa-headset"></i></span>
                                    <span class="quote-call-text">
                                        <small>Beklemek istemiyor musunuz?</small>
                                        <strong>{{ $quoteCompany['phone'] }}</strong>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-6">
                    <div class="blog-details-content ml-30 md:ml-0 sm:ml-0">

                        <div class="heading2 mt-24">
                            <h3>{{ $rendered['title'] }}</h3>
                            @if (filled($rendered['excerpt']))
                                <p class="mt-16">{{ $rendered['excerpt'] }}</p>
                            @endif
                        </div>


                        @php($cover = $service->getFirstMedia('cover'))
                        @if ($cover)
                            <article>
                                <div class="details-content">
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url('medium') }}"
                                            alt="{{ $rendered['title'] }}">
                                    </div>
                                </div>
                            </article>
                        @endif

                        <article>
                            <div class="details-content">


                                @if (filled($rendered['content']))
                                    <div class="heading2 mt-24">
                                        {!! $rendered['content'] !!}
                                    </div>
                                @endif

                            </div>
                        </article>

                        {{-- Bölgeye özel metin: aynı hizmetin bütün bölge sayfaları ortak
                             içeriği paylaşır, özgün olan tek parça budur. Panelde
                             doldurulmadıysa blok hiç basılmaz. --}}
                        @if ($region && filled($region->description))
                            <div class="region-note">
                                <h2>
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $region->placeholders()['region'] }} için notumuz
                                </h2>
                                <p>{{ $region->description }}</p>
                            </div>
                        @endif

                        <div class="details-border"></div>



                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="sidebar-area position-relative top-0">


                        {{-- Bölge listesi: her ilçe bağlı olduğu ilin altında toplanır.
                             İlin kendisi hizmete bağlı değilse başlık tıklanamaz —
                             o adrese karşılık gelen bir sayfa yok. --}}
                        @if ($regionGroups !== [])
                            <div class="region-widget" data-region-widget>
                                <h3>Hizmet Verdiğimiz Bölgeler</h3>
                                <p class="region-widget-note">
                                    {{ $genericTitle }} hizmetimizi aşağıdaki bölgelerde veriyoruz.
                                </p>

                                @if ($service->coveredRegions()->count() > 8)
                                    <div class="region-search">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="search" placeholder="Bölge ara" aria-label="Bölge ara"
                                            data-region-search>
                                    </div>
                                @endif

                                <div class="region-groups">
                                    @foreach ($regionGroups as $group)
                                        <div class="region-group" data-region-group>
                                            @if ($group['page'])
                                                <a class="region-city @if ($region?->is($group['page'])) is-current @endif"
                                                    href="{{ route('hizmetler.show-region', [$service->slug, $group['page']->slug_path]) }}"
                                                    data-region-item>
                                                    <i class="fa-solid fa-location-dot"></i>
                                                    <span>{{ $group['city']->name }}</span>
                                                </a>
                                            @else
                                                <span class="region-city is-plain" data-region-item>
                                                    <i class="fa-solid fa-location-dot"></i>
                                                    <span>{{ $group['city']->name }}</span>
                                                </span>
                                            @endif

                                            @if ($group['children']->isNotEmpty())
                                                <ul class="region-children">
                                                    @foreach ($group['children'] as $child)
                                                        <li>
                                                            <a class="@if ($region?->is($child)) is-current @endif"
                                                                href="{{ route('hizmetler.show-region', [$service->slug, $child->slug_path]) }}"
                                                                data-region-item>{{ $child->name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <p class="region-empty" data-region-empty hidden>Aradığınız bölge listede yok.</p>
                            </div>
                        @endif




                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== BLOG DETAILS AREA END =====-->

    <x-googlecomment />

    @if ($projects->isNotEmpty())
        <!--===== SERVICE PROJECTS START =====-->

        <div class="portfolio sp">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 m-auto text-center">
                        <div class="heading2">
                            <h2>Bu Hizmette Yaptığımız İşler</h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($projects as $serviceProject)
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                            @include('pages.projects.partials.card', ['project' => $serviceProject])
                        </div>
                    @endforeach
                </div>
                <div class="row mt-40">
                    <div class="col-lg-12 text-center">
                        <a class="theme-btn3" href="{{ route('projeler') }}">
                            Tüm İşlerimiz
                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                            <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!--===== SERVICE PROJECTS END =====-->
    @endif
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/card.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/services/show.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/pages/services/show.js') }}"></script>
@endpush
