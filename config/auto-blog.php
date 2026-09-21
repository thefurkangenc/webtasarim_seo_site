<?php

/*
| Otomatik blog üretimi (cron).
|
| Metin üretimi paneldeki ChatGPT sağlayıcı kaydını kullanır: adres ve API
| anahtarı oradan okunur (/admin/ai-provider). Model, uzunluk ve zaman aşımı
| gibi bu işe özel ayarlar burada durur — panel sağlayıcısındaki değerler
| panel içi üretime göre ayarlıdır, cron onları ezmemelidir.
|
| Görsel üretimi ayrı bir uç noktadır (/images/generations) ve panelde
| karşılığı yoktur; tamamen buradan yönetilir.
*/

return [

    /*
    | Cron adresindeki gizli anahtar: /otomatik-blog/{secret}
    | Boş bırakılırsa uç nokta kapalıdır (404 döner).
    */
    'secret' => env('AUTO_BLOG_SECRET', 'etkisoft'),

    /*
    | Kullanılacak sağlayıcı kaydının id'si. Boşsa ilk aktif ChatGPT
    | (driver = openai) kaydı seçilir.
    */
    'provider_id' => env('AUTO_BLOG_PROVIDER_ID'),

    'text' => [
        'model' => env('AUTO_BLOG_TEXT_MODEL', 'gpt-5.6-luna'),
        /*
        | gpt-6-astra bir muhakeme modelidir ve `temperature` KABUL ETMEZ —
        | resmî geçiş kılavuzu parametrenin kaldırılmasını söyler. Onun yerine
        | muhakeme derinliği ayarlanır: low | medium | high | xhigh | max
        | ('none' desteklenmez). Blog metni yazmak muhakeme isteyen bir iş
        | değil; düşük tutmak bütçeyi gövdeye bırakır.
        */
        'reasoning_effort' => 'low',
        /*
        | DİKKAT: bu bütçe muhakeme token'ları + JSON çıktısının TOPLAMIDIR.
        | Dar tutulunca model gövdeyi bütçeye sığdırmak için kısaltıyor —
        | 8000 token'da 2000 kelimelik Türkçe HTML çıkmıyordu. Türkçe'de
        | kelime başına ~2,5 token gider, HTML etiketleri de üstüne biner.
        | Model sınırı 128.000.
        */
        'max_tokens' => 32000,
        // Genişletme turu da bu süreyi kullanır; GenerateAutoBlogJob hesaba katar.
        'timeout' => 240,
        // Modelden istenecek gövde uzunluğu.
        'words' => '1500-2000',
        /*
        | Gövde bu sayının altında kalırsa TEK bir genişletme turu yapılır.
        | `words` değişirse burası da güncellenmeli.
        */
        'min_words' => 1200,
        /*
        | Konuyu model kendisi seçtiği için, tekrar etmemesi adına mevcut
        | yazıların başlıkları prompta eklenir. Bu sayı kaç başlık
        | gönderileceğini sınırlar; arşiv büyüdükçe prompt şişmesin.
        */
        'history' => 200,
    ],

    'image' => [
        'enabled' => true,
        'model' => env('AUTO_BLOG_IMAGE_MODEL', 'gpt-image-2.5-sunburst'),
        /*
        | config/media.php > presets.blog.cover ile AYNI olmalı (1536x512 = 3:1).
        | gpt-image-2.5 en az 655.360 piksel ister: 1200x400 = 480.000 ve
        | reddedilir, 1536x512 = 786.432 geçer. İki kenar da 16'nın katı,
        | oran tam 3:1 (izin verilen üst sınır).
        */
        'size' => '1536x512',
        // low | medium | high | xhigh | max — metin ağırlıklı kapakta medium alt sınır.
        'quality' => env('AUTO_BLOG_IMAGE_QUALITY', 'medium'),
        'timeout' => 180,
    ],

    'defaults' => [
        /*
        | draft | published — taslak bırakmak bilinçlidir: Google denetimsiz
        | ve ölçekli yapay zeka içeriğini düşük kalite sayar.
        */
        'status' => 'draft',
        'blog_category_id' => null,
        // Yazar. Boşsa en eski kullanıcı atanır; cron'da oturum yoktur.
        'author_id' => null,
    ],

    'tags' => [
        'Kurumsal Web Sitesi',
        'Web Sitesi Fiyatları',
        'Google Ads',
        'Sosyal Medya Reklamları',
        'Organik Trafik',
        'Google Sıralaması',
        'Özel Yazılım & Otomasyon',
        'Dijital Dönüşüm',
        'ERP - CRM',
        'İş Yönetimi',
        'Pazaryeri',
        'Mobil Uygulama',
        'Yapay Zeka',
        'Trendyol',
        'Online Satış',
        'Google Hizmetleri',
    ]

];
