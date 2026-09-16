@extends('layout.app')
@section('title', 'Hakkımızda')


@section('content')

    <!--===== BREACRUMBS START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>Hakkımızda</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="index.html">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>Hakkımızda</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== BREACRUMBS END =====-->

    <!--===== ABOUT AREA START =====-->

    <div class="about2 sp ">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about2-images">
                        <img src="{{ asset('assets/img/about.jpg') }}" alt="">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="heading2 ml-30 md:ml-0 sm:ml-0 md:mt-30 sm:mt-30">
                        <span class="sub-title ">
                            <img style="width: 20px; height: 20px; margin-right: 5px;"
                                src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                            Neden Biz?
                        </span>
                        <h2>{{ $aboutTitle}}</h2>
                        @if (filled($aboutContent))
                            <div class="mt-16 body-font fs-16">{!! $aboutContent !!}</div>
                        @endif
                        <div class="button mt-30" data-aos="fade-left" data-aos-duration="1000">
                            <a class="default-btn" href="{{ route('iletisim') }}">İletişime Geç <i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== ABOUT AREA END =====-->



    <!--===== TESTIMONIAL AREA START =====-->

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
                    <div class="home-reviews__score" data-aos="fade-left" data-aos-duration="900">
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
    <!--===== TESTIMONIAL AREA END =====-->

@endsection
