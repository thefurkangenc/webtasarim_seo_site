<?php

/*
| Gelen talepler (lead) modülü. Durum ve kaynak etiketlerinin tek kaynağı —
| panel listesi, filtreler ve rozet renkleri buradan okunur.
*/

return [

    /*
    | Durumlar. Anahtar veritabanına yazılır (İngilizce), etiket arayüzde
    | görünür. `color` panelin Tailwind paletinden bir renk ailesi.
    */
    /*
    | `color` Tailwind renk ailesidir (sınıf adı üretmek için), `chart` ise
    | ApexCharts'a verilen ham hex — grafik kütüphanesi Tailwind sınıfı
    | okuyamaz, iki gösterim bu yüzden ayrı duruyor.
    */
    'statuses' => [
        'new' => ['label' => 'Yeni', 'color' => 'primary', 'chart' => '#605dff', 'icon' => 'mark_email_unread'],
        'in_progress' => ['label' => 'İşlemde', 'color' => 'warning', 'chart' => '#ffb264', 'icon' => 'hourglass_top'],
        'done' => ['label' => 'Tamamlandı', 'color' => 'success', 'chart' => '#37d80a', 'icon' => 'task_alt'],
        'spam' => ['label' => 'Spam', 'color' => 'danger', 'chart' => '#ee3e5d', 'icon' => 'report'],
    ],

    /*
    | Talebin hangi formdan geldiği. Yeni bir form eklenince buraya bir satır
    | yazılır; listede filtre olarak kendiliğinden görünür.
    */
    'sources' => [
        'contact' => 'İletişim formu',
    ],

    // Liste ekranında bir sayfada gösterilecek kayıt sayısı.
    'per_page' => 20,

    // CSV dışa aktarımda en fazla bu kadar satır (tarayıcıyı kilitlememek için).
    'export_limit' => 5000,
];
