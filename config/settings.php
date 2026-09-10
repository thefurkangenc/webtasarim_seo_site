<?php

/*
| Site ayarları. Sekme listesi tek kaynaktır; sidebar bu diziden üretilir.
| Firma gibi key-value sekmeler: tabs/{grup}.blade.php + FormRequest + update metodu.
| Sosyal medya ve entegrasyonlar: kendi controller'ı + tabs/{grup}.blade.php.
*/

return [

    'groups' => [
        'company' => [
            'title' => 'Firma Bilgileri',
            'icon' => 'apartment',
        ],
        'contents' => [
            'title' => 'İçerikler',
            'icon' => 'article',
        ],
        'social' => [
            'title' => 'Sosyal Medya',
            'icon' => 'share',
        ],
        'seo' => [
            'title' => 'SEO',
            'icon' => 'travel_explore',
        ],
        'schema' => [
            'title' => 'Schema.org',
            'icon' => 'data_object',
        ],
        'mail' => [
            'title' => 'E-Posta',
            'icon' => 'mail',
        ],
        'contact' => [
            'title' => 'İletişim Formu',
            'icon' => 'inbox',
        ],
        'tracking' => [
            'title' => 'İzleme Kodları',
            'icon' => 'monitoring',
        ],
        'analytics' => [
            'title' => 'Analitik (GA4)',
            'icon' => 'insights',
        ],
        'cookie' => [
            'title' => 'Çerez Çubuğu',
            'icon' => 'cookie',
        ],
        'integrations' => [
            'title' => 'Entegrasyonlar',
            'icon' => 'extension',
        ],
        'maintenance' => [
            'title' => 'Bakım Modu',
            'icon' => 'construction',
        ],
    ],

    /*
    | Kayıt yokken formlar ve ön yüz bu metinlerle çalışır. İlk kayıttan
    | sonra settings tablosundaki değer geçerli olur.
    */
    'defaults' => [
        'analytics' => [
            'property_id' => '',
            'client_email' => '',
            // service_account: şifreli tam JSON — defaults'ta boş, DB'de Crypt ile.
        ],
        'schema' => [
            // Google, bir web ajansı için LocalBusiness'ın alt türü olan
            // ProfessionalService'i önerir. Fiziksel adres/saat yayınlamak
            // istemeyen kullanıcı "Organization"a çeker.
            'business_type' => 'ProfessionalService',
            'founding_year' => '',
            'tax_id' => '',
            'tax_office' => '',
            'price_range' => '₺₺',
            'area_served' => 'Türkiye',
            'same_as' => '',
            'search_url' => '',
            'description' => '',
            // Gün gün çalışma saatleri, JSON: {"mon":{"closed":false,"opens":"09:00","closes":"18:00"}, ...}
            'opening_hours' => '',
        ],
        'contact' => [
            'enabled' => '1',
            'to_email' => '',
            'cc_email' => '',
            'subject' => 'İletişim formu: {name}',
            'heading' => 'Bize yazın',
            'intro' => 'Sorularınız için formu doldurun; en kısa sürede dönüş yaparız.',
            'success_message' => 'Mesajınız alındı. En kısa sürede dönüş yapacağız.',
            'error_message' => 'Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.',
            'auto_reply_enabled' => '0',
            'auto_reply_subject' => 'Mesajınız bize ulaştı',
            'auto_reply_body' => "Merhaba {name},\n\nMesajınız için teşekkür ederiz. En kısa sürede sizinle iletişime geçeceğiz.\n\nSaygılarımızla",
            'privacy_required' => '1',
            'privacy_text' => 'Kişisel verilerimin {kvkk} kapsamında işlenmesini kabul ediyorum.',
        ],
        'maintenance' => [
            'enabled' => '0',
            'title' => 'Kısa bir ara veriyoruz',
            'message' => 'Sitemiz şu anda güncelleniyor. Lütfen daha sonra tekrar deneyin.',
            'retry_after' => '',
            'bypass_secret' => '',
        ],
        'cookie' => [
            'enabled' => '0',
            'title' => 'Çerezleri kullanıyoruz',
            'description' => 'Deneyiminizi iyileştirmek, trafiği ölçmek ve (onayınızla) pazarlama yapmak için çerez kullanıyoruz. Zorunlu çerezler sitenin çalışması için gereklidir; diğerlerini reddedebilirsiniz. Ayrıntılar için {policy} sayfamıza bakabilirsiniz.',
            'accept_label' => 'Tümünü kabul et',
            'reject_label' => 'Yalnızca zorunlular',
            'customize_label' => 'Tercihler',
            'save_label' => 'Tercihleri kaydet',
            'policy_label' => 'çerez politikası',
            'necessary_title' => 'Zorunlu',
            'necessary_description' => 'Oturum, güvenlik ve çerez tercihlerinizi hatırlamak için kullanılır. Kapatılamaz.',
            'functional_title' => 'İşlevsel',
            'functional_description' => 'Canlı destek (Tawk.to) gibi sohbet araçlarının çalışması için gerekir.',
            'analytics_title' => 'Analitik',
            'analytics_description' => 'Ziyaretçi istatistikleri: Google Analytics, Google Tag Manager ve Yandex Metrica.',
            'marketing_title' => 'Pazarlama',
            'marketing_description' => 'Reklam ve dönüşüm pikselleri: Meta, TikTok, LinkedIn ve Bing UET.',
            'lifetime_days' => '180',
            'version' => '1',
        ],
    ],

    /*
    | Harita boşken Türkiye'ye bakılır. Kullanıcı bir nokta seçince
    | latitude/longitude bu varsayılanların yerine geçer.
    */
    'map' => [
        'default_lat' => 39.9334,
        'default_lng' => 32.8597,
        'default_zoom' => 6,
        'selected_zoom' => 16,
    ],

];
