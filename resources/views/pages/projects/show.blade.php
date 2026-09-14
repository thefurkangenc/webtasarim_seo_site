@extends('layout.app')

@php
    $seo = $project->seoMeta();
    $cover = $project->getFirstMedia('cover');
    $gallery = $project->getMedia('gallery');
    $video = $project->videoEmbed();
    $videoFile = $project->getFirstMedia('video');
    $results = $project->resultRows();
    $technologies = $project->technologies ?? [];
    $shareUrl = urlencode((string) ($project->publicUrl() ?? url()->current()));

    // Künye satırları tek yerde kurulur: boş olanlar elenir, sıra sabit kalır.
    $facts = collect([
        ['Müşteri', $project->client_name, null],
        ['Sektör', $project->sector, null],
        ['Kategori', $project->category?->name, $project->category ? route('projeler.kategori', $project->category->slug) : null],
        ['Tamamlanma', $project->completedLabel(), null],
        ['Süre', $project->duration, null],
    ])->filter(fn ($row) => filled($row[1]));
@endphp

@section('title', $seo['title'] ?: $project->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/show.css') }}">
@endpush

@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $project->title }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('projeler') }}">Neler Yaptık</a></li>
                                @if ($project->category)
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>
                                        <a href="{{ route('projeler.kategori', $project->category->slug) }}">{{ $project->category->name }}</a>
                                    </li>
                                @endif
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ $project->title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA END =====-->

    <!--===== PORTFOLIO DETAILS AREA START =====-->

    <div class="portfolio-details-area sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog-details-content">
                        <article>
                            <div class="details-content">
                                @if ($cover)
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url() }}" alt="{{ $project->title }}">
                                    </div>
                                @endif

                                @if (filled($project->excerpt) || filled($project->content))
                                    <div class="heading2 mt-24">
                                        <h3>Proje Hakkında</h3>
                                        @if (filled($project->excerpt))
                                            <p class="mt-16">{{ $project->excerpt }}</p>
                                        @endif
                                    </div>

                                    @if (filled($project->content))
                                        <div class="details-body mt-16">
                                            {!! $project->content !!}
                                        </div>
                                    @endif
                                @endif

                                @if ($results !== [])
                                    {{--
                                        Ölçülebilir sonuçlar. `direction` yalnızca ok yönünü belirler,
                                        renk sabit kalır: "çıkma oranı %60 düştü" iyi bir sonuçtur —
                                        yön tek başına iyi/kötü demez.
                                    --}}
                                    <div class="heading2 mt-50">
                                        <h3>Sonuçlar</h3>
                                    </div>
                                    <div class="counters-area-details mt-10">
                                        <div class="row">
                                            @foreach ($results as $result)
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="details-counter-box mt-30">
                                                        <h3>
                                                            @if ($result['direction'] === 'up')
                                                                <i class="fa-solid fa-arrow-trend-up"></i>
                                                            @elseif ($result['direction'] === 'down')
                                                                <i class="fa-solid fa-arrow-trend-down"></i>
                                                            @endif
                                                            {{ $result['value'] }}
                                                        </h3>
                                                        <p class="mt-10">{{ $result['label'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($gallery->isNotEmpty())
                                    <div class="heading2 mt-50">
                                        <h3>Proje Görselleri</h3>
                                    </div>
                                    <div class="row" data-project-gallery>
                                        @foreach ($gallery as $media)
                                            <div class="col-md-6">
                                                <div class="image mt-30">
                                                    <a href="{{ $media->url() }}" data-gallery-item
                                                        data-title="{{ $media->alt ?: $project->title }}">
                                                        <img class="w-full" src="{{ $media->url('medium') }}"
                                                            alt="{{ $media->alt ?: $project->title }}">
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($video || $videoFile)
                                    <div class="heading2 mt-50">
                                        <h3>Proje Videosu</h3>
                                    </div>
                                    <div class="mt-20">
                                        @if ($video)
                                            <div class="ratio ratio-16x9">
                                                <iframe src="{{ $video['embed_url'] }}" title="{{ $project->title }}"
                                                    loading="lazy" allowfullscreen
                                                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                            </div>
                                        @else
                                            <video class="w-full" controls preload="metadata"
                                                @if ($cover) poster="{{ $cover->url('medium') }}" @endif>
                                                <source src="{{ $videoFile->url() }}" type="{{ $videoFile->mime_type }}">
                                            </video>
                                        @endif
                                    </div>
                                @endif

                                @if ($project->services->isNotEmpty())
                                    <div class="heading2 mt-50">
                                        <h3>Bu Projede Verdiğimiz Hizmetler</h3>
                                        <div class="details-list-item mt-20">
                                            <ul>
                                                @foreach ($project->services as $service)
                                                    <li>
                                                        <span class="check"><i class="fa-solid fa-check"></i></span>
                                                        @if ($serviceUrl = $service->publicUrl())
                                                            <a href="{{ $serviceUrl }}">{{ $service->publicLinkLabel() }}</a>
                                                        @else
                                                            {{ $service->publicLinkLabel() }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if ($project->testimonial)
                                    @php($testimonialPhoto = $project->testimonial->getFirstMedia('photo'))
                                    <div class="details-quote mt-50">
                                        <blockquote>
                                            <i class="fa-solid fa-quote-left"></i>
                                            <p>{{ $project->testimonial->content }}</p>
                                            <footer class="mt-20">
                                                @if ($testimonialPhoto)
                                                    <img src="{{ $testimonialPhoto->url('thumb') }}"
                                                        alt="{{ $project->testimonial->name }}" width="56" height="56">
                                                @endif
                                                <span>
                                                    <strong>{{ $project->testimonial->name }}</strong>
                                                    @if (filled($project->testimonial->title))
                                                        <small>{{ $project->testimonial->title }}</small>
                                                    @endif
                                                </span>
                                            </footer>
                                        </blockquote>
                                    </div>
                                @endif

                                @include('pages.projects.partials.faqs', ['project' => $project])
                            </div>
                        </article>

                        <div class="details-border"></div>
                        <div class="details-content">
                            <div class="details-social-tags">
                                @if ($project->tags->isNotEmpty())
                                    <div class="tags">
                                        <ul>
                                            <li class="text">Etiketler:</li>
                                            @foreach ($project->tags as $tag)
                                                <li class="tag"><a href="{{ route('projeler') }}">#{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="social-icons">
                                    <ul>
                                        <li class="text">Paylaş:</li>
                                        <li class="icon">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="Facebook'ta paylaş">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="icon">
                                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($project->title) }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="X'te paylaş">
                                                <i class="fa-brands fa-x-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="icon">
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="LinkedIn'de paylaş">
                                                <i class="fa-brands fa-linkedin-in"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="sidebar-area ml-30 md:ml-0 sm:ml-0 md:mt-40 sm:mt-40">
                        @if ($facts->isNotEmpty() || $technologies !== [] || filled($project->project_url))
                            <div class="_sidebar-widget _portfolio">
                                <h3>Proje Künyesi</h3>

                                @if ($facts->isNotEmpty())
                                    <div class="portfolio-list">
                                        <ul>
                                            @foreach ($facts as [$label, $value, $url])
                                                <li>
                                                    {{ $label }}:
                                                    <span>
                                                        @if ($url)
                                                            <a href="{{ $url }}">{{ $value }}</a>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($technologies !== [])
                                    <div class="project-tech mt-20">
                                        <h4>Kullanılan Teknolojiler</h4>
                                        <ul class="project-tech-list mt-10">
                                            @foreach ($technologies as $technology)
                                                <li>{{ $technology }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (filled($project->project_url))
                                    <div class="button mt-20">
                                        <a class="theme-btn3" href="{{ $project->project_url }}" target="_blank"
                                            rel="noopener noreferrer">
                                            Siteyi Görüntüle
                                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                                            <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="_sidebar-widget _contact mt-40">
                            <h3>Benzer bir proje mi planlıyorsunuz?</h3>
                            <p class="mt-10">İhtiyacınızı anlatın, size uygun kurguyu birlikte çıkaralım.</p>
                            <div class="button mt-20">
                                <a class="theme-btn3" href="{{ route('iletisim') }}">
                                    Teklif Alın
                                    <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                                    <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== PORTFOLIO DETAILS AREA END =====-->

    @if ($related->isNotEmpty())
        <!--===== RELATED PROJECTS START =====-->

        <div class="portfolio sp sec-bg1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 m-auto text-center">
                        <div class="heading2">
                            <h2>Benzer İşler</h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($related as $relatedProject)
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                            @include('pages.projects.partials.card', ['project' => $relatedProject])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!--===== RELATED PROJECTS END =====-->
    @endif

    @include('pages.projects.partials.cta')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/pages/project/show.js') }}"></script>
@endpush
