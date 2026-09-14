{{--
    Proje kartı — üç yerde kullanılır: liste ızgarası, detaydaki "Benzer
    İşler" ve hizmet detayındaki "bu hizmette yaptığımız işler". Tek kaynak
    olduğu için kart görünümü bir yerde değişince üçü birlikte değişir.

    $project  App\Models\Project\Project
--}}
@php
    $cardUrl = $project->publicUrl() ?? route('projeler.show', $project->slug);
    $cardCover = $project->getFirstMedia('cover');
    $cardLabel = $project->category?->name ?: $project->sector;
@endphp

<div class="portfolio-box">
    @if ($cardCover)
        <div class="image-area">
            <div class="image">
                <img src="{{ $cardCover->url('medium') }}" alt="{{ $project->title }}">
            </div>
            <a href="{{ $cardUrl }}" class="arrow" aria-label="{{ $project->title }}">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    @endif
    <div class="content-area">
        @if (filled($cardLabel))
            <span>{{ $cardLabel }}</span>
        @endif
        <a href="{{ $cardUrl }}">{{ $project->title }}</a>
    </div>
</div>
