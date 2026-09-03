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
        ],
    ],
];
