{{--
    Yan menülü şablon: sağda aynı bölümdeki sayfaların listesi durur.

    Bölüm PageService::sectionNavigation() tarafından belirlenir — sayfanın alt
    sayfaları varsa onlar, yoksa kardeşleri listelenir. Kök seviyedeki çocuksuz
    bir sayfada gösterilecek bölüm olmadığı için $section null gelir ve içerik
    tek sütuna yayılır.
--}}
@extends('pages.page.layout')

@section('page.body')
    <div class="sp">
        <div class="container">
            <div class="row">
                <div class="{{ $section ? 'col-lg-8' : 'col-lg-10 m-auto' }}">
                    <div class="legal-content">
                        @if (filled($page->content))
                            {!! $page->content !!}
                        @else
                            <p>Bu sayfa henüz hazırlanmadı.</p>
                        @endif
                    </div>
                </div>

                @if ($section)
                    <div class="col-lg-4">
                        <aside class="page-nav">
                            <h4 class="page-nav-title">
                                @if ($section['url'])
                                    <a href="{{ $section['url'] }}">{{ $section['title'] }}</a>
                                @else
                                    {{ $section['title'] }}
                                @endif
                            </h4>

                            <ul class="page-nav-list">
                                @foreach ($section['items'] as $item)
                                    <li class="{{ $item->is($page) ? 'is-current' : '' }}">
                                        <a href="{{ url($item->path) }}">
                                            {{ $item->title }}
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
