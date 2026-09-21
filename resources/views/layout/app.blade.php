<!DOCTYPE html>
<html lang="tr" class="is-preloading">

<head>
    <x-site.meta />
    <x-site.schema :context="$schemaContext ?? null" />
    <x-site.tracking placement="head" />

    @include('layout.partials.css')
</head>

<body class="body1">
    <x-site.tracking placement="body" />
    <div class="paginacontainer">

        <div class="progress-wrap">
            <svg class="progress-circle svg-content" width="100%" height="100%" viewbox="-1 -1 102 102">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
            </svg>
        </div>

    </div>

    @php
        $preloaderCompany = \App\Support\Settings::group('company')['name'] ?? config('app.name');
    @endphp

    <div class="site-preloader" id="site-preloader" aria-live="polite" aria-busy="true">
        <div class="site-preloader__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
        <div class="site-preloader__inner">
            <div class="site-preloader__mark" aria-hidden="true">
                <svg class="site-preloader__ring" viewBox="0 0 120 120" focusable="false">
                    <circle cx="60" cy="60" r="54" />
                    <circle class="site-preloader__ring-progress" cx="60" cy="60" r="54" />
                </svg>
                <img class="site-preloader__logo" src="{{ asset('assets/img/icons/icon.png') }}"
                    alt="{{ $preloaderCompany }}" width="56" height="56">
            </div>
            <p class="site-preloader__label">{{ $preloaderCompany }}</p>
        </div>
    </div>

    <x-site.notices />

    @include('layout.partials.header')

    <main>

        @yield('content')


    </main>


    @include('layout.partials.footer')

    <x-site.floating-cta />

    <x-site.cookie-banner />

    @include('layout.partials.scripts')
    <x-site.integrations />

</body>

</html>
