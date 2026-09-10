{{--
    Üst menü (header) öğe ağacını Trezo teması (vl-header-area14) markup'ıyla
    basar. Özyinelemeli: her seviye kendini $depth + 1 ile yeniden include eder.

    $items  MenuRenderer::render('header') çıktısı
    $depth  0 = kök, 1 = alt menü, 2 = alt-altı (tema en fazla 3 seviye açar)

    Mobil menü ayrıca kodlanmaz — front-end main.js bu <ul>'yi klonlayıp
    .vl-offcanvas-menu içine kopyalar.
--}}
@php($depth = $depth ?? 0)
@php($ulClass = match (true) {
    $depth === 1 => 'sub-menu',
    $depth >= 2 => 'sub-menu menu1',
    default => null,
})

<ul @if ($ulClass) class="{{ $ulClass }}" @endif>
    @foreach ($items as $item)
        @php($hasChildren = ! empty($item['children']))
        @php($liClass = trim(($hasChildren && $depth === 0 ? 'has-dropdown ' : '') . ($item['active'] ? 'current-menu-item' : '')))
        @php($aClass = trim(($hasChildren && $depth >= 1 ? 'span-arrow ' : '') . ($item['active'] && $depth === 0 ? 'active' : '')))

        <li @if ($liClass) class="{{ $liClass }}" @endif>
            <a href="{{ $item['url'] }}" @if ($aClass) class="{{ $aClass }}" @endif
                @if ($item['target'] === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                {{ $item['label'] }}
                @if ($hasChildren)
                    <span><i class="{{ $depth === 0 ? 'fa-regular fa-angle-down' : 'fa-solid fa-angle-right d-lg-block d-none' }}"></i></span>
                @endif
            </a>

            @if ($hasChildren)
                @include('layout.partials.menu-nav', ['items' => $item['children'], 'depth' => $depth + 1])
            @endif
        </li>
    @endforeach
</ul>
