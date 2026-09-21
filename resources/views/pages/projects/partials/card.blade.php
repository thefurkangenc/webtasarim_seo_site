{{--
    Proje kartı — üç yerde kullanılır: liste ızgarası, detaydaki "Benzer
    İşler" ve hizmet detayındaki "bu hizmette yaptığımız işler". Tek kaynak
    olduğu için kart görünümü bir yerde değişince üçü birlikte değişir.
    CSS'i paylaşan üç sayfa da `assets/css/pages/project/card.css`'i yükler.

    $project  App\Models\Project\Project
--}}
@php
    $cardUrl = $project->publicUrl() ?? route('projeler.show', $project->slug);
    $cardCover = $project->getFirstMedia('cover');
    $cardLabel = $project->category?->name ?: $project->sector;

    // Kategoriye sabit bir ton ver ki aynı kategori her kartta aynı renkte
    // görünsün (liste, benzer işler, hizmet detayı fark etmez) — detay
    // sayfasındaki istatistik kartlarıyla aynı dört tonluk palet.
    $cardAccents = ['#155FFF', '#FD6543', '#11819B', '#C98A2E'];
    $cardAccent = $cardAccents[($project->category?->id ?? 0) % count($cardAccents)];

    $cardResult = $project->resultRows()[0] ?? null;
@endphp

<div class="project-card" style="--card-accent: {{ $cardAccent }}">
    <a href="{{ $cardUrl }}" class="project-card-media" aria-label="{{ $project->title }}">
        @if ($cardCover)
            <img src="{{ $cardCover->url('medium') }}" alt="{{ $project->title }}">
        @endif
        @if (filled($cardLabel))
            <span class="project-card-tag">{{ $cardLabel }}</span>
        @endif
    </a>
    <div class="project-card-body">
        <a href="{{ $cardUrl }}" class="project-card-title">{{ $project->title }}</a>

        @if (filled($project->client_name))
            <p class="project-card-client">{{ Str::limit($project->excerpt, 135, '...') }}</p>
        @endif

        @if ($cardResult)
            <div class="project-card-result">
                <span class="project-card-result-icon">
                    @if ($cardResult['direction'] === 'up')
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    @elseif ($cardResult['direction'] === 'down')
                        <i class="fa-solid fa-arrow-trend-down"></i>
                    @else
                        <i class="fa-solid fa-minus"></i>
                    @endif
                </span>
                <span class="project-card-result-value">{{ $cardResult['value'] }}</span>
                <span class="project-card-result-label">{{ $cardResult['label'] }}</span>
            </div>
        @endif

        <a href="{{ $cardUrl }}" class="project-card-link">
            Çalışmayı incele
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</div>
