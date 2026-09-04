<?php

/*
| Ön yüz entegrasyonları. Kart listesi tek kaynaktır; sekme bu diziden üretilir.
| Değerler settings.integrations grubunda JSON olarak saklanır.
*/

return [

    'whatsapp' => [
        'title' => 'WhatsApp',
        'description' => 'Sitenin köşesinde WhatsApp sohbet balonu.',
        'icon' => 'whatsapp.svg',
    ],

    'tawk' => [
        'title' => 'Tawk.to',
        'description' => 'Canlı destek sohbet penceresi.',
        'icon' => 'tawk.svg',
    ],

    'phone' => [
        'title' => 'Telefon',
        'description' => 'Tek dokunuşla arama butonu.',
        'icon' => 'phone.svg',
    ],

    'maps' => [
        'title' => 'Google Maps',
        'description' => 'Firma bilgileri haritası ve konum seçici.',
        'icon' => 'maps.svg',
    ],

];
