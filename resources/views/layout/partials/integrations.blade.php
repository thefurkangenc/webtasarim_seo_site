@php
    $widgets = app(\App\Services\Integration\IntegrationService::class)->frontend();
@endphp

@if ($widgets !== [])
    @if (isset($widgets['whatsapp']))
        <a href="{{ $widgets['whatsapp']['url'] }}" target="_blank" rel="noopener noreferrer"
            class="site-fab"
            style="{{ ($widgets['whatsapp']['position'] ?? 'right') === 'left' ? 'left' : 'right' }}: 30px; bottom: {{ 100 + (int) ($widgets['whatsapp']['offset'] ?? 0) }}px;"
            aria-label="WhatsApp ile yazın">
            <img src="{{ asset('admin/assets/images/icons/integrations/whatsapp.svg') }}" alt="">
        </a>
    @endif

    @if (isset($widgets['phone']))
        <a href="{{ $widgets['phone']['url'] }}"
            class="site-fab"
            style="{{ ($widgets['phone']['position'] ?? 'right') === 'left' ? 'left' : 'right' }}: 30px; bottom: {{ 100 + (int) ($widgets['phone']['offset'] ?? 0) }}px;"
            aria-label="Telefon ile arayın">
            <img src="{{ asset('admin/assets/images/icons/integrations/phone.svg') }}" alt="">
        </a>
    @endif

    @if (isset($widgets['tawk']) && \App\Support\Consent::allows('functional'))
        <script>
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement('script'), s0 = document.getElementsByTagName('script')[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/'
                    + {!! \Illuminate\Support\Js::from($widgets['tawk']['property_id']) !!} + '/'
                    + {!! \Illuminate\Support\Js::from($widgets['tawk']['widget_id']) !!};
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    @endif
@endif
