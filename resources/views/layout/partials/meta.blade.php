@php
    // Sayfa kendi @section('title', ...) tanımlıyorsa (blog/legal gibi) o kullanılır,
    // yoksa (ör. ana sayfa) site geneli SEO ayarlarındaki meta başlığa düşülür.
    $metaSeo = \App\Support\Settings::group('seo');
    $metaCompany = \App\Support\Settings::group('company');
    $metaSiteName = $metaSeo['site_name'] ?: ($metaCompany['name'] ?: config('app.name'));
    $metaPageTitle = trim($__env->yieldContent('title'));
    $metaTitle = $metaPageTitle !== ''
        ? $metaPageTitle.' | '.$metaSiteName
        : ($metaSeo['meta_title'] ?: $metaSiteName);
    $metaDescription = $metaSeo['meta_description'] ?? null;
    $metaKeywords = $metaSeo['meta_keywords'] ?? null;
    $metaOgMediaId = $metaSeo['og_media_id'] ?? null;
    $metaOgImage = $metaOgMediaId ? \App\Models\Media\Media::query()->find($metaOgMediaId)?->url('medium') : null;
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
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $metaSiteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:url" content="{{ url()->current() }}">
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
