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
    <x-googlecomment />
    <!--===== TESTIMONIAL AREA END =====-->

@endsection
