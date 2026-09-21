@php
    // Sayfa title section tanımlıyorsa (blog/legal gibi) o kullanılır,
    // yoksa (ör. ana sayfa) site geneli SEO ayarlarındaki meta başlığa düşülür.
    $metaSeo = \App\Support\Settings::merged('seo');
    $metaCompany = \App\Support\Settings::merged('company');
    $metaSiteName = ($metaSeo['site_name'] ?? null) ?: ($metaCompany['name'] ?? null) ?: config('app.name');
    $metaPageTitle = trim($__env->yieldContent('title'));
    $metaTitle = $metaPageTitle !== ''
        ? $metaPageTitle . ' - ' . $metaSiteName
        : (($metaSeo['meta_title'] ?? null) ?: $metaSiteName);

    // Sayfa meta_description / meta_keywords / meta_image section
    // tanımlıyorsa (hizmet/bölge sayfaları gibi, kendi SEO alanları olan
    // modeller için) o kullanılır, tanımlamayan her sayfa (ana sayfa, hakkımızda,
    // yasal sayfalar...) davranışı hiç değişmeden site geneli ayarlara düşer.
    $metaPageDescription = trim($__env->yieldContent('meta_description'));
    $metaPageKeywords = trim($__env->yieldContent('meta_keywords'));
    $metaPageImage = trim($__env->yieldContent('meta_image'));

    $metaDescription = $metaPageDescription !== '' ? $metaPageDescription : ($metaSeo['meta_description'] ?? null);
    $metaKeywords = $metaPageKeywords !== '' ? $metaPageKeywords : ($metaSeo['meta_keywords'] ?? null);

    // Canonical: kaydın kendi SEO alanında bir adres yazılmışsa o kazanır
    // (panelde bilerek girilmiştir), yoksa sayfa kendine döner. Sayfalamada
    // ?page=N korunur — bkz. App\Support\Canonical.
    $metaPageCanonical = trim($__env->yieldContent('canonical'));
    $metaCanonical = $metaPageCanonical !== '' ? $metaPageCanonical : \App\Support\Canonical::current();

    // robots: yalnızca varsayılandan sapınca basılır. Etiketin yokluğu arama
    // motoru için zaten "index,follow" demek; her sayfaya yazmak gürültü.
    $metaPageRobots = trim($__env->yieldContent('robots'));
    $metaRobots = $metaPageRobots !== '' ? $metaPageRobots : 'index,follow';

    $metaOgMediaId = $metaSeo['og_media_id'] ?? null;
    $metaOgImage = $metaPageImage !== ''
        ? $metaPageImage
        : ($metaOgMediaId ? \App\Models\Media\Media::query()->find($metaOgMediaId)?->url('medium') : null);
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $metaTitle }}</title>

@if (filled($metaDescription))
<meta name="description" content="{{ $metaDescription }}">
@endif
@if (filled($metaKeywords))
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
<link rel="canonical" href="{{ $metaCanonical }}">
@if ($metaRobots !== 'index,follow')
<meta name="robots" content="{{ $metaRobots }}">
@endif

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $metaSiteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:url" content="{{ $metaCanonical }}">
@if (filled($metaDescription))
<meta property="og:description" content="{{ $metaDescription }}">
@endif
@if ($metaOgImage)
<meta property="og:image" content="{{ $metaOgImage }}">
@endif

<meta name="twitter:card" content="{{ $metaOgImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
@if (filled($metaDescription))
<meta name="twitter:description" content="{{ $metaDescription }}">
@endif
@if ($metaOgImage)
<meta name="twitter:image" content="{{ $metaOgImage }}">
@endif
