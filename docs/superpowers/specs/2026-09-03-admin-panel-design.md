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

**Durum: tamamlandı (2026-09-03).**

1. Tailwind build — kaynak `resources/css/admin/style.css`, `npm run admin:css`
2. `spatie/laravel-permission` + `User`'a `HasRoles` + rol/izin seeder
3. Admin login (`sign-in.html` baz alındı) + `auth` middleware
4. `RespondsWithJson` trait + exception handler JSON kuralı
5. `MediaService`
6. `core/` JS dosyaları (http, form, modal, table, toast, confirm)
7. `config/admin-menu.php` + `MenuService` + dinamik sidebar
8. Form Blade component'leri (`resources/views/admin/components/form/`)

### Doğrulanan davranışlar

Geçici bir smoke test kurulup çalıştırıldı ve sonrasında tamamen kaldırıldı:

| Doğrulama | Sonuç |
|---|---|
| Derlenmiş CSS'ten sınıf kaybı | Kaybolan 12 selector'ın hiçbiri kullanımda değil; eklenen 0 |
| Özel bileşen kuralları (`trezo-card`, `add-new-popup`, `sidebar-area`...) | Korundu |
| Yeni Tailwind class'ının derlenmesi | `!border-danger-500` ve `mt-[137px]` çıktıya girdi |
| Giriş → dashboard → çıkış döngüsü | 200 / 302 zinciri doğru |
| Hatalı parola | Girişe geri döner |
| Paginator JSON şekli | `data` + `meta` sözleşmeye uygun; `password` gizli |
| `DomainException` | 422 `{success:false,message}` |
| İzinsiz kullanıcı | Menü öğesi gizlendi, route 403 JSON |
| ES module MIME tipi | `application/javascript` |
| Ön yüz | `/` ve `/blog` 200, etkilenmedi |

### Bilinçli sapmalar

- `ajax-modal.blade.php` proje sahibine bırakılmıştı; dosya boş olduğu ve
  `modal.js` onsuz çalışamadığı için iskelet dolduruldu. `modal.js` ayrıca
  element bulunmazsa aynı markup'ı çalışma anında üretir, yani iskelet
  değiştirilse de bozulmaz.
- Template'te toggle/switch bileşeni yok; `switch` component'i Tailwind
  `peer` varyantıyla kuruldu. Specificity analizi yapıldı:
  `peer-checked` (0,2,0) > custom `dark` varyantı (0,1,0), dark mode'da doğru çalışır.
- Model'lerde Laravel 13 attribute stili (`#[Fillable]`) benimsendi —
  skeleton'daki `User` modeli bu stilde.

## 9. Modül Yol Haritası

Sıra: Kullanıcı/Rol -> Blog Kategori -> Blog -> İletişim Mesajları ->
Hizmetler -> Site Ayarları -> Slider -> Referanslar -> SSS -> Yorumlar.

Blog kalıbı oturduktan sonra sonraki modüller belirgin şekilde hızlanır.
Modüller proje sahibinin talebiyle, teker teker yapılır.

---

# Ek: Medya Kütüphanesi, Kırpma ve İzin Sistemi

Tarih: 2026-09-03 · Durum: tamamlandı

## Alınan kararlar

| Konu | Karar |
|---|---|
| Medya bağı | `media` tablosu + `mediables` polymorphic pivot (koleksiyonlu) |
| Kırpma | Orijinal saklanır, koordinat gönderilir, sunucuda `intervention/image` v4 ile kırpılır |
| Kırpma UI | Cropper.js v1.6.3, `public/admin/assets/js/vendor/cropper/` altında yerel kopya |
| Türetme | Yüklemede `thumb` + `medium`, hepsi WebP |
| File manager | `/admin/media` sayfası + form içinden açılan seçici modal, ortak markup |
| Klasör | Mantıksal (`media_folders.parent_id`), disk düz `uploads/YYYY/MM` |
| İzin kaynağı | `config/permissions.php` — name/label/category/guard_name + rol desenleri |
| Kırpma zorunluluğu | Preset tanımlıysa modal açılır ve orana kilitlenir |
| SVG | İşlenmeden saklanır, yüklemede sanitize edilir |
| Yükleme anı | Form submit'ten **önce** — alanın değeri her zaman bir `media_id` |

## Yükleme anı kararının gerekçesi

Kütüphaneden seçim zaten `media_id` döndürüyor. Ertelenmiş yüklemede alanın
değeri bazen blob bazen id olurdu ve iki ayrı kod yolu gerekirdi; ayrıca
mevcut bir görseli yeniden kırpma akışı ertelenmiş modele hiç oturmuyor.
Bedeli, terk edilen formlardan kalan sahipsiz medya — file manager'daki
**"Bağlantısız"** filtresi bunları tek tıkla listeler.

## Katman haritası

```
config/permissions.php          izinlerin tek kaynağı
config/media.php                disk, conversion'lar, kırpma preset'leri

app/Models/Media/Media.php          toPayload() ile tek tip JSON gövdesi
app/Models/Media/MediaFolder.php    ağaç + breadcrumb
app/Models/Concerns/HasMedia.php    getMedia / getFirstMedia / mediaUrl / syncMedia

app/Services/Media/MediaService.php        store / recrop / update / delete / move / list
app/Services/Media/MediaFolderService.php  tree / create / update / delete

public/admin/assets/js/core/cropper.js        kırpma modalı
public/admin/assets/js/core/media-browser.js  klasör + ızgara
public/admin/assets/js/core/media-picker.js   seçici modal
public/admin/assets/js/core/media-field.js    <x-admin::form.image> davranışı
```

## Doğrulanan davranışlar

| Doğrulama | Sonuç |
|---|---|
| Preset ile kırpma | Çıktı tam 1200×630 |
| Presetsiz yükleme | Orijinal boyut korunuyor (646×804) |
| Conversion'lar | thumb 400×400 (cover), medium 1000×525 (scale) |
| Yeniden kırpma | Orijinalden üretiliyor, dosya sayısı sabit (sızıntı yok), bağlantı korunuyor |
| Silme | Ana + orijinal + tüm conversion'lar diskten siliniyor, pivot temizleniyor |
| Bağlantısız filtresi | Yalnızca hiçbir kayda bağlı olmayanları döndürüyor |
| Klasör korumaları | Dolu klasör silinemiyor, döngüsel taşıma engelleniyor |
| Uzantı/boyut sınırı | `DomainException` → 422 JSON |
| SVG | Kırpılmıyor, `<script>` / `on*` / `javascript:` temizleniyor |
| İzin seeder'ı | Etiket + kategori yazılıyor, `editor` deseni `media.*` = 4 izin |
| Orphan izin | Siliniyor değil, uyarı basılıyor |
| Uç noktalar | upload / form / update / recrop / folders 200, geçersiz istek 422 |

## Yol boyunca düzeltilen üç hata

1. **Preset uygulanmıyordu.** `config("media.presets.{$preset}")` nokta
   notasyonu yüzünden `presets → blog → cover` diye çözüyordu, oysa anahtar
   birebir `'blog.cover'`. Dizi erişimine çevrildi (`MediaService` ve
   `image.blade.php`, ikisinde de yorumla işaretli).
2. **Doğrulama mesajları çevrilmemişti.** `APP_LOCALE=tr` idi ama hiç dil
   dosyası yayınlanmamıştı; arayüze `validation.required_with` ham anahtarı
   sızıyordu. `lang/tr/validation.php` tam çeviri olarak yazıldı (111 anahtar,
   `en` ile birebir), `attributes` dizisiyle alan adları da Türkçeleşti.

3. **Döndürme yönü tersti.** Intervention v4'ün `rotate()` metodunun saat
   yönünün tersine döndürdüğü varsayılıp açı negatifleniyordu; gerçekte
   pozitif açı saat yönündedir (Cropper.js ile aynı). Kırmızı çeyrekli bir
   test görseliyle piksel düzeyinde yakalandı: `rotate: 90` görseli sağ-üst
   yerine sol-alta taşıyordu. Negatifleme kaldırıldı ve 90/180/270, yatay ve
   dikey çevirme ile `rotate + flip` birleşimi tek tek doğrulandı.

   > Sıra önemlidir: **döndür → aynala → kırp**. Cropper.js'in modeli budur;
   > değiştirilirse çıktı sessizce yanlış olur.

## Bilinen sınır

- `ValidationException`'ın "(and N more errors)" eki Laravel'de sabit
  İngilizce. Arayüz alan bazlı mesajları gösterdiği için görünmüyor.
- SVG sanitizer regex tabanlıdır, kapsamlı değildir. SVG yüklemesini
  güvenilen kullanıcılara açık tut.
- `Blade::anonymousComponentPath()` hedef klasör yoksa her view render'ında
  hata fırlatır. `resources/views/admin/components/` boş bırakılmamalıdır.

## Yapılmayanlar

- `<x-admin::form.gallery>` (çoklu görsel) — pivot destekliyor, ilk ihtiyaç
  duyan modülle birlikte yapılacak.
- Klasör yeniden adlandırma/taşıma arayüzü — API hazır, UI yalnızca oluşturma
  sunuyor.
- Kırpma preset'lerinin panelden yönetimi — şimdilik config.
