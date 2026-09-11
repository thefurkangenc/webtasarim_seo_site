@php
    $footerCompany = \App\Support\Settings::group('company');
    $footerLogoId = $footerCompany['logo_media_id'] ?? null;
    $footerLogo = $footerLogoId ? \App\Models\Media\Media::query()->find($footerLogoId) : null;
    $footerSocialLinks = app(\App\Services\SocialLink\SocialLinkService::class)->list();
    $footerMenus = app(\App\Services\Menu\MenuRenderer::class);
    $footerPrimary = $footerMenus->render('footer_primary');
    $footerSecondary = $footerMenus->render('footer_secondary');
@endphp

<footer class="vl-footer-area14" style="background-image: url({{ asset('assets/img/bg/footer-bg11.png') }});">

    <!-- footer area start -->
    <div class="footer-bottom-content">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                    <div class="vl-footer-widget-black6 vl-footer1-logo-area mr-50 mb-50">
                        <div class="vl-footer-logo black-logo">
                            <a href="{{ route('anasayfa') }}"><img
                                    src="{{ $footerLogo?->url('medium') ?? asset('assets/img/logo/black-logo.png') }}"
                                    alt="{{ $footerCompany['name'] ?? '' }}"></a>
                        </div>
                        @if (filled($footerCompany['short_description'] ?? null))
                            <div class="vl-footer-text heading6 mt-20">
                                <p class="mt-16">{{ $footerCompany['short_description'] }}</p>
                            </div>
                        @endif
                        @if ($footerSocialLinks !== [])
                            <div class="vl-footer-social6 text-start mt-20">
                                @foreach ($footerSocialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                                        title="{{ $link['name'] }}">
                                        @if ($link['icon'])
                                            <img src="{{ $link['icon']['url'] }}" alt="{{ $link['name'] }}"
                                                style="width: 16px; height: 16px; object-fit: contain; vertical-align: middle;">
                                        @else
                                            {{ $link['name'] }}
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <div class="vl-footer-text heading6 mt-30">
                            <h4>Bülten</h4>
                            <p class="mt-16 mb-16">Kampanya ve duyurulardan haberdar olun.</p>
                            @include('layout.partials.subscribe-form', ['source' => 'footer'])
                        </div>
                    </div>
                </div>
                {{-- Footer bağlantı sütunları panelden yönetilir: Menüler ›
                     Footer 1 / Footer 2. Sütun başlığı da o menünün başlığından gelir. --}}
                @if ($footerPrimary !== [])
                    <div class="col-lg-2  col-md-6 col-6">
                        <div class="vl-footer-widget-black6 mb-50 ml-20 md:ml-30 sm:ml-0">
                            <h4>{{ $footerMenus->heading('footer_primary') }}</h4>
                            <div class="vl-footer-list">
                                <ul>
                                    @foreach ($footerPrimary as $item)
                                        <li>
                                            <a href="{{ $item['url'] }}"
                                                @if ($item['target'] === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $item['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($footerSecondary !== [])
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="vl-footer-widget-black6 mb-50 ml-70 md:ml-0 sm:ml-0">
                            <h4>{{ $footerMenus->heading('footer_secondary') }}</h4>
                            <div class="vl-footer-list">
                                <ul>
                                    @foreach ($footerSecondary as $item)
                                        <li>
                                            <a href="{{ $item['url'] }}"
                                                @if ($item['target'] === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $item['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-lg-3 col-md-8 col-sm-6">
                    <div class="vl-footer-contact6 vl-footer-widget-black6 mb-50 sm:ml-0 md:ml-0">
                        <h4>İletişim Bilgileri</h4>

                        @if (filled($footerCompany['email'] ?? null))
                            <div class="single-contact-item">
                                <div class="icon">
                                    <img src="{{ asset('assets/img/icons/footer-contact-icon1.svg') }}" alt="">
                                </div>
                                <div class="text">
                                    <a href="mailto:{{ $footerCompany['email'] }}">{{ $footerCompany['email'] }}</a>
                                </div>
                            </div>
                        @endif

                        @if (filled($footerCompany['address'] ?? null))
                            <div class="single-contact-item">
                                <div class="icon">
                                    <img src="{{ asset('assets/img/icons/footer-contact-icon2.svg') }}" alt="">
                                </div>
                                <div class="text">
                                    <span>{{ $footerCompany['address'] }}</span>
                                </div>
                            </div>
                        @endif

                        @if (filled($footerCompany['phone'] ?? null))
                            <div class="single-contact-item">
                                <div class="icon">
                                    <img src="{{ asset('assets/img/icons/footer-contact-icon3.svg') }}" alt="">
                                </div>
                                <div class="text">
                                    <a href="{{ \App\Support\Phone::href($footerCompany['phone']) }}">{{ $footerCompany['phone'] }}</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- footer area end -->

    <!-- copy-right area start -->
    <div class="container">
        <div class="row vl-copyright6 _dv-top align-items-center">
            <div class="col-lg-6">
                <div class="copyright-text left-side">
                    <p>ⓒCopyright {{ date('Y') }} {{ $footerCompany['name'] ?? config('app.name') }} . Tüm hakları saklıdır</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="copyright-text right-side text-end sm:text-start md:text-start">
                    <a href="{{ route('kvkk') }}">KVKK</a>
                    <a href="{{ route('cerez-politikasi') }}" class="add-before">Çerez politikası</a>
                </div>
            </div>
        </div>
    </div>
    <!-- copy-right area end -->

</footer>
