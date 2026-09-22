@php
    $headerCompany = \App\Support\Settings::group('company');
    $headerLogoId = $headerCompany['logo_media_id'] ?? null;
    $headerLogo = $headerLogoId ? \App\Models\Media\Media::query()->find($headerLogoId) : null;
    $headerSocialLinks = app(\App\Services\SocialLink\SocialLinkService::class)->list();
    $headerMenu = app(\App\Services\Menu\MenuRenderer::class)->render('header');
    $headerPhone = $headerCompany['phone'] ?? null;
    $isHome = request()->routeIs('anasayfa');
@endphp

<header class="site-header">
    <div id="vl-header-sticky" class="site-header__bar{{ $isHome ? ' is-home' : ' is-inner' }}">
        <div class="container">
            <div class="site-header__row">
                <a href="{{ route('anasayfa') }}" class="site-header__logo">
                    @if ($headerLogo)
                        <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ $headerCompany['name'] ?? '' }}"
                            class="site-header__logo-img site-header__logo-img--light">
                        <img src="{{ $headerLogo->url('medium') }}" alt="{{ $headerCompany['name'] ?? '' }}"
                            class="site-header__logo-img site-header__logo-img--dark">
                    @else
                        <img src="{{ asset('assets/img/logo/white-logo.png') }}" alt="{{ $headerCompany['name'] ?? '' }}"
                            class="site-header__logo-img site-header__logo-img--light">
                        <img src="{{ asset('assets/img/logo/black-logo.png') }}" alt="{{ $headerCompany['name'] ?? '' }}"
                            class="site-header__logo-img site-header__logo-img--dark">
                    @endif
                </a>

                <nav class="site-header__nav d-none d-lg-block" aria-label="Ana menü">
                    {{-- Öğeler panelden: Menüler › Üst Menü. Mobil menü main.js bu <ul>'yi klonlar. --}}
                        <div class="vl-mobile-menu-active">
                            @include('layout.partials.menu-nav', ['items' => $headerMenu, 'depth' => 0])
                        </div>
                </nav>

                <div class="site-header__aside">
                    @if (filled($headerPhone))
                        <a class="site-header__phone d-none d-xl-inline-flex"
                            href="{{ \App\Support\Phone::href($headerPhone) }}">
                            <span class="site-header__phone-icon" aria-hidden="true"><i
                                    class="fa-solid fa-phone"></i></span>
                            <span>{{ $headerPhone }}</span>
                        </a>
                    @endif

                    <a class="site-header__cta d-none d-md-inline-flex" href="{{ route('iletisim') }}">
                        İletişim
                    </a>

                    <button type="button" class="site-header__burger vl-offcanvas-toggle d-lg-none"
                        aria-label="Menüyü aç">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="vl-offcanvas site-offcanvas">
    <div class="vl-offcanvas-wrapper site-offcanvas__wrap">
        <div class="vl-offcanvas-header site-offcanvas__head">
            <a href="{{ route('anasayfa') }}" class="site-header__logo">
                <img src="{{ $headerLogo?->url('medium') ?? asset('assets/img/logo/black-logo.png') }}"
                    alt="{{ $headerCompany['name'] ?? '' }}" class="site-header__logo-img">
            </a>
            <button type="button" class="vl-offcanvas-close-toggle site-offcanvas__close" aria-label="Menüyü kapat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="vl-offcanvas-menu site-offcanvas__menu d-lg-none">
            <nav></nav>
        </div>

        @if (filled($headerPhone) || filled($headerCompany['email'] ?? null) || filled($headerCompany['address'] ?? null))
            <div class="vl-offcanvas-info site-offcanvas__contact">
                <p class="site-offcanvas__label">İletişim</p>

                @if (filled($headerPhone))
                    <a href="{{ \App\Support\Phone::href($headerPhone) }}" class="site-offcanvas__link">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <span>{{ $headerPhone }}</span>
                    </a>
                @endif

                @if (filled($headerCompany['email'] ?? null))
                    <a href="mailto:{{ $headerCompany['email'] }}" class="site-offcanvas__link">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <span>{{ $headerCompany['email'] }}</span>
                    </a>
                @endif

                @if (filled($headerCompany['address'] ?? null))
                    <p class="site-offcanvas__link">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>{{ $headerCompany['address'] }}</span>
                    </p>
                @endif
            </div>
        @endif

        @if ($headerSocialLinks !== [])
            <div class="vl-offcanvas-social site-offcanvas__social">
                <p class="site-offcanvas__label">Bizi takip edin</p>
                <div class="site-offcanvas__social-row">
                    @foreach ($headerSocialLinks as $link)
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $link['name'] }}">
                            @if ($link['icon'])
                                <img src="{{ $link['icon']['url'] }}" alt="{{ $link['name'] }}">
                            @else
                                <span>{{ $link['name'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <a class="site-header__cta site-offcanvas__cta" href="{{ route('iletisim') }}">İletişime Geç</a>
    </div>
</div>

<div class="vl-offcanvas-overlay"></div>
