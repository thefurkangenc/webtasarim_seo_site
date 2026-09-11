<?php

/*
| Ön yüz entegrasyonları — ziyaretçinin sitede GÖRDÜĞÜ eklentiler.
| Kart listesi tek kaynaktır; sekme bu diziden üretilir.
| Değerler settings.integrations grubunda JSON olarak saklanır.
|
| `placement`: kullanıcıya "bu şey sitenin neresinde çıkıyor" demek için.
| Entegrasyonun çalışmaya hazır olup olmadığı (zorunlu alanlar dolu mu)
| IntegrationService::ready() içinde, tek yerde tanımlıdır.
*/

return [

    'whatsapp' => [
        'title' => 'WhatsApp',
        'description' => 'Ziyaretçi tek tıkla WhatsApp’tan size yazar.',
        'icon' => 'whatsapp.svg',
        'placement' => 'Sayfanın sağ alt köşesinde yeşil balon',
    ],

    'tawk' => [
        'title' => 'Tawk.to',
        'description' => 'Canlı destek sohbet penceresi. Ücretsiz bir hesap açmanız gerekir.',
        'icon' => 'tawk.svg',
        'placement' => 'Sayfanın sağ alt köşesinde sohbet penceresi',
    ],

    'phone' => [
        'title' => 'Telefon',
        'description' => 'Telefondan girenler için tek dokunuşla arama butonu.',
        'icon' => 'phone.svg',
        'placement' => 'Mobilde sayfanın alt köşesinde arama butonu',
    ],

    'maps' => [
        'title' => 'Google Maps',
        'description' => 'İletişim sayfasındaki konum haritası.',
        'icon' => 'maps.svg',
        'placement' => 'İletişim sayfası ve firma bilgileri',
    ],

];
