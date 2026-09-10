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
    @endphp

    <div class="hero6" style="background-image: url(assets/img/hero/hero6-bg.jpg);">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="main-heading6">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                src="assets/img/icons/span3.svg"
                                alt="">{{ $hero->badge ?: 'Her adımda şeffaf raporlama.' }}</span>
                        <h1>{{ $hero->title ?: 'Akıllı SEO ile Daha Fazla Trafik, Müşteri ve Satış' }}</h1>
                        <p class="mt-16">
                            {{ $hero->description ?: 'Uzman ekibimizle sitenizi arama sonuçlarında yükseltiyor, düzenli organik trafik kazandırıyor ve ziyaretçileri müşteriye dönüştürüyoruz.' }}
                        </p>
                        <div class="buttons">
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
                        </div>

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
                                            <div class="d-flex align-items-center"
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


    <!--===== ABOUT AREA START =====-->
    @php
        $homeAboutTitle = \App\Support\Settings::get('contents.about_title');
        $homeAboutContent = \App\Support\Settings::get('contents.about_content');
    @endphp

    <div class="about1 sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="heading1">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                src="assets/img/icons/span1.svg" alt=""> ABOUT US</span>
                        <h2 class="text-anime-style-3">{{ $homeAboutTitle ?: 'Dijitalde İz Bırakan Çözümler Üretiyoruz' }}
                        </h2>
                        @if (filled($homeAboutContent))
                            <div class="mt-16" data-aos="fade-right" data-aos-duration="900">{!! $homeAboutContent !!}</div>
                        @else
                            <p class="mt-16" data-aos="fade-right" data-aos-duration="900">Web tasarımdan dijital
                                pazarlamaya,
                                işletmenizin ihtiyaç duyduğu her alanda uzman ekibimizle yanınızdayız. Modern, hızlı
                                ve etkili çözümlerle markanızı bir adım öne taşıyoruz.</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-8 text-end sm:text-start md:text-start md:mt-30 sm:mt-30">
                    <div class="button" data-aos="fade-left" data-aos-duration="1000">
                        <a href="about.html" class="theme-btn1">Work With Us</a>
                    </div>
                </div>
            </div>
            <div class="row mt-30 align-items-end about1-boxs-all">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="500">
                    <div class="about1-box box1 white-heading mt-30">
                        <div class="top-heading">
                            <h5>Clients Served Worldwide</h5>
                            <p class="mt-16">Partnering with businesses across the globe to achieve outstanding results.
                            </p>
                        </div>
                        <div class="bottom-heading">
                            <h3><span class="counter">500</span> +</h3>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    <div class="about1-box box2 white-heading mt-30">
                        <div class="top-heading">
                            <h5>Projects Successfully Completed</h5>
                            <p class="mt-16">Delivering customized campaigns that drive traffic and boost conversions.</p>
                        </div>
                        <div class="bottom-heading">
                            <h3><span class="counter">700</span> +</h3>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    <div class="about1-box box3 white-heading mt-30">
                        <div class="top-heading">
                            <h5>Revenue Generated for Clients</h5>
                            <p class="mt-16">Partnering with businesses across the globe to achieve outstanding results.
                            </p>
                        </div>
                        <div class="bottom-heading">
                            <h3>$<span class="counter">200</span>M+</h3>
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
    <div class="choose14-section sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="choose14-imges">
                        <div class="img1" data-aos="zoom-out" data-aos-duration="1000">
                            <img src="assets/img/others/choose14-img1.png" alt="">
                        </div>
                        <div class="img2" data-aos="zoom-out" data-aos-duration="1100">
                            <img src="assets/img/others/choose14-img2.png" alt="">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="choose14-hading-area heading14">
                        <h5 data-aos="fade-up" data-aos-duration="900"><svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewbox="0 0 20 20" fill="none">
                                <path
                                    d="M9.99935 1.66699C10.3121 1.66699 10.6205 1.68394 10.9243 1.71783C11.0331 1.72986 11.1385 1.76321 11.2344 1.81596C11.3303 1.86871 11.4148 1.93983 11.4832 2.02526C11.5516 2.11069 11.6025 2.20875 11.633 2.31386C11.6635 2.41896 11.6731 2.52905 11.661 2.63783C11.649 2.7466 11.6156 2.85195 11.5629 2.94784C11.5101 3.04373 11.439 3.12829 11.3536 3.1967C11.2682 3.2651 11.1701 3.31601 11.065 3.34652C10.9599 3.37702 10.8498 3.38653 10.741 3.37449C9.36772 3.22086 7.98062 3.49778 6.77158 4.16693C5.56254 4.83608 4.59131 5.8644 3.99224 7.10966C3.39316 8.35491 3.19585 9.75555 3.42759 11.1178C3.65933 12.4801 4.30867 13.7368 5.2858 14.7139C6.26292 15.691 7.51954 16.3403 8.88183 16.5721C10.2441 16.8038 11.6448 16.6065 12.89 16.0074C14.1353 15.4084 15.1636 14.4371 15.8327 13.2281C16.5019 12.0191 16.7788 10.632 16.6252 9.25866C16.6131 9.14988 16.6227 9.0398 16.6532 8.93469C16.6837 8.82959 16.7346 8.73152 16.803 8.64609C16.9411 8.47356 17.1422 8.36297 17.3618 8.33866C17.5815 8.31435 17.8019 8.3783 17.9744 8.51645C18.0598 8.58486 18.131 8.66942 18.1837 8.76531C18.2365 8.86121 18.2698 8.96655 18.2818 9.07533C18.3152 9.37922 18.3321 9.68755 18.3327 10.0003C18.3327 14.6028 14.6018 18.3337 9.99935 18.3337C5.39685 18.3337 1.66602 14.6028 1.66602 10.0003C1.66602 5.39783 5.39685 1.66699 9.99935 1.66699ZM9.97268 6.56449C10.0276 6.77843 9.99534 7.00542 9.883 7.19558C9.77065 7.38575 9.5874 7.52353 9.37352 7.57866C8.78591 7.73259 8.27438 8.09505 7.93438 8.59842C7.59438 9.10178 7.44914 9.71166 7.52577 10.3142C7.60239 10.9168 7.89564 11.4709 8.3508 11.8732C8.80595 12.2754 9.39192 12.4984 9.99935 12.5003C10.5538 12.5006 11.0925 12.3165 11.5309 11.9771C11.9693 11.6377 12.2824 11.1621 12.421 10.6253C12.4801 10.4156 12.6189 10.2374 12.8078 10.1288C12.9967 10.0202 13.2206 9.98992 13.4315 10.0444C13.6425 10.0989 13.8237 10.2338 13.9364 10.4203C14.049 10.6067 14.0842 10.8299 14.0343 11.042C13.7787 12.0219 13.175 12.8753 12.3361 13.4426C11.4972 14.01 10.4805 14.2525 9.47582 14.1249C8.47115 13.9973 7.5473 13.5083 6.87685 12.7493C6.20641 11.9902 5.83524 11.0131 5.83268 10.0003C5.83254 9.07648 6.13943 8.17879 6.70511 7.44839C7.27079 6.71798 8.06318 6.19629 8.95768 5.96533C9.06367 5.93801 9.174 5.93184 9.28238 5.94717C9.39075 5.96249 9.49504 5.99902 9.5893 6.05466C9.68355 6.1103 9.76592 6.18396 9.8317 6.27144C9.89748 6.35892 9.94539 6.4585 9.97268 6.56449ZM15.4193 1.77283C15.5715 1.83589 15.7015 1.94261 15.793 2.0795C15.8845 2.21639 15.9334 2.37733 15.9335 2.54199V4.06699H17.4577C17.6225 4.06703 17.7836 4.11592 17.9206 4.20749C18.0576 4.29906 18.1644 4.4292 18.2274 4.58145C18.2905 4.7337 18.307 4.90122 18.2748 5.06285C18.2427 5.22448 18.1634 5.37295 18.0469 5.48949L15.0993 8.43366C14.9431 8.58995 14.7312 8.67778 14.5102 8.67783H12.4993L10.976 10.202C10.8196 10.3584 10.6076 10.4462 10.3864 10.4462C10.1653 10.4462 9.95322 10.3584 9.79685 10.202C9.64048 10.0456 9.55264 9.83355 9.55264 9.61241C9.55264 9.39127 9.64048 9.17919 9.79685 9.02283L11.321 7.50033V5.48866C11.3211 5.26766 11.4089 5.05574 11.5652 4.89949L14.511 1.95283C14.6276 1.83621 14.7761 1.75679 14.9377 1.7246C15.0994 1.69241 15.267 1.7089 15.4193 1.77199M14.2668 4.55283L12.9877 5.83366V7.01199H14.166L15.446 5.73283H15.1002C14.8792 5.73283 14.6672 5.64503 14.5109 5.48875C14.3546 5.33247 14.2668 5.12051 14.2668 4.89949V4.55283Z"
                                    fill="#050422"></path>
                            </svg> Why Choose Us</h5>
                        <div class="space16"></div>
                        <h2 class="text-anime-style-3">
                            {{ $homeWhyHeading['title'] ?? '' ?: 'Vizyonunuzla Birlikte Büyüyen Akıllı Çözümler' }}</h2>
                        <div class="space16"></div>
                        <p data-aos="fade-up" data-aos-duration="1000">
                            {{ $homeWhyHeading['description'] ?? '' ?: 'Sadece teknik değil, iş ortağınızız. İhtiyaçlarınızı anlamaya zaman ayırır, hızlı yanıt verir ve işinize özel çözümler üretiriz.' }}
                        </p>
                        <div class="space32"></div>
                        <div class="choose-flex-area" data-aos="fade-up" data-aos-duration="1100">
                            <img src="assets/img/others/abotu14-author1.png" alt="">
                            <div class="text14">
                                <h4>2K+ Review</h4>
                                <div class="space10"></div>
                                <ul>
                                    <li><i class="fa-solid fa-star"></i></li>
                                    <li><i class="fa-solid fa-star"></i></li>
                                    <li><i class="fa-solid fa-star"></i></li>
                                    <li><i class="fa-solid fa-star"></i></li>
                                    <li><i class="fa-solid fa-star"></i></li>
                                    <li>(5.0 Rating)</li>
                                </ul>
                            </div>
                        </div>
                        @if ($homeWhyChooseUs->isNotEmpty())
                            <div class="row">
                                @foreach ($homeWhyChooseUs as $reason)
                                    <div class="col-lg-6 col-md-6" data-aos="fade-up"
                                        data-aos-duration="{{ 900 + $loop->index * 100 }}">
                                        <div class="choose14-boxarea">
                                            <a href="#">{{ $reason->title }}</a>
                                            <div class="space16"></div>
                                            <p>{{ $reason->description }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="space32"></div>
                        <div class="btn-area1" data-aos="fade-up" data-aos-duration="1000">
                            <a href="#" class="theme-btn27">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== CHOOSE AREA END =====-->


    <!--===== TESTIMONIAL AREA START =====-->
    @php
        $homeTestimonials = app(\App\Services\Testimonial\TestimonialService::class)->active();
    @endphp
    @if ($homeTestimonials->isNotEmpty())
        <div class="testimonial14-section sp">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="heading15">
                            <h5 data-aos="fade-left" data-aos-duration="900"><svg xmlns="http://www.w3.org/2000/svg"
                                    width="17" height="18" viewbox="0 0 17 18" fill="none">
                                    <path
                                        d="M8.49935 0.667969C8.81213 0.667969 9.12046 0.684913 9.42435 0.718802C9.53313 0.73084 9.63847 0.764185 9.73436 0.816934C9.83025 0.869684 9.91482 0.940803 9.98322 1.02623C10.0516 1.11166 10.1025 1.20973 10.133 1.31483C10.1635 1.41994 10.1731 1.53002 10.161 1.6388C10.149 1.74758 10.1156 1.85292 10.0629 1.94881C10.0101 2.04471 9.93901 2.12927 9.85358 2.19767C9.76815 2.26608 9.67009 2.31699 9.56498 2.34749C9.45988 2.378 9.34979 2.38751 9.24102 2.37547C7.86772 2.22184 6.48062 2.49875 5.27158 3.1679C4.06254 3.83706 3.09131 4.86538 2.49224 6.11063C1.89316 7.35589 1.69585 8.75653 1.92759 10.1188C2.15933 11.4811 2.80867 12.7377 3.7858 13.7149C4.76292 14.692 6.01954 15.3413 7.38183 15.5731C8.74412 15.8048 10.1448 15.6075 11.39 15.0084C12.6353 14.4093 13.6636 13.4381 14.3327 12.2291C15.0019 11.02 15.2788 9.63293 15.1252 8.25964C15.1131 8.15086 15.1227 8.04077 15.1532 7.93567C15.1837 7.83056 15.2346 7.7325 15.303 7.64707C15.4411 7.47453 15.6422 7.36395 15.8618 7.33964C16.0815 7.31532 16.3019 7.37928 16.4744 7.51743C16.5598 7.58584 16.631 7.6704 16.6837 7.76629C16.7365 7.86218 16.7698 7.96752 16.7818 8.0763C16.8152 8.38019 16.8321 8.68852 16.8327 9.0013C16.8327 13.6038 13.1018 17.3346 8.49935 17.3346C3.89685 17.3346 0.166016 13.6038 0.166016 9.0013C0.166016 4.3988 3.89685 0.667969 8.49935 0.667969ZM8.47268 5.56547C8.5276 5.77941 8.49534 6.00639 8.383 6.19656C8.27065 6.38672 8.0874 6.52451 7.87352 6.57964C7.28591 6.73356 6.77438 7.09603 6.43438 7.59939C6.09438 8.10276 5.94914 8.71264 6.02577 9.31522C6.10239 9.9178 6.39564 10.4719 6.8508 10.8742C7.30595 11.2764 7.89192 11.4993 8.49935 11.5013C9.05377 11.5015 9.59254 11.3175 10.0309 10.978C10.4693 10.6386 10.7824 10.1631 10.921 9.6263C10.9801 9.4166 11.1189 9.23836 11.3078 9.12978C11.4967 9.0212 11.7206 8.9909 11.9315 9.04538C12.1425 9.09986 12.3237 9.23478 12.4364 9.42125C12.549 9.60771 12.5842 9.83089 12.5343 10.043C12.2787 11.0229 11.675 11.8763 10.8361 12.4436C9.99724 13.011 8.98048 13.2535 7.97582 13.1259C6.97115 12.9983 6.0473 12.5093 5.37685 11.7502C4.70641 10.9912 4.33524 10.014 4.33268 9.0013C4.33254 8.07746 4.63943 7.17977 5.20511 6.44936C5.77079 5.71896 6.56318 5.19726 7.45768 4.9663C7.56367 4.93898 7.674 4.93281 7.78238 4.94814C7.89075 4.96347 7.99504 5 8.0893 5.05564C8.18355 5.11128 8.26592 5.18494 8.3317 5.27242C8.39748 5.35989 8.44539 5.45947 8.47268 5.56547ZM13.9193 0.773802C14.0715 0.836868 14.2015 0.943583 14.293 1.08048C14.3845 1.21737 14.4334 1.37831 14.4335 1.54297V3.06797H15.9577C16.1225 3.068 16.2836 3.1169 16.4206 3.20847C16.5576 3.30004 16.6644 3.43017 16.7274 3.58242C16.7905 3.73467 16.807 3.9022 16.7748 4.06383C16.7427 4.22546 16.6634 4.37393 16.5469 4.49047L13.5993 7.43464C13.4431 7.59093 13.2312 7.67876 13.0102 7.6788H10.9993L9.47602 9.20297C9.31965 9.35934 9.10757 9.44718 8.88643 9.44718C8.6653 9.44718 8.45322 9.35934 8.29685 9.20297C8.14048 9.0466 8.05264 8.83452 8.05264 8.61339C8.05264 8.39225 8.14048 8.18017 8.29685 8.0238L9.82102 6.5013V4.48964C9.82106 4.26864 9.90889 4.05671 10.0652 3.90047L13.011 0.953802C13.1276 0.837191 13.2761 0.757767 13.4377 0.725578C13.5994 0.693389 13.767 0.709881 13.9193 0.772969M12.7668 3.5538L11.4877 4.83464V6.01297H12.666L13.946 4.7338H13.6002C13.3792 4.7338 13.1672 4.646 13.0109 4.48972C12.8546 4.33344 12.7668 4.12148 12.7668 3.90047V3.5538Z"
                                        fill="#120734"></path>
                                </svg> Testimonials </h5>
                            <div class="space12"></div>
                            <h2 class="text-anime-style-3">What Our Clients Saying About SEOX Agency</h2>
                        </div>
                    </div>

                    <div class="col-lg-5"></div>
                    <div class="col-lg-2">
                        <div class="tes14-arrows">
                            <div class="prev14-arrow">
                                <button><i class="fa-solid fa-angle-left"></i></button>
                            </div>

                            <div class="next14-arrow">
                                <button><i class="fa-solid fa-angle-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-60">
                    <div class="col-lg-12" data-aos="zoom-out" data-aos-duration="1000">
                        <div class="testimonial14-slider-area">
                            @foreach ($homeTestimonials as $testimonial)
                                <div class="testimonial14-boxarea">
                                    <div class="testi14-auhtor-area">
                                        <div class="img1">
                                            <img src="{{ asset('assets/img/icons/test14-quito1.svg') }}" alt=""
                                                class="test14-quito1">
                                            <img src="{{ $testimonial->getFirstMedia('photo')?->url('thumb') ?? asset('assets/img/testimonial/team2-image1.png') }}"
                                                alt="{{ $testimonial->name }}" class="team2-image1">
                                        </div>
                                        <div class="tes14-textarea">
                                            <a href="#">{{ $testimonial->name }}</a>
                                            @if (filled($testimonial->title))
                                                <div class="space12"></div>
                                                <p>{{ $testimonial->title }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="space24"></div>
                                    <p class="pera">“{{ $testimonial->content }}”</p>
                                    <div class="space24"></div>
                                    <div class="starts-area">
                                        <ul class="test14-stars">
                                            @for ($i = 0; $i < $testimonial->rating; $i++)
                                                <li><i class="fa-solid fa-star"></i></li>
                                            @endfor
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--===== TESTIMONIAL AREA END =====-->

    <!--===== BLOG AREA START =====-->
    @php
        $homeBlogs = app(\App\Services\Blog\BlogService::class)->active(3);
    @endphp
    @if ($homeBlogs->isNotEmpty())
        <div class="blog8 sp bg-cover bg-cover" style="background-image: url(assets/img/bg/sec-bg10.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 m-auto text-center">
                        <div class="heading10">
                            <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                    src="assets/img/icons/span10.svg" alt=""> Blog</span>
                            <h2 class="text-anime-style-3">Güncel Yazılar ve İçgörüler</h2>
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
                                            <a href="{{ route('blog.show', $homeBlog->id) }}">
                                                <img class="w-full" src="{{ $homeBlogCover->url() }}"
                                                    alt="{{ $homeBlog->title }}">
                                            </a>
                                        </div>
                                    @endif
                                    @if ($homeBlogDate)
                                        <div class="vl-blog10-meta">
                                            <a href="{{ route('blog.show', $homeBlog->id) }}" class="date"><img
                                                    src="assets/img/icons/date10.svg" alt="">
                                                {{ $homeBlogDate->translatedFormat('d M') }}</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="vl-blog-10-content heading10">
                                    @if ($homeBlog->author)
                                        <div class="vl-blog10-meta">
                                            <a href="{{ route('blog.show', $homeBlog->id) }}" class="user"><img
                                                    src="assets/img/icons/user10.svg" alt="">
                                                {{ $homeBlog->author->name }}</a>
                                        </div>
                                    @endif
                                    <h5 class="mt-16 mb-16"><a
                                            href="{{ route('blog.show', $homeBlog->id) }}">{{ $homeBlog->title }}</a></h5>
                                    <a href="{{ route('blog.show', $homeBlog->id) }}" class="learn10">Devamını Oku <span
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
