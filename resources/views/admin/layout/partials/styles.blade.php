<link rel="stylesheet" href="{{ asset('admin/assets/css/remixicon.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/apexcharts.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/simplebar.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/prism.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/jsvectormap.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/vendor/cropper.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/vendor/choices.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/vendor/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">

@stack('admin.css')

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('admin/assets/images/favicon.ico') }}">


{{-- Gövde fontu (Poppins) self-host: @font-face tanımları style.css içinde,
     dosyalar public/admin/assets/fonts/poppins/ altında. Google Fonts'tan
     çekilmiyor — en sık kullanılan iki ağırlık önden yükleniyor. --}}
<link rel="preload" as="font" type="font/woff2" crossorigin
    href="{{ asset('admin/assets/fonts/poppins/poppins-400-latin.woff2') }}">
<link rel="preload" as="font" type="font/woff2" crossorigin
    href="{{ asset('admin/assets/fonts/poppins/poppins-500-latin.woff2') }}">

{{-- Material Symbols hâlâ Google'dan geliyor (ikon fontu, gövde fontu değil). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Material Icons -->
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
