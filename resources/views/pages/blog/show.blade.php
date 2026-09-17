@extends('layout.app')

@php
    $seo = $blog->seoMeta();
    $cover = $blog->getFirstMedia('cover');
    $publishedAt = $blog->published_at ?? $blog->created_at;
    $wordCount = str_word_count(strip_tags((string) $blog->content));
    $readingMinutes = max(1, (int) ceil($wordCount / 200));
    $shareUrl = urlencode((string) $blog->publicUrl());
    $shareTitle = urlencode($blog->title);
@endphp

@section('title', $seo['title'] ?: $blog->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])

@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        @if ($blog->category)
                            <span class="blog-detail-hero__category">{{ $blog->category->name }}</span>
                        @endif
                        <h1>{{ $blog->title }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('blog') }}">Blog</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ Str::limit($blog->title, 48) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="blog-detail sp" aria-label="Blog yazısı">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="blog-detail__article">
                        @if ($cover)
                            <figure class="blog-detail__cover">
                                <img src="{{ $cover->url() }}" alt="{{ $blog->title }}" >
                            </figure>
                        @endif

                        <div class="blog-detail__meta">
                            @if ($publishedAt)
                                <time datetime="{{ $publishedAt->toDateString() }}">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    {{ \App\Support\DateFormat::long($publishedAt) }}
                                </time>
                            @endif
                            @if ($blog->author)
                                <span class="blog-detail__meta-author">
                                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                                    {{ $blog->author->name }}
                                </span>
                            @endif
                            <span class="blog-detail__meta-read">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                {{ $readingMinutes }} dk okuma
                            </span>
                        </div>

                        @if (filled($blog->excerpt))
                            <p class="blog-detail__lead">{{ $blog->excerpt }}</p>
                        @endif

                        @if (filled($blog->content))
                            <div class="blog-detail__body">
                                {!! $blog->content !!}
                            </div>
                        @endif

                        @if ($blog->faqs->isNotEmpty())
                            <div class="blog-detail__faq">
                                <h2>Sıkça Sorulan Sorular</h2>
                                <div class="accordion" id="blog-faq-{{ $blog->id }}">
                                    @foreach ($blog->faqs as $index => $faq)
                                        <div class="accordion-item">
                                            <h3 class="accordion-header">
                                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#blog-{{ $blog->id }}-faq-{{ $faq->id }}"
                                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                    aria-controls="blog-{{ $blog->id }}-faq-{{ $faq->id }}">
                                                    {{ $faq->question }}
                                                </button>
                                            </h3>
                                            <div id="blog-{{ $blog->id }}-faq-{{ $faq->id }}"
                                                class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                                data-bs-parent="#blog-faq-{{ $blog->id }}">
                                                <div class="accordion-body">
                                                    {!! $faq->answer !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <footer class="blog-detail__footer">
                            @if ($blog->tags->isNotEmpty())
                                <div class="blog-detail__tags">
                                    <span class="blog-detail__tags-label">Etiketler</span>
                                    <ul>
                                        @foreach ($blog->tags as $tag)
                                            <li><a href="{{ route('blog') }}">#{{ $tag->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="blog-detail-share blog-detail-share--inline">
                                <span class="blog-detail__tags-label">Paylaş</span>
                                <div class="blog-detail-share__icons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook'ta paylaş"><i class="fa-brands fa-facebook-f"></i></a>
                                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener noreferrer" aria-label="X'te paylaş"><i class="fa-brands fa-x-twitter"></i></a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn'de paylaş"><i class="fa-brands fa-linkedin-in"></i></a>
                                    <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . $blog->publicUrl()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp'ta paylaş"><i class="fa-brands fa-whatsapp"></i></a>
                                </div>
                            </div>
                        </footer>
                    </article>
                </div>

                <div class="col-lg-4">
                    @include('pages.blog.partials.sidebar', ['blog' => $blog, 'readingMinutes' => $readingMinutes])
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="blog-list blog-list--related sp sec-bg1" aria-label="Diğer yazılar">
            <div class="container">
                <div class="blog-list__head text-center">
                    <h2 class="blog-list__related-title">Diğer Yazılar</h2>
                    <p class="blog-list__lead">İlginizi çekebilecek diğer rehber ve içgörüler.</p>
                </div>
                <div class="row blog-list__grid">
                    @foreach ($related as $relatedBlog)
                        <div class="col-lg-4 col-md-6">
                            @include('pages.blog.partials.card', ['blog' => $relatedBlog])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('pages.blog.partials.cta')
@endsection
