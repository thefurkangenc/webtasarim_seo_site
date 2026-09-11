<?php

/*
| Sistem sağlığı kontrolleri. Panel /admin/health, rapor `health.report`
| anahtarında cache'lenir; saatlik `health:check` komutu tazeler (sunucuda
| `schedule:run` cron'u gerekir), panelden "Yeniden Tara" ile de zorlanır.
|
| Eşikler burada tek yerde durur — kontrol sınıfları sayı sabiti taşımaz.
*/

return [

    /*
    | Kontroller panelde bu gruplar altında toplanır. `keys` listesinde
    | geçmeyen bir kontrol "other" grubuna düşer — yeni bir kontrol eklenince
    | panel bozulmaz, sadece en alta gider.
    */
    'groups' => [
        'queue' => [
            'label' => 'Kuyruk ve Zamanlanmış Görevler',
            'icon' => 'conveyor_belt',
            'description' => 'Arka planda çalışan işler: yapay zeka üretimi, site haritası, e-posta gönderimi, link taraması. Buradaki bir sorun “kaydettim ama hiçbir şey olmadı” şikâyetinin en yaygın sebebidir.',
            'keys' => ['queue_worker', 'failed_jobs', 'cron'],
        ],
        'server' => [
            'label' => 'Sunucu',
            'icon' => 'dns',
            'description' => 'Sitenin üzerinde çalıştığı makine: disk alanı, veritabanı bağlantısı ve dosya yazma izinleri.',
            'keys' => ['disk', 'database', 'writable'],
        ],
        'security' => [
            'label' => 'Güvenlik ve Ortam',
            'icon' => 'encrypted',
            'description' => 'Ziyaretçinin gördüğü güvenlik: SSL sertifikası ve sitenin canlı ortam ayarlarında olup olmadığı.',
            'keys' => ['ssl', 'debug'],
        ],
        'services' => [
            'label' => 'Dış Servisler',
            'icon' => 'cloud_sync',
            'description' => 'Siteye bağladığınız dış hizmetler. Kurulu olmayanlar hata sayılmaz, soluk görünür.',
            // Google kontrolleri kapsam başına ayrı anahtar üretir (google_*);
            // önek eşleşmesi için sonu yıldızlı yazılır.
            'keys' => ['smtp', 'google', 'google_*'],
        ],
        'other' => [
            'label' => 'Diğer',
            'icon' => 'more_horiz',
            'description' => 'Gruplandırılmamış kontroller.',
            'keys' => [],
        ],
    ],

    // Rapor bu süreden eskiyse panel açılırken kendiliğinden yenilenir.
    'cache_minutes' => 15,

    'queue' => [
        /*
        | Kuyruk işçisi boşta dönerken de sinyal bırakır (Looping olayı,
        | bkz. App\Listeners\RecordQueueHeartbeat). Sinyal bu süreden
        | eskiyse işçi durmuş sayılır.
        */
        'heartbeat_minutes' => 5,

        // Bekleyen bir iş bu kadar süredir alınmadıysa işçi tıkanmış demektir.
        'pending_age_minutes' => 15,

        // Başarısız iş sayısı eşikleri.
        'failed_warning' => 1,
        'failed_critical' => 10,

        // Panelde listelenecek başarısız iş sayısı.
        'failed_list_limit' => 25,
    ],

    'disk' => [
        'warning_percent' => 85,
        'critical_percent' => 95,
    ],

    'ssl' => [
        'warning_days' => 21,
        'critical_days' => 7,
        'timeout' => 5,
    ],

    // `schedule:run` cron'unun son çalışması bu süreden eskiyse uyarılır.
    'cron_minutes' => 30,

    /*
    | Yazılabilir olması gereken dizinler. Biri yazılamıyorsa log, cache,
    | oturum ve medya yüklemeleri sessizce bozulur.
    */
    'writable_paths' => [
        'storage/app' => 'Medya ve dosya deposu',
        'storage/framework' => 'Cache / oturum / derlenmiş görünümler',
        'storage/logs' => 'Uygulama günlükleri',
        'bootstrap/cache' => 'Yapılandırma ve route önbelleği',
    ],

    /*
    | Kontrol edilecek Google servisleri: etiket => OAuth kapsamı. Kimlik
    | GA4 ayarlarındaki service account'tur (ikisi aynı JSON'u kullanır).
    */
    'google_scopes' => [
        'Google Analytics' => 'https://www.googleapis.com/auth/analytics.readonly',
        'Search Console' => 'https://www.googleapis.com/auth/webmasters',
    ],

    /*
    | Durum anahtarı -> etiket, renk ve sıra. Sıra panelde önce sorunlu
    | kartların görünmesi için kullanılır.
    */
    'statuses' => [
        'critical' => ['label' => 'Kritik', 'icon' => 'error', 'order' => 0],
        'warning' => ['label' => 'Dikkat', 'icon' => 'warning', 'order' => 1],
        'skipped' => ['label' => 'Kurulu değil', 'icon' => 'remove', 'order' => 2],
        'ok' => ['label' => 'Sorun yok', 'icon' => 'check_circle', 'order' => 3],
    ],

    /*
    | Kritik bir sorun varken günde bir kez özet e-posta. Alıcı iletişim
    | formununkiyle aynıdır (settings: contact.to_email, yoksa company.email).
    */
    'notify' => [
        'enabled' => true,
        'time' => '08:30',
    ],
];
