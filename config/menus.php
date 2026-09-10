<?php

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;

/*
| Menü yöneticisi ayarları.
|
| `locations` sabittir: MenuSeeder her anahtar için bir `menus` satırı basar,
| panelden menü eklenip silinmez. Yeni bir konum eklemek için buraya bir satır
| + seeder'ı tekrar çalıştır + ön yüzde ilgili yere <x-menu> yerleştir.
*/

return [

    'locations' => [
        'header' => [
            'name' => 'Üst Menü (Header)',
            // Header'da sütun başlığı yok.
            'title' => null,
        ],
        'footer_primary' => [
            'name' => 'Footer — 1. Sütun',
            'title' => 'Hızlı Erişim',
        ],
        'footer_secondary' => [
            'name' => 'Footer — 2. Sütun',
            'title' => 'Hizmetlerimiz',
        ],
    ],

    /*
    | "Kayda bağla" seçeneğinde listelenen modeller. Her biri
    | App\Contracts\LinksToPublicPage arayüzünü uygular; menü öğesinin URL'i
    | render anında modelin publicUrl()'inden çözülür, kayıt taşınırsa bağ
    | kendiliğinden güncel kalır.
    |
    | 'query' yalnızca seçilebilir (yayında) kayıtları döndürmeli.
    */
    'linkables' => [
        'page' => [
            'label' => 'Sayfa',
            'model' => Page::class,
            'query' => fn () => Page::query()->orderBy('path'),
            'option_label' => fn (Page $page) => $page->path === $page->slug
                ? $page->title
                : str_repeat('— ', substr_count($page->path, '/')).$page->title,
        ],
        'service' => [
            'label' => 'Hizmet',
            'model' => Service::class,
            'query' => fn () => Service::query()->where('status', Service::STATUS_PUBLISHED)->orderBy('title'),
            'option_label' => fn (Service $service) => $service->title,
        ],
        'blog' => [
            'label' => 'Blog Yazısı',
            'model' => Blog::class,
            'query' => fn () => Blog::query()->where('status', Blog::STATUS_PUBLISHED)->orderByDesc('published_at'),
            'option_label' => fn (Blog $blog) => $blog->title,
        ],
    ],

    /*
    | "Hazır bağlantı" seçeneğinde listelenen parametresiz ön yüz route'ları.
    | Elle URL yazmak yerine route adı saklanır — adres değişse de bağ kopmaz.
    */
    'routes' => [
        'anasayfa' => 'Ana Sayfa',
        'hakkimizda' => 'Hakkımızda',
        'hizmetler' => 'Hizmetler (liste)',
        'blog' => 'Blog (liste)',
        'iletisim' => 'İletişim',
        'kvkk' => 'KVKK Aydınlatma Metni',
        'cerez-politikasi' => 'Çerez Politikası',
    ],

    /*
    | İç içe geçme sınırı. Ön yüz teması (vl-header-area14) 3 seviye dropdown
    | destekliyor; daha derini görsel olarak açılmaz.
    */
    'max_depth' => 3,

];
