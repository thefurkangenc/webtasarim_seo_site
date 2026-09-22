@extends('layout.app')
@section('title', 'İletişim')
@section('meta_description', 'Gaziantep Web Tasarım Ajansı telefon numarası, Gaziantep web tasarım ajansı adresi')
@section('meta_keywords', 'gaziantep web tasarım ajansı, telefon numarası, adres')

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
                            <div class="container">
                                <div class="home-refs__head">

                                    <span class="sub-title">
                                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                                            src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                                        İletişim
                                    </span>

                                <h2 id="home-refs-title" class="text-anime-style-3">{{ $heading }}</h2>
                                <p class="home-refs__lead">
                                    {{ $intro }}
                                </p>
                            </div>
                        </div>
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
                                            <x-captcha form="contact" />
                                        </div>
                                        <div class="col-md-12">
                                            <div class="button mt-30">
                                                <button class="ui-btn ui-btn--solid" type="submit" data-contact-submit>
                                                    Gönder <i class="fa-solid fa-arrow-right"></i>
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
                        <img style="border-radius: 5px;" class="w-full" src="{{asset('assets/img/contact.jpg')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== CONTACT AREA END =====-->

    <!--===== NEDEN BIZ =====-->
    @include('pages.why-choose-us.partials.section', ['bg' => 'sec-bg2'])
    <!--===== NEDEN BIZ END =====-->

    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3183.5645613344795!2d37.369014176279094!3d37.06785855256528!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1531e17a1ec59fe9%3A0x6be44d52e235f7bb!2zRXRraSBTb2Z0IOKAkyBHYXppYW50ZXAgV2ViIFRhc2FyxLFtIHZlIFlhesSxbMSxbSBBamFuc8Sx!5e0!3m2!1str!2str!4v1790069965001!5m2!1str!2str" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/contact-form.css') }}">
@endpush

@push('scripts')
    @if ($enabled)
        <script src="{{ asset('assets/js/contact-form.js') }}"></script>
    @endif
@endpush
