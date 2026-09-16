@extends('layout.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/home/index.css') }}">
@endpush

@section('content')
    <!--===== HERO AREA START =====-->

    @php
        $hero = app(\App\Services\Hero\HeroService::class)->current();
        $heroImages = $hero->getMedia('gallery');
        $heroMarqueeRepeats = $heroImages->isEmpty() ? 1 : (int) ceil(8 / $heroImages->count());
        $heroBackground = $hero->getFirstMedia('background');
    @endphp

    <div class="hero6" style="background-image: url({{ $heroBackground?->url() ?: asset('assets/img/hero/hero6-bg.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="main-heading6">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900">
                            <img style="width: 20px; height: 20px; margin-right: 5px;"
                                src="{{ asset('assets/img/icons/icon.png') }}"
                                alt=""> {{ $hero->badge }}</span>
                        <h1>{{ $hero->title}}</h1>
                        <p class="mt-16">
                            {{ $hero->description }}
                        </p>
                        {{-- <div class="buttons">
                            @if (filled($hero->button_text))
                                <a href="{{ $hero->button_url ?: route('iletisim') }}" class="theme-btn11">
                                    <span class="theme-btn11__shape"></span>
                                    <span class="theme-btn11__shape"></span>
                                    <span class="theme-btn11__shape"></span>
                                    <span class="theme-btn11__shape"></span>
                                    <span class="theme-btn11__text">{{ $hero->button_text }}</span>
                                    <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                        class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                </a>
                            @endif
                            <div class="video-buttton6 play-btn" href="https://www.youtube.com/watch?v=Y8XpQpW5OVY">
                                <a id="play-video" class="video-play-button">
                                    <span></span>
                                </a>
                                <p>Play Video</p>
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>
        </div>
        @if ($heroImages->isNotEmpty())
            <div class="hero6-bottom-slider">
                <section class="hero10-benar">
                    <div class="container-fluid p-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="marquee-wrap">

                                    <div class="marquee-text">
                                        @foreach ([false, true] as $isDuplicate)
                                            <div class="d-flex align-items-center" style="gap: 50px;"
                                                @if ($isDuplicate) aria-hidden="true" @endif>
                                                @for ($i = 0; $i < $heroMarqueeRepeats; $i++)
                                                    @foreach ($heroImages as $heroImage)
                                                        <div class="brand-single-box">
                                                            <img src="{{ $heroImage->url() }}" alt="{{ $heroImage->alt }}">
                                                        </div>
                                                    @endforeach
                                                @endfor
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="slider-after"></div>
                    </div>
                </section>
            </div>
        @endif
    </div>




    <div class="about1 sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="heading1">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900">
                            <img style="width: 20px; height: 20px; margin-right: 5px;"
                                src="{{ asset('assets/img/icons/icon.png') }}"
                                alt=""> Hakkımızda </span>
                        <h2 class="text-anime-style-3">İnternette Yoksanız
                            Müşteriniz Sizi Nasıl
                            Bulacak?
                        </h2>
                            <div class="mt-16" data-aos="fade-right" data-aos-duration="900">
                                Firmanız hakkında araştırma yapan müşterilerin karşısına profesyonel bir web sitesiyle çıkın. Müşterileriniz kim olduğunuzu, ne yaptığınızı, hangi hizmetleri sunduğunuzu ve sizinle nasıl iletişime geçebileceğini tek yerde görebilsin.
                            </div>
                    </div>
                </div>
                <div class="col-lg-8 text-end sm:text-start md:text-start md:mt-30 sm:mt-30">
                    <div class="button"     >
                        <a href="{{route('hakkimizda')}}" class="default-btn ">
                            <i style="font-size:15px;" class="fa-solid fa-phone-volume"></i> &nbsp; Detayları Konuşalım!
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-30 align-items-end about1-boxs-all">
                <div class="col-lg-4 col-md-6">
                    <div class="about1-box box1 white-heading mt-30">
                        <div class="top-heading">
                            <h5>Google'da Bulunun</h5>
                            <p class="mt-16">
                                Müşterileriniz firmanızın adını veya sunduğunuz hizmeti aradığında karşılarına işletmenize ait bir web sitesi çıksın.
                            </p>
                        </div>
                        <div class="bottom-heading">
                            <h3><span class="">GOOGLE'DA
                                YERİNİZİ ALIN</span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="about1-box box2 white-heading mt-30">
                        <div class="top-heading">
                            <h5>İşinizi Anlatın</h5>
                            <p class="mt-16">
                                Müşterileriniz kim olduğunuzu, ne yaptığınızı, hangi hizmetleri sunduğunuzu ve sizinle nasıl iletişime geçebileceğini tek yerde görebilsin.
                            </p>
                        </div>
                        <div class="bottom-heading">
                            <h3><span class="">
                                GÜVEN OLUŞTURUN

                            </span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" >
                    <div class="about1-box box3 white-heading mt-30">
                        <div class="top-heading">
                            <h5>Yeni Müşterilere Ulaşın</h5>
                            <p class="mt-16">
                                Google'da sizi arayan potansiyel müşterilerin karşısına çıkın. Web sitenizi arama motorlarına uygun bir altyapıyla hazırlıyoruz.

                            </p>
                        </div>
                        <div class="bottom-heading">
                            <h3><span class="">GÜVENİ SATIŞA ÇEVİRİN!</span></h3>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== ABOUT AREA END =====-->

    <!--===== SERVICE AREA START =====-->
    @php
        $homeServices = app(\App\Services\Service\ServiceService::class)->active(6);
    @endphp


    <div class="service6 sp sec-bg5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="heading6 _mt-50">
                        <span class="sub-title">Hizmetlerimiz</span>
                        <h2 class="text-anime-style-3">İşinizi Büyütecek Hizmetler</h2>
                        <p class="mt-16">Web tasarım, SEO ve dijital pazarlama ile markanızı arama sonuçlarında öne çıkarıyoruz. İhtiyacınıza uygun çözümlerle daha fazla görünürlük, trafik ve müşteri hedefliyoruz.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="buttons text-end sm:text-start md:text-start sm:mt-20 md:mt-20" data-aos="fade-left"
                        data-aos-duration="1100">
                        <a href="{{ route('hizmetler') }}" class="theme-btn11">
                            <span class="theme-btn11__shape"></span>
                            <span class="theme-btn11__shape"></span>
                            <span class="theme-btn11__shape"></span>
                            <span class="theme-btn11__shape"></span>
                            <span class="theme-btn11__text">Tüm Hizmetler</span>
                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                    class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>

            @if ($homeServices->isEmpty())
                <div class="row mt-30">
                    <div class="col-lg-8 m-auto text-center">
                        <p>Henüz yayınlanmış bir hizmet bulunmuyor.</p>
                    </div>
                </div>
            @else
                <div class="row mt-30">
                    @foreach ($homeServices as $homeService)
                        @php
                            $homeServiceGeneric = $homeService->renderGeneric();
                            $homeServiceCover = $homeService->getFirstMedia('cover');
                        @endphp
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="900">
                            <div class="service6-box mt-30">
                                @if ($homeServiceCover)
                                    <div class="thumb">
                                        <a href="{{ route('hizmetler.show', $homeService->slug) }}">
                                            <img src="{{ $homeServiceCover->url() }}"
                                                alt="{{ $homeServiceGeneric['title'] }}">
                                        </a>
                                    </div>
                                @endif
                                <div class="content heading6">
                                    <h4><a
                                            href="{{ route('hizmetler.show', $homeService->slug) }}">{{ $homeServiceGeneric['title'] }}</a>
                                    </h4>
                                    <p class="mt-16">
                                        {{ $homeServiceGeneric['excerpt'] ?: \Illuminate\Support\Str::limit(strip_tags((string) $homeServiceGeneric['content']), 120) }}
                                    </p>
                                    <a href="{{ route('hizmetler.show', $homeService->slug) }}" class="learn">Detaylı
                                        Bilgi <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                            class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    <!--===== SERVICE AREA END =====-->

    <!--===== CHOOSE AREA START =====-->
    @php
        $homeWhyChooseUs = app(\App\Services\WhyChooseUs\WhyChooseUsService::class)->active();
        $homeWhyHeading = \App\Support\Settings::group('why_choose_us');
    @endphp
    <section class="home-why sp" aria-labelledby="home-why-title">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <div class="home-why__visual" data-aos="fade-right" data-aos-duration="900">
                        <img src="{{ asset('assets/img/neden-biz.jpg') }}"
                            alt="Web tasarım, yazılım ve dijital pazarlama çözümleri"
                            width="640" height="520" loading="lazy">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="home-why__content heading14">
                        <span class="sub-title">
                            <img style="width: 20px; height: 20px; margin-right: 5px;"
                                src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                            Neden Biz?
                        </span>

                        <h2 id="home-why-title" class="text-anime-style-3">
                            {{ $homeWhyHeading['title'] }}
                        </h2>

                        <p class="home-why__lead">
                            {{ $homeWhyHeading['description'] }}
                        </p>

                        @if ($homeWhyChooseUs->isNotEmpty())
                            <ul class="home-why__points">
                                @foreach ($homeWhyChooseUs as $reason)
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

                        <div class="home-why__actions">
                            <a href="{{ route('iletisim') }}" class="theme-btn27">Ücretsiz Görüşme Planlayın</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--===== CHOOSE AREA END =====-->
    @php $homeReferences = app(\App\Services\Reference\ReferenceService::class)->active(); @endphp
    @if ($homeReferences->isNotEmpty())
        <section class="home-refs sp sec-bg5" aria-labelledby="home-refs-title">
            <div class="container">
                <div class="home-refs__head text-center">
                    <span class="sub-title">
                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                            src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                        Referanslar
                    </span>
                    <h2 id="home-refs-title" class="text-anime-style-3">Bizimle Çalışan Şirketler</h2>
                    <p class="home-refs__lead">
                        Farklı sektörlerden yüzlerce işletme dijital dönüşümünde bizi tercih etti.
                    </p>
                </div>

                <div class="home-refs__grid">
                    @foreach ($homeReferences as $reference)
                        @php $referenceLogo = $reference->getFirstMedia('logo'); @endphp
                        @if ($referenceLogo)
                            <div class="home-refs__item">
                                @if (filled($reference->url))
                                    <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer"
                                        title="{{ $reference->name }}">
                                        <img src="{{ $referenceLogo->url('reference.logo') }}"
                                            alt="{{ $reference->name }}" loading="lazy">
                                    </a>
                                @else
                                    <img src="{{ $referenceLogo->url('reference.logo') }}"
                                        alt="{{ $reference->name }}" loading="lazy">
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="home-refs__bar">
                    <div class="home-refs__bar-text">
                        <p class="home-refs__bar-title">Sıradaki başarı hikayesi sizinki olabilir!</p>
                        <p class="home-refs__bar-note">Ücretsiz ön görüşme · Size özel teklif · Çözüm Odaklı Yaklaşım</p>
                    </div>
                    <a href="{{ route('iletisim') }}" class="default-btn">Detayları Konuşalım</a>
                </div>
            </div>
        </section>
    @endif
    <!--===== GOOGLE YORUMLARI =====-->
    @php
        $homeTestimonials = app(\App\Services\Testimonial\TestimonialService::class)->active();
        $homeReviewsAvg = round($homeTestimonials->avg('rating'), 1);
        $homeReviewsCount = $homeTestimonials->count();
        $homeGoogleReviewLink = collect(app(\App\Services\SocialLink\SocialLinkService::class)->list())
            ->first(
                fn ($link) => str_contains(strtolower($link['url']), 'google.com/maps')
                    || str_contains(strtolower($link['url']), 'g.page')
                    || str_contains(strtolower($link['url']), 'business.google'),
            );
        $homeGoogleReviewUrl = $homeGoogleReviewLink['url'] ?? null;
    @endphp
    @if ($homeTestimonials->isNotEmpty())
        <section class="home-reviews sp" aria-labelledby="home-reviews-title">
            <div class="container">
                <div class="row align-items-end g-4 home-reviews__top">
                    <div class="col-lg-7">
                        <div class="home-reviews__intro">
                            <span class="home-reviews__badge">
                                <svg class="home-reviews__g-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18" aria-hidden="true">
                                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.27 7.09-17.65z"/>
                                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                </svg>
                                Google Yorumları
                            </span>
                            <h2 id="home-reviews-title" class="text-anime-style-3">Müşterilerimiz Google'da Ne Diyor?</h2>
                            <p class="home-reviews__lead">
                                Gerçek iş ortaklarımızın deneyimleri
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="home-reviews__score">
                            <div class="home-reviews__score-brand">
                                <svg class="home-reviews__g-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="28" height="28" aria-hidden="true">
                                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.27 7.09-17.65z"/>
                                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                </svg>
                                <div>
                                    <p class="home-reviews__score-label">Google puanı</p>
                                    <p class="home-reviews__score-value">
                                        {{ number_format($homeReviewsAvg, 1, ',', '.') }}
                                        <span class="home-reviews__score-max">/ 5</span>
                                    </p>
                                </div>
                            </div>
                            <div class="home-reviews__score-stars" aria-hidden="true">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($homeReviewsAvg))
                                        <i class="fa-solid fa-star"></i>
                                    @elseif ($homeReviewsAvg > floor($homeReviewsAvg) && $i === (int) ceil($homeReviewsAvg))
                                        <i class="fa-solid fa-star-half-stroke"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div class="home-reviews__slider-wrap">
                    <div class="home-reviews__arrows tes14-arrows">
                        <div class="prev14-arrow">
                            <button type="button" aria-label="Önceki yorum"><i class="fa-solid fa-angle-left"></i></button>
                        </div>
                        <div class="next14-arrow">
                            <button type="button" aria-label="Sonraki yorum"><i class="fa-solid fa-angle-right"></i></button>
                        </div>
                    </div>

                    <div class="testimonial14-slider-area home-reviews__slider" data-aos="fade-up" data-aos-duration="1000">
                        @foreach ($homeTestimonials as $testimonial)
                            @php $reviewPhoto = $testimonial->getFirstMedia('photo'); @endphp
                            <article class="testimonial14-boxarea home-reviews__card">
                                <header class="home-reviews__card-head">
                                    <span class="home-reviews__source">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="16" height="16" aria-hidden="true">
                                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.27 7.09-17.65z"/>
                                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                        </svg>
                                        Google Yorumu
                                    </span>
                                    <ul class="home-reviews__stars" aria-label="{{ $testimonial->rating }} yıldız">
                                        @for ($i = 0; $i < $testimonial->rating; $i++)
                                            <li><i class="fa-solid fa-star"></i></li>
                                        @endfor
                                    </ul>
                                </header>

                                <blockquote class="home-reviews__quote">“{{ $testimonial->content }}”</blockquote>

                                <footer class="home-reviews__author">
                                    @if ($reviewPhoto)
                                        <img class="home-reviews__avatar" src="{{ $reviewPhoto->url('testimonial.photo') }}"
                                            alt="{{ $testimonial->name }}" width="44" height="44" loading="lazy">
                                    @else
                                        <span class="home-reviews__avatar home-reviews__avatar--placeholder" aria-hidden="true">
                                            <i class="fa-solid fa-user"></i>
                                        </span>
                                    @endif
                                    <div class="home-reviews__author-meta">
                                        <cite class="home-reviews__name">{{ $testimonial->name }}</cite>
                                        @if (filled($testimonial->title))
                                            <span class="home-reviews__role">{{ $testimonial->title }}</span>
                                        @endif
                                    </div>
                                </footer>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="home-reviews__bar">
                    <div class="home-reviews__bar-text">
                        <p class="home-reviews__bar-title">Siz de memnun müşterilerimiz arasına katılın</p>
                        <p class="home-reviews__bar-note">Projelerimiz Google'da yüksek memnuniyetle değerlendiriliyor</p>
                    </div>
                    @if ($homeGoogleReviewUrl)
                        <a href="{{ $homeGoogleReviewUrl }}" class="home-reviews__cta" target="_blank" rel="noopener noreferrer">
                            Google'da Yorumları Gör
                        </a>
                    @else
                        <a href="{{ route('iletisim') }}" class="default-btn">Ücretsiz Teklif Alın</a>
                    @endif
                </div>
            </div>
        </section>
    @endif
    <!--===== GOOGLE YORUMLARI END =====-->

    <!--===== BLOG AREA START =====-->
    @php
        $homeBlogs = app(\App\Services\Blog\BlogService::class)->active(3);
    @endphp
    @if ($homeBlogs->isNotEmpty())
        <div class="blog8 sp bg-cover bg-cover" style="background-image: url(assets/img/bg/sec-bg10.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <div class="heading10">
                            <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                    src="assets/img/icons/span10.svg" alt=""> Blog</span>
                            <h2 class="text-anime-style-3">Güncel Yazılar ve İçgörüler</h2>
                            <p class="mt-16">Web tasarım, SEO ve dijital pazarlama üzerine pratik rehberler paylaşıyoruz. Sitenizi arama sonuçlarında güçlendirecek güncel öneriler burada.</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($homeBlogs as $homeBlog)
                        @php
                            $homeBlogCover = $homeBlog->getFirstMedia('cover');
                            $homeBlogDate = $homeBlog->published_at ?? $homeBlog->created_at;
                        @endphp
                        <div class="col-lg-4 col-md-6" data-aos="fade-up"
                            data-aos-duration="{{ 800 + $loop->index * 200 }}">
                            <div class="vl-blog-10-item mt-30">
                                <div class="vl-blog-10-thumb">
                                    @if ($homeBlogCover)
                                        <div class="image-anime image">
                                            <a href="{{ $homeBlog->publicUrl() }}">
                                                <img class="w-full" src="{{ $homeBlogCover->url() }}"
                                                    alt="{{ $homeBlog->title }}">
                                            </a>
                                        </div>
                                    @endif
                                    @if ($homeBlogDate)
                                        <div class="vl-blog10-meta">
                                            <a href="{{ $homeBlog->publicUrl() }}" class="date"><img
                                                    src="assets/img/icons/date10.svg" alt="">
                                                {{ $homeBlogDate->translatedFormat('d M') }}</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="vl-blog-10-content heading10">
                                    @if ($homeBlog->author)
                                        <div class="vl-blog10-meta">
                                            <a href="{{ $homeBlog->publicUrl() }}" class="user"><img
                                                    src="assets/img/icons/user10.svg" alt="">
                                                {{ $homeBlog->author->name }}</a>
                                        </div>
                                    @endif
                                    <h5 class="mt-16 mb-16"><a
                                            href="{{ $homeBlog->publicUrl() }}">{{ $homeBlog->title }}</a></h5>
                                    <a href="{{ $homeBlog->publicUrl() }}" class="learn10">Devamını Oku <span
                                            class="arrow1"><i class="fa-regular fa-arrow-right"></i></span><span
                                            class="arrow2"><i class="fa-regular fa-arrow-right"></i></span></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <!--===== BLOG AREA END =====-->
@endsection
