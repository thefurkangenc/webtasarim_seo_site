<?php

/*
| Denetim kaydı (audit log) ayarları.
|
| Loglama iki yoldan yazılır:
|   1. App\Models\Concerns\LogsActivity trait'i — modele eklenince ekleme/
|      düzenleme/silme kendiliğinden loglanır.
|   2. App\Support\Activity::record() — model olayı olmayan durumlar için
|      (giriş, yetkisiz erişim, toplu işlem, sıralama, ayar kaydetme).
*/

return [

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    /*
    | Değeri hiçbir zaman saklanmayacak alanlar. Adı bu listedekilerden birini
    | İÇEREN her alan maskelenir (api_key, smtp_password, access_token ...).
    | Alanın değiştiği bilgisi yine kaydedilir, yalnızca değer "•••" olur.
    */
    'redact' => [
        'password',
        'secret',
        'token',
        'api_key',
        'apikey',
        'private_key',
        'credential',
        'authorization',
    ],

    /*
    | Diff'e hiç girmeyen alanlar. sort_order burada: sürükle-bırak sıralama
    | tek bir "reorder" olayı olarak loglanıyor, satır satır değil.
    */
    'ignore' => [
        'created_at',
        'updated_at',
        'remember_token',
        'sort_order',
    ],

    /*
    | Uzun metin alanları logda tam saklanmaz (içerik alanları megabaytlarca
    | olabilir). Bu uzunluğu aşan değerler kırpılır ve sonuna "…" eklenir.
    */
    'max_value_length' => 500,

    /*
    | IP -> konum çözümlemesi. Log yazılırken DEĞİL, kuyrukta yapılır; servis
    | yavaşlarsa ya da çökerse log kaydı yine eksiksiz yazılmış olur.
    | Aynı IP tekrar sorulmasın diye sonuç önbelleğe alınır.
    */
    'geo' => [
        'enabled' => env('ACTIVITY_LOG_GEO', true),
        'endpoint' => 'http://ip-api.com/json/{ip}?fields=status,message,country,countryCode,regionName,city,lat,lon,timezone,isp',
        'timeout' => 4,
        'cache_days' => 30,
    ],

    /*
    | `php artisan activity-log:prune` bu günden eskisini siler. Zamanlanmış
    | görev olarak KURULU DEĞİL — ne zaman temizleneceği sizin kararınız.
    */
    'retention_days' => env('ACTIVITY_LOG_RETENTION_DAYS', 365),

    /*
    | Modül anahtarı -> arayüz etiketi ve ikonu. Anahtar, log_name kolonuna
    | yazılan değerdir. Burada tanımlı olmayan bir modül de loglanır; arayüzde
    | anahtarı olduğu gibi görünür.
    */
    'modules' => [
        'auth' => ['label' => 'Oturum', 'icon' => 'lock'],
        'security' => ['label' => 'Güvenlik', 'icon' => 'shield'],
        'page' => ['label' => 'Sayfalar', 'icon' => 'description'],
        'menu' => ['label' => 'Menüler', 'icon' => 'menu'],
        'redirect' => ['label' => 'Yönlendirmeler', 'icon' => 'alt_route'],
        'blog' => ['label' => 'Blog', 'icon' => 'article'],
        'blog-category' => ['label' => 'Blog Kategorileri', 'icon' => 'category'],
        'service' => ['label' => 'Hizmetler', 'icon' => 'home_repair_service'],
        'service-region' => ['label' => 'Hizmet Bölgeleri', 'icon' => 'map'],
        'faq' => ['label' => 'Sıkça Sorulan Sorular', 'icon' => 'help'],
        'testimonial' => ['label' => 'Müşteri Yorumları', 'icon' => 'reviews'],
        'reference' => ['label' => 'Referanslar', 'icon' => 'handshake'],
        'why-choose-us' => ['label' => 'Neden Biz', 'icon' => 'workspace_premium'],
        'hero' => ['label' => 'Tanıtım Alanı', 'icon' => 'slideshow'],
        'social-link' => ['label' => 'Sosyal Medya', 'icon' => 'share'],
        'media' => ['label' => 'Medya', 'icon' => 'perm_media'],
        'user' => ['label' => 'Kullanıcılar', 'icon' => 'group'],
        'role' => ['label' => 'Roller ve İzinler', 'icon' => 'admin_panel_settings'],
        'setting' => ['label' => 'Site Ayarları', 'icon' => 'settings'],
        'contact' => ['label' => 'İletişim Formu', 'icon' => 'inbox'],
        'ai' => ['label' => 'Yapay Zeka', 'icon' => 'smart_toy'],
        'tag' => ['label' => 'Etiketler', 'icon' => 'sell'],
        'indexnow' => ['label' => 'Hızlı İndeksleme', 'icon' => 'bolt'],
    ],

    /*
    | Olay anahtarı -> etiket, ikon, renk ailesi ve önem derecesi.
    | Renk adları panelin Tailwind paletinden (primary/success/danger/...).
    */
    'events' => [
        'created' => ['label' => 'Eklendi', 'icon' => 'add_circle', 'color' => 'success', 'severity' => 'info'],
        'updated' => ['label' => 'Düzenlendi', 'icon' => 'edit', 'color' => 'primary', 'severity' => 'info'],
        'deleted' => ['label' => 'Silindi', 'icon' => 'delete', 'color' => 'danger', 'severity' => 'notice'],
        'reorder' => ['label' => 'Sıralandı', 'icon' => 'swap_vert', 'color' => 'secondary', 'severity' => 'info'],
        'bulk_delete' => ['label' => 'Toplu Silme', 'icon' => 'delete_sweep', 'color' => 'danger', 'severity' => 'warning'],
        'bulk_move' => ['label' => 'Toplu Taşıma', 'icon' => 'drive_file_move', 'color' => 'secondary', 'severity' => 'notice'],
        'upload' => ['label' => 'Yükleme', 'icon' => 'upload', 'color' => 'info', 'severity' => 'info'],
        'login' => ['label' => 'Giriş', 'icon' => 'login', 'color' => 'success', 'severity' => 'info'],
        'logout' => ['label' => 'Çıkış', 'icon' => 'logout', 'color' => 'secondary', 'severity' => 'info'],
        'login_failed' => ['label' => 'Başarısız Giriş', 'icon' => 'gpp_bad', 'color' => 'warning', 'severity' => 'warning'],
        'lockout' => ['label' => 'Giriş Kilitlendi', 'icon' => 'block', 'color' => 'danger', 'severity' => 'critical'],
        'password_changed' => ['label' => 'Parola Değişti', 'icon' => 'key', 'color' => 'warning', 'severity' => 'warning'],
        'forbidden' => ['label' => 'Yetkisiz Erişim', 'icon' => 'gpp_maybe', 'color' => 'danger', 'severity' => 'critical'],
        'submitted' => ['label' => 'Form Gönderimi', 'icon' => 'send', 'color' => 'info', 'severity' => 'info'],
        'notified' => ['label' => 'Bildirildi', 'icon' => 'bolt', 'color' => 'info', 'severity' => 'info'],
        'failed' => ['label' => 'Başarısız', 'icon' => 'error', 'color' => 'danger', 'severity' => 'warning'],
    ],

    /*
    | Önem derecesi -> arayüz rengi. critical olanlar log listesinde
    | vurgulanır.
    */
    'severities' => [
        'info' => ['label' => 'Bilgi', 'color' => 'gray'],
        'notice' => ['label' => 'Dikkat', 'color' => 'secondary'],
        'warning' => ['label' => 'Uyarı', 'color' => 'warning'],
        'critical' => ['label' => 'Kritik', 'color' => 'danger'],
    ],
];
