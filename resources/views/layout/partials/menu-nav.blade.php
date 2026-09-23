{{--
    Üst menü (header) öğe ağacını Trezo teması (vl-header-area14) markup'ıyla
    basar. Özyinelemeli: her seviye kendini $depth + 1 ile yeniden include eder.

    $items  MenuRenderer::render('header') çıktısı
    $depth  0 = kök, 1 = alt menü, 2 = alt-altı (tema en fazla 3 seviye açar)
    $parentLabel  üst öğenin adı — alt <ul> için aria-label

    Mobil menü ayrıca kodlanmaz — front-end main.js bu <ul>'yi klonlayıp
    .vl-offcanvas-menu içine kopyalar.
--}}
@php
    $depth = $depth ?? 0;
    $parentLabel = $parentLabel ?? null;
    $ulClass = match (true) {
        $depth === 1 => 'sub-menu',
        $depth >= 2 => 'sub-menu menu1',
        default => null,
    };
@endphp

<ul
    @if ($ulClass) class="{{ $ulClass }}" @endif
    @if ($parentLabel) aria-label="{{ $parentLabel }} alt menüsü" @endif
>
    @foreach ($items as $item)
        @php
            $hasChildren = ! empty($item['children']);
            $liClass = trim(($hasChildren && $depth === 0 ? 'has-dropdown ' : '') . ($item['active'] ? 'current-menu-item' : ''));
            $aClass = trim(($hasChildren && $depth >= 1 ? 'span-arrow ' : '') . ($item['active'] && $depth === 0 ? 'active' : ''));
            $ariaLabel = $item['label'];
            if ($item['target'] === '_blank') {
                $ariaLabel .= ' (yeni sekmede açılır)';
            } elseif ($hasChildren) {
                $ariaLabel .= ' — alt menü';
            }
        @endphp

        <li @if ($liClass) class="{{ $liClass }}" @endif>
            <a href="{{ $item['url'] }}"
                aria-label="{{ $ariaLabel }}"
                @if ($item['active']) aria-current="page" @endif
                @if ($hasChildren) aria-haspopup="true" @endif
                @if ($aClass) class="{{ $aClass }}" @endif
                @if ($item['target'] === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                {{ $item['label'] }}
                @if ($hasChildren)
                    <span aria-hidden="true"><i class="{{ $depth === 0 ? 'fa-regular fa-angle-down' : 'fa-solid fa-angle-right d-lg-block d-none' }}"></i></span>
                @endif
            </a>

            @if ($hasChildren)
                @include('layout.partials.menu-nav', [
                    'items' => $item['children'],
                    'depth' => $depth + 1,
                    'parentLabel' => $item['label'],
                ])
            @endif
        </li>
    @endforeach
</ul>
