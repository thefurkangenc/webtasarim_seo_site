@extends('layout.app')
@section('title', 'Hizmetler')
@section('meta_description', 'Gaziantep Web Tasarım Ajansı hizmetleri, Gaziantep web tasarım hizmet fiyatları')
@section('meta_keywords', 'gaziantep web tasarım ajansı, gaziantep web tasarım hizmetleri, web tasarım hizmet fiyatları')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/services/index.css') }}">
@endpush

@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>Hizmetler</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>Hizmetler</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--=== SERVICE AREA START === -->

    <div class="service6 sp sec-bg5">
        <div class="container">
           <div class="col-lg-6 m-auto text-center">
            <div class="home-refs__head text-center">

                <span class="sub-title">
                    <img style="width: 20px; height: 20px; margin-right: 5px;"
                        src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                    Hizmetler
                </span>

                <h2 id="home-refs-title" class="text-anime-style-3">Hizmetlerimiz</h2>
                <p class="home-refs__lead">
                    Web tasarım, SEO ve dijital pazarlama ile markanızın dijital dönüşümünü hızlandırıyoruz. İhtiyacınıza uygun çözümlerle daha fazla görünürlük, trafik ve müşteri hedefliyoruz.
                </p>
            </div>
           </div>
            @if ($services->isEmpty())
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p>Henüz yayınlanmış bir hizmet bulunmuyor.</p>
                    </div>
                </div>
            @else
                <div class="row mt-30">
                    @foreach ($services as $service)
                        @php
                            $generic = $service->renderGeneric();
                            $cover = $service->getFirstMedia('cover');
                        @endphp
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="900">
                            <div class="service6-box mt-30">
                                @if ($cover)
                                    <div class="thumb">
                                        <a href="{{ route('hizmetler.show', $service->slug) }}">
                                            <img src="{{ $cover->url() }}" alt="{{ $generic['title'] }}">
                                        </a>
                                    </div>
                                @endif
                                <div class="content heading6">
                                    <h4><a href="{{ route('hizmetler.show', $service->slug) }}">{{ $generic['title'] }}</a></h4>
                                    <p class="mt-16">{{ Str::limit(strip_tags((string) $generic['excerpt']), 160) }}</p>
                                    <a href="{{ route('hizmetler.show', $service->slug) }}" class="learn">Detaylı Bilgi <span class="arrow1"><i
                                                class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                class="fa-solid fa-arrow-right"></i></span></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!--=== SERVICE AREA END === -->

@endsection
