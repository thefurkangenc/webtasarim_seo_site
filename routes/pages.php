<?php

use App\Http\Controllers\Page\PageController;
use Illuminate\Support\Facades\Route;

/*
| Panelden yönetilen dinamik sayfaların catch-all route'u.
|
| NEDEN AYRI BİR DOSYA: Laravel route'ları kayıt sırasına göre eşleştirir ve
| bu route her şeyi yakalar. `routes/web.php`'nin sonunda durduğunda bile
| yetmiyor — `bootstrap/app.php` önce web.php'yi, SONRA `then:` kancasında
| admin route'larını yüklüyor, dolayısıyla web.php'deki bir catch-all tüm
| /admin adreslerini yutuyordu. Bu dosya `then:` içinde, admin grubundan
| sonra, uygulamanın EN SON route'u olarak yüklenir.
|
| Yeni bir route grubu eklenirse bu dosyanın yüklenmesi yine en sonda kalmalı.
|
| Desen bilinçli olarak dar: yalnızca küçük harf, rakam, tire, alt tire ve
| bölüm ayıracı '/'. Nokta içeren istekler (robots.txt, *.jpg) ve büyük harfli
| adresler hiç eşleşmez, doğrudan 404'e gider — veritabanına gidilmez.
|
| Bir sayfanın kök slug'ı kayıtlı bir route'un ilk segmentini gölgelemesin diye
| doğrulama tarafında App\Support\ReservedPath devreye girer.
*/

Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '[a-z0-9_-]+(?:/[a-z0-9_-]+)*')
    ->name('sayfa.show');
