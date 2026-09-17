@extends('layout.app')

@section('title', 'Blog')

@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>Blog</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>Blog</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="blog-list sp" aria-label="Blog yazıları">
        <div class="container">
            <div class="blog-list__head text-center">
                <p class="blog-list__lead">
                    Web tasarım, SEO ve dijital pazarlama üzerine güncel rehberler ve içgörüler.
                </p>
            </div>

            @if ($blogs->isEmpty())
                <p class="blog-list__empty text-center">Henüz yayınlanmış bir yazı bulunmuyor.</p>
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
