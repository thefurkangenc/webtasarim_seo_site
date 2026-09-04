@extends('layout.app')
@section('title', $title)

@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $title }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ $title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <div class="legal-content">
                        @if (filled($content))
                            {!! $content !!}
                        @else
                            <p>Bu sayfa henüz hazırlanmadı.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/legal.css') }}">
@endpush
