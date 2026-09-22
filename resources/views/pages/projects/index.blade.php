@extends('layout.app')
@section('title', 'Neler Yaptık')
@section('description', 'Gaziantep Web Tasarım Ajansı Projeler, Neler Yaptık sayfası')
@section('keywords', 'gaziantep web tasarım ajansı, neler yaptık, projeler')
@section('canonical', route('projeler'))
@section('robots', 'noindex, nofollow')

@php
    // Kategori sayfasında başlık ve meta kategoriden gelir; ana listede
    // site geneli SEO ayarlarına düşülür (/hizmetler ve /blog ile aynı).
    $listingTitle = $category?->name ?? 'Çalışmalarımız';
    $listingSeo = $category?->seoMeta();
@endphp
@section('meta_description', 'Gaziantep Web Tasarım Ajansı ' . $listingTitle . ' sayfası')
@section('meta_keywords', 'gaziantep web tasarım ajansı, ' . $listingTitle . ' sayfası')
@if (! empty($listingSeo['canonical']))
    @section('canonical', (string) $listingSeo['canonical'])
@endif
@if (! empty($listingSeo['robots']))
    @section('robots', (string) $listingSeo['robots'])
@endif


@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $listingTitle }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                @if ($category)
                                    <li><a href="{{ route('projeler') }}">Neler Yaptık</a></li>
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $category->name }}</li>
                                @else
                                    <li>Neler Yaptık</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA END =====-->

    <!--===== PORTFOLIO AREA START =====-->

    <div class="blog1 sp bg1 _relative">
        <div class="home-refs__head text-center">

            <span class="sub-title">
                <img style="width: 20px; height: 20px; margin-right: 5px;"
                    src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Projeler">
                Projeler
            </span>

            <h2 id="home-refs-title" class="text-anime-style-3">Neler Yaptık ?</h2>
        </div>
        <div class="container">
            @if ($category && filled($category->description))
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p>{{ $category->description }}</p>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p class="mt-30">
                            Çalışmalarımızı inceleyin ve seçtiğiniz hizmetlerimizle ilgili detaylı bilgi alın.
                        </p>
                    </div>
                </div>
            @endif

            @if ($categories->isNotEmpty())
                {{-- Filtre: gerçek linklerle — her kategori indekslenebilir bir adres. --}}
                <div class="row">
                    <div class="col-lg-12 mt-4">
                        <div class="project-filter-bar">
                            <a href="{{ route('projeler') }}" class="project-filter {{ $category ? '' : 'active' }}">Tümü</a>
                            @foreach ($categories as $item)
                                <a href="{{ route('projeler.kategori', $item->slug) }}"
                                    class="project-filter {{ $category?->is($item) ? 'active' : '' }}">{{ $item->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($projects->isEmpty())
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p class="mt-30">
                            {{ $category
                                ? 'Bu kategoride henüz yayınlanmış bir proje yok.'
                                : 'Henüz yayınlanmış bir proje bulunmuyor.' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="row mt-30">
                    @foreach ($projects as $project)
                        <div class="col-lg-4 col-md-6 mt-30">
                            @include('pages.projects.partials.card', ['project' => $project])
                        </div>
                    @endforeach
                </div>

                <div class="row mt-40">
                    <div class="col-lg-12">
                        {{ $projects->links('vendor.pagination.theme') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!--===== PORTFOLIO AREA END =====-->

    @include('pages.projects.partials.cta')
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/card.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/index.css?v=1') }}">
@endpush
