<?php

/*
| Kütüphane videolarının kuyrukta işlenmesi. Oynatıcı bu çıktıyı okur;
| gömülü YouTube/Vimeo adresleri buraya girmez.
|
| ffmpeg / ffprobe sistem ikiliğidir (Composer paketi yok). Yol boşsa
| PATH'teki `ffmpeg` / `ffprobe` kullanılır. PHP-FPM PATH'i brew yolunu
| içermeyebilir; App\Support\Ffmpeg yaygın konumlara da bakar.
*/

return [

    'ffmpeg' => env('FFMPEG_PATH', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_PATH', 'ffprobe'),

    // Kaynak yükseklik hedeften küçükse o basamak üretilmez (yukarı ölçek yok).
    'heights' => [1080, 720, 480],

    'crf' => 23,
    'audio_bitrate' => '128k',

    'sprite' => [
        'interval' => 2,
        'width' => 160,
        'columns' => 5,
    ],

    'poster_at' => 1.0,

    // ProcessVideoJob timeout (saniye). uniqueFor bundan uzun olmalı.
    'timeout' => 600,

];
