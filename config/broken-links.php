<?php

use App\Models\Blog\Blog;
use App\Models\Hero\Hero;
use App\Models\Menu\MenuItem;
use App\Models\Page\Page;
use App\Models\Project\Project;
use App\Models\Service\Service;

/*
| Kırık link denetimi. Tarama kuyrukta çalışır (ScanBrokenLinksJob), haftada
| bir kendiliğinden (`broken-links:scan`, bkz. bootstrap/app.php) ya da
| panelden elle tetiklenir.
|
| İç adresler ağ isteği olmadan çözülür: route tablosuna bakılır, dinamik
| adreslerde kaydın varlığı/yayın durumu sorgulanır. Yalnızca dış adresler
| için HTTP isteği atılır.
*/

return [

    // Dış adres isteklerinin saniye cinsinden sınırları.
    'timeout' => 8,
    'connect_timeout' => 5,

    /*
    | Bazı sunucular tarayıcı gibi görünmeyen isteklere 403 döner; kimliğimizi
    | gizlemiyoruz ama yaygın bir tarayıcı imzasıyla gidiyoruz.
    */
    'user_agent' => 'Mozilla/5.0 (compatible; BrokenLinkChecker/1.0; +link-denetimi)',

    /*
    | Bot korumalı siteler (Cloudflare, sosyal ağlar) gerçek ziyaretçiye
    | açık olan adresler için de bu kodları döndürür. Kırık saymıyoruz —
    | aksi halde liste yanlış uyarıyla dolar.
    */
    'ignored_statuses' => [401, 403, 405, 429, 999],

    /*
    | Hiç denenmeyen alan adları: giriş duvarı ya da agresif bot koruması
    | yüzünden sonucu güvenilmez olanlar.
    */
    'skipped_hosts' => [
        'facebook.com', 'instagram.com', 'linkedin.com', 'x.com', 'twitter.com',
    ],

    // http/https dışında taranmayan şemalar.
    'skipped_schemes' => ['mailto', 'tel', 'sms', 'javascript', 'data', 'whatsapp', 'skype'],

    /*
    | Taranan kaynaklar. Anahtar, broken_links.source_type kolonuna yazılan
    | sınıf adıdır; etiket panelde kaynak filtresinde görünür.
    */
    'sources' => [
        Page::class => 'Sayfa',
        Blog::class => 'Blog Yazısı',
        Service::class => 'Hizmet',
        Project::class => 'Proje (Neler Yaptık)',
        MenuItem::class => 'Menü Öğesi',
        Hero::class => 'Tanıtım Alanı',
    ],

    'kinds' => [
        'link' => 'Bağlantı',
        'image' => 'Görsel',
    ],

    'scopes' => [
        'internal' => 'İç adres',
        'external' => 'Dış adres',
    ],

    /*
    | Sebep anahtarı -> kullanıcıya gösterilen açıklama. Anahtarlar
    | App\Services\BrokenLink\LinkChecker içinde üretilir.
    */
    'reasons' => [
        'not_found' => 'Sayfa bulunamadı',
        'unpublished' => 'Hedef yayında değil',
        'missing_file' => 'Dosya sunucuda yok',
        'server_error' => 'Sunucu hatası',
        'timeout' => 'Yanıt vermedi',
        'connection' => 'Bağlantı kurulamadı',
        'invalid' => 'Geçersiz adres',
        'unresolved' => 'Adres çözülemiyor',
    ],
];
