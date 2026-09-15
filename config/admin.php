<?php

/*
| Panel URL öneki. Route adları admin.* olarak kalır; değişen yalnızca
| adresin ilk segmentidir (/admin, /panel, /yonetim...).
|
| Değiştirmek için .env içine ADMIN_PREFIX=panel yazın, ardından
| php artisan config:clear (config cache'liyse). Sihirbazda alanı yoktur;
| ayarlar tablosundan okunmaz.
*/

return [

    'prefix' => env('ADMIN_PREFIX', 'admin'),

];
