@php
    $banner = app(\App\Services\Cookie\CookieService::class)->banner();
@endphp

@if ($banner)
    @php $consent = $banner['consent']; @endphp

    <div class="cookie-banner" data-cookie-banner @if ($consent['decided']) hidden @endif>
        <div class="cookie-banner__bar">
            <div class="container">
                <div class="cookie-banner__inner">
                    <div class="cookie-banner__copy">
                        <p class="cookie-banner__title">{{ $banner['title'] }}</p>
                        <p class="cookie-banner__text">{!! $banner['description'] !!}</p>
                    </div>
                    <div class="cookie-banner__actions">
                        <button type="button" class="cookie-btn cookie-btn--ghost" data-cookie-reject>
                            {{ $banner['reject_label'] }}
                        </button>
                        <button type="button" class="cookie-btn cookie-btn--ghost" data-cookie-customize>
                            {{ $banner['customize_label'] }}
                        </button>
                        <button type="button" class="cookie-btn cookie-btn--solid" data-cookie-accept>
                            {{ $banner['accept_label'] }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="cookie-banner__panel" data-cookie-panel hidden>
            <div class="container">
                <div class="cookie-banner__panel-inner">
                    <label class="cookie-option">
                        <span>
                            <strong>{{ $banner['necessary_title'] }}</strong>
                            <span>{{ $banner['necessary_description'] }}</span>
                        </span>
                        <input type="checkbox" checked disabled>
                    </label>
                    <label class="cookie-option">
                        <span>
                            <strong>{{ $banner['functional_title'] }}</strong>
                            <span>{{ $banner['functional_description'] }}</span>
                        </span>
                        <input type="checkbox" data-cookie-cat="functional" @checked($consent['functional'])>
                    </label>
                    <label class="cookie-option">
                        <span>
                            <strong>{{ $banner['analytics_title'] }}</strong>
                            <span>{{ $banner['analytics_description'] }}</span>
                        </span>
                        <input type="checkbox" data-cookie-cat="analytics" @checked($consent['analytics'])>
                    </label>
                    <label class="cookie-option">
                        <span>
                            <strong>{{ $banner['marketing_title'] }}</strong>
                            <span>{{ $banner['marketing_description'] }}</span>
                        </span>
                        <input type="checkbox" data-cookie-cat="marketing" @checked($consent['marketing'])>
                    </label>
                    <div class="cookie-banner__panel-actions">
                        <button type="button" class="cookie-btn cookie-btn--solid" data-cookie-save>
                            {{ $banner['save_label'] }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="cookie-settings" data-cookie-open @if (! $consent['decided']) hidden @endif>
        Çerez ayarları
    </button>

    <script type="application/json" id="cookie-banner-config">{!! json_encode($banner['config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
