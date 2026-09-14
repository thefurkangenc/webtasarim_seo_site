<?php

use Database\Seeders\AiPromptSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\MediaPresetSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceRegionSeeder;

/*
| İlk kurulum sihirbazı. Adımlar tarayıcıda toplanır; "Kurulumu başlat"
| deyince görevler sırayla işlenir. Tamamlanınca settings.setup.completed
| yazılır ve /kurulum kapanır.
*/

return [

    /*
    | Sihirbazdan kapatılamayan içerik omurgası. Sayfalar ve gelen talepler
    | her kurumsal sitede açık kalır.
    */
    'locked_modules' => ['page', 'lead'],

    'steps' => [
        ['key' => 'admin', 'label' => 'Yönetici', 'icon' => 'person'],
        ['key' => 'company', 'label' => 'Firma', 'icon' => 'apartment'],
        ['key' => 'modules', 'label' => 'Modüller', 'icon' => 'widgets'],
        ['key' => 'contact', 'label' => 'İletişim', 'icon' => 'inbox'],
        ['key' => 'mail', 'label' => 'E-posta', 'icon' => 'mail'],
        ['key' => 'legal', 'label' => 'Yasal', 'icon' => 'policy'],
        ['key' => 'summary', 'label' => 'Özet', 'icon' => 'task_alt'],
    ],

    /*
    | Animasyon ekranındaki görev sırası. ffmpeg sistem paketi kurulmaz;
    | yalnızca kontrol edilir, yoksa o OS için kopyalanabilir komut döner.
    */
    'tasks' => [
        ['key' => 'foundation', 'label' => 'Altyapı hazırlanıyor'],
        ['key' => 'admin', 'label' => 'Yönetici hesabı oluşturuluyor'],
        ['key' => 'company', 'label' => 'Firma bilgileri kaydediliyor'],
        ['key' => 'modules', 'label' => 'Modüller ayarlanıyor'],
        ['key' => 'contact', 'label' => 'İletişim formu ayarlanıyor'],
        ['key' => 'mail', 'label' => 'E-posta ayarlanıyor'],
        ['key' => 'legal', 'label' => 'Yasal sayfalar hazırlanıyor'],
        ['key' => 'menus', 'label' => 'Menüler kuruluyor'],
        ['key' => 'storage', 'label' => 'Dosya bağlantısı kuruluyor'],
        ['key' => 'ffmpeg', 'label' => 'ffmpeg kontrol ediliyor'],
        ['key' => 'finalize', 'label' => 'Kurulum tamamlanıyor'],
    ],

    'seeders' => [
        RolePermissionSeeder::class,
        CountrySeeder::class,
        ModuleSeeder::class,
        MediaPresetSeeder::class,
        AiPromptSeeder::class,
        ServiceRegionSeeder::class,
        MenuSeeder::class,
    ],

    /*
    | `php artisan setup:reset` bunlara dokunmaz. Sihirbazın foundation
    | adımı da aynı seeder'ları tekrar basar.
    */
    'keep_tables' => [
        'migrations',
        'roles',
        'permissions',
        'role_has_permissions',
        'countries',
        'modules',
        'media_presets',
        'ai_prompts',
        'service_regions',
        'menus',
    ],

    /*
    | İçerik, kullanıcı, ayar ve log. Truncate edilir; kurulum yeniden açılır.
    */
    'reset_tables' => [
        'users',
        'model_has_roles',
        'model_has_permissions',
        'password_reset_tokens',
        'sessions',
        'settings',
        'pages',
        'blogs',
        'blog_categories',
        'services',
        'service_region_service',
        'projects',
        'project_categories',
        'project_service',
        'galleries',
        'testimonials',
        'references',
        'faqs',
        'faqables',
        'why_choose_us',
        'heroes',
        'announcements',
        'popups',
        'leads',
        'subscribers',
        'social_links',
        'tags',
        'taggables',
        'seo',
        'menu_items',
        'media',
        'media_folders',
        'mediables',
        'ai_providers',
        'ai_generations',
        'activity_logs',
        'revisions',
        'redirects',
        'not_found_logs',
        'broken_links',
        'notification_reads',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ],

    'legal' => [
        'kvkk' => <<<'HTML'
<p>Bu aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında {name} tarafından hazırlanmıştır. Metni kendi faaliyetinize göre Ayarlar → İçerikler bölümünden güncelleyin.</p>
<p>İletişim formu ve benzeri kanallardan ilettiğiniz ad, e-posta, telefon ve mesaj içeriği; talebinizi karşılamak ve yasal yükümlülükleri yerine getirmek amacıyla işlenir. Verileriniz üçüncü kişilerle pazarlama amacıyla paylaşılmaz.</p>
<p>Haklarınız (erişim, düzeltme, silme, itiraz) için {email} adresinden bize ulaşabilirsiniz.</p>
HTML,
        'cookie' => <<<'HTML'
<p>{name} sitesi, sitenin çalışması, güvenliği ve (onayınızla) ölçüm için çerez kullanır. Zorunlu çerezler kapatılamaz; analitik ve pazarlama çerezlerini çubuktan reddedebilirsiniz.</p>
<p>Çerez tercihlerinizi daha sonra da değiştirebilirsiniz. Ayrıntılı listeyi bu sayfada tutuyoruz; metni Ayarlar → İçerikler bölümünden güncelleyin.</p>
HTML,
    ],
];
