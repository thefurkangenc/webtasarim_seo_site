<?php

use App\Captcha\Drivers\PuzzleDriver;
use App\Http\Middleware\EnsureSetupCompleted;
use App\Http\Middleware\EnsureSiteIsLive;

/*
| Captcha yapılandırması.
|
| Bu dosya app/Captcha klasörünün İÇİNDEDİR — klasörü başka bir projeye
| kopyaladığında ayarlar da birlikte gelir, config/ altına bir şey eklemen
| gerekmez. Panelde "Güvenlik Doğrulaması" ayar sekmesi varsa oradaki
| değerler buradakileri ezer (App\Captcha\Support\PanelSettings); panel yoksa
| buradaki değerler geçerli olur.
*/

return [

    // Ana anahtar. Kapatılınca <x-captcha /> hiçbir şey basmaz ve kural
    // her zaman geçer — formlar dokunulmadan çalışmaya devam eder.
    'enabled' => true,

    'driver' => 'puzzle',

    'drivers' => [
        'puzzle' => PuzzleDriver::class,
    ],

    // Panelin sürücü seçiminde göstereceği adlar.
    'labels' => [
        'puzzle' => 'Kaydırmalı yapboz',
    ],

    /*
    | Hangi formda çalışacağı. <x-captcha form="contact" /> ile eşleşir;
    | listede olmayan ya da false olan bir form adı doğrulama istemez.
    */
    'forms' => [
        'contact' => true,
        'quote' => true,
        'newsletter' => true,
        'login' => true,
    ],

    // Jetonları imzalayan anahtar. Boşsa APP_KEY kullanılır.
    'key' => env('CAPTCHA_KEY'),

    'route_prefix' => 'captcha',

    /*
    | "web" grubuna eklenmiş ama captcha uçlarını kesmemesi gereken ara
    | katmanlar. Bakım modu açıkken panel girişindeki captcha da çalışsın
    | diye gerekli. Projede olmayan sınıf sessizce atlanır.
    */
    'without_middleware' => [
        EnsureSiteIsLive::class,
        EnsureSetupCompleted::class,
    ],

    // Bulmacanın geçerlilik süresi (sn) — kullanıcı sayfayı açıp beklerse.
    'challenge_ttl' => 300,

    // Çözüldükten sonra formun gönderilmesi için tanınan süre (sn).
    'ticket_ttl' => 900,

    // IP başına dakikalık istek sınırı.
    'throttle' => [
        'challenge' => '40,1',
        'verify' => '40,1',
    ],

    'puzzle' => [
        'width' => 320,
        'height' => 180,
        'piece' => 54,

        // Doğru kabul edilen sapma (görsel piksel). Büyütmek kolaylaştırır.
        'tolerance' => 6,

        /*
        | Davranış eşikleri. Formu anında dolduran basit botlar buradan
        | elenir: insan sürüklemesi bir anda bitmez ve tek sıçramada olmaz.
        */
        'min_duration' => 250,
        'max_duration' => 120000,
        'min_moves' => 4,
    ],

];
