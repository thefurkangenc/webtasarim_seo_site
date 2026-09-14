@extends('layout.app')

@php
    // Kategori sayfasında başlık ve meta kategoriden gelir; ana listede
    // site geneli SEO ayarlarına düşülür (/hizmetler ve /blog ile aynı).
    $listingTitle = $category?->name ?? 'Neler Yaptık';
    $listingSeo = $category?->seoMeta();
@endphp

@section('title', $listingSeo['title'] ?? $listingTitle)
@section('meta_description', (string) ($listingSeo['description'] ?? ''))
@section('meta_keywords', (string) ($listingSeo['keywords'] ?? ''))
@section('meta_image', (string) ($listingSeo['image'] ?? ''))

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
        <div class="container">
            @if ($category && filled($category->description))
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p>{{ $category->description }}</p>
                    </div>
                </div>
            @endif

            @if ($categories->isNotEmpty())
                {{-- Filtre: temanın sekme görünümü, ama gerçek linklerle — her kategori indekslenebilir bir adres. --}}
                <div class="row">
                    <div class="col-lg-10 m-auto text-center">
                        <div class="categories-buttons">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item">
                                    <a class="nav-link {{ $category ? '' : 'active' }}"
                                        href="{{ route('projeler') }}">Tümü</a>
                                </li>
                                @foreach ($categories as $item)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $category?->is($item) ? 'active' : '' }}"
                                            href="{{ route('projeler.kategori', $item->slug) }}">{{ $item->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
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
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
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
