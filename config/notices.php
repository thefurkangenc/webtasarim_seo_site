<?php

/*
| Duyuru şeridi ve popup. İki ayrı modül; ortak olan yalnızca "kim görsün"
| (tüm site / anasayfa / seçili sayfalar) ve zaman aralığıdır.
|
| Aynı anda birden fazla kayıt yayındaysa, bu sayfaya uyan en yeni kayıt
| gösterilir. Ziyaretçi kapattığında tarayıcıda kalıcı olarak saklanır.
*/

return [

    'audiences' => [
        'all' => 'Tüm site',
        'home' => 'Yalnızca anasayfa',
        'selected' => 'Seçili sayfa ve yazılar',
    ],

    'tones' => [
        'primary' => 'Mavi',
        'warning' => 'Turuncu',
        'dark' => 'Koyu',
    ],

    // Popup açılmadan önce beklenecek saniye (0 = hemen).
    'popup_delay_max' => 30,
];
