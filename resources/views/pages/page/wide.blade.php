{{--
    Tam genişlik şablonu: içerik konteynerin tamamını kullanır. Tablo, galeri
    ve gömülü içerik (harita, video, iframe) barındıran sayfalar için.
--}}
@extends('pages.page.layout')

@section('page.body')
    <div class="sp">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="legal-content page-content-wide">
                        @if (filled($page->content))
                            {!! $page->content !!}
                        @else
                            <p>Bu sayfa henüz hazırlanmadı.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
