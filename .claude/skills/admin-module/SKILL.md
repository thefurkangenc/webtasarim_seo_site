---
name: admin-module
description: Use when creating a new admin panel module (blog, services, sliders, settings, users...) or adding CRUD to an existing one. Gives the ordered end-to-end recipe - migration, model, service, form requests, controller, routes, permissions, blade views, ajax modal form, page JS and sidebar entry - with the exact file paths to create.
---

# Yeni Admin Modülü Kurulumu

Bu reçete bir modülü uçtan uca kurar. Adımları **sırayla** uygula; her adım
bir sonrakinin girdisidir.

Birlikte kullan: `laravel-architecture` (katman kuralları),
`trezo-ui` (markup), `admin-js` (frontend).

## 0. Önce netleştir

Kod yazmadan önce şunlar belli olmalı — belli değilse **sor**:

- Alanlar: isim, tip, zorunluluk, doğrulama sınırları
- İlişkiler (kategori, kullanıcı, üst kayıt)
- Görsel/dosya var mı, kaç tane, hangi boyut sınırı
- Zengin metin (Quill) gerekiyor mu
- Liste ekranında hangi kolonlar, hangi filtreler
- Sıralama (`sort_order`) gerekiyor mu
- Detay (`show`) sayfası gerekiyor mu, yoksa sadece liste + modal mı

## 1. İsim türetme

Modül `BlogCategory` örneği:

| Şey | Değer |
|---|---|
| PHP namespace | `BlogCategory` |
| Klasör / route / view / js | `blog-category` |
| Tablo | `blog_categories` |
| İzinler | `blog-category.view` / `.create` / `.update` / `.delete` |
| Route adları | `admin.blog-category.index` vb. |

## 2. Migration

```sh
php artisan make:migration create_blog_categories_table
```

Konvansiyonlar:
- Durum alanı: `status` (boolean, default `true`)
- Sıralama: `sort_order` (unsignedInteger, default `0`)
- Görsel: `image` (string, nullable) — yolu tutar, URL değil
- Slug: `slug` (string, unique)
- Yumuşak silme yalnızca gerçekten geri alınabilir olması gereken içerikte

## 3. Model

`app/Models/BlogCategory/BlogCategory.php` — `Admin/` segmenti **yok**.
`$fillable`, `casts()` metodu, ilişkiler. İş kuralı koyma.

## 4. Service

`app/Services/BlogCategory/BlogCategoryService.php`

Standart public yüzey:

```php
public function list(array $filters): LengthAwarePaginator
public function formData(?Model $record): array      // modal formunun ihtiyacı (kayıt + select seçenekleri)
public function create(array $data): Model
public function update(Model $record, array $data): Model
public function delete(Model $record): void
```

`formData()` modal formuna gidecek her şeyi tek dizide döndürür:

```php
public function formData(?BlogCategory $category): array
{
    return [
        'category' => $category,
        'parents'  => BlogCategory::where('status', true)->pluck('name', 'id'),
    ];
}
```

Private metot kuralı: sadece uzun ya da paylaşılan bloklar için.
Detaylar `laravel-architecture` skill'inde.

## 5. Form Request'ler

`app/Http/Requests/Admin/BlogCategory/` altında üç dosya:

| Dosya | İş |
|---|---|
| `BlogCategoryCreateRequest` | `authorize()` -> `blog-category.create` |
| `BlogCategoryUpdateRequest` | `authorize()` -> `blog-category.update`, unique kuralları `ignore()` ile |
| `BlogCategoryFilterRequest` | liste parametreleri: `search`, `sort`, `direction`, `page`, `per_page` + modüle özel filtreler |

`FilterRequest` kuralları `core/table.js`'in gönderdiği parametrelerle
birebir eşleşmelidir; eşleşmezse liste sessizce filtresiz döner.

Doğrulama mesajları Türkçe.

## 6. Controller

`app/Http/Controllers/Admin/BlogCategory/BlogCategoryController.php`

Metotlar: `index`, `datatable`, `form`, `store`, `update`, `destroy`.
Detay sayfası varsa `show`. Şablon `laravel-architecture` skill'inde.

## 7. Route

`routes/admin.php` içine modül bloğu:

```php
Route::prefix('blog-category')->name('blog-category.')->controller(BlogCategoryController::class)->group(function () {
    Route::get('/', 'index')->name('index')->middleware('permission:blog-category.view');
    Route::get('/datatable', 'datatable')->name('datatable')->middleware('permission:blog-category.view');
    Route::get('/form/{blog_category?}', 'form')->name('form')->middleware('permission:blog-category.view');
    Route::post('/', 'store')->name('store');
    Route::put('/{blog_category}', 'update')->name('update');
    Route::delete('/{blog_category}', 'destroy')->name('destroy');
});
```

Yazma işlemlerinin yetkisi FormRequest'in `authorize()` metodundadır,
route middleware'i tekrar etmez.

## 8. İzinler

Seeder'a dört izni ekle ve `super-admin` rolüne bağla:

```php
foreach (['view', 'create', 'update', 'delete'] as $action) {
    Permission::firstOrCreate(['name' => "blog-category.{$action}"]);
}
```

Seeder'ı çalıştırmayı unutma.

## 9. View'lar

```
resources/views/admin/pages/blog-category/index.blade.php
resources/views/admin/pages/blog-category/modals/form.blade.php
resources/views/admin/pages/blog-category/show.blade.php     <- sadece gerekiyorsa
```

- `index.blade.php`: breadcrumb + kart + arama/filtre + boş `<tbody>` + "Yeni Ekle" butonu.
  Satırlar JS ile basılır, Blade'de `@foreach` **yok**.
- `modals/form.blade.php`: sadece `<form>` ve alanları. Kart kabuğu ya da
  modal iskeleti tekrar edilmez — `ajax-modal` onu sağlar.

Form `action` ve `method`'u kaydın varlığına göre kurulur:

```blade
<form action="{{ $category ? route('admin.blog-category.update', $category) : route('admin.blog-category.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if ($category) @method('PUT') @endif
    ...
</form>
```

Markup kalıpları için `trezo-ui`.

## 10. Sayfa JS

`public/admin/assets/js/pages/blog-category/index.js`

DataTable + AjaxModal + silme akışı. Şablon `admin-js` skill'inde.

## 11. Sayfa CSS

`public/admin/assets/css/pages/blog-category/index.css`

**Yalnızca gerçek ihtiyaç varsa.** Tailwind build kurulu olduğu için çoğu
modülün özel CSS'i olmaz. Boş dosya açma; açtıysan `@push('admin.css')` ile ekle.

## 12. Sidebar

`config/admin-menu.php`'ye giriş ekle. `sidebar.blade.php` elle düzenlenmez.

```php
['title' => 'Kategoriler', 'route' => 'admin.blog-category.index', 'permission' => 'blog-category.view'],
```

## 13. Doğrula

Tarayıcıda aç ve şunları gerçekten dene — "yaptım" demeden önce:

- [ ] Liste yükleniyor, arama ve sıralama çalışıyor
- [ ] Sayfalama çalışıyor
- [ ] "Yeni Ekle" modalı açılıyor
- [ ] Boş form gönderildiğinde hatalar alan altında görünüyor
- [ ] Kayıt oluşuyor, modal kapanıyor, liste tazeleniyor
- [ ] Düzenleme modalı mevcut değerlerle doluyor
- [ ] Görsel yükleniyor, güncellemede eskisi siliniyor
- [ ] Silme onayı çıkıyor ve kayıt siliniyor
- [ ] Yetkisiz kullanıcı sidebar'da modülü görmüyor ve route'a erişemiyor
- [ ] Dark mode bozulmamış

## Oluşturulan dosyaların özeti

```
database/migrations/xxxx_create_blog_categories_table.php
app/Models/BlogCategory/BlogCategory.php
app/Services/BlogCategory/BlogCategoryService.php
app/Http/Requests/Admin/BlogCategory/BlogCategoryCreateRequest.php
app/Http/Requests/Admin/BlogCategory/BlogCategoryUpdateRequest.php
app/Http/Requests/Admin/BlogCategory/BlogCategoryFilterRequest.php
app/Http/Controllers/Admin/BlogCategory/BlogCategoryController.php
resources/views/admin/pages/blog-category/index.blade.php
resources/views/admin/pages/blog-category/modals/form.blade.php
public/admin/assets/js/pages/blog-category/index.js
```

Değiştirilenler: `routes/admin.php`, `config/admin-menu.php`, izin seeder'ı.
