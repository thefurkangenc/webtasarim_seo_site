@extends('layout.app')
@section('title', 'Hizmetler')
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

    <div class="service14-section sp">
        <div class="container">
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
                            // Listede bölge bağlamı yok — yer tutucular kaldırılmış
                            // genel (şemsiye) haliyle gösterilir.
                            $generic = $service->renderGeneric();
                            $iconIndex = ($loop->iteration - 1) % 6 + 1;
                        @endphp
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="900">
                            <div class="service14-boxarea">
                                <div class="icons">
                                    <img src="{{ asset("assets/img/icons/service14-icon{$iconIndex}.svg") }}" alt="">
                                </div>
                                <div class="space28"></div>
                                <div class="content14-area">
                                    <a href="{{ route('hizmetler.show', $service->slug) }}" class="title">{{ $generic['title'] }}</a>
                                    <div class="space16"></div>
                                    <p>{{ $generic['excerpt'] ?: \Illuminate\Support\Str::limit(strip_tags((string) $generic['content']), 120) }}</p>
                                    <div class="space24"></div>
                                    <div class="btn-area1">
                                        <a href="{{ route('hizmetler.show', $service->slug) }}" class="theme-btn27">Detaylı Bilgi</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!--=== SERVICE AREA END === -->

    <!--===== COUNTER AREA START =====-->

    <div class="inner-page-counter-sec bg-cover" style="background-image: url(assets/img/bg/about-page-count-bg.jpg);">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-heading text-center">
                        <span class="sub-title"><img src="assets/img/icons/span1.svg" alt=""> SEOX INTERESTING
                            FACTS</span>
                    </div>
                </div>
            </div>
            <div class="row mt-10">
                <div class="col-lg col-md-4">
                    <div class="inner-counter-box mt-30">
                        <h3>500+</h3>
                        <p>Agency Employees</p>
                    </div>
                </div>

                <div class="col-lg col-md-4">
                    <div class="inner-counter-box mt-30">
                        <h3>900+</h3>
                        <p>Project Complete</p>
                    </div>
                </div>

                <div class="col-lg col-md-4">
                    <div class="inner-counter-box mt-30">
                        <h3>$200M</h3>
                        <p>Revenue Generated</p>
                    </div>
                </div>

                <div class="col-lg col-md-4">
                    <div class="inner-counter-box mt-30">
                        <h3>110K</h3>
                        <p>Satisfied Client</p>
                    </div>
                </div>

                <div class="col-lg col-md-4">
                    <div class="inner-counter-box mt-30">
                        <h3>109+</h3>
                        <p>Countries Include</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== COUNTER AREA END =====-->


    <!--===== CONTACT AREA START =====-->

    <div class="contact2 sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="heading2">
                        <div class="contact2-form">
                            <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                    src="assets/img/icons/span3.svg" alt="">CONTACT US </span>
                            <h2 class="text-anime-style-3">Lets Work Together</h2>
                            <p class="mt-16" data-aos="fade-right" data-aos-duration="900">eady to take your social
                                media presence to the next level? Let’s work together to create impactful strategies
                                drive engagement, growth, and success for your brand.</p>
                            <form action="#" data-aos="fade-right" data-aos-duration="1000">
                                <div class="row mt-16">
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="email" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="number" placeholder="Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <select class="wide">
                                                <option value="1">Service Type</option>
                                                <option value="2">Option 1</option>
                                                <option value="3">Option 2</option>
                                                <option value="4">Option 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <textarea rows="5" placeholder="How can we help you?"></textarea>
                                        </div>
                                        <div class="button mt-30">
                                            <button class="theme-btn3" type="submit">Send <span class="arrow1"><i
                                                        class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                        class="fa-solid fa-arrow-right"></i></span></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact2-image image-anime reveal ml-40 md:ml-0 sm:ml-0 md:mt-30 sm:mt-30">
                        <img class="w-full" src="assets/img/others/contact2-image.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== CONTACT AREA END =====-->
@endsection
