<?php

use App\Models\Blog\Blog;
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
| Revizyon geçmişi. Modele App\Models\Concerns\HasRevisions eklenir; kayıt
| her değiştiğinde ÖNCEKİ hali saklanır ve panelden geri yüklenebilir.
|
| Buradaki liste yalnızca arayüz içindir (filtre, etiket, düzenleme adresi).
| Bir modelin revizyon tutması trait'e bağlıdır, bu listeye değil.
*/

return [

    // Kayıt başına saklanacak en fazla revizyon; eskiler kendiliğinden silinir.
    'keep' => 25,

    /*
    | Anlık görüntüye girmeyen kolonlar. Kimlik ve zaman damgaları geri
    | yüklenmez; sayaçlar ve puanlar içerikten yeniden hesaplanır.
    */
    'ignore' => [
        'id', 'created_at', 'updated_at', 'deleted_at',
        'seo_score', 'readability_score', 'score_checks', 'analyzed_at',
    ],

    /*
    | Revizyon tutan modeller.
    |
    | service: geri yükleme bu servisin `update` metoduna verilir — slug/path
    | üretimi, alt ağaç yeniden yazımı ve otomatik 301 gibi kurallar orada
    | yaşıyor, kopyalanmamalı. null ise trait'in genel geri yüklemesi kullanılır.
    */
    'models' => [
        'page' => [
            'class' => Page::class,
            'label' => 'Sayfalar',
            'icon' => 'description',
            'service' => PageService::class,
            'edit_route' => 'admin.page.edit',
        ],
        'blog' => [
            'class' => Blog::class,
            'label' => 'Blog',
            'icon' => 'article',
            'service' => BlogService::class,
            'edit_route' => 'admin.blog.edit',
        ],
        'service' => [
            'class' => Service::class,
            'label' => 'Hizmetler',
            'icon' => 'home_repair_service',
            'service' => ServiceService::class,
            'edit_route' => 'admin.service.edit',
        ],
        'faq' => [
            'class' => Faq::class,
            'label' => 'Sıkça Sorulan Sorular',
            'icon' => 'help',
            'service' => FaqService::class,
            'edit_route' => null,
        ],
        'reference' => [
            'class' => Reference::class,
            'label' => 'Referanslar',
            'icon' => 'handshake',
            'service' => ReferenceService::class,
            'edit_route' => null,
        ],
        'testimonial' => [
            'class' => Testimonial::class,
            'label' => 'Müşteri Yorumları',
            'icon' => 'reviews',
            'service' => TestimonialService::class,
            'edit_route' => null,
        ],
        'why-choose-us' => [
            'class' => WhyChooseUs::class,
            'label' => 'Neden Biz',
            'icon' => 'workspace_premium',
            'service' => WhyChooseUsService::class,
            'edit_route' => null,
        ],
    ],

    /*
    | Karşılaştırma ekranındaki alan adları. Listede olmayan alan ham
    | anahtarıyla gösterilir.
    */
    'labels' => [
        'title' => 'Başlık',
        'name' => 'Ad',
        'question' => 'Soru',
        'answer' => 'Cevap',
        'slug' => 'Adres (slug)',
        'path' => 'Tam adres',
        'excerpt' => 'Özet',
        'content' => 'İçerik',
        'description' => 'Açıklama',
        'template' => 'Şablon',
        'status' => 'Durum',
        'sort_order' => 'Sıra',
        'published_at' => 'Yayın tarihi',
        'is_featured' => 'Öne çıkan',
        'parent_id' => 'Üst sayfa',
        'blog_category_id' => 'Kategori',
        'user_id' => 'Yazar',
        'rating' => 'Puan',
        'url' => 'Adres',
        'seo.meta_title' => 'SEO başlığı',
        'seo.meta_description' => 'SEO açıklaması',
        'seo.meta_keywords' => 'Anahtar kelimeler',
        'seo.canonical_url' => 'Canonical adres',
        'seo.robots_index' => 'Dizine eklensin',
        'seo.robots_follow' => 'Bağlantılar izlensin',
        'seo.og_media_id' => 'Paylaşım görseli',
        'seo.schema_type' => 'Schema türü',
        'seo.schema_json' => 'Schema JSON',
        'seo.schema_override' => 'Schema elle yazıldı',
        'seo.focus_keyword' => 'Odak kelime',
        'tags' => 'Etiketler',
        'faqs' => 'Bağlı SSS',
        'regions' => 'Hizmet bölgeleri',
        'media.cover' => 'Kapak görseli',
        'media.logo' => 'Logo',
        'media.photo' => 'Fotoğraf',
        'media.gallery' => 'Galeri',
    ],

    // Karşılaştırmada uzun metinler bu uzunlukta kırpılır.
    'preview_length' => 600,
];
