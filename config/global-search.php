<?php

use App\Models\Blog\Blog;
use App\Models\Faq\Faq;
use App\Models\Lead\Lead;
use App\Models\Media\Media;
use App\Models\Page\Page;
use App\Models\Project\Project;
use App\Models\Service\Service;
use App\Models\Subscriber\Subscriber;
use App\Models\Testimonial\Testimonial;

/*
| Header'daki global arama (Ctrl+K).
|
| Her kaynak bir modül. `permission` YOKSA kaynak hiç aranmaz — arama ucu
| yetki ara katmanından muaf tutulduğu için (her kullanıcı arayabilir) filtre
| burada, sunucu tarafında yapılır; yetkisi olmayan kullanıcıya o modülün
| kaydı DÖNMEZ.
|
| Her giriş:
|   label     -> sonuç listesindeki grup başlığı
|   icon      -> material symbols adı
|   model     -> Eloquent sınıfı
|   columns   -> LIKE ile aranacak kolonlar
|   title     -> sonuç satırının başlığı olarak basılacak kolon
|   subtitle  -> başlığın altındaki ikincil bilgi (opsiyonel)
|   route     -> kayda giden admin route adı; null ise `index_route` kullanılır
|   permission-> bu kaynağın görünmesi için gereken izin
*/

return [

    // Kaynak başına en fazla kaç sonuç.
    'limit' => 5,

    // Bundan kısa aramalar hiç sorguya çevrilmez.
    'min_length' => 2,

    'sources' => [
        'page' => [
            'label' => 'Sayfalar',
            'icon' => 'description',
            'model' => Page::class,
            'columns' => ['title', 'path'],
            'title' => 'title',
            'subtitle' => 'path',
            'route' => 'admin.page.edit',
            'permission' => 'page.index',
        ],
        'blog' => [
            'label' => 'Blog Yazıları',
            'icon' => 'article',
            'model' => Blog::class,
            'columns' => ['title', 'slug', 'excerpt'],
            'title' => 'title',
            'subtitle' => 'slug',
            'route' => 'admin.blog.edit',
            'permission' => 'blog.index',
        ],
        'service' => [
            'label' => 'Hizmetler',
            'icon' => 'design_services',
            'model' => Service::class,
            'columns' => ['title', 'slug', 'excerpt'],
            'title' => 'title',
            'subtitle' => 'slug',
            'route' => 'admin.service.edit',
            'permission' => 'service.index',
        ],
        'project' => [
            'label' => 'Neler Yaptık',
            'icon' => 'workspaces',
            'model' => Project::class,
            'columns' => ['title', 'slug', 'client_name', 'sector'],
            'title' => 'title',
            'subtitle' => 'client_name',
            'route' => 'admin.project.edit',
            'permission' => 'project.index',
        ],
        'lead' => [
            'label' => 'Gelen Talepler',
            'icon' => 'inbox',
            'model' => Lead::class,
            'columns' => ['name', 'email', 'phone', 'subject'],
            'title' => 'name',
            'subtitle' => 'email',
            'route' => 'admin.lead.show',
            'permission' => 'lead.index',
        ],
        'testimonial' => [
            'label' => 'Müşteri Yorumları',
            'icon' => 'reviews',
            'model' => Testimonial::class,
            'columns' => ['name', 'title', 'content'],
            'title' => 'name',
            'subtitle' => 'title',
            'route' => null,
            'index_route' => 'admin.testimonial.index',
            'permission' => 'testimonial.index',
        ],
        'faq' => [
            'label' => 'Sıkça Sorulan Sorular',
            'icon' => 'quiz',
            'model' => Faq::class,
            'columns' => ['question', 'answer'],
            'title' => 'question',
            'subtitle' => null,
            'route' => null,
            'index_route' => 'admin.faq.index',
            'permission' => 'faq.index',
        ],
        'subscriber' => [
            'label' => 'Bülten Aboneleri',
            'icon' => 'mail',
            'model' => Subscriber::class,
            'columns' => ['email'],
            'title' => 'email',
            'subtitle' => null,
            'route' => null,
            'index_route' => 'admin.subscriber.index',
            'permission' => 'subscriber.index',
        ],
        'media' => [
            'label' => 'Medya',
            'icon' => 'perm_media',
            'model' => Media::class,
            'columns' => ['name', 'original_name', 'alt'],
            'title' => 'name',
            'subtitle' => 'original_name',
            'route' => null,
            'index_route' => 'admin.media.index',
            'permission' => 'media.index',
        ],
    ],

    /*
    | Ayar sekmeleri ve panel ekranları da aranabilir — "çerez" yazan biri
    | Ayarlar → Çerez Çubuğu'na ulaşmalı. Bunlar veritabanında değil, bu
    | yüzden ayrı bir liste; eşleşme başlık üzerinde yapılır.
    */
    'screens' => [
        ['label' => 'Site Ayarları', 'keywords' => 'ayarlar site genel yapılandırma', 'route' => 'admin.setting.index', 'permission' => 'setting.index'],
        ['label' => 'Site Haritası ve Hızlı İndeksleme', 'keywords' => 'sitemap indexnow robots hızlı indeksleme indeks arama motoru', 'route' => 'admin.sitemap.index', 'permission' => 'sitemap.index'],
        ['label' => 'Search Console', 'keywords' => 'google arama performansı sorgu tıklama gösterim indeks denetimi', 'route' => 'admin.search-console.index', 'permission' => 'search-console.index'],
        ['label' => 'Analitik', 'keywords' => 'analytics ga4 trafik ziyaretçi oturum', 'route' => 'admin.analytics.index', 'permission' => 'analytics.index'],
        ['label' => 'SEO Sağlığı', 'keywords' => 'seo skor puan odak anahtar kelime okunabilirlik', 'route' => 'admin.seo.index', 'permission' => 'seo.index'],
        ['label' => 'Sistem Sağlığı', 'keywords' => 'health kuyruk disk ssl sertifika sunucu cron başarısız iş', 'route' => 'admin.health.index', 'permission' => 'health.index'],
        ['label' => 'Yönlendirmeler ve 404 Kayıtları', 'keywords' => 'redirect yönlendirme 301 404 bulunamadı', 'route' => 'admin.redirect.index', 'permission' => 'redirect.index'],
        ['label' => 'Kırık Linkler', 'keywords' => 'broken link kırık bozuk bağlantı', 'route' => 'admin.broken-link.index', 'permission' => 'broken-link.index'],
        ['label' => 'Revizyonlar', 'keywords' => 'revizyon geçmiş sürüm geri al', 'route' => 'admin.revision.index', 'permission' => 'revision.index'],
        ['label' => 'Log Kayıtları', 'keywords' => 'log denetim audit kim ne yaptı', 'route' => 'admin.activity-log.index', 'permission' => 'activity-log.index'],
        ['label' => 'Menüler', 'keywords' => 'menü menu navigasyon header footer', 'route' => 'admin.menu.index', 'permission' => 'menu.index'],
        ['label' => 'Duyuru Şeridi', 'keywords' => 'duyuru şeridi announcement bar', 'route' => 'admin.announcement.index', 'permission' => 'announcement.index'],
        ['label' => 'Açılır Pencereler', 'keywords' => 'popup açılır pencere modal bülten', 'route' => 'admin.popup.index', 'permission' => 'popup.index'],
        ['label' => 'Roller ve İzinler', 'keywords' => 'rol yetki izin kullanıcı', 'route' => 'admin.role.index', 'permission' => 'role.index'],
        ['label' => 'Profilim', 'keywords' => 'hesap şifre parola avatar profil fotoğraf', 'route' => 'admin.profile.edit', 'permission' => null],
    ],
];
