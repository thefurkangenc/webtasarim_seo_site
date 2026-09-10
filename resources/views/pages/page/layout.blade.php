{{--
    Dinamik sayfaların ortak kabuğu. Üç şablon (default / wide / sidebar)
    bunu genişletir ve yalnızca `page.body` bölümünü doldurur; başlık, meta
    etiketleri, üst görsel, kırılım ve SSS bloğu burada tek yerde durur.

    Hangi şablonun hangi Blade'e karşılık geldiği config/pages.php'de.
--}}
@extends('layout.app')

@php($seo = $page->seoMeta())

@section('title', $seo['title'] ?: $page->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])

@section('content')
    @include('pages.page.partials.hero')

    @yield('page.body')

    @include('pages.page.partials.faqs')
@endsection

@push('css')
    {{-- .legal-content zengin metin tipografisi; .page-nav yan menü. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/legal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/page.css') }}">
@endpush
