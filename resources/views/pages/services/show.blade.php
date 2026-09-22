@extends('layout.app')
{{-- Sekme başlığı SEO alanından gelir; boşsa hizmetin kendi başlığına düşer.
Bölge sayfasında bu değer bölge adıyla nitelenmiş olarak gelir (bkz.
Service::renderFor), böylece her bölge adresi kendi başlığını alır. --}}
@section('title', $rendered['seo']['title'] ?: $rendered['title'])
@section('meta_description', (string) $rendered['seo']['description'])
@section('meta_keywords', (string) $rendered['seo']['keywords'])
{{-- @section @if içine alınmaz: derleyici bölümü kapatmaz, @endforeach eşleşmez. --}}
@section('canonical', $region ? '' : (string) $rendered['seo']['canonical'])
@section('robots', (string) $rendered['seo']['robots'])
@section('meta_image', (string) $rendered['seo']['image'])
@section('content')
@php
    // Bölge sayfasında üst kırılım "hizmetin genel adı" olarak bölgesiz
    // (yer tutucusuz) başlığı gösterir — "Gaziantep Web Tasarım > Gaziantep"
    // gibi tekrarlı görünmesin diye burada ayrıca çözülür.
    $genericTitle = $service->renderGeneric()['title'];
    if ($region) {
        $genericTitle = $region->name . ' ' . $genericTitle;
    }
    // Kırılımın bölge basamakları adresle aynı sırayı izler (il > ilçe).
    // Hizmete bağlı olmayan bir üst bölgenin sayfası yoktur, o yüzden
    // linksiz basılır.
    $regionTrail = $region
        ? $region->ancestorsAndSelf()->map(fn($step) => [
            'name' => $step->name,
            'url' => $step->is($region) || $service->coveredRegions()->contains($step)
                ? route('hizmetler.show-region', [$service->slug, $step->slug_path])
                : null,
        ])
        : collect();
@endphp

<!--===== HERO AREA START =====-->

<div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 m-auto text-center">
                <div class="inner-main-heading">
                    <h1>{{ $genericTitle }}</h1>
                    <div class="breadcrumbs-pages">
                        <ul>
                            <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                            <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                            <li><a href="{{ route('hizmetler') }}">Hizmetler</a></li>
                            @if ($region)
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('hizmetler.show', $service->slug) }}">{{ $rendered['title'] }}</a>
                                </li>
                                @foreach ($regionTrail as $step)
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>
                                        @if ($step['url'] && !$loop->last)
                                            <a href="{{ $step['url'] }}">{{ $step['name'] }}</a>
                                        @else
                                            {{ $step['name'] }}
                                        @endif
                                    </li>
                                @endforeach
                            @else
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ $rendered['title'] }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="blog-details-area sp ">
    <div class="container">
        <div class="row">
            {{-- Kenar çubuğu masaüstünde solda kalır; mobilde içerikten SONRA
            gelir (order-2). Ziyaretçi önce ne aldığını okur, sonra formu
            görür — form üstteyken sayfa "önce bilgi ver" diye açılıyordu. --}}
            <!-- Sidebar -->
            <div class="col-lg-3 order-2 order-lg-1">
                <div class="sidebar-area service-quote-sidebar">
                    <x-site.quote-form :service="$service" :region="$region"
                        heading="Ücretsiz teklif alın"
                        intro="İki kısa adım — uzmanımız sizi arasın." />
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 order-1 order-lg-2">
                <div class="blog-details-content ml-30 md:ml-0 sm:ml-0">

                    <div class="heading2 mt-24">
                        <h2>{{ $genericTitle }} - {{ $company['name'] }}</h2>
                        @if (filled($rendered['excerpt']))
                            <p class="mt-16">{{ $rendered['excerpt'] }}</p>
                        @endif
                    </div>

                    @php
                        $cover = $service->getFirstMedia('cover')?->originalUrl();
                    @endphp
                    @if ($cover)
                        <article>
                            <div class="details-content">
                                <div class="image">
                                    <a href="tel:{{ $company['phone'] }}">
                                        <img class="w-full" src="{{ $cover }}"
                                            alt="{{ $rendered['title'] }}">
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endif

                    <article>
                        <div class="details-content body-font">

                            @if (filled($rendered['content']))
                                <div class="heading2 mt-24">
                                    {!! $rendered['content'] !!}
                                </div>
                            @endif
                            <hr>
                            @if(!$region)
                                @if($service->regions->isNotEmpty())
                                    @foreach($service->regions as $city)
                                        <p>
                                            <a title="{{ $city->name }} - {{ $service->renderGeneric()['title'] }}"
                                                href="{{ route('hizmetler.show-region', [$service->slug, $city->slug_path]) }}">
                                                {{ $city->name }} - {{ $service->renderGeneric()['title'] }}
                                            </a>
                                        </p>
                                    @endforeach
                                @endif
                            @else
                                @if($region)
                                    @if($region->isMain())
                                        @foreach($region->children as $district)
                                            <p>
                                                <a title="{{ $district->name }} - {{ $service->renderGeneric()['title'] }}"
                                                    href="{{ route('hizmetler.show-region', [$service->slug, $district->slug_path]) }}">
                                                    {{ $district->name }} - {{ $service->renderGeneric()['title'] }}
                                                </a>
                                            </p>
                                        @endforeach
                                    @endif
                                @endif
                            @endif
                        </div>
                    </article>

                    @if ($region && filled($region->description))
                        <div class="region-note">
                            <h2>
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $genericTitle }}
                            </h2>
                            <p>{{ $region->description }}</p>
                        </div>

                    @endif
                    <div class="details-border"></div>
                </div>
            </div>



        </div>
    </div>
</div>

<!--===== BLOG DETAILS AREA END =====-->

@if ($projects->isNotEmpty())
    <!--===== SERVICE PROJECTS START =====-->
    <div class="portfolio sp sec-bg5">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-center">
                    <div class="home-refs__head text-center">

                        <span class="sub-title">
                            <img style="width: 20px; height: 20px; margin-right: 5px;"
                                src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Projeler">
                            Projeler
                        </span>

                        <h2 id="home-refs-title" class="text-anime-style-3">{{$service->title}} İşlerimiz</h2>
                        <p class="home-refs__lead">
                            {{  $service->title }} hizmetinde yaptığımız işleri inceleyin ve seçtiğiniz hizmetlerimizle
                            ilgili detaylı bilgi alın.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row mt-30">
                @foreach ($projects as $serviceProject)
                    <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                        @include('pages.projects.partials.card', ['project' => $serviceProject])
                    </div>
                @endforeach
            </div>
            <div class="row mt-40">
                <div class="col-lg-12 text-center">
                    <a class="ui-btn ui-btn--solid" href="{{ route('projeler') }}">
                        Tüm İşlerimiz <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!--===== SERVICE PROJECTS END =====-->
@endif



@if ($region && $region->isMain())
@php
    $faqs = $service->renderedFaqs($region);
@endphp

@if ($faqs->isNotEmpty())
<!--===== SERVICE FAQ START =====-->
<section class="sp service-faq">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 m-auto">
                <div class="home-refs__head text-center">

                    <span class="sub-title">
                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                            src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Sıkça Sorulan Sorular">
                        {{ $company['name'] }}
                    </span>

                    <h2 id="home-refs-title" class="text-anime-style-3">{{$service->title}} Sıkça Sorulan Sorular</h2>
                    <p class="home-refs__lead">
                        {{  $service->title }} hizmetinde yaptığımız işleri inceleyin ve seçtiğiniz hizmetlerimizle
                        ilgili detaylı bilgi alın. Sıkça sorulan soruları ve net yanıtlarını bir araya getirdik.
                    </p>
                </div>

                <div class="service-faq__list" id="service-faq-{{ $service->id }}">
                    @foreach ($faqs as $index => $faq)
                    @php
                        $target = "service-{$service->id}-faq-{$faq['id']}";
                    @endphp

                    <div class="faq-card">
                        <h3 class="faq-card__head">
                            <button class="faq-card__btn {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#{{ $target }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $target }}">
                                <span class="faq-card__q">{{ $faq['question'] }}</span>
                                <span class="faq-card__icon" aria-hidden="true"></span>
                            </button>
                        </h3>
                        <div id="{{ $target }}" class="faq-card__panel collapse {{ $index === 0 ? 'show' : '' }}"
                            data-bs-parent="#service-faq-{{ $service->id }}">
                            <div class="faq-card__answer">{!! nl2br(e($faq['answer'])) !!}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="service-faq__cta">
                    <p>Aradığınız cevabı bulamadınız mı?</p>
                    <a class="ui-btn ui-btn--solid ui-btn--sm" href="{{ route('iletisim') }}">
                        Bize Sorun <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!--===== SERVICE FAQ END =====-->
@endif
@endif


@if ($otherServices->isNotEmpty())
    @include('pages.services.partials.section', ['services' => $otherServices])
@endif

<x-googlecomment />

@if($region && !$region->isMain())
    @if($region->id % 2 == 0)
        @include('pages.why-choose-us.partials.section')
    @else
        {{-- referanslarının listesini göster --}}
        @if ($references->isNotEmpty())
            @include('pages.references.partials.section', ['references' => $references])
        @endif
    @endif
@endif


@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/card.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/home/index.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/css/pages/services/show.css?v=' . filemtime(public_path('assets/css/pages/services/show.css'))) }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/pages/services/show.js') }}"></script>
@endpush
