@php
    $tracking = \App\Support\Settings::group('tracking');
    $placement = $placement ?? 'head';
    $consent = \App\Support\Consent::snapshot();
    $analytics = $consent['analytics'];
    $marketing = $consent['marketing'];
    $optional = $analytics || $marketing;
@endphp

@if ($placement === 'head')
    @if (filled($tracking['google_site_verification'] ?? null))
        <meta name="google-site-verification" content="{{ $tracking['google_site_verification'] }}">
    @endif
    @if (filled($tracking['yandex_verification'] ?? null))
        <meta name="yandex-verification" content="{{ $tracking['yandex_verification'] }}">
    @endif
    @if (filled($tracking['bing_verification'] ?? null))
        <meta name="msvalidate.01" content="{{ $tracking['bing_verification'] }}">
    @endif

    @if ($analytics && filled($tracking['gtm_id'] ?? null))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer',{!! \Illuminate\Support\Js::from($tracking['gtm_id']) !!});</script>
    @endif

    @if ($analytics && filled($tracking['ga4_id'] ?? null) && blank($tracking['gtm_id'] ?? null))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($tracking['ga4_id']) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', {!! \Illuminate\Support\Js::from($tracking['ga4_id']) !!});
        </script>
    @endif

    @if ($optional)
        {!! $tracking['head_scripts'] ?? '' !!}
    @endif
@elseif ($placement === 'body')
    @if ($analytics && filled($tracking['gtm_id'] ?? null))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode($tracking['gtm_id']) }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @if ($optional)
        {!! $tracking['body_scripts'] ?? '' !!}
    @endif
@elseif ($placement === 'foot')
    @if ($marketing && filled($tracking['meta_pixel_id'] ?? null))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', {!! \Illuminate\Support\Js::from($tracking['meta_pixel_id']) !!});
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ urlencode($tracking['meta_pixel_id']) }}&ev=PageView&noscript=1" alt=""></noscript>
    @endif

    @if ($analytics && filled($tracking['yandex_metrica_id'] ?? null))
        <script>
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
            (window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
            ym({!! \Illuminate\Support\Js::from((int) $tracking['yandex_metrica_id']) !!}, 'init', {clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true});
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/{{ urlencode($tracking['yandex_metrica_id']) }}" style="position:absolute; left:-9999px;" alt=""></div></noscript>
    @endif

    @if ($marketing && filled($tracking['bing_uet_id'] ?? null))
        <script>
            (function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:{!! \Illuminate\Support\Js::from($tracking['bing_uet_id']) !!}};
            o.q=w[u],w[u]=new UET(o),w[u].push('pageLoad')},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){
            var s=this.readyState;s&&s!=='loaded'&&s!=='complete'||(f(),n.onload=n.onreadystatechange=null)};
            i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,'script','//bat.bing.com/bat.js','uetq');
        </script>
    @endif

    @if ($marketing && filled($tracking['tiktok_pixel_id'] ?? null))
        <script>
            !function (w, d, t) {w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
            ttq.load({!! \Illuminate\Support\Js::from($tracking['tiktok_pixel_id']) !!});
            ttq.page();
            }(window, document, 'ttq');
        </script>
    @endif

    @if ($marketing && filled($tracking['linkedin_partner_id'] ?? null))
        <script>
            _linkedin_partner_id = {!! \Illuminate\Support\Js::from($tracking['linkedin_partner_id']) !!};
            window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
            window._linkedin_data_partner_ids.push(_linkedin_partner_id);
        </script>
        <script async src="https://snap.licdn.com/li.lms-analytics/insight.min.js"></script>
        <noscript><img height="1" width="1" style="display:none;" alt=""
            src="https://px.ads.linkedin.com/collect/?pid={{ urlencode($tracking['linkedin_partner_id']) }}&fmt=gif"></noscript>
    @endif
@endif
