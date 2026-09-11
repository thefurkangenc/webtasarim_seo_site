<?php

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;

/*
| IndexNow — bir adres değiştiğinde arama motorlarına "bu adresi yeniden tara"
| diyen açık protokol. Kullanıcı ayarları (açık/kapalı, anahtar) `settings`
| grubu `indexnow`'da; bu dosya sabit tanımları taşır.
|
| ÖNEMLİ: Google IndexNow'ı DESTEKLEMİYOR. Google tarafı için site haritasının
| Search Console'a bildirilmesi kullanılır (/admin/search-console). IndexNow,
| aşağıdaki motorlara tek istekle ulaşır.
*/

return [

    /*
    | Ortak uç nokta: buraya gönderilen bildirim protokole katılan tüm
    | motorlara dağıtılır (motor başına ayrı istek atmaya gerek yok).
    */
    'endpoint' => 'https://api.indexnow.org/indexnow',

    // Protokol sınırı: tek istekte en fazla bu kadar adres.
    'batch_size' => 10000,

    // Üretilen anahtarın uzunluğu (protokol 8-128 arası karakter istiyor).
    'key_length' => 32,

    // Panelde gösterilen gönderim geçmişinde tutulacak kayıt sayısı.
    'history_limit' => 30,

    // Bildirimi alan motorlar — yalnızca panelde bilgi olarak gösterilir.
    'engines' => ['Bing', 'Yandex', 'Seznam.cz', 'Naver', 'Yep'],

    /*
    | Kaydedilince/silinince adresi bildirilecek modeller. Her biri
    | App\Contracts\SubmitsToIndexNow uygular (IndexNowObserver,
    | AppServiceProvider'da bağlanır).
    |
    | ServiceRegion burada YOK: hizmet × bölge sayfasının adresi hizmete bağlı,
    | bölge kaydının kendi başına bir adresi yok. Bölge değişiklikleri
    | "Tüm adresleri gönder" ile ya da hizmet kaydedilince bildirilir.
    */
    'observed_models' => [
        Page::class,
        Blog::class,
        Service::class,
    ],
];
