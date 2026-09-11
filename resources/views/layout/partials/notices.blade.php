@php
    $notices = app(\App\Services\Notice\NoticeResolver::class)->forRequest(request());
    $bar = $notices['bar'];
    $popup = $notices['popup'];
@endphp

@if ($bar)
    <div class="site-bar site-bar--{{ $bar['tone'] }}" data-site-bar data-notice-id="{{ $bar['id'] }}" hidden>
        <div class="container">
            <div class="site-bar__inner">
                <p class="site-bar__message">{{ $bar['message'] }}</p>
                @if (filled($bar['button_url']) && filled($bar['button_label']))
                    <a class="site-bar__link" href="{{ $bar['button_url'] }}">{{ $bar['button_label'] }}</a>
                @endif
                <button type="button" class="site-bar__close" data-notice-close aria-label="Kapat">&times;</button>
            </div>
        </div>
    </div>
@endif

@if ($popup)
    <div class="site-popup" data-site-popup data-notice-id="{{ $popup['id'] }}" data-delay="{{ $popup['delay_seconds'] }}" hidden>
        <div class="site-popup__backdrop" data-notice-close></div>
        <div class="site-popup__card" role="dialog" aria-modal="true">
            <button type="button" class="site-popup__close" data-notice-close aria-label="Kapat">&times;</button>
            @if ($popup['image'])
                <img class="site-popup__image" src="{{ $popup['image'] }}" alt="">
            @endif
            <h2 class="site-popup__heading">{{ $popup['heading'] }}</h2>
            @if (filled($popup['body']))
                <p class="site-popup__body">{{ $popup['body'] }}</p>
            @endif
            @if (filled($popup['button_url']) && filled($popup['button_label']))
                <a class="theme-btn3 site-popup__cta" href="{{ $popup['button_url'] }}">{{ $popup['button_label'] }}</a>
            @endif
            @if ($popup['collect_email'])
                @include('layout.partials.subscribe-form', ['source' => 'popup'])
            @endif
        </div>
    </div>
@endif
