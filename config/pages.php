<?php

/*
| Sayfa yöneticisi ayarları.
|
| Şablonlar tek kaynaktır: form select'i, doğrulama kuralı ve ön yüzün
| hangi Blade'i render edeceği hepsi buradan okunur. Yeni şablon eklemek
| için buraya bir satır + `view` anahtarının gösterdiği Blade dosyası yeter,
| PHP tarafında başka hiçbir yere dokunulmaz.
*/

return [

    'templates' => [

        'default' => [
            'label' => 'Varsayılan (dar içerik)',
            'description' => 'Ortalanmış, okumaya uygun genişlikte metin sütunu. Kurumsal ve yasal sayfalar için.',
            'view' => 'pages.page.default',
        ],

        'wide' => [
            'label' => 'Tam genişlik',
            'description' => 'İçerik kenarlara kadar yayılır. Tablo, galeri ve gömülü içerik barındıran sayfalar için.',
            'view' => 'pages.page.wide',
        ],

        'sidebar' => [
            'label' => 'Yan menülü',
            'description' => 'Sağda aynı bölümdeki diğer sayfaların listesi çıkar. Çok sayfalı bölümler için.',
            'view' => 'pages.page.sidebar',
        ],

    ],

    /*
    | İç içe geçme sınırı. 1 = yalnızca kök sayfalar, 3 = /a/b/c.
    | URL'lerin okunabilir kalması ve breadcrumb'ın şişmemesi için düşük tutulur.
    */
    'max_depth' => 3,

    /*
    | Sayfa slug'ı olarak kullanılamayacak kök segmentler. Kayıtlı route'ların
    | sabit ilk segmentleri (admin, blog, hizmetler, iletisim...) buna ek olarak
    | App\Support\ReservedPath tarafından kendiliğinden toplanır — burada
    | yalnızca route tablosunda görünmeyenler sayılır.
    */
    'reserved' => [
        'api',
        'storage',
        'vendor',
        'sitemap.xml',
        'robots.txt',
        'feed',
        'rss',
        'login',
        'logout',
        'register',
        'password',
        'sayfa',
        'assets',
    ],

];
