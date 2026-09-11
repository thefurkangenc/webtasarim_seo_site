<?php

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Models\ServiceRegion\ServiceRegion;

/*
| Site haritası (sitemap.xml) üretimi. Kullanıcı ayarları (aktif kaynaklar,
| hariç tutulan/ek adresler) `settings` tablosunda grup `sitemap` olarak
| tutulur — bu dosya sabit tanımları taşır.
*/

return [

    // storage/app/{path} — üretilen XML dosyalarının yazıldığı yer.
    'path' => 'sitemaps',

    // Sitemaps.org limiti: dosya başına en fazla bu kadar URL, aşılırsa
    // sitemap-{kaynak}-1.xml, -2.xml şeklinde bölünür.
    'max_urls_per_file' => 50000,

    // Kapak görselini <image:image> olarak ekle.
    'image_sitemap' => true,

    // Panelde aç/kapa switch'i gösterilen kaynaklar. Sıra, panelde listelenme
    // sırasıdır. 'extra' (elle eklenen adresler) listede değil — ayrı bir
    // textarea'dan gelir, her zaman denenir.
    'sources' => [
        'static' => 'Statik sayfalar',
        'pages' => 'Sayfalar',
        'blog' => 'Blog yazıları',
        'services' => 'Hizmetler',
        'regions' => 'Hizmet × bölge sayfaları',
    ],

    // Sabit ön yüz route'ları — isme göre route() ile çözülür.
    'static_routes' => [
        'anasayfa', 'hakkimizda', 'hizmetler', 'blog', 'iletisim', 'kvkk', 'cerez-politikasi',
    ],

    /*
    | robots.txt — panelden düzenlenmediği sürece kullanılacak gövde. `Sitemap:`
    | satırı buraya YAZILMAZ; çalışan adresten (route) üretilip gövdenin sonuna
    | kendiliğinden eklenir, böylece alan adı hiçbir yere sabitlenmez.
    */
    'robots_default' => <<<'TXT'
    User-agent: *
    Disallow: /admin
    Disallow: /bakim-onizleme
    TXT,

    // Kaydedilince/silinince site haritasını yeniden üretecek modeller
    // (SitemapObserver, AppServiceProvider'da bağlanır).
    'observed_models' => [
        Page::class,
        Blog::class,
        Service::class,
        ServiceRegion::class,
    ],
];
