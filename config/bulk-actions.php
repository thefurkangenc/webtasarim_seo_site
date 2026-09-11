<?php

use App\Models\Blog\Blog;
use App\Models\BlogCategory\BlogCategory;
use App\Models\Faq\Faq;
use App\Models\Page\Page;
use App\Models\Reference\Reference;
use App\Models\Service\Service;
use App\Models\Testimonial\Testimonial;
use App\Models\WhyChooseUs\WhyChooseUs;
use App\Services\Blog\BlogService;
use App\Services\Faq\FaqService;
use App\Services\Page\PageService;
use App\Services\Reference\ReferenceService;
use App\Services\Service\ServiceService;
use App\Services\Testimonial\TestimonialService;
use App\Services\WhyChooseUs\WhyChooseUsService;

/*
| Liste ekranlarındaki toplu işlemler.
|
| Tek bir motor vardır: App\Services\Bulk\BulkService. Her modül buraya bir
| giriş yazar; ne controller ne de JS modül başına çoğaltılır. Arayüzdeki
| butonlar da bu listeden basılır (<x-admin::bulk-bar module="blog" />).
|
| İşlem türleri:
|   update  -> `values` sabit değerleri yazar; `field` verilirse değer
|              kullanıcıdan alınır (input: select | text)
|   tags    -> seçili kayıtlara etiket EKLER (mevcutlar korunur)
|   delete  -> kaydı siler (modülün kendi servisi üzerinden)
|
| Toplu güncelleme normal `save()` ile yapılır: site haritası/IndexNow
| gözlemcileri ve revizyon geçmişi böylece çalışmaya devam eder. Bu yüzden
| tek seferde işlenecek kayıt sayısı `max` ile sınırlıdır.
*/

return [

    'max' => 100,

    'modules' => [

        'blog' => [
            'model' => Blog::class,
            'service' => BlogService::class,
            'noun' => 'yazı',
            'actions' => [
                'publish' => [
                    'label' => 'Yayınla',
                    'icon' => 'publish',
                    'type' => 'update',
                    'values' => ['status' => Blog::STATUS_PUBLISHED],
                ],
                'draft' => [
                    'label' => 'Taslağa al',
                    'icon' => 'drafts',
                    'type' => 'update',
                    'values' => ['status' => Blog::STATUS_DRAFT],
                ],
                'category' => [
                    'label' => 'Kategori ata',
                    'icon' => 'category',
                    'type' => 'update',
                    'field' => 'blog_category_id',
                    'input' => 'select',
                    'placeholder' => 'Kategori seçin',
                    'options_from' => [BlogCategory::class, 'name'],
                ],
                'tags' => [
                    'label' => 'Etiket ekle',
                    'icon' => 'sell',
                    'type' => 'tags',
                    'input' => 'text',
                    'placeholder' => 'Etiketler (virgülle)',
                ],
                'delete' => [
                    'label' => 'Sil',
                    'icon' => 'delete',
                    'type' => 'delete',
                    'danger' => true,
                ],
            ],
        ],

        'page' => [
            'model' => Page::class,
            'service' => PageService::class,
            'noun' => 'sayfa',
            'actions' => [
                'publish' => [
                    'label' => 'Yayınla',
                    'icon' => 'publish',
                    'type' => 'update',
                    'values' => ['status' => Page::STATUS_PUBLISHED],
                ],
                'draft' => [
                    'label' => 'Taslağa al',
                    'icon' => 'drafts',
                    'type' => 'update',
                    'values' => ['status' => Page::STATUS_DRAFT],
                ],
                'delete' => [
                    'label' => 'Sil',
                    'icon' => 'delete',
                    'type' => 'delete',
                    'danger' => true,
                ],
            ],
        ],

        'service' => [
            'model' => Service::class,
            'service' => ServiceService::class,
            'noun' => 'hizmet',
            'actions' => [
                'publish' => [
                    'label' => 'Yayınla',
                    'icon' => 'publish',
                    'type' => 'update',
                    'values' => ['status' => Service::STATUS_PUBLISHED],
                ],
                'draft' => [
                    'label' => 'Taslağa al',
                    'icon' => 'drafts',
                    'type' => 'update',
                    'values' => ['status' => Service::STATUS_DRAFT],
                ],
                'delete' => [
                    'label' => 'Sil',
                    'icon' => 'delete',
                    'type' => 'delete',
                    'danger' => true,
                ],
            ],
        ],

        'faq' => [
            'model' => Faq::class,
            'service' => FaqService::class,
            'noun' => 'soru',
            'actions' => [
                'activate' => ['label' => 'Yayınla', 'icon' => 'visibility', 'type' => 'update', 'values' => ['is_active' => true]],
                'deactivate' => ['label' => 'Gizle', 'icon' => 'visibility_off', 'type' => 'update', 'values' => ['is_active' => false]],
                'delete' => ['label' => 'Sil', 'icon' => 'delete', 'type' => 'delete', 'danger' => true],
            ],
        ],

        'reference' => [
            'model' => Reference::class,
            'service' => ReferenceService::class,
            'noun' => 'referans',
            'actions' => [
                'activate' => ['label' => 'Yayınla', 'icon' => 'visibility', 'type' => 'update', 'values' => ['is_active' => true]],
                'deactivate' => ['label' => 'Gizle', 'icon' => 'visibility_off', 'type' => 'update', 'values' => ['is_active' => false]],
                'delete' => ['label' => 'Sil', 'icon' => 'delete', 'type' => 'delete', 'danger' => true],
            ],
        ],

        'testimonial' => [
            'model' => Testimonial::class,
            'service' => TestimonialService::class,
            'noun' => 'yorum',
            'actions' => [
                'activate' => ['label' => 'Yayınla', 'icon' => 'visibility', 'type' => 'update', 'values' => ['is_active' => true]],
                'deactivate' => ['label' => 'Gizle', 'icon' => 'visibility_off', 'type' => 'update', 'values' => ['is_active' => false]],
                'delete' => ['label' => 'Sil', 'icon' => 'delete', 'type' => 'delete', 'danger' => true],
            ],
        ],

        'why-choose-us' => [
            'model' => WhyChooseUs::class,
            'service' => WhyChooseUsService::class,
            'noun' => 'madde',
            'actions' => [
                'activate' => ['label' => 'Yayınla', 'icon' => 'visibility', 'type' => 'update', 'values' => ['is_active' => true]],
                'deactivate' => ['label' => 'Gizle', 'icon' => 'visibility_off', 'type' => 'update', 'values' => ['is_active' => false]],
                'delete' => ['label' => 'Sil', 'icon' => 'delete', 'type' => 'delete', 'danger' => true],
            ],
        ],
    ],
];
