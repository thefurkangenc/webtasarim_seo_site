<?php

/*
| Yönetilebilir 13 içerik modülünün değişmeyen gerçeği. Panelden sadece
| isim (override) ve aktiflik değişir — o `modules` tablosunda durur.
| Yeni bir modül route'u koddan geldiği için bu dosya elle yazılır.
|
| `routes`: bu modülün kapsadığı admin route prefix'leri — module.active
| middleware'i bunlara uygulanır (bkz. routes/admin.php).
*/

return [

    'definitions' => [
        'page' => [
            'label' => 'Sayfalar',
            'icon' => 'description',
            'description' => 'Kurumsal statik sayfalar (Hakkımızda, KVKK vb.).',
            'routes' => ['page'],
        ],
        'blog' => [
            'label' => 'Blog',
            'icon' => 'article',
            'description' => 'Blog yazıları ve kategorileri.',
            'routes' => ['blog', 'blog-category'],
        ],
        'service' => [
            'label' => 'Hizmetler',
            'icon' => 'design_services',
            'description' => 'Sunulan hizmetler ve hizmet bölgeleri.',
            'routes' => ['service', 'service-region'],
        ],
        'project' => [
            'label' => 'Neler Yaptık',
            'icon' => 'workspaces',
            'description' => 'Vaka çalışmaları ve proje kategorileri.',
            'routes' => ['project', 'project-category'],
        ],
        'testimonial' => [
            'label' => 'Müşteri Yorumları',
            'icon' => 'reviews',
            'description' => 'Müşteri yorumları.',
            'routes' => ['testimonial'],
        ],
        'reference' => [
            'label' => 'Referanslar',
            'icon' => 'handshake',
            'description' => 'Referans logoları/listesi.',
            'routes' => ['reference'],
        ],
        'faq' => [
            'label' => 'Sıkça Sorulan Sorular',
            'icon' => 'quiz',
            'description' => 'Sıkça sorulan sorular.',
            'routes' => ['faq'],
        ],
        'why-choose-us' => [
            'label' => 'Neden Biz',
            'icon' => 'verified',
            'description' => '"Neden Biz" maddeleri.',
            'routes' => ['why-choose-us'],
        ],
        'hero' => [
            'label' => 'Tanıtım Alanı',
            'icon' => 'wallpaper',
            'description' => 'Ana sayfa tanıtım alanı (slider/banner).',
            'routes' => ['hero'],
        ],
        'announcement' => [
            'label' => 'Duyuru Şeridi',
            'icon' => 'campaign',
            'description' => 'Üst duyuru şeridi.',
            'routes' => ['announcement'],
        ],
        'popup' => [
            'label' => 'Açılır Pencereler',
            'icon' => 'web_asset',
            'description' => 'Açılır pencere kampanyaları.',
            'routes' => ['popup'],
        ],
        'subscriber' => [
            'label' => 'Bülten Aboneleri',
            'icon' => 'forward_to_inbox',
            'description' => 'Bülten abonelik formu ve listesi.',
            'routes' => ['subscriber'],
        ],
        'lead' => [
            'label' => 'Gelen Talepler',
            'icon' => 'inbox',
            'description' => 'İletişim formundan gelen talepler.',
            'routes' => ['lead'],
        ],
    ],

];
