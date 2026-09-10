<?php

use App\Models\Page\Page;
use App\Models\Service\Service;

/*
| Yönlendirme yöneticisi ayarları.
*/

return [

    // 404 alan bir yol için yönlendirme aranır ve yoksa kaydedilir. Bu
    // önekler yönlendirme sisteminin tamamen dışında tutulur — panel,
    // API, dosya istekleri asla yönlendirilmez ve 404 loguna girmez.
    'ignore_prefixes' => [
        'admin',
        'api',
        'storage',
        'vendor',
        '_debugbar',
        'livewire',
    ],

    // Bu uzantılarla biten istekler (eksik görsel, .map dosyası, bot
    // taramaları) 404 loguna yazılmaz — gürültü yaratır, işe yaramaz.
    'ignore_extensions' => [
        'php', 'asp', 'aspx', 'jsp', 'env', 'git',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp',
        'css', 'js', 'map', 'woff', 'woff2', 'ttf', 'eot',
        'xml', 'txt', 'json',
    ],

    'status_codes' => [
        301 => '301 — Kalıcı',
        302 => '302 — Geçici',
        307 => '307 — Geçici (metot korunur)',
        410 => '410 — Kalıcı olarak kaldırıldı',
    ],

    'match_types' => [
        'exact' => 'Birebir',
        'prefix' => 'Önek (ve altındaki her şey)',
        'regex' => 'Düzenli ifade (regex)',
    ],

    // Yönlendirme zinciri en fazla bu kadar adım izlenir (döngü koruması).
    'max_chain_depth' => 10,

    // Slug/adres değişiminde otomatik 301 üreten modeller. Model
    // App\Contracts\LinksToPublicPage uygular; gözlemci `redirectableMoved()`
    // ile eski/yeni yolu alır.
    'auto_from' => [
        Page::class,
        Service::class,
    ],

];
