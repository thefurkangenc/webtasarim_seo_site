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

> Slug kolonu varsa `->unique()` ver ve serviste
> `App\Support\Slug::unique($data['slug'] ?? null ?: $data['name'], '<tablo>', $id)`
> kullan — Türkçe karakterleri çevirir, çakışmada sona sayı ekler.

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

`config/permissions.php` içine kategori ve izinleri ekle:

```php
'categories' => [
    'blog-category' => 'Blog Kategorileri',
],

'permissions' => [
    ['name' => 'blog-category.view',   'label' => 'Blog Kategori - Listele', 'category' => 'blog-category', 'guard_name' => 'web'],
    ['name' => 'blog-category.create', 'label' => 'Blog Kategori - Ekle',    'category' => 'blog-category', 'guard_name' => 'web'],
    ['name' => 'blog-category.update', 'label' => 'Blog Kategori - Düzenle', 'category' => 'blog-category', 'guard_name' => 'web'],
    ['name' => 'blog-category.delete', 'label' => 'Blog Kategori - Sil',     'category' => 'blog-category', 'guard_name' => 'web'],
],
```

Sonra `php artisan db:seed --class=RolePermissionSeeder` çalıştır.
Editörün erişmesi gerekiyorsa `roles.editor` desenine `blog-category.*` ekle.

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

## 12b. Görsel alanı varsa

1. Modele `use App\Models\Concerns\HasMedia;` ekle — `image` kolonu **açma**.
2. `config/media.php` → `presets` dizisine kırpma boyutunu ekle
   (`'blog.cover' => ['width' => 1200, 'height' => 630, 'label' => '...']`).
3. Formda `<x-admin::form.image name="cover_media_id" preset="blog.cover"
   :media="$record?->getFirstMedia('cover')" />`.
4. Request'e `'cover_media_id' => ['nullable', 'integer', 'exists:media,id']`.
5. Serviste kaydettikten sonra `$record->syncMedia($data['cover_media_id'] ?? null, 'cover');`
   — `media_id` alanı `$fillable`'a **girmez**, pivot üzerinden bağlanır.
6. Listede/sitede `$record->mediaUrl('cover', 'thumb')`.

## 12c. SEO alanları varsa

1. Modele `use App\Models\Concerns\HasSeo;` ekle — modül tablosuna meta kolonu **açma**.
2. Request'e `use App\Http\Requests\Concerns\ValidatesSharedFields;` ve kurallara
   `...$this->seoRules()`.
3. Formda `<x-admin::form.seo :model="$record" path="blog" />`.
   Kaynak alan adları `title`/`excerpt` değilse `titleSource` / `descriptionSource` ver.
4. Serviste `$record->syncSeo($data['seo'] ?? []);`.
5. Modal içindeki bir formdaysa, modal açıldıktan sonra `initSeoFields(modal.body)` çağır.

## 12d. Etiket alanı varsa

1. Modele `use App\Models\Concerns\HasTags;`.
2. Kurallara `...$this->tagRules()`.
3. Formda `<x-admin::form.tags :model="$record" />`.
4. Serviste `$record->syncTags($data['tags'] ?? []);`.

## 12e. Yapay zeka üretimi varsa

Sunucuda kod yazılmaz. Panelden `/admin/ai-prompt` ekranında modülün anahtarıyla
(`hizmet.content` gibi) bir şablon tanımlanır, sayfa JS'inde:

```js
const output = await aiGenerator.open('hizmet.content', { defaults: { title } });
```

Butonu `@can('ai.generate')` ile sar.

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
