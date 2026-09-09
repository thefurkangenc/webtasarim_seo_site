<?php

/*
| İzinlerin tek kaynağı. RolePermissionSeeder bu dosyayı okur.
|
| Yeni izin eklemek: aşağıdaki 'permissions' dizisine bir satır ekle ve
| `php artisan db:seed --class=RolePermissionSeeder` çalıştır.
| Seeder tekrar çalıştırılabilir — mevcut kayıtlar ve rol atamaları korunur.
|
| İzin adı serbesttir; önerilen kalıp <modül>.<eylem> ya da
| <üst>.<modül>.<eylem> (örn. company.employee.index).
*/

return [

    /*
    | Panelde izinleri gruplamak için kullanılan kategori etiketleri.
    | Anahtar, izin satırındaki 'category' değeriyle eşleşir.
    */
    'categories' => [
        'user' => 'Kullanıcılar',
        'role' => 'Roller ve İzinler',
        'media' => 'Medya',
        'setting' => 'Site Ayarları',
        'ai' => 'Yapay Zeka',
        'blog' => 'Blog',
        'testimonial' => 'Müşteri Yorumları',
        'reference' => 'Referanslar',
        'faq' => 'Sıkça Sorulan Sorular',
        'why-choose-us' => 'Neden Biz',
        'hero' => 'Tanıtım Alanı',
    ],

    'permissions' => [

        ['name' => 'user.view', 'label' => 'Kullanıcı - Listele', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.create', 'label' => 'Kullanıcı - Ekle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.update', 'label' => 'Kullanıcı - Düzenle', 'category' => 'user', 'guard_name' => 'web'],
        ['name' => 'user.delete', 'label' => 'Kullanıcı - Sil', 'category' => 'user', 'guard_name' => 'web'],

        ['name' => 'role.view', 'label' => 'Rol - Listele', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.create', 'label' => 'Rol - Ekle', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.update', 'label' => 'Rol - Düzenle', 'category' => 'role', 'guard_name' => 'web'],
        ['name' => 'role.delete', 'label' => 'Rol - Sil', 'category' => 'role', 'guard_name' => 'web'],

        ['name' => 'media.view', 'label' => 'Medya - Görüntüle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.create', 'label' => 'Medya - Yükle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.update', 'label' => 'Medya - Düzenle', 'category' => 'media', 'guard_name' => 'web'],
        ['name' => 'media.delete', 'label' => 'Medya - Sil', 'category' => 'media', 'guard_name' => 'web'],

        ['name' => 'setting.view', 'label' => 'Site Ayarları - Görüntüle', 'category' => 'setting', 'guard_name' => 'web'],
        ['name' => 'setting.update', 'label' => 'Site Ayarları - Düzenle', 'category' => 'setting', 'guard_name' => 'web'],

        ['name' => 'ai-provider.view', 'label' => 'Yapay Zeka Sağlayıcı - Listele', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.create', 'label' => 'Yapay Zeka Sağlayıcı - Ekle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.update', 'label' => 'Yapay Zeka Sağlayıcı - Düzenle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-provider.delete', 'label' => 'Yapay Zeka Sağlayıcı - Sil', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'ai-prompt.view', 'label' => 'Yapay Zeka Şablon - Listele', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.create', 'label' => 'Yapay Zeka Şablon - Ekle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.update', 'label' => 'Yapay Zeka Şablon - Düzenle', 'category' => 'ai', 'guard_name' => 'web'],
        ['name' => 'ai-prompt.delete', 'label' => 'Yapay Zeka Şablon - Sil', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'ai.generate', 'label' => 'Yapay Zeka - İçerik Üret', 'category' => 'ai', 'guard_name' => 'web'],

        ['name' => 'blog-category.view', 'label' => 'Blog Kategori - Listele', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.create', 'label' => 'Blog Kategori - Ekle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.update', 'label' => 'Blog Kategori - Düzenle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog-category.delete', 'label' => 'Blog Kategori - Sil', 'category' => 'blog', 'guard_name' => 'web'],

        ['name' => 'blog.view', 'label' => 'Blog - Listele', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.create', 'label' => 'Blog - Ekle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.update', 'label' => 'Blog - Düzenle', 'category' => 'blog', 'guard_name' => 'web'],
        ['name' => 'blog.delete', 'label' => 'Blog - Sil', 'category' => 'blog', 'guard_name' => 'web'],

        ['name' => 'testimonial.view', 'label' => 'Müşteri Yorumu - Listele', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.create', 'label' => 'Müşteri Yorumu - Ekle', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.update', 'label' => 'Müşteri Yorumu - Düzenle', 'category' => 'testimonial', 'guard_name' => 'web'],
        ['name' => 'testimonial.delete', 'label' => 'Müşteri Yorumu - Sil', 'category' => 'testimonial', 'guard_name' => 'web'],

        ['name' => 'reference.view', 'label' => 'Referans - Listele', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.create', 'label' => 'Referans - Ekle', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.update', 'label' => 'Referans - Düzenle', 'category' => 'reference', 'guard_name' => 'web'],
        ['name' => 'reference.delete', 'label' => 'Referans - Sil', 'category' => 'reference', 'guard_name' => 'web'],

        ['name' => 'faq.view', 'label' => 'SSS - Listele', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.create', 'label' => 'SSS - Ekle', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.update', 'label' => 'SSS - Düzenle', 'category' => 'faq', 'guard_name' => 'web'],
        ['name' => 'faq.delete', 'label' => 'SSS - Sil', 'category' => 'faq', 'guard_name' => 'web'],

        ['name' => 'why-choose-us.view', 'label' => 'Neden Biz - Listele', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.create', 'label' => 'Neden Biz - Ekle', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.update', 'label' => 'Neden Biz - Düzenle', 'category' => 'why-choose-us', 'guard_name' => 'web'],
        ['name' => 'why-choose-us.delete', 'label' => 'Neden Biz - Sil', 'category' => 'why-choose-us', 'guard_name' => 'web'],

        // Tekil kayıt modülü: ekleme/silme izni yok.
        ['name' => 'hero.view', 'label' => 'Tanıtım Alanı - Görüntüle', 'category' => 'hero', 'guard_name' => 'web'],
        ['name' => 'hero.update', 'label' => 'Tanıtım Alanı - Düzenle', 'category' => 'hero', 'guard_name' => 'web'],

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
        'admin' => '*',
        'editor' => ['media.*', 'ai.generate', 'blog.*', 'blog-category.view', 'testimonial.*', 'reference.*', 'faq.*', 'why-choose-us.*', 'hero.*'],
    ],

];
