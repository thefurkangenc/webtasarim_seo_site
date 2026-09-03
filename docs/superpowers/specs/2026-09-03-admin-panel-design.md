# Admin Panel — Mimari Tasarım

Tarih: 2026-09-03
Durum: Onaylandı

## 1. Bağlam

Proje bir web tasarım ajansı sitesi. Ön yüz (Bootstrap tabanlı, `resources/views/pages/`)
taslak olarak hazır. Bu tasarım, siteyi besleyecek admin panelinin mimarisini tanımlar.

Admin arayüzü **Trezo** (Tailwind CSS v4) hazır HTML template'i üzerine kurulur.
Template kaynağı: `resources/views/admin/html/` (219 HTML sayfa + assets).
Çalışan assetler: `public/admin/assets/`.

Ön yüz ve admin **iki ayrı dünyadır**: ön yüz Bootstrap, admin Tailwind.
CSS/JS asla karışmaz.

## 2. Alınan Kararlar

| Konu | Karar | Gerekçe |
|---|---|---|
| Admin CSS | Tailwind v4 CLI build kurulur | `style.css` derlenmiş çıktı; build olmadan yeni utility class'lar sessizce çalışmaz |
| Auth | `users` tablosu, ayrı `admins` tablosu yok | Ön yüzde üyelik/giriş olmayacak, tek kullanıcı tipi var |
| Yetki | `spatie/laravel-permission` | Olgun, rol+izin ihtiyacını tam karşılıyor |
| Dil | Tek dil (TR), Blade'de hardcoded metin | Çok dil ihtiyacı yok; lang dosyası ek yük olurdu |
| İsimlendirme | Kod ve DB İngilizce, arayüz metni Türkçe | Laravel/paket konvansiyonlarıyla uyum |
| Medya | Kendi `MediaService`'imiz | Bağımlılık yok, tam kontrol, ihtiyaç basit |
| Listeleme | AJAX tablo, native ES modules | Modal yapısıyla tutarlı, sayfa yenilenmez |
| Form gönderimi | JSON response, JS tabloyu tazeler | 422 hatalarını alan altına basmak için |
| Test | Yazılmayacak | Hız önceliği (proje sahibinin kararı) |
| Route dosyası | Tek `routes/admin.php` | Modül sayısı artınca bölünecek |
| Sidebar | `config/admin-menu.php` üzerinden dinamik | Her modülde Blade düzenlemek yerine tek satır config |

## 3. Klasör Konvansiyonu

Modül adı örnek: `Blog`. Türetmeler:

- PHP namespace: `Blog` (StudlyCase)
- Route prefix / view / js / css klasörü: `blog` (kebab-case, çok kelimeli: `blog-category`)
- Tablo: `blogs` (snake_case, çoğul)

```
app/Http/Controllers/Admin/Blog/BlogController.php
app/Http/Requests/Admin/Blog/BlogCreateRequest.php
app/Http/Requests/Admin/Blog/BlogUpdateRequest.php
app/Http/Requests/Admin/Blog/BlogFilterRequest.php
app/Services/Blog/BlogService.php            <- Admin ve Web ortak kullanır
app/Models/Blog/Blog.php

routes/admin.php                             <- tüm admin route'ları burada

resources/views/admin/pages/blog/index.blade.php
resources/views/admin/pages/blog/show.blade.php
resources/views/admin/pages/blog/modals/form.blade.php

public/admin/assets/js/pages/blog/index.js
public/admin/assets/css/pages/blog/index.css <- SADECE gerçek ihtiyaç varsa
```

`Admin/` segmenti Controller ve Request'te vardır, Service ve Model'de **yoktur**:
servis ve modeller ileride ön yüz tarafından da kullanılacak, ortak katmandır.

## 4. Katman Sözleşmesi

### Controller
Sadece üç iş yapar: FormRequest'i al, servisi çağır, view ya da JSON döndür.
İçinde Eloquent sorgusu, iş kuralı, `try/catch` ve dallanma bulunmaz.

### Service
Eloquent ve iş kuralı buradadır. Public metotlar eylemleri karşılar
(`list`, `create`, `update`, `delete`). Private metot **yalnızca** bir blok
gerçekten uzunsa veya iki public metot tarafından paylaşılıyorsa açılır.
Tek satırlık işler (slug üretimi gibi) için ayrı metot açılmaz.

### Request
Doğrulama ve `authorize()` burada. Create ve Update ayrı sınıflardır
(unique kuralları farklılaşır). Filtre parametreleri de `FilterRequest`
ile doğrulanır — controller'a ham `$request` girmez.

### Hata yönetimi
Servis `DomainException` fırlatır; global exception handler bunu
`{success: false, message}` JSON'una çevirir. Controller'da try/catch yoktur.

## 5. JSON Sözleşmesi

```
200  { "success": true,  "message": "...", "data": {...} }
422  { "success": false, "message": "..." }                    (iş kuralı hatası)
422  { "message": "...", "errors": { "title": ["..."] } }      (validation, Laravel default)
403  { "success": false, "message": "Bu işlem için yetkiniz yok." }
```

Controller'lar `App\Http\Controllers\Concerns\RespondsWithJson` trait'inden
`success()` / `error()` alır.

## 6. Frontend Mimarisi (admin)

jQuery kullanılmaz. Native `<script type="module">` ile ES modules.
Build adımı yoktur — tarayıcı `import`'u doğrudan çözer.

```
public/admin/assets/js/core/
    http.js      fetch sarmalayıcı: CSRF header, JSON parse, 422/403/500 ayrımı
    form.js      form -> FormData, hata boyama/temizleme
    modal.js     AjaxModal: open(url) / close() / onSubmit
    table.js     DataTable: fetch + render + arama(debounce) + sayfalama + sıralama
    toast.js     bildirim
    confirm.js   silme onayı
```

Sayfa JS'i (`pages/blog/index.js`) yalnızca modüle özel olanı içerir:
kolon tanımı, satır render'ı, buton event'leri. Tipik boyut 40-60 satır.

### Modal sözleşmesi
Template'in modal mekanizması: `.add-new-popup` elemanına `.active` class'ı
eklenince açılır (`style.scss` içinde `opacity/visibility` geçişi tanımlı).
Genişlik `.popup-dialog` üzerindeki `max-w-[...]` ile belirlenir.

`resources/views/admin/layout/modals/ajax-modal.blade.php` iskeleti bu
mekanizmayı kullanır; `core/modal.js` içeriği `#ajax-modal-body` içine basar.
Bu dosyanın iskeleti proje sahibi tarafından kurulacaktır.

## 7. Tailwind Build

Mevcut `public/admin/assets/scss/style.scss` gerçekte bir Tailwind v4 CSS
entry'sidir (`@import "tailwindcss"` + `@theme` + `@apply`'lı bileşenler, 1613 satır).

Plan:
1. Kaynak `resources/css/admin/style.css`'e taşınır (public/ altında kaynak durmamalı).
2. Başına `@source` direktifleri eklenir:
   - `resources/views/admin/**/*.blade.php`
   - `resources/views/admin/html/**/*.html`  (template class'ları kaybolmasın)
   - `public/admin/assets/js/**/*.js`
3. `@tailwindcss/cli` eklenir; npm script'leri `admin:css` ve `admin:css:watch`.
   Çıktı yine `public/admin/assets/css/style.css` — `styles.blade.php` değişmez.
4. Build öncesi `style.css` yedeklenir, sonrası ile karşılaştırılır.
   Mevcut sınıf setinin kaybolmadığı teyit edilmeden ilerlenmez.

Vite'a bağlanmaz: `@vite()` manifest gerektirir ve mevcut `asset()` linklerini bozar.

## 8. Faz 0 — Modüllerden Önceki Altyapı

1. Tailwind build
2. `spatie/laravel-permission` + `User` modeline `HasRoles` + rol/izin seeder
3. Admin login (`sign-in.html` baz alınır) + `routes/admin.php`'ye `auth` middleware
4. `RespondsWithJson` trait + exception handler JSON kuralı
5. `MediaService`
6. `core/` JS dosyaları
7. `config/admin-menu.php` + sidebar'ı bundan üreten Blade
8. Ortak form Blade component'leri (`resources/views/admin/components/`)

## 9. Modül Yol Haritası

Sıra: Kullanıcı/Rol -> Blog Kategori -> Blog -> İletişim Mesajları ->
Hizmetler -> Site Ayarları -> Slider -> Referanslar -> SSS -> Yorumlar.

Blog kalıbı oturduktan sonra sonraki modüller belirgin şekilde hızlanır.
Modüller proje sahibinin talebiyle, teker teker yapılır.
