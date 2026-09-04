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
        'editor' => ['media.*', 'ai.generate', 'blog.*', 'blog-category.view'],
    ],

];
