 @php
     $faviconLogoId = \App\Support\Settings::get('company.logo_media_id');
     $faviconUrl = $faviconLogoId ? \App\Models\Media\Media::query()->find($faviconLogoId)?->url('medium') : null;
 @endphp

 <!--=====FAB ICON=======-->
 <link rel="shortcut icon" href="{{ $faviconUrl ?? asset('assets/img/logo/title3.svg') }}" type="image/x-icon">


 <!--=====CSS=======-->
 <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/nice-select.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/slick-slider.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/aos.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/swiper%4014.2.0/swiper-bundle.min.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/mobile-menu.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/utility.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/integrations.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/references.css') }}">
 @if (\App\Support\Settings::bool('cookie.enabled'))
     <link rel="stylesheet" href="{{ asset('assets/css/cookie-banner.css') }}">
 @endif

 @stack('css')



 <!--=====JQUERY=======-->
 <script src="{{ asset('assets/js/jquery-3-7-1.min.js') }}"></script>
