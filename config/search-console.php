<?php

/*
| Google Search Console paneli. Kimlik bilgisi burada DEĞİL: GA4 ile aynı
| service account JSON'u (`settings` grubu `analytics`) kullanılır; bu dosya
| sabit tanımları ve Google'ın döndürdüğü kodların Türkçe karşılıklarını tutar.
| Seçilen site adresi `settings` grubu `search_console` > `site_url`'de durur.
*/

return [

    /*
    | Search Console verisi canlı değildir, yaklaşık 2 gün gecikmelidir. Rapor
    | aralığının bitişi bu kadar gün geriye çekilir; aksi halde son günler boş
    | görünür ve önceki dönemle kıyas yanlış çıkar.
    */
    'lag_days' => 2,

    // Panelde seçilebilen gün aralıkları.
    'ranges' => [7, 28, 90],

    // Rapor cache süresi (dakika) — veri günlük güncellendiği için uzun tutulur.
    'cache_minutes' => 30,

    // Tablolarda gösterilecek satır sayıları.
    'row_limits' => [
        'queries' => 25,
        'pages' => 15,
        'countries' => 8,
    ],

    /*
    | Google'ın İngilizce kod/metinlerinin Türkçe karşılıkları. Listede olmayan
    | bir değer geldiğinde ham hali gösterilir — panel hiçbir zaman boş kalmaz.
    */
    'labels' => [

        'verdict' => [
            'PASS' => 'Sorun yok',
            'PARTIAL' => 'Kısmen sorunlu',
            'FAIL' => 'Sorunlu',
            'NEUTRAL' => 'Bilgi yok',
            'VERDICT_UNSPECIFIED' => 'Bilgi yok',
        ],

        'coverage' => [
            'Submitted and indexed' => 'Gönderildi ve indekslendi',
            'Indexed, not submitted in sitemap' => 'İndekslendi (site haritasında yok)',
            'Discovered - currently not indexed' => 'Keşfedildi, henüz indekslenmedi',
            'Crawled - currently not indexed' => 'Tarandı, henüz indekslenmedi',
            'URL is unknown to Google' => 'Google bu adresi hiç görmemiş',
            'Page with redirect' => 'Yönlendirme yapan sayfa',
            'Duplicate without user-selected canonical' => 'Kopya içerik (asıl sayfa belirtilmemiş)',
            'Duplicate, Google chose different canonical than user' => 'Kopya içerik (Google başka sayfayı asıl seçti)',
            'Alternate page with proper canonical tag' => 'Alternatif sayfa (asıl sayfası belirtilmiş)',
            'Excluded by ‘noindex’ tag' => '“İndekslenmesin” etiketiyle dışlandı',
            'Blocked by robots.txt' => 'robots.txt ile engellendi',
            'Blocked due to unauthorized request (401)' => 'Yetkisiz istek nedeniyle engellendi (401)',
            'Not found (404)' => 'Bulunamadı (404)',
            'Soft 404' => 'Yumuşak 404 (boş/anlamsız sayfa)',
            'Server error (5xx)' => 'Sunucu hatası (5xx)',
        ],

        'robots' => [
            'ALLOWED' => 'İzin veriliyor',
            'DISALLOWED' => 'robots.txt engelliyor',
            'ROBOTS_TXT_STATE_UNSPECIFIED' => 'Bilgi yok',
        ],

        'indexing' => [
            'INDEXING_ALLOWED' => 'İndekslenmesine izin veriliyor',
            'BLOCKED_BY_META_TAG' => 'Sayfadaki etiket engelliyor',
            'BLOCKED_BY_HTTP_HEADER' => 'Sunucu başlığı engelliyor',
            'BLOCKED_BY_ROBOTS_TXT' => 'robots.txt engelliyor',
            'INDEXING_STATE_UNSPECIFIED' => 'Bilgi yok',
        ],

        'fetch' => [
            'SUCCESSFUL' => 'Sayfa başarıyla alındı',
            'SOFT_404' => 'Yumuşak 404',
            'BLOCKED_ROBOTS_TXT' => 'robots.txt engelledi',
            'NOT_FOUND' => 'Bulunamadı (404)',
            'ACCESS_DENIED' => 'Erişim reddedildi (401)',
            'ACCESS_FORBIDDEN' => 'Erişim yasak (403)',
            'SERVER_ERROR' => 'Sunucu hatası (5xx)',
            'REDIRECT_ERROR' => 'Yönlendirme hatası',
            'BLOCKED_4XX' => 'İstemci hatası (4xx)',
            'INTERNAL_CRAWL_ERROR' => 'Google tarafında tarama hatası',
            'INVALID_URL' => 'Geçersiz adres',
            'PAGE_FETCH_STATE_UNSPECIFIED' => 'Bilgi yok',
        ],

        'device' => [
            'DESKTOP' => 'Masaüstü',
            'MOBILE' => 'Mobil',
            'TABLET' => 'Tablet',
        ],

        // Search Console ülkeyi 3 harfli kodla döndürür.
        'country' => [
            'tur' => 'Türkiye',
            'usa' => 'ABD',
            'deu' => 'Almanya',
            'gbr' => 'Birleşik Krallık',
            'nld' => 'Hollanda',
            'fra' => 'Fransa',
            'bel' => 'Belçika',
            'aut' => 'Avusturya',
            'che' => 'İsviçre',
            'swe' => 'İsveç',
            'dnk' => 'Danimarka',
            'nor' => 'Norveç',
            'ita' => 'İtalya',
            'esp' => 'İspanya',
            'rus' => 'Rusya',
            'ukr' => 'Ukrayna',
            'aze' => 'Azerbaycan',
            'kaz' => 'Kazakistan',
            'geo' => 'Gürcistan',
            'irn' => 'İran',
            'irq' => 'Irak',
            'sau' => 'Suudi Arabistan',
            'are' => 'Birleşik Arap Emirlikleri',
            'qat' => 'Katar',
            'kwt' => 'Kuveyt',
            'egy' => 'Mısır',
            'mar' => 'Fas',
            'dza' => 'Cezayir',
            'bgr' => 'Bulgaristan',
            'grc' => 'Yunanistan',
            'rou' => 'Romanya',
            'pol' => 'Polonya',
            'cyp' => 'Kıbrıs',
            'ind' => 'Hindistan',
            'pak' => 'Pakistan',
            'chn' => 'Çin',
            'jpn' => 'Japonya',
            'kor' => 'Güney Kore',
            'can' => 'Kanada',
            'aus' => 'Avustralya',
            'bra' => 'Brezilya',
            'zaf' => 'Güney Afrika',
        ],
    ],
];
