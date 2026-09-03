# Proje Kuralları

Kurumsal Web sitesi. Laravel 13 / PHP 8.3.
Ön yüz (Bootstrap) taslak halinde hazır; aktif iş **admin panelidir**.

Mimari kararların gerekçesi: `docs/superpowers/specs/2026-09-03-admin-panel-design.md`

## Stack

| | |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Admin UI | Trezo — Tailwind CSS v4 admin template |
| Yetki | spatie/laravel-permission (`users` tablosu üzerinde) |
| Admin JS | Native ES modules, **jQuery yok** |
| Ön yüz | Bootstrap 5 (ayrı dünya, admin ile karışmaz) |

## İki Ayrı Dünya

- **Ön yüz**: `resources/views/pages/` + `resources/views/layout/` + `public/assets/` → Bootstrap
- **Admin**: `resources/views/admin/` + `public/admin/assets/` → Tailwind

Bir taraftan diğerine CSS/JS/markup taşınmaz.

## Sert Kurallar

1. **Admin'de jQuery yasak.** Native DOM API ve `fetch` kullan.
2. **Tailwind class'ı uydurma.** Admin CSS derlenmiş bir çıktıdır. Yeni class
   yazmadan önce build'in kurulu ve çalışır olduğundan emin ol; emin değilsen
   `resources/views/admin/html/` içindeki template markup'ından kopyala.
3. **Controller ince kalır.** İçinde Eloquent, iş kuralı, `try/catch` olmaz.
4. **Her iş için ayrı metot açma.** Servis metodu ancak blok gerçekten uzunsa
   veya paylaşılıyorsa bölünür.
5. **Kod ve DB İngilizce, arayüz metni Türkçe.** Türkçe metin Blade'e doğrudan
   yazılır, lang dosyası kullanılmaz.
6. Yeni paket eklemeden önce sor.

## Klasör Konvansiyonu

Modül `Blog` için türetmeler — PHP `Blog`, klasör/route `blog`, tablo `blogs`.
Çok kelimeli modül: PHP `BlogCategory`, klasör/route `blog-category`, tablo `blog_categories`.

```
app/Http/Controllers/Admin/Blog/BlogController.php
app/Http/Requests/Admin/Blog/{BlogCreateRequest,BlogUpdateRequest,BlogFilterRequest}.php
app/Services/Blog/BlogService.php          <- Admin/Web ORTAK, Admin/ segmenti yok
app/Models/Blog/Blog.php                   <- Admin/ segmenti yok

routes/admin.php                           <- tüm admin route'ları tek dosyada

resources/views/admin/pages/blog/index.blade.php
resources/views/admin/pages/blog/show.blade.php
resources/views/admin/pages/blog/modals/form.blade.php

public/admin/assets/js/pages/blog/index.js
public/admin/assets/css/pages/blog/index.css   <- SADECE gerçek ihtiyaç varsa; boş dosya açma
```

## JSON Sözleşmesi

```
200  { "success": true,  "message": "...", "data": {...} }
422  { "success": false, "message": "..." }                 iş kuralı hatası
422  { "message": "...", "errors": { "title": ["..."] } }   validation (Laravel default)
403  { "success": false, "message": "Bu işlem için yetkiniz yok." }
```

## Skill'ler — ne zaman hangisi

| Skill | Ne zaman |
|---|---|
| `laravel-architecture` | Controller/Service/Request/Model yazarken veya düzenlerken |
| `admin-module` | Yeni bir admin modülü kurarken (uçtan uca reçete) |
| `trezo-ui` | Admin Blade'i yazarken, herhangi bir arayüz markup'ı üretirken |
| `admin-js` | `public/admin/assets/js/` altında JS yazarken |

Admin tarafında iş yapıyorsan bu skill'leri **kod yazmadan önce** aç.

## Agent'lar

| Agent | İşi |
|---|---|
| `trezo-ui-extractor` | Template HTML'lerinden (her biri 2000-3000 satır) doğru bileşen markup'ını çıkarır |
| `admin-module-builder` | Bir modülü uçtan uca kurar |
| `convention-reviewer` | Yazılan kodu bu kurallara karşı denetler |

## Kurulu Altyapı (Faz 0)

Modüller bunların üzerine kurulur — yeniden yazma, kullan.

| Ne | Nerede |
|---|---|
| Tailwind kaynağı | `resources/css/admin/style.css` → `npm run admin:css` |
| JSON yanıtları | `App\Http\Controllers\Concerns\RespondsWithJson` (`success()`/`error()`) |
| Medya kütüphanesi | `App\Services\Media\MediaService` + `MediaFolderService`, `/admin/media` |
| Modele medya bağlama | `App\Models\Concerns\HasMedia` trait'i (polymorphic, koleksiyonlu) |
| Kırpma | `<x-admin::form.image preset="blog.cover">` + `core/cropper.js` |
| İzinler | `config/permissions.php` (tek kaynak) → `RolePermissionSeeder` |
| Doğrulama metinleri | `lang/tr/validation.php` (tam çeviri, `APP_LOCALE=tr`) |
| Sidebar menü | `config/admin-menu.php` + `App\Services\Admin\MenuService` |
| Form alanları | `<x-admin::form.input|textarea|select|switch|image|actions>` |
| Modal iskeleti | `resources/views/admin/layout/modals/ajax-modal.blade.php` (layout'ta include edili) |
| JS çekirdeği | `public/admin/assets/js/core/` — http, form, modal, table, toast, confirm |
| Giriş | `admin.login` / `admin.logout`, `auth` middleware `routes/admin.php`'de |
| Roller | `super-admin` (Gate::before ile her izne sahip), `admin`, `editor` |

Yeni modülün izinlerini `config/permissions.php` içindeki `permissions` dizisine
ekle, kategorisini `categories`'e yaz ve seeder'ı tekrar çalıştır. Seeder
tekrar çalıştırılabilir: mevcut kayıtlar ve rol atamaları korunur, config'ten
kaldırılan izinler silinmez (yalnızca uyarı basılır).

## Medya Kullanımı

Modelde:

```php
use App\Models\Concerns\HasMedia;

class Blog extends Model
{
    use HasMedia;
}
```

Formda — alan görseli **hemen** yükler ve gizli input'a `media_id` yazar:

```blade
<x-admin::form.image name="cover_media_id" label="Kapak Görseli"
                     preset="blog.cover" :media="$blog?->getFirstMedia('cover')" />
```

Serviste:

```php
$blog->syncMedia($data['cover_media_id'] ?? null, 'cover');
```

Okurken: `$blog->getFirstMedia('cover')`, `$blog->mediaUrl('cover', 'thumb')`.

Kırpma boyutları `config/media.php` içindeki `presets` dizisinden gelir.
Preset tanımlıysa kırpma modalı açılır ve orana kilitlenir; tanımlı değilse
dosya doğrudan yüklenir. **Modelde `image` kolonu açma** — bağlantı
`mediables` pivotu üzerinden kurulur.

## Komutlar

```sh
npm run admin:css            # admin Tailwind derlemesi — yeni class yazdıysan ŞART
npm run admin:css:watch      # geliştirme sırasında
php artisan db:seed --class=RolePermissionSeeder   # izin güncellemesi
php artisan db:seed          # rol/izin + yönetici kullanıcı
vendor/bin/pint              # kod formatı
```

## Yapılmayacaklar

- Test yazılmayacak (proje sahibinin kararı).
- Çok dil desteği kurulmayacak — tek dil Türkçe.
- Modüller **talep edilmeden** kurulmayacak; teker teker, sırayla ilerlenir.
