{{--
    Sayfa üst bloğu. Arka plan, sayfaya bir üst görsel yüklendiyse o görsel,
    yüklenmediyse sitenin standart iç sayfa deseni olur. Kırılım kökten bu
    sayfaya kadar PageService::ancestors() zincirinden kurulur.
--}}
@php($heroImage = $page->mediaUrl('cover', 'medium'))

<div class="inner-hero" style="background-image: url({{ $heroImage ?? asset('assets/img/bg/hero12-bg1.png') }});">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 m-auto text-center">
                <div class="inner-main-heading">
                    <h1>{{ $page->title }}</h1>

                    @if (filled($page->excerpt))
                        <p class="page-hero-excerpt">{{ $page->excerpt }}</p>
                    @endif

                    <div class="breadcrumbs-pages">
                        <ul>
                            <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>

                            @foreach ($ancestors as $ancestor)
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ url($ancestor->path) }}">{{ $ancestor->title }}</a></li>
                            @endforeach

                            <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                            <li>{{ $page->title }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
