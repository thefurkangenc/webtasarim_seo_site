@extends('layout.app')

@section('meta_description', 'Gaziantep Web Tasarım Ajansı, Gaziantep web tasarım hizmetleri, Gaziantep web tasarım fiyatları, Gaziantep web site fiyatları')
@section('meta_keywords', 'gaziantep web tasarım ajansı, web tasarım fiyatları, web tasarım hizmetleri, web site fiyatları')
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/home/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/references/index.css') }}">
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
                                alt="Gaziantep Web Tasarım Ajansı"> {{ $hero->badge }}</span>
                        <h1>{{ $hero->title}}</h1>
                        <p class="mt-16">
                            {{ $hero->description }}
                        </p>
                        {{-- <div class="buttons">
                            @if (filled($hero->button_text))
                                <a href="{{ $hero->button_url ?: route('iletisim') }}" class="ui-btn ui-btn--solid ui-btn--light ui-btn--lg">
                                    {{ $hero->button_text }} <i class="fa-solid fa-arrow-right"></i>
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
                                alt="Gaziantep Web Tasarım Ajansı Hakkımızda"> Hakkımızda </span>
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
                        <a href="{{ route('hakkimizda') }}" class="ui-btn ui-btn--solid">
                            <i class="fa-solid fa-phone-volume"></i> Detayları Konuşalım!
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
    @include('pages.services.partials.section')
    <!--===== SERVICE AREA END =====-->

    <!--===== CHOOSE AREA START =====-->
    <!--===== NEDEN BIZ =====-->
    @include('pages.why-choose-us.partials.section')
    <!--===== NEDEN BIZ END =====-->
    @php $homeReferences = app(\App\Services\Reference\ReferenceService::class)->active(); @endphp
    @if ($homeReferences->isNotEmpty())
        @include('pages.references.partials.section', ['references' => $homeReferences])
    @endif
    <!--===== GOOGLE YORUMLARI =====-->
    <x-googlecomment />
    <!--===== GOOGLE YORUMLARI END =====-->

    <!--===== BLOG AREA START =====-->
    @php
        $homeBlogs = app(\App\Services\Blog\BlogService::class)->active(6);
    @endphp
    @if ($homeBlogs->isNotEmpty())
        <div class="blog8 sp bg-cover bg-cover" style="background-image: url(assets/img/bg/sec-bg10.jpg);">
            <div class="container">
                <div class="home-refs__head text-center">

                    <span class="sub-title">
                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                            src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Blog">
                        Blog
                    </span>

                    <h2 id="home-refs-title" class="text-anime-style-3">Blog Yazılarımız</h2>
                    <p class="home-refs__lead">
                        Gaziantep Web Tasarım Ajansı olarak: web siteler, dijital pazarlama ve SEO hizmetleri ile ilgili en güncel bilgileri ve en iyi çözümleri sunuyoruz.
                    </p>
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
                                                    src="assets/img/icons/date10.svg" alt="Gaziantep Web Tasarım Ajansı Blog Tarihi">
                                                {{ $homeBlogDate->translatedFormat('d M') }}</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="vl-blog-10-content heading10">
                                    @if ($homeBlog->author)
                                        <div class="vl-blog10-meta">
                                            <a href="{{ $homeBlog->publicUrl() }}" class="user"><img
                                                    src="assets/img/icons/user10.svg" alt="Gaziantep Web Tasarım Ajansı Blog Yazarı">
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
