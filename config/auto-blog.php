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
        'model' => env('AUTO_BLOG_TEXT_MODEL', 'gpt-4o-mini'),
        'temperature' => 0.7,
        /*
        | Yanıt kesilirse JSON bozulur ve üretim hata verir. Türkçe HTML'de
        | kabaca kelime başına 2 token gider; `words` büyütülürse bu da
        | büyütülmelidir (gpt-4o-mini sınırı 16384).
        */
        'max_tokens' => 8000,
        'timeout' => 180,
        // Modelden istenecek gövde uzunluğu.
        'words' => '1500-2000',
        /*
        | Konuyu model kendisi seçtiği için, tekrar etmemesi adına mevcut
        | yazıların başlıkları prompta eklenir. Bu sayı kaç başlık
        | gönderileceğini sınırlar; arşiv büyüdükçe prompt şişmesin.
        */
        'history' => 200,
    ],

    'image' => [
        'enabled' => true,
        'model' => env('AUTO_BLOG_IMAGE_MODEL', 'gpt-image-1-mini'),
        // Yatay üretilir; MediaService blog.cover presetiyle 1200x630'a kırpar.
        'size' => '1536x1024',
        // low | medium | high — medium yaklaşık 0,015 USD/görsel.
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

];
