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
    'statuses' => [
        'new' => ['label' => 'Yeni', 'color' => 'primary', 'icon' => 'mark_email_unread'],
        'in_progress' => ['label' => 'İşlemde', 'color' => 'warning', 'icon' => 'hourglass_top'],
        'done' => ['label' => 'Tamamlandı', 'color' => 'success', 'icon' => 'task_alt'],
        'spam' => ['label' => 'Spam', 'color' => 'danger', 'icon' => 'report'],
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
