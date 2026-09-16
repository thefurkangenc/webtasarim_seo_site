@php
    $faviconLogoId = \App\Support\Settings::get('company.logo_media_id');
    $faviconUrl = $faviconLogoId ? \App\Models\Media\Media::query()->find($faviconLogoId)?->url('medium') : null;
 @endphp

<!--=====FAB ICON=======-->
<link rel="shortcut icon" href="{{ $faviconUrl ?? asset('assets/img/logo/title3.svg') }}" type="image/x-icon">


<!--=====FONTS=======-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
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
<link rel="stylesheet" href="{{ asset('assets/css/main.css?v='.time()) }}">
<link rel="stylesheet" href="{{ asset('assets/css/integrations.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/references.css') }}">
@if (\App\Support\Settings::bool('cookie.enabled'))
    <link rel="stylesheet" href="{{ asset('assets/css/cookie-banner.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('assets/css/notices.css') }}">

@stack('css')



<!--=====JQUERY=======-->
<script src="{{ asset('assets/js/jquery-3-7-1.min.js') }}"></script>
