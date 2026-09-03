<header>

    <div id="vl-header-sticky" class="vl-header-area{{ request()->routeIs('anasayfa') ? '14' : '14' }} header-tranperent">
        <div class="container header2-bg">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="vl-logo">
                        <a href="{{ route('anasayfa') }}" class="header1-logo-block"><img src="{{ asset('assets/img/logo/white-logo.png') }}"
                                alt=""></a>
                    </div>
                </div>
                <div class="col-lg-7 d-none d-lg-block text-end">
                    <div class="vl-main-menu">
                        <nav class="vl-mobile-menu-active">
                            <ul>
                                <li>
                                    <a href="{{ route('anasayfa') }}">Ana Sayfa</a>

                                </li>

                                <li><a href="{{ route('hakkimizda') }}">Hakkımızda</a></li>


                                <li><a href="{{ route('hizmetler') }}">Hizmetler</a></li>

                                <li><a href="{{ route('blog') }}">Blog</a></li>
                                <li><a href="{{ route('iletisim') }}">İletişim</a></li>

                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="vl-header3-btns text-end d-none d-lg-block">
                        <div class="button">
                            <a class="theme-btn27" href="{{ route('iletisim') }}">Bizimle İletişime Geç</a>
                        </div>
                    </div>
                    <div class="vl-header-action-item d-block d-lg-none">
                        <button type="button" class="vl-offcanvas-toggle">
                            <i class="fa-duotone fa-solid fa-bars-staggered"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


<!--===== MOBILE HEADER STARTS =======-->
<div class="vl-offcanvas vl-header-area1">
    <div class="vl-offcanvas-wrapper">
        <div class="vl-offcanvas-header d-flex justify-content-between align-items-center mb-90">
            <div class="vl-offcanvas-logo">
                <a href="{{ route('anasayfa') }}" class="header1-logo-block"><img src="{{ asset('assets/img/logo/black-logo.png') }}"
                        alt=""></a>
            </div>
            <div class="vl-offcanvas-close">
                <button class="vl-offcanvas-close-toggle"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div class="vl-offcanvas-menu d-lg-none mb-40">
            <nav></nav>
        </div>

        <div class="space20"></div>
        <div class="vl-offcanvas-info">
            <h4 class="black1 text-24 mb-30 leading-24 font-semibold">İletişim</h4>
            <div class="single-contact flex align-items-center">
                <div class="text">
                    <a href="tel:+11234567890"
                        class="ml-10 gray2 inline-block p-10-0 text-18 leading-18 text _hover1 font-medium">+90 555 555
                        55 55</a>
                </div>
            </div>

            <div class="single-contact flex align-items-center mt-6">
                <div class="text">
                    <a href="#"
                        class="ml-10 gray2 inline-block p-10-0 text-18 leading-18 text _hover1 font-medium">İstanbul,
                        Türkiye</a>
                </div>
            </div>

            <div class="single-contact flex align-items-center mt-6">
                <div class="text">
                    <a href="mailto:Hosticconsult@com"
                        class="ml-10 gray2 inline-block p-10-0 text-18 leading-18 text _hover1 font-medium">webtasarim@gmail.com</a>
                </div>
            </div>

        </div>
        <div class="space20"></div>
        <div class="vl-offcanvas-social">
            <h4 class="black1 text-24 mb-20 mt-20 leading-24 font-semibold">Bizi Takip Edin</h4>
            <div class="vl-copyright-social2 text-start mt-20">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
        </div>

    </div>
</div>

<!--===== MOBILE HEADER ENDS =======-->
<div class="vl-offcanvas-overlay"></div>
