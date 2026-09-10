<?php

/*
| Admin sidebar menüsü. sidebar.blade.php bu dosyadan üretilir, elle düzenlenmez.
|
| Grup:  ['title' => 'Başlık', 'items' => [...]]
| Öğe:   [
|            'title'      => 'Görünen ad',
|            'icon'       => 'material symbols ikon adı',   (sadece üst seviye öğede)
|            'route'      => 'admin.blog.index',            (children yoksa zorunlu)
|            'active'     => 'admin.blog.*',                (opsiyonel; yoksa route kullanılır)
|            'permission' => 'blog.index',                  (opsiyonel; yoksa herkese açık)
|            'children'   => [ ...alt öğeler... ],          (opsiyonel)
|        ]
|
| Bir öğe, izni olmayan kullanıcıya gösterilmez. Tüm alt öğeleri gizlenen bir
| üst öğe de otomatik gizlenir.
*/

return [

    [
        'title' => 'Modüller',
        'items' => [
            [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'route' => 'admin.dashboard',
            ],
            [
                'title' => 'Tanıtım Alanı',
                'icon' => 'wallpaper',
                'route' => 'admin.hero.index',
                'active' => 'admin.hero.*',
                'permission' => 'hero.index',
            ],
            [
                'title' => 'Sayfalar',
                'icon' => 'description',
                'route' => 'admin.page.index',
                'active' => 'admin.page.*',
                'permission' => 'page.index',
            ],
            [
                'title' => 'Blog',
                'icon' => 'article',
                'children' => [
                    [
                        'title' => 'Yazılar',
                        'route' => 'admin.blog.index',
                        'active' => 'admin.blog.*',
                        'permission' => 'blog.index',
                    ],
                    [
                        'title' => 'Kategoriler',
                        'route' => 'admin.blog-category.index',
                        'active' => 'admin.blog-category.*',
                        'permission' => 'blog-category.index',
                    ],
                ],
            ],
            [
                'title' => 'Hizmetler',
                'icon' => 'design_services',
                'children' => [
                    [
                        'title' => 'Hizmetler',
                        'route' => 'admin.service.index',
                        'active' => 'admin.service.*',
                        'permission' => 'service.index',
                    ],
                    [
                        'title' => 'Hizmet Bölgeleri',
                        'route' => 'admin.service-region.index',
                        'active' => 'admin.service-region.*',
                        'permission' => 'service-region.index',
                    ],
                ],
            ],
            [
                'title' => 'Müşteri Yorumları',
                'icon' => 'reviews',
                'route' => 'admin.testimonial.index',
                'active' => 'admin.testimonial.*',
                'permission' => 'testimonial.index',
            ],
            [
                'title' => 'Referanslar',
                'icon' => 'handshake',
                'route' => 'admin.reference.index',
                'active' => 'admin.reference.*',
                'permission' => 'reference.index',
            ],
            [
                'title' => 'Sıkça Sorulan Sorular',
                'icon' => 'quiz',
                'route' => 'admin.faq.index',
                'active' => 'admin.faq.*',
                'permission' => 'faq.index',
            ],
            [
                'title' => 'Neden Biz',
                'icon' => 'verified',
                'route' => 'admin.why-choose-us.index',
                'active' => 'admin.why-choose-us.*',
                'permission' => 'why-choose-us.index',
            ],
        ],
    ],

    [
        'title' => 'Kullanıcı Yönetimi',
        'items' => [
            [
                'title' => 'Roller',
                'icon' => 'admin_panel_settings',
                'route' => 'admin.role.index',
                'active' => 'admin.role.*',
                'permission' => 'role.index',
            ],
        ],
    ],

    [
        'title' => 'Genel',
        'items' => [

            [
                'title' => 'Menüler',
                'icon' => 'menu',
                'route' => 'admin.menu.index',
                'active' => 'admin.menu.*',
                'permission' => 'menu.index',
            ],
            [
                'title' => 'Yönlendirmeler',
                'icon' => 'alt_route',
                'route' => 'admin.redirect.index',
                'active' => 'admin.redirect.*',
                'permission' => 'redirect.index',
            ],

            [
                'title' => 'Medya Kütüphanesi',
                'icon' => 'perm_media',
                'route' => 'admin.media.index',
                'active' => 'admin.media.*',
                'permission' => 'media.index',
            ],
            [
                'title' => 'Site Ayarları',
                'icon' => 'settings',
                'route' => 'admin.setting.index',
                'active' => 'admin.setting.*',
                'permission' => 'setting.index',
            ],
            [
                'title' => 'Log Kayıtları',
                'icon' => 'history',
                'route' => 'admin.activity-log.index',
                'active' => 'admin.activity-log.*',
                'permission' => 'activity-log.index',
            ],
            [
                'title' => 'Yapay Zeka',
                'icon' => 'smart_toy',
                'children' => [
                    [
                        'title' => 'Sağlayıcılar',
                        'route' => 'admin.ai-provider.index',
                        'active' => 'admin.ai-provider.*',
                        'permission' => 'ai-provider.index',
                    ],
                    [
                        'title' => 'Prompt Şablonları',
                        'route' => 'admin.ai-prompt.index',
                        'active' => 'admin.ai-prompt.*',
                        'permission' => 'ai-prompt.index',
                    ],
                ],
            ],
        ],
    ],

];
