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
|            'permission' => 'blog.view',                   (opsiyonel; yoksa herkese açık)
|            'children'   => [ ...alt öğeler... ],          (opsiyonel)
|        ]
|
| Bir öğe, izni olmayan kullanıcıya gösterilmez. Tüm alt öğeleri gizlenen bir
| üst öğe de otomatik gizlenir.
*/

return [
    [
        'title' => 'Genel',
        'items' => [
            [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'route' => 'admin.dashboard',
            ],
            [
                'title' => 'Medya Kütüphanesi',
                'icon' => 'perm_media',
                'route' => 'admin.media.index',
                'active' => 'admin.media.*',
                'permission' => 'media.view',
            ],
            [
                'title' => 'Site Ayarları',
                'icon' => 'settings',
                'route' => 'admin.setting.index',
                'active' => 'admin.setting.*',
                'permission' => 'setting.view',
            ],
        ],
    ],

    [
        'title' => 'İçerik',
        'items' => [
            [
                'title' => 'Blog',
                'icon' => 'article',
                'children' => [
                    [
                        'title' => 'Yazılar',
                        'route' => 'admin.blog.index',
                        'active' => 'admin.blog.*',
                        'permission' => 'blog.view',
                    ],
                    [
                        'title' => 'Kategoriler',
                        'route' => 'admin.blog-category.index',
                        'active' => 'admin.blog-category.*',
                        'permission' => 'blog-category.view',
                    ],
                ],
            ],
        ],
    ],

    [
        'title' => 'Yapay Zeka',
        'items' => [
            [
                'title' => 'Yapay Zeka',
                'icon' => 'smart_toy',
                'children' => [
                    [
                        'title' => 'Sağlayıcılar',
                        'route' => 'admin.ai-provider.index',
                        'active' => 'admin.ai-provider.*',
                        'permission' => 'ai-provider.view',
                    ],
                    [
                        'title' => 'Prompt Şablonları',
                        'route' => 'admin.ai-prompt.index',
                        'active' => 'admin.ai-prompt.*',
                        'permission' => 'ai-prompt.view',
                    ],
                ],
            ],
        ],
    ],
];
