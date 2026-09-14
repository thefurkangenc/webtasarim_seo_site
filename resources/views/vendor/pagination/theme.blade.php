{{--
    Ön yüz sayfalaması — temanın .theme-pagination markup'ı.

    Laravel'in varsayılan paginator view'leri Tailwind içindir; ön yüz
    Bootstrap olduğu için olduğu gibi kullanılsa bozuk görünür. Kullanımı:

        {{ $projects->links('vendor.pagination.theme') }}

    Sayfa numaraları temada iki hanelidir (01, 02) — sprintf onu korur.
--}}
@if ($paginator->hasPages())
    <div class="theme-pagination text-center">
        <ul>
            @if (! $paginator->onFirstPage())
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Önceki sayfa">
                        <i class="fa-solid fa-angle-left"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            <a class="{{ $page == $paginator->currentPage() ? 'active' : '' }}" href="{{ $url }}">
                                {{ sprintf('%02d', $page) }}
                            </a>
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Sonraki sayfa">
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </li>
            @endif
        </ul>
    </div>
@endif
