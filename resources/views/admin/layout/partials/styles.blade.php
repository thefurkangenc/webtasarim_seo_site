<link rel="stylesheet" href="{{ asset('assets/admin/css/remixicon.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/apexcharts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/simplebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/prism.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/jsvectormap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/cropper.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/choices.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/video-player.css') }}">

@stack('admin.css')

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('assets/admin/images/favicon.ico') }}">


{{-- Gövde fontu (Poppins) self-host: @font-face tanımları style.css içinde,
     dosyalar public/assets/admin/fonts/poppins/ altında. Google Fonts'tan
     çekilmiyor — en sık kullanılan iki ağırlık önden yükleniyor. --}}
<link rel="preload" as="font" type="font/woff2" crossorigin
    href="{{ asset('assets/admin/fonts/poppins/poppins-400-latin.woff2') }}">
<link rel="preload" as="font" type="font/woff2" crossorigin
    href="{{ asset('assets/admin/fonts/poppins/poppins-500-latin.woff2') }}">

{{-- Material Symbols hâlâ Google'dan geliyor (ikon fontu, gövde fontu değil). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Material Icons -->
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
