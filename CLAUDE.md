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
| Editör | TinyMCE 7 (GPL self-host, `js/vendor/tinymce/`) |
| Yapay zeka | ChatGPT / DeepSeek / Ollama — panelden yönetilir, kuyrukta çalışır |
| Kuyruk | `database` sürücüsü — `php artisan queue:work` çalışıyor olmalı |
| Select | Choices.js — `<x-admin::form.select>` varsayılan, `plain` ile kapatılır |
| Tarih | Flatpickr — `<x-admin::form.date>`, Türkçe/24 saat |
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

## Route Yükleme Sırası — dikkat

`bootstrap/app.php` route dosyalarını şu sırayla yükler:

```
1. routes/web.php      (ön yüz)
2. routes/admin.php    ("admin" öneki, then: kancasında)
3. routes/pages.php    (dinamik sayfaların catch-all'ı)
```

Laravel route'ları **kayıt sırasına** göre eşleştirir. `routes/pages.php`
içindeki `/{path}` her şeyi yakaladığı için en sonda olmak zorundadır —
`web.php`'nin sonunda durduğunda bile yetmiyor, çünkü admin route'ları ondan
sonra yükleniyor ve catch-all tüm `/admin` adreslerini yutuyor.

**Yeni bir route grubu eklenirse `bootstrap/app.php`'de `pages.php` satırının
ÜSTÜNE eklenir.** Ön yüze yeni bir sabit adres eklenince ayrıca
`App\Support\ReservedPath` onu kendiliğinden rezerve eder (kayıtlı route'ların
sabit ilk segmentlerini toplar), böylece o adla bir sayfa oluşturulamaz.

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
| SEO alanları | `App\Models\Concerns\HasSeo` + `<x-admin::form.seo>` (polymorphic `seo` tablosu) |
| Etiketler | `App\Models\Concerns\HasTags` + `<x-admin::form.tags>` (`tags` + `taggables`) |
| Zengin metin | `<x-admin::form.editor>` — TinyMCE 7, `core/editor.js` |
| Yapay zeka | `App\Services\Ai\AiService` + `core/ai-generator.js`, `/admin/ai-provider`, `/admin/ai-prompt` |
| Alan adı dönüşümü | `App\Support\Field` — `seo.meta_title` → `name="seo[meta_title]"` |
| Benzersiz slug | `App\Support\Slug::unique($deger, 'blogs', $id)` — Türkçe karakter duyarlı |
| Sürükle-bırak sıralama | `HasSortOrder` (model) + `ReordersRecords` (servis) + `ReorderRequest` — `sort_order` formda yok, `core/table.js`'in `reorder` seçeneği |
| Modal iskeleti | `resources/views/admin/layout/modals/ajax-modal.blade.php` (layout'ta include edili) |
| JS çekirdeği | `public/admin/assets/js/core/` — http, form, modal, table, toast, confirm, editor, seo-field, tag-input, ai-generator |
| Giriş | `admin.login` / `admin.logout`, `auth` middleware `routes/admin.php`'de |
| Roller | `super-admin` (Gate::before ile her izne sahip), `admin`, `editor` |
| Log kayıtları (denetim) | `App\Models\Concerns\LogsActivity` + `<x-admin::activity-log-button>`, `/admin/activity-log` |
| Sayfa yöneticisi | `App\Models\Page\Page` + `App\Services\Page\PageService`, `/admin/page` — hiyerarşik, `path` kolonu ön yüz adresini tutar, `/{path}` catch-all (`routes/pages.php`) |
| Menü yöneticisi | `App\Models\Menu\{Menu,MenuItem}` + `App\Services\Menu\{MenuService,MenuRenderer}`, `/admin/menu` — sabit konumlar (header, footer×2), iç içe sürükle-bırak; ön yüzde `MenuRenderer::render('header')` |
| Ön yüz bağlanabilir kayıt | `App\Contracts\LinksToPublicPage` — `publicUrl()` + `publicLinkLabel()`. Menü öğesi bir kayda polimorfik bağlanabilsin diye `Page`/`Service`/`Blog` uygular; yeni bir modül menüden seçilebilir olsun istiyorsa bu arayüzü uygular ve `config/menus.php` > `linkables`'a eklenir |
| Yönlendirme + 404 | `App\Models\Redirect\{Redirect,NotFoundLog}` + `App\Services\Redirect\{RedirectResolver,RedirectService,NotFoundLogger}`, `/admin/redirect`. `NotFoundHttpException` render kancası (`bootstrap/app.php`) ön yüz 404'lerini önce yönlendirmeye çevirir, yoksa `not_found_logs`'a yazar |
| Otomatik 301 | `App\Contracts\RedirectsOnMove` (`redirectableMove()`) + `App\Observers\RedirectObserver` — `config/redirects.php` > `auto_from` listesindeki model (`Page`, `Service`) adresi değişince eski → yeni 301'i kendiliğinden oluşur, zincirler düzleşir |

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

## Paylaşılan Bileşenler

Bu üçü modüle özel değildir; yeni modülde yeniden yazma, çağır.

### SEO

```php
class Blog extends Model { use HasSeo; }
```

```blade
<x-admin::form.seo :model="$blog" path="blog" />
```

```php
$blog->syncSeo($data['seo'] ?? []);   // serviste
$blog->seoMeta();                     // ön yüzde <meta> için çözümlenmiş dizi
```

FormRequest'te `use ValidatesSharedFields;` ve kurallara `...$this->seoRules()`.
Meta başlık/açıklama boşsa modelin `title`/`excerpt` alanına düşer; alan adları
farklıysa modelde `seoFallbacks()` ezilir. **Modül tablosuna meta kolonu açma.**

### Etiketler

```php
class Blog extends Model { use HasTags; }
```

```blade
<x-admin::form.tags :model="$blog" />
```

```php
$blog->syncTags($data['tags'] ?? []);   // olmayan etiket oluşturulur
$blog->tagNames();
```

Kurallara `...$this->tagRules()` eklenir. Eşleşme slug üzerinden yapılır:
"Web Tasarım" ile "web tasarım" aynı etikettir.

### Editör

```blade
<x-admin::form.editor name="content" :value="$blog?->content" :height="560" />
```

TinyMCE 7 self-host (`public/admin/assets/js/vendor/tinymce/`). Görsel butonu
medya seçicisini açar — editöre giren görsel de kütüphaneye kaydolur. Karanlık
mod değişince editör, içerik korunarak yeniden kurulur.

Alan adı nokta notasyonuyla verilir (`seo.meta_title`); bileşen HTML `name`
özniteliğini `seo[meta_title]` yapar, `data-error` yuvası nokta notasyonunda
kalır — Laravel hataları o anahtarla döndürüyor.

### Log Kaydı (denetim/audit)

**Yeni kurulan her modül bunu alır — atlanmaz.** Modelde tek satır:

```php
class Blog extends Model { use LogsActivity; }
```

Bu kadar; ekleme/düzenleme/silme kendiliğinden loglanır (IP, tarayıcı,
işletim sistemi, cihaz, konum, yapan kullanıcı ve alan bazlı diff dahil).
İndex sayfasına buton eklenir:

```blade
<x-admin::activity-log-button module="blog" />
```

`module` değeri modelin `activityLogName()`'i ile (varsayılan: sınıf adının
kebab-case hali) eşleşmeli. Satır aksiyonlarına "Geçmiş" ikonu eklemek için
sayfa JS'inde:

```js
import { historyButton } from '../../core/activity-log.js';
// satır şablonunda: ${historyButton('App\\Models\\Blog\\Blog', item.id)}
```

Model olayı **olmayan** durumlar (sıralama, toplu işlem, ayar kaydetme, giriş/
çıkış, yetkisiz erişim) için `App\Support\Activity::record(...)` — bu proje
zaten `ReordersRecords`, `SettingService`, `MediaService` içinde bağlı, yeni
bir modülde benzer bir toplu/sıralama işlemi varsa aynı kalıp izlenir.

Merkezi liste `/admin/activity-log`. Ayrıntı: `config/activity-log.php`
(modül/olay etiketleri ve ikonları, maskelenecek alanlar, IP-konum ayarları).

## Yapay Zeka Modülü

Sağlayıcılar ve prompt şablonları panelden yönetilir; üretim **kuyrukta** çalışır.

```
/admin/ai-provider   ChatGPT / DeepSeek / Ollama kayıtları (anahtar şifreli saklanır)
/admin/ai-prompt     şablonlar; `key` alanı hangi modülde görüneceğini belirler
```

Bir modüle üretim eklemek:

```js
const output = await aiGenerator.open('blog.content', { defaults: { title } });
if (output) { /* alanları doldur */ }
```

Sunucuda yeni kod gerekmez — panelden o `key` ile bir şablon tanımlamak yeter.
Şablon `{{keywords}}`, `{{title}}`, `{{category}}`, `{{length}}`, `{{notes}}`
yer tutucularını kullanır ve modelden JSON ister; `AiService` kod çitlerini
temizleyip ilk `{` ile son `}` arasını ayrıştırır, başarısızsa üretimi `failed`
işaretler ve ham yanıtı hataya yazar.

Sürücü eklemek: `ChatDriver` arayüzünü uygulayan bir sınıf + `config/ai.php`'ye
bir satır + `AiService::DRIVERS` eşlemesine bir giriş.

> **Kuyruk işçisi çalışmıyorsa hiçbir üretim tamamlanmaz.** Geliştirirken
> `php artisan queue:work` açık olmalı; arayüz 20 saniye sonra bunu uyarır.

## Komutlar

```sh
npm run admin:css            # admin Tailwind derlemesi — yeni class yazdıysan ŞART
npm run admin:css:watch      # geliştirme sırasında
php artisan db:seed --class=RolePermissionSeeder   # izin güncellemesi
php artisan db:seed          # rol/izin + yönetici kullanıcı
php artisan queue:work       # yapay zeka üretimi için ŞART
vendor/bin/pint              # kod formatı
```

## Yapılmayacaklar

- Test yazılmayacak (proje sahibinin kararı).
- Çok dil desteği kurulmayacak — tek dil Türkçe.
- Modüller **talep edilmeden** kurulmayacak; teker teker, sırayla ilerlenir.
