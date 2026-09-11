@extends('layout.app')
@section('title', 'Bülten aboneliği')
@section('content')
    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>Abonelik iptal edildi</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <p>{{ $email }} adresinin bülten aboneliği durduruldu. İsterseniz sitedeki formdan yeniden kaydolabilirsiniz.</p>
                    <a class="theme-btn3 mt-20" href="{{ route('anasayfa') }}">Ana sayfaya dön</a>
                </div>
            </div>
        </div>
    </div>
@endsection
