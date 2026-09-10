@extends('layout.app')

@php
    $seo = $blog->seoMeta();
    $cover = $blog->getFirstMedia('cover');
    $publishedAt = $blog->published_at ?? $blog->created_at;
    $shareUrl = urlencode((string) $blog->publicUrl());
@endphp

@section('title', $seo['title'] ?: $blog->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/blog/show.css') }}">
@endpush

@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $blog->title }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('blog') }}">Blog</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ $blog->title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--===== BLOG DETAILS AREA START =====-->

    <div class="blog-details-area sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto">
                    <div class="blog-details-content">
                        <article>
                            <div class="details-content">
                                @if ($cover)
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url() }}" alt="{{ $blog->title }}">
                                    </div>
                                @endif
                                <div class="vl-blog12-meta mt-24">
                                    @if ($publishedAt)
                                        <a href="{{ $blog->publicUrl() }}" class="date"><img
                                                src="{{ asset('assets/img/icons/date1.svg') }}" alt="">
                                            {{ $publishedAt->translatedFormat('d F Y') }}</a>
                                    @endif
                                    @if ($blog->author)
                                        <a href="{{ $blog->publicUrl() }}" class="author"><img
                                                src="{{ asset('assets/img/icons/author1.svg') }}" alt="">
                                            {{ $blog->author->name }}</a>
                                    @endif
                                </div>
                                <div class="heading2 mt-24">
                                    <h3>{{ $blog->title }}</h3>
                                </div>
                                @if (filled($blog->content))
                                    <div class="details-body mt-16">
                                        {!! $blog->content !!}
                                    </div>
                                @endif
                            </div>
                        </article>

                        @if ($blog->faqs->isNotEmpty())
                            <div class="research-faq mt-40">
                                <h3>Sıkça Sorulan Sorular</h3>
                                <div class="accordion accordion1" id="blog-faq-{{ $blog->id }}">
                                    @foreach ($blog->faqs as $index => $faq)
                                        <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#blog-{{ $blog->id }}-faq-{{ $faq->id }}"
                                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                    aria-controls="blog-{{ $blog->id }}-faq-{{ $faq->id }}">
                                                    {{ $faq->question }}
                                                </button>
                                            </h2>
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

                        <div class="details-border"></div>
                        <div class="details-content">
                            <div class="details-social-tags">
                                @if ($blog->tags->isNotEmpty())
                                    <div class="tags">
                                        <ul>
                                            <li class="text">Etiketler:</li>
                                            @foreach ($blog->tags as $tag)
                                                <li class="tag"><a href="{{ route('blog') }}">#{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="social-icons">
                                    <ul>
                                        <li class="text">Paylaş:</li>
                                        <li class="icon"><a
                                                href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer"><i
                                                    class="fa-brands fa-facebook-f"></i></a>
                                        </li>
                                        <li class="icon"><a
                                                href="https://www.instagram.com/"
                                                target="_blank" rel="noopener noreferrer"><i
                                                    class="fa-brands fa-instagram"></i></a></li>
                                        <li class="icon"><a
                                                href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($blog->title) }}"
                                                target="_blank" rel="noopener noreferrer"><i
                                                    class="fa-brands fa-x-twitter"></i></a></li>
                                        <li class="icon"><a
                                                href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer"><i
                                                    class="fa-brands fa-linkedin-in"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="details-border"></div>

                        <div class="comment-area heading2">
                            <h3>Blog Comments (2)</h3>
                            <div class="details-single-comment mt-30">
                                <div class="top-area">
                                    <div class="author-area">
                                        <div class="author-image">
                                            <img src="{{ asset('assets/img/blog/comment-box-image1.png') }}" alt="">
                                        </div>
                                        <div class="text">
                                            <a href="#" class="date"><img src="{{ asset('assets/img/icons/date1.svg') }}"
                                                    alt=""> 8 December 2025</a>
                                            <h4><a href="#">Alex Robertson</a></h4>
                                        </div>
                                    </div>
                                    <div class="reply">
                                        <a href="#"><i class="fa-solid fa-reply"></i> Reply</a>
                                    </div>
                                </div>
                                <p>In today’s dynamic business landscape, organizations face numerous challenges that
                                    require strategic thinking and expert guidance. Business consulting serves as a crucial
                                    resource, providing companies with the insights an tools necessary.</p>
                            </div>

                            <div class="details-single-comment mt-30 ml-30 sm:ml-0">
                                <div class="top-area">
                                    <div class="author-area">
                                        <div class="author-image">
                                            <img src="{{ asset('assets/img/blog/comment-box-image2.png') }}" alt="">
                                        </div>
                                        <div class="text">
                                            <a href="#" class="date"><img src="{{ asset('assets/img/icons/date1.svg') }}"
                                                    alt=""> 8 December 2025</a>
                                            <h4><a href="#">Theo Hernandez</a></h4>
                                        </div>
                                    </div>
                                    <div class="reply">
                                        <a href="#"><i class="fa-solid fa-reply"></i> Reply</a>
                                    </div>
                                </div>
                                <p>At Advicx, our consulting services are tailored to meet the unique needs of each client,
                                    focusing on areas such as operational efficiency, market expansion, and digital
                                    transformation. By leveraging data analytics and.</p>
                            </div>
                        </div>


                        <div class="contact-details-form heading2 mt-40">
                            <h3>Leave a Reply</h3>
                            <p class="mt-12">Provide clear contact information, including phone number, email, and
                                address.</p>
                            <form action="#">
                                <div class="row mt-16">
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="email" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="number" placeholder="Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <select class="wide">
                                                <option value="1">Service Type</option>
                                                <option value="2">Option 1</option>
                                                <option value="3">Option 2</option>
                                                <option value="4">Option 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <textarea rows="5" placeholder="How can we help you?"></textarea>
                                        </div>
                                        <div class="button mt-30">
                                            <button class="theme-btn3" type="submit">Send <span class="arrow1"><i
                                                        class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                        class="fa-solid fa-arrow-right"></i></span></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== BLOG DETAILS AREA END =====-->

    @if ($related->isNotEmpty())
        <!--===== BLOG AREA START =====-->

        <div class="blog sp sec-bg1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 m-auto text-center">
                        <div class="heading2">
                            <h2>Diğer Yazılar</h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($related as $relatedBlog)
                        @php
                            $relatedCover = $relatedBlog->getFirstMedia('cover');
                            $relatedDate = $relatedBlog->published_at ?? $relatedBlog->created_at;
                        @endphp
                        <div class="col-lg-6">
                            <div class="vl-blog-11-item mt-30" data-aos="fade-up" data-aos-duration="900">
                                @if ($relatedCover)
                                    <div class=" vl-blog-11-thumb image-anime overflow-hidden _relative">
                                        <a href="{{ $relatedBlog->publicUrl() }}">
                                            <img class="w-full" src="{{ $relatedCover->url() }}"
                                                alt="{{ $relatedBlog->title }}">
                                        </a>
                                    </div>
                                @endif
                                <div class="vl-blog-11-content heading2">
                                    <div class="vl-blog11-meta pb-16">
                                        @if ($relatedDate)
                                            <a href="{{ $relatedBlog->publicUrl() }}" class="date"><img
                                                    src="{{ asset('assets/img/icons/date1.svg') }}" alt="">
                                                {{ $relatedDate->translatedFormat('d.m.Y') }}</a>
                                        @endif
                                        @if ($relatedBlog->author)
                                            <a href="{{ $relatedBlog->publicUrl() }}" class="author"><img
                                                    src="{{ asset('assets/img/icons/author1.svg') }}" alt="">
                                                {{ $relatedBlog->author->name }}</a>
                                        @endif
                                    </div>
                                    <h4><a href="{{ $relatedBlog->publicUrl() }}">{{ $relatedBlog->title }}</a></h4>
                                    <a href="{{ $relatedBlog->publicUrl() }}" class="learn">Devamını Oku <span
                                            class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                            class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!--===== BLOG AREA END =====-->
    @endif
@endsection
