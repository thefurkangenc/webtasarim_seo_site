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
    <x-googlecomment />
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
