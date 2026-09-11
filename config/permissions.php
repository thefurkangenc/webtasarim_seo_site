<?php

/*
| İzinlerin tek kaynağı. RolePermissionSeeder her satırı veritabanına yazar.
|
| Ad, admin route adının `admin.` öneki silinmiş halidir:
|   admin.blog.index  →  blog.index
| Yeni izin: aşağıdaki diziye bir satır ekle, sonra
| `php artisan db:seed --class=RolePermissionSeeder`.
*/

return [

    'categories' => [
        'user' => 'Kullanıcılar',
        'role' => 'Roller ve İzinler',
        'media' => 'Medya',
        'setting' => 'Site Ayarları',
        'ai' => 'Yapay Zeka',
        'page' => 'Sayfalar',
        'menu' => 'Menüler',
        'redirect' => 'Yönlendirmeler',
        'broken-link' => 'Kırık Linkler',
        'lead' => 'Gelen Talepler',
        'schema' => 'Schema.org',
        'analytics' => 'Analitik (GA4)',
        'seo' => 'SEO Sağlığı',
        'search-console' => 'Search Console',
        'sitemap' => 'Site Haritası',
        'indexnow' => 'Hızlı İndeksleme (IndexNow)',
        'blog' => 'Blog',
        'testimonial' => 'Müşteri Yorumları',
        'reference' => 'Referanslar',
        'faq' => 'Sıkça Sorulan Sorular',
        'why-choose-us' => 'Neden Biz',
        'hero' => 'Tanıtım Alanı',
        'service' => 'Hizmetler',
        'service-region' => 'Hizmet Bölgeleri',
        'activity-log' => 'Log Kayıtları',
    ],

    'permissions' => [

        ['name' => 'user.index', 'label' => 'Kullanıcı - Listele', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.create', 'label' => 'Kullanıcı - Ekle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.store', 'label' => 'Kullanıcı - Kaydet', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.show', 'label' => 'Kullanıcı - Görüntüle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.edit', 'label' => 'Kullanıcı - Düzenle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.update', 'label' => 'Kullanıcı - Güncelle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.destroy', 'label' => 'Kullanıcı - Sil', 'category' => 'user', 'guard_name' => 'web'],

        ['name' => 'role.index', 'label' => 'Rol - Listele', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.datatable', 'label' => 'Rol - Tablo', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.create', 'label' => 'Rol - Ekle', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.store', 'label' => 'Rol - Kaydet', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.edit', 'label' => 'Rol - Düzenle', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.update', 'label' => 'Rol - Güncelle', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.destroy', 'label' => 'Rol - Sil', 'category' => 'role', 'guard_name' => 'web'],

        ['name' => 'media.index', 'label' => 'Medya - Listele', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.datatable', 'label' => 'Medya - Tablo', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.stats', 'label' => 'Medya - İstatistik', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.picker', 'label' => 'Medya - Seçici', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.upload', 'label' => 'Medya - Yükle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.form', 'label' => 'Medya - Form', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.update', 'label' => 'Medya - Güncelle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.recrop', 'label' => 'Medya - Yeniden kırp', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.destroy', 'label' => 'Medya - Sil', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.bulk-move', 'label' => 'Medya - Toplu taşı', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.bulk-delete', 'label' => 'Medya - Toplu sil', 'category' => 'media', 'guard_name' => 'web'],

        ['name' => 'media.folders.index', 'label' => 'Medya Klasör - Listele', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.folders.tree', 'label' => 'Medya Klasör - Ağaç', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.folders.store', 'label' => 'Medya Klasör - Kaydet', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.folders.update', 'label' => 'Medya Klasör - Güncelle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.folders.destroy', 'label' => 'Medya Klasör - Sil', 'category' => 'media', 'guard_name' => 'web'],

        ['name' => 'setting.index', 'label' => 'Site Ayarları - Listele', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.edit', 'label' => 'Site Ayarları - Düzenle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.company.update', 'label' => 'Firma - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.seo.update', 'label' => 'SEO - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.schema.update', 'label' => 'Schema.org - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.analytics.update', 'label' => 'Analitik - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.mail.update', 'label' => 'Posta - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.mail.test', 'label' => 'Posta - Test', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.tracking.update', 'label' => 'İzleme - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.contents.update', 'label' => 'İçerikler - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.contact.update', 'label' => 'İletişim - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.cookie.update', 'label' => 'Çerez - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.maintenance.update', 'label' => 'Bakım - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],

        ['name' => 'social-link.index', 'label' => 'Sosyal Medya - Listele', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'social-link.form', 'label' => 'Sosyal Medya - Form', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'social-link.store', 'label' => 'Sosyal Medya - Kaydet', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'social-link.reorder', 'label' => 'Sosyal Medya - Sırala', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'social-link.update', 'label' => 'Sosyal Medya - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'social-link.destroy', 'label' => 'Sosyal Medya - Sil', 'category' => 'setting', 'guard_name' => 'web'],

        ['name' => 'integration.form', 'label' => 'Entegrasyon - Form', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'integration.toggle', 'label' => 'Entegrasyon - Aç / Kapat', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'integration.update', 'label' => 'Entegrasyon - Güncelle', 'category' => 'setting', 'guard_name' => 'web'],

        ['name' => 'ai-provider.index', 'label' => 'Yapay Zeka Sağlayıcı - Listele', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.datatable', 'label' => 'Yapay Zeka Sağlayıcı - Tablo', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.form', 'label' => 'Yapay Zeka Sağlayıcı - Form', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.store', 'label' => 'Yapay Zeka Sağlayıcı - Kaydet', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.update', 'label' => 'Yapay Zeka Sağlayıcı - Güncelle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.test', 'label' => 'Yapay Zeka Sağlayıcı - Test', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.destroy', 'label' => 'Yapay Zeka Sağlayıcı - Sil', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'ai-prompt.index', 'label' => 'Yapay Zeka Şablon - Listele', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.datatable', 'label' => 'Yapay Zeka Şablon - Tablo', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.form', 'label' => 'Yapay Zeka Şablon - Form', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.store', 'label' => 'Yapay Zeka Şablon - Kaydet', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.update', 'label' => 'Yapay Zeka Şablon - Güncelle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.destroy', 'label' => 'Yapay Zeka Şablon - Sil', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'ai.generate.form', 'label' => 'Yapay Zeka Üretim - Form', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai.generate.store', 'label' => 'Yapay Zeka Üretim - Kaydet', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai.generate.show', 'label' => 'Yapay Zeka Üretim - Görüntüle', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'page.index', 'label' => 'Sayfa - Listele', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.datatable', 'label' => 'Sayfa - Tablo', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.create', 'label' => 'Sayfa - Ekle', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.store', 'label' => 'Sayfa - Kaydet', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.reorder', 'label' => 'Sayfa - Sırala', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.edit', 'label' => 'Sayfa - Düzenle', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.update', 'label' => 'Sayfa - Güncelle', 'category' => 'page', 'guard_name' => 'web'],
        ['name' => 'page.destroy', 'label' => 'Sayfa - Sil', 'category' => 'page', 'guard_name' => 'web'],

        ['name' => 'menu.index', 'label' => 'Menü - Görüntüle', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu.edit', 'label' => 'Menü - Düzenleyici', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu.tree', 'label' => 'Menü - Ağaç', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu.update', 'label' => 'Menü - Güncelle', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu.save-tree', 'label' => 'Menü - Sıralama Kaydet', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu.items.store', 'label' => 'Menü - Öğe Ekle', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu-item.update', 'label' => 'Menü - Öğe Güncelle', 'category' => 'menu', 'guard_name' => 'web'],
        ['name' => 'menu-item.destroy', 'label' => 'Menü - Öğe Sil', 'category' => 'menu', 'guard_name' => 'web'],

        ['name' => 'redirect.index', 'label' => 'Yönlendirme - Görüntüle', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.datatable', 'label' => 'Yönlendirme - Tablo', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.stats', 'label' => 'Yönlendirme - Özet', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.analyze', 'label' => 'Yönlendirme - Zincir Analizi', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.export', 'label' => 'Yönlendirme - Dışa Aktar', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.import', 'label' => 'Yönlendirme - İçe Aktar', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.form', 'label' => 'Yönlendirme - Form', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.store', 'label' => 'Yönlendirme - Ekle', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.toggle', 'label' => 'Yönlendirme - Aç / Kapat', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.update', 'label' => 'Yönlendirme - Güncelle', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'redirect.destroy', 'label' => 'Yönlendirme - Sil', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'not-found.datatable', 'label' => '404 Kayıtları - Tablo', 'category' => 'redirect', 'guard_name' => 'web'],
        ['name' => 'not-found.destroy', 'label' => '404 Kayıtları - Sil', 'category' => 'redirect', 'guard_name' => 'web'],

        ['name' => 'broken-link.index', 'label' => 'Kırık Link - Görüntüle', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.datatable', 'label' => 'Kırık Link - Tablo', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.stats', 'label' => 'Kırık Link - Özet', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.export', 'label' => 'Kırık Link - Dışa Aktar', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.scan', 'label' => 'Kırık Link - Tarama Başlat', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.ignore', 'label' => 'Kırık Link - Yok Say', 'category' => 'broken-link', 'guard_name' => 'web'],
        ['name' => 'broken-link.destroy', 'label' => 'Kırık Link - Sil', 'category' => 'broken-link', 'guard_name' => 'web'],

        ['name' => 'schema.index', 'label' => 'Schema.org - Doğrulama Ekranı', 'category' => 'schema', 'guard_name' => 'web'],
        ['name' => 'schema.preview', 'label' => 'Schema.org - Önizle / Denetle', 'category' => 'schema', 'guard_name' => 'web'],

        ['name' => 'analytics.index', 'label' => 'Analitik - Panel', 'category' => 'analytics', 'guard_name' => 'web'],
        ['name' => 'analytics.data', 'label' => 'Analitik - Özet Veri', 'category' => 'analytics', 'guard_name' => 'web'],
        ['name' => 'analytics.realtime', 'label' => 'Analitik - Canlı Veri', 'category' => 'analytics', 'guard_name' => 'web'],
        ['name' => 'analytics.page-views', 'label' => 'Analitik - Liste Görüntülemeleri', 'category' => 'analytics', 'guard_name' => 'web'],
        ['name' => 'analytics.test', 'label' => 'Analitik - Bağlantı Testi', 'category' => 'analytics', 'guard_name' => 'web'],

        ['name' => 'lead.index', 'label' => 'Gelen Talepler - Listele', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.datatable', 'label' => 'Gelen Talepler - Tablo', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.stats', 'label' => 'Gelen Talepler - Özet', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.show', 'label' => 'Gelen Talepler - Görüntüle', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.update', 'label' => 'Gelen Talepler - Güncelle', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.reply', 'label' => 'Gelen Talepler - Yanıtla', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.read', 'label' => 'Gelen Talepler - Okundu İşaretle', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.bulk', 'label' => 'Gelen Talepler - Toplu İşlem', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.export', 'label' => 'Gelen Talepler - Dışa Aktar', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.destroy', 'label' => 'Gelen Talepler - Sil', 'category' => 'lead', 'guard_name' => 'web'],
        ['name' => 'lead.restore', 'label' => 'Gelen Talepler - Geri Al', 'category' => 'lead', 'guard_name' => 'web'],

        ['name' => 'indexnow.index', 'label' => 'Hızlı İndeksleme - Panel', 'category' => 'indexnow', 'guard_name' => 'web'],
        ['name' => 'indexnow.update', 'label' => 'Hızlı İndeksleme - Ayarları Güncelle', 'category' => 'indexnow', 'guard_name' => 'web'],
        ['name' => 'indexnow.submit', 'label' => 'Hızlı İndeksleme - Adres Bildir', 'category' => 'indexnow', 'guard_name' => 'web'],
        ['name' => 'indexnow.submit-all', 'label' => 'Hızlı İndeksleme - Tüm Adresleri Bildir', 'category' => 'indexnow', 'guard_name' => 'web'],
        ['name' => 'indexnow.regenerate-key', 'label' => 'Hızlı İndeksleme - Anahtarı Yenile', 'category' => 'indexnow', 'guard_name' => 'web'],

        ['name' => 'search-console.index', 'label' => 'Search Console - Panel', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.update', 'label' => 'Search Console - Ayarları Güncelle', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.sites', 'label' => 'Search Console - Mülkleri Listele', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.performance', 'label' => 'Search Console - Arama Performansı', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.sitemaps', 'label' => 'Search Console - Site Haritaları', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.submit', 'label' => 'Search Console - Site Haritası Gönder', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.inspect', 'label' => 'Search Console - URL Denetimi', 'category' => 'search-console', 'guard_name' => 'web'],
        ['name' => 'search-console.test', 'label' => 'Search Console - Bağlantı Testi', 'category' => 'search-console', 'guard_name' => 'web'],

        ['name' => 'seo.index', 'label' => 'SEO Sağlığı - Panel', 'category' => 'seo', 'guard_name' => 'web'],
        ['name' => 'seo.datatable', 'label' => 'SEO Sağlığı - Rapor', 'category' => 'seo', 'guard_name' => 'web'],
        ['name' => 'seo.rescore', 'label' => 'SEO Sağlığı - Yeniden Puanla', 'category' => 'seo', 'guard_name' => 'web'],

        ['name' => 'blog.index', 'label' => 'Blog - Listele', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.datatable', 'label' => 'Blog - Tablo', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.create', 'label' => 'Blog - Ekle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.store', 'label' => 'Blog - Kaydet', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.edit', 'label' => 'Blog - Düzenle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.update', 'label' => 'Blog - Güncelle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.destroy', 'label' => 'Blog - Sil', 'category' => 'blog', 'guard_name' => 'web'],

        ['name' => 'blog-category.index', 'label' => 'Blog Kategori - Listele', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.datatable', 'label' => 'Blog Kategori - Tablo', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.form', 'label' => 'Blog Kategori - Form', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.store', 'label' => 'Blog Kategori - Kaydet', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.reorder', 'label' => 'Blog Kategori - Sırala', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.update', 'label' => 'Blog Kategori - Güncelle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.destroy', 'label' => 'Blog Kategori - Sil', 'category' => 'blog', 'guard_name' => 'web'],

        ['name' => 'testimonial.index', 'label' => 'Müşteri Yorumu - Listele', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.datatable', 'label' => 'Müşteri Yorumu - Tablo', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.form', 'label' => 'Müşteri Yorumu - Form', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.store', 'label' => 'Müşteri Yorumu - Kaydet', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.reorder', 'label' => 'Müşteri Yorumu - Sırala', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.update', 'label' => 'Müşteri Yorumu - Güncelle', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.destroy', 'label' => 'Müşteri Yorumu - Sil', 'category' => 'testimonial', 'guard_name' => 'web'],

        ['name' => 'reference.index', 'label' => 'Referans - Listele', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.datatable', 'label' => 'Referans - Tablo', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.form', 'label' => 'Referans - Form', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.store', 'label' => 'Referans - Kaydet', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.reorder', 'label' => 'Referans - Sırala', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.update', 'label' => 'Referans - Güncelle', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.destroy', 'label' => 'Referans - Sil', 'category' => 'reference', 'guard_name' => 'web'],

        ['name' => 'faq.index', 'label' => 'SSS - Listele', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.datatable', 'label' => 'SSS - Tablo', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.form', 'label' => 'SSS - Form', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.store', 'label' => 'SSS - Kaydet', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.reorder', 'label' => 'SSS - Sırala', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.update', 'label' => 'SSS - Güncelle', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.destroy', 'label' => 'SSS - Sil', 'category' => 'faq', 'guard_name' => 'web'],

        ['name' => 'why-choose-us.index', 'label' => 'Neden Biz - Listele', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.datatable', 'label' => 'Neden Biz - Tablo', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.form', 'label' => 'Neden Biz - Form', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.store', 'label' => 'Neden Biz - Kaydet', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.reorder', 'label' => 'Neden Biz - Sırala', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.heading', 'label' => 'Neden Biz - Başlık', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.update', 'label' => 'Neden Biz - Güncelle', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.destroy', 'label' => 'Neden Biz - Sil', 'category' => 'why-choose-us', 'guard_name' => 'web'],

        ['name' => 'hero.index', 'label' => 'Tanıtım Alanı - Listele', 'category' => 'hero', 'guard_name' => 'web'],
        ['name' => 'hero.update', 'label' => 'Tanıtım Alanı - Güncelle', 'category' => 'hero', 'guard_name' => 'web'],

        ['name' => 'service.index', 'label' => 'Hizmet - Listele', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.datatable', 'label' => 'Hizmet - Tablo', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.create', 'label' => 'Hizmet - Ekle', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.store', 'label' => 'Hizmet - Kaydet', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.reorder', 'label' => 'Hizmet - Sırala', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.edit', 'label' => 'Hizmet - Düzenle', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.update', 'label' => 'Hizmet - Güncelle', 'category' => 'service', 'guard_name' => 'web'],
        ['name' => 'service.destroy', 'label' => 'Hizmet - Sil', 'category' => 'service', 'guard_name' => 'web'],

        ['name' => 'service-region.index', 'label' => 'Hizmet Bölgesi - Listele', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.datatable', 'label' => 'Hizmet Bölgesi - Tablo', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.breadcrumb', 'label' => 'Hizmet Bölgesi - Yol', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.form', 'label' => 'Hizmet Bölgesi - Form', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.store', 'label' => 'Hizmet Bölgesi - Kaydet', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.reorder', 'label' => 'Hizmet Bölgesi - Sırala', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.update', 'label' => 'Hizmet Bölgesi - Güncelle', 'category' => 'service-region', 'guard_name' => 'web'],
        ['name' => 'service-region.destroy', 'label' => 'Hizmet Bölgesi - Sil', 'category' => 'service-region', 'guard_name' => 'web'],

        ['name' => 'activity-log.index', 'label' => 'Log Kayıtları - Görüntüle', 'category' => 'activity-log', 'guard_name' => 'web'],

        ['name' => 'sitemap.index', 'label' => 'Site Haritası - Görüntüle', 'category' => 'sitemap', 'guard_name' => 'web'],
        ['name' => 'sitemap.update', 'label' => 'Site Haritası - Ayarları Güncelle', 'category' => 'sitemap', 'guard_name' => 'web'],
        ['name' => 'sitemap.generate', 'label' => 'Site Haritası - Yeniden Oluştur', 'category' => 'sitemap', 'guard_name' => 'web'],

    ],

    /*
    | Rollere atanacak izinler. '*' tümü demektir; desenler Str::is() ile
    | eşleşir (örn. 'media.*'). Burada olmayan rol seeder'da oluşturulmaz.
    |
    | super-admin ayrıca AppServiceProvider'daki Gate::before ile her izne
    | sahiptir; buradaki kayıt yalnızca rolün var olmasını garanti eder.
    */
    'roles' => [
        'super-admin' => '*',
    ],

];
