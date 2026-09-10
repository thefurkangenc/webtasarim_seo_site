{{-- Varsayılan şablon: ortalanmış, okumaya uygun genişlikte tek metin sütunu. --}}
@extends('pages.page.layout')

@section('page.body')
    <div class="sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <div class="legal-content">
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
