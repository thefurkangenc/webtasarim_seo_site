@extends('layout.app')
@section('title', 'İletişim')
@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>İletişim</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>İletişim</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--===== CONTACT AREA START =====-->

    <div class="contact2 sp">
        <div class="container">
            <div class="row">
                @if (filled($email))
                    <div class="col-lg-4 col-md-6">
                        <div class="contact-page-box">
                            <div class="icon">
                                <img src="{{ asset('assets/img/icons/contact-page-icon1.svg') }}" alt="">
                            </div>
                            <div class="content">
                                <h3>E-posta</h3>
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            </div>
                        </div>
                    </div>
                @endif
                @if (filled($phone))
                    <div class="col-lg-4 col-md-6">
                        <div class="contact-page-box">
                            <div class="icon">
                                <img src="{{ asset('assets/img/icons/contact-page-icon2.svg') }}" alt="">
                            </div>
                            <div class="content">
                                <h3>Telefon</h3>
                                <a href="{{ $tel_href }}">{{ $phone }}</a>
                            </div>
                        </div>
                    </div>
                @endif
                @if (filled($address))
                    <div class="col-lg-4 col-md-6">
                        <div class="contact-page-box">
                            <div class="icon">
                                <img src="{{ asset('assets/img/icons/contact-page-icon3.svg') }}" alt="">
                            </div>
                            <div class="content">
                                <h3>Adres</h3>
                                <p>{{ $address }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-lg-6 mt-60">
                    <div class="heading2">
                        <div class="contact2-form">
                            <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                    src="{{ asset('assets/img/icons/span3.svg') }}" alt="">İLETİŞİM</span>
                            <h2 class="text-anime-style-3">{{ $heading }}</h2>
                            @if (filled($intro))
                                <p class="mt-16" data-aos="fade-right" data-aos-duration="900">{{ $intro }}</p>
                            @endif

                            @if ($enabled)
                                <form action="{{ route('iletisim.store') }}" method="POST" data-contact-form
                                    data-aos="fade-right" data-aos-duration="1000">
                                    @csrf
                                    <div class="contact-form-alert" data-contact-alert hidden></div>
                                    <div class="hp-field" aria-hidden="true">
                                        <label for="website">Website</label>
                                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                    </div>
                                    <div class="row mt-16">
                                        <div class="col-md-12">
                                            <div class="single-input">
                                                <input type="text" name="name" placeholder="Adınız soyadınız" required maxlength="150" autocomplete="name">
                                                <span class="contact-field-error" data-error="name" hidden></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-input">
                                                <input type="email" name="email" placeholder="E-posta adresiniz" required maxlength="150" autocomplete="email">
                                                <span class="contact-field-error" data-error="email" hidden></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-input">
                                                <input type="tel" name="phone" placeholder="Telefon (isteğe bağlı)" maxlength="50" autocomplete="tel">
                                                <span class="contact-field-error" data-error="phone" hidden></span>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="single-input">
                                                <textarea name="message" rows="5" placeholder="Mesajınız" required maxlength="5000"></textarea>
                                                <span class="contact-field-error" data-error="message" hidden></span>
                                            </div>
                                        </div>
                                        @if ($privacy_required)
                                            <div class="col-md-12">
                                                <label class="contact-privacy">
                                                    <input type="checkbox" name="privacy" value="1" required>
                                                    <span>{!! $privacy_html !!}</span>
                                                </label>
                                                <span class="contact-field-error" data-error="privacy" hidden></span>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="button mt-30">
                                                <button class="theme-btn3" type="submit" data-contact-submit>Gönder
                                                    <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                                                    <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <p class="mt-16">İletişim formu şu anda kapalıdır. Bize e-posta veya telefonla ulaşabilirsiniz.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact2-image mt-60 image-anime reveal ml-40 md:ml-0 sm:ml-0 md:mt-30 sm:mt-30">
                        <img class="w-full" src="assets/img/others/contact2-image.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== CONTACT AREA END =====-->

    <!--===== CHOOSE AREA START =====-->

    <div class="choose2 sp sec-bg2">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="choose2-images mr-40 sm:mr-0 md:mr-0">
                        <div class="image1 image-anime reveal">
                            <img src="assets/img/others/choose2-image1.png" alt="">
                        </div>
                        <div class="image2 image-anime reveal">
                            <img src="assets/img/others/choose2-image2.png" alt="">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 sm:mt-30 md:mt-30">
                    <div class="heading2">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                src="assets/img/icons/span3.svg" alt="">WHY CHOOSE US </span>
                        <h2 class="text-anime-style-3">Your Success, Our Priority</h2>
                        <p class="mt-16" data-aos="fade-right" data-aos-duration="800">Proven track record of boosting
                            engagement and sales. Expert team fluent in the latest trends and technologies. Dedicated
                            account managers ensuring personalized service.</p>

                        <div class="choose2-apps">
                            <div class="row">
                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="200">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app1.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>12,570+</h4>
                                            <p>Account Boosted</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="300">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app2.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>350+</h4>
                                            <p>Account Managed</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="350">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app3.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>5,482+</h4>
                                            <p>Account Optimized</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="400">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app4.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>5,558+</h4>
                                            <p>Account Grow</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="450">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app5.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>4,568+</h4>
                                            <p>User Hired</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6" data-aos="fade-up" data-aos-duration="900" data-aos-delay="500">
                                    <div class="choose2-single-apps">
                                        <div class="apps-image">
                                            <img src="assets/img/apps/choose-app6.png" alt="">
                                        </div>
                                        <div class="apps-info">
                                            <h4>9,587+</h4>
                                            <p>Account Promoted</p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== CHOOSE AREA END =====-->

    @if ($map_embed)
        <div class="contact-map-page">
            <iframe src="{{ $map_embed }}" width="600" height="450" style="border:0;" allowfullscreen=""
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harita"></iframe>
        </div>
    @endif

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/contact-form.css') }}">
@endpush

@push('scripts')
    @if ($enabled)
        <script src="{{ asset('assets/js/contact-form.js') }}"></script>
    @endif
@endpush
