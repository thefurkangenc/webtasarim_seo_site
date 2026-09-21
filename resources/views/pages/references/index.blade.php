@extends('layout.app')

@section('title', 'Referanslar')
@section('meta_description', 'Gaziantep Web Tasarım Ajansı referansları, Gaziantep Web Tasarım Ajansı Kimlerle çalıştı?')
@section('meta_keywords', 'gaziantep web tasarım ajansı, referanslar, kimlerle çalıştı?')

@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>Referanslar</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>Referanslar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.references.partials.section', [
        'references' => $references,
        'showEyebrow' => false,
    ])
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/references/index.css') }}">
@endpush
