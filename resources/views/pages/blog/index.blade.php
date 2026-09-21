@extends('layout.app')

@php
    // Aynı view üç adresi karşılar: /blog, /blog/kategori/{slug}, /blog/etiket/{slug}.
    // Kategoride başlık ve meta kategorinin kendi SEO kaydından gelir; etikette
    // öyle bir kayıt yok, o yüzden etiket adından türetilir.
    $taxonomySeo = $category?->seoMeta() ?? [];

    $listingTitle = $category?->name
        ?? ($tag ? $tag->name . ' etiketli yazılar' : 'Blog');

    $listingLead = $category && filled($category->description)
        ? $category->description
        : ($tag
            ? $tag->name . ' konusunda yayınladığımız tüm rehber ve içgörüler.'
            : 'Web tasarım, SEO ve dijital pazarlama üzerine güncel rehberler ve içgörüler.');

    $listingDescription = ($taxonomySeo['description'] ?? null)
        ?: ($category || $tag
            ? $listingLead
            : 'Gaziantep Web tasarım, Kurumsal Web sitesi ve SEO hizmetleri hakkında güncel rehberler ve içgörüler.');

    $listingKeywords = ($taxonomySeo['keywords'] ?? null)
        ?: ($category || $tag
            ? mb_strtolower($category?->name ?? $tag->name) . ', ' . mb_strtolower($category?->name ?? $tag->name) . ' blog'
            : 'gaziantep web tasarım, kurumsal web sitesi, web tasarım ajansı, web sitesi fiyatları, web tasarım fiyatları');

    $emptyText = $category
        ? 'Bu kategoride henüz yayınlanmış bir yazı yok.'
        : ($tag
            ? 'Bu etiketle henüz yayınlanmış bir yazı yok.'
            : 'Henüz yayınlanmış bir yazı bulunmuyor.');
@endphp

@section('title', ($taxonomySeo['title'] ?? null) ?: $listingTitle)
@section('meta_description', (string) $listingDescription)
@section('meta_keywords', (string) $listingKeywords)
@if (!empty($taxonomySeo['image']))
@section('meta_image', (string) $taxonomySeo['image'])
@endif
@if (!empty($taxonomySeo['canonical']))
@section('canonical', (string) $taxonomySeo['canonical'])
@endif
@if (!empty($taxonomySeo['robots']))
@section('robots', (string) $taxonomySeo['robots'])
@endif


@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        @if ($tag)
                            <span class="blog-detail-hero__category">Etiket</span>
                        @endif
                        <h1>{{ $listingTitle }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                @if ($category || $tag)
                                    <li><a href="{{ route('blog') }}">Blog</a></li>
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $category?->name ?? $tag->name }}</li>
                                @else
                                    <li>Blog</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="blog-list sp" aria-label="Blog yazıları">
        <div class="container">
            <div class="home-refs__head text-center">

                <span class="sub-title">
                    <img style="width: 20px; height: 20px; margin-right: 5px;"
                        src="{{ asset('assets/img/icons/icon.png') }}" alt="">
                    Blog
                </span>

                <h2 id="home-refs-title" class="text-anime-style-3">{{ $listingTitle }}</h2>
                <p class="home-refs__lead">
                    {{ $listingLead }}
                </p>
            </div>



        @if ($categories->isNotEmpty())
            {{-- Filtre: gerçek linklerle — her kategori indekslenebilir bir adres. --}}
            <div class="blog-filter-bar mt-4">
                <a href="{{ route('blog') }}" class="blog-filter {{ $category || $tag ? '' : 'active' }}">Tümü</a>

                @foreach ($categories as $item)
                    <a href="{{ route('blog.kategori', $item->slug) }}"
                        class="blog-filter {{ $category?->is($item) ? 'active' : '' }}">
                        {{ $item->name }}
                        <span class="blog-filter__count">{{ $item->blogs_count }}</span>
                    </a>
                @endforeach

                {{-- Etiket sayfasında hiçbir kategori seçili değil; nerede olduğu
                belirsiz kalmasın diye etiketin kendisi seçili rozet olarak durur.
                Link değil — zaten bu sayfadayız. --}}
                @if ($tag)
                    <span class="blog-filter active">#{{ $tag->name }}</span>
                @endif
            </div>
        @endif

        @if ($blogs->isEmpty())
            <p class="blog-list__empty text-center">{{ $emptyText }}</p>

            @if ($category || $tag)
                <p class="text-center">
                    <a href="{{ route('blog') }}" class="blog-card__link">
                        Tüm yazılara dön <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </p>
            @endif
        @else
            <div class="row blog-list__grid">
                @foreach ($blogs as $blog)
                    <div class="col-lg-4 col-md-6">
                        @include('pages.blog.partials.card', ['blog' => $blog])
                    </div>
                @endforeach
            </div>

            <div class="blog-list__pagination">
                {{ $blogs->links('vendor.pagination.theme') }}
            </div>
        @endif
        </div>
    </section>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/blog/taxonomy.css') }}">
@endpush
