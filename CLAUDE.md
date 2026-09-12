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
| Alan yardım balonu | `<x-admin::form.help topic="grup.alan">` — metin `config/form-help.php`'den (jargonsuz HTML). `form.input/textarea/select/switch/date/image/editor/tags/faqs/map` + `form.label` bir `help="..."` prop'u alır; label'ın yanına (?) ikonu koyar, `core/help-popover.js` popover'ı `<body>`'ye taşır. Yeni bir label eklerken `config/form-help.php`'ye karşılığını yaz |
| Ayarlar sekmeleri | `config/settings.php` > `groups` (her biri `section` + `description` taşır) + `sections` (Site Kimliği / Arama Motorları / İletişim / Ziyaretçi Deneyimi / Sistem). Sol menü bu bölümlere göre gruplanır; `section`'ı olmayan sekme "Diğer" altına düşer |
| Tablo filtresinde checkbox | `core/table.js` `params()` checkbox'ı özel ele alır: işaretsizken `.value` "on" döndüğü için filtre hep açık sanılıyordu. İşaretsiz = boş (gönderilmez), işaretli = `value` ya da `1`. **Filtre checkbox'ına ayrı bir gizli input eklemeye gerek yok** |
| Sidebar aktif öğeye kaydırma | `core/sidebar-scroll.js` — açılışta aktif menü öğesini SimpleBar sarmalayıcısında ortalar (öğe zaten görünüyorsa dokunmaz) |
| Tekrarlanabilir satır alanı | `<x-admin::form.repeater>` + `core/repeater.js`. Kolonları `:columns` ile verilir (`key`/`label`/`type`/`options`/`placeholder`/`width`), satır iskeleti `<template>` içinde durur ve `__index__` yer tutucusu JS'te artan bir sayıyla değişir. **İndis silmede yeniden numaralanmaz**: PHP boşluklu indisleri de dizi okur, sıra DOM sırasıdır (tarayıcı FormData'yı DOM sırasına göre gönderir), servis tarafı `values()` ile yeniden indisler. Sunucu render'ı ve `<template>` AYNI partial'ı kullanır (`components/form/partials/repeater-row.blade.php`) |
| Serbest metin listesi | `<x-admin::form.chips>` — `<x-admin::form.tags>` ile **aynı JS'i** paylaşır (`core/tag-input.js`); tek fark öneri uç noktası verilmemesidir (`data-tag-endpoint` yoksa serbest liste kipi). Etiketler `tags` tablosunda ortak bir sözlüktür, chips ise kaydın kendi alanında yaşar. `tag-input.js` iki bileşen paylaştığı için `scripts.blade.php`'de GLOBAL yüklenir — bileşen içinde `@once` ile eklenirse Blade her çağrı yeri için ayrı kimlik üretir ve script iki kez basılır |
| Video alanı | `<x-admin::form.video>` + `core/video-field.js` — iki sekme: gömülü adres (YouTube/Vimeo) **ya da** kütüphaneden mp4. Sekme değiştirmek diğer alanı TEMİZLER, böylece forma tek kaynak gider ve sunucunun öncelik kuralı uydurması gerekmez. Adres çözümü `App\Support\VideoEmbed` (kısa adres/shorts/zaman damgası dahil); JS tarafında aynı mantığın bir kopyası canlı "tanındı/tanınmadı" satırını basar |
| Medyada video | `config/media.php` > `accepts` içinde `mp4`/`webm`, sınırı `max_size_by_extension` ezer (64 MB). **Sunucuda `upload_max_filesize` ve `post_max_size` de yükseltilmeli**, yoksa istek Laravel'e hiç ulaşmaz. `MediaUploadRequest` en gevşek sınırı kaba elek olarak kullanır, gerçek sınır `MediaService::guard()`'da tek yerde. `Media::isVideo()` + payload'daki `is_video`: videoyu `<img>`'e koyan her yer önce bunu sormak zorunda |
| Revizyon tekilleştirme | `RevisionService::$captured` "aynı istekte bir kez" korumasıdır; **uzun yaşayan süreçlerde sıfırlanmalı** — `AppServiceProvider`'da `Queue::looping(fn () => RevisionService::flushCaptured())` bağlı. Unutulursa kuyruk işçisi ayakta olduğu sürece ilk kaydetmeden sonraki değişiklikler geçmişe yazılmaz |
| Bileşen bazlı yetki | `App\Http\Requests\Concerns\FiltersPermissionedFields` — SEO/Schema.org/Etiketler/Sınıflandırma/SSS bloklarını modül içinde TEK TEK yetkilendirir (`blog.seo`, `blog.schema-org`, `blog.tags`, `blog.classification`, `blog.faqs` gibi — `classification` modüle göre değişen sınıflandırma alanıdır: `blog_category_id`, `service_regions`, `project_category_id`). Create Request'te `use FiltersPermissionedFields;` + `protected function permissionedFields(): array { return $this->sharedComponentPermissions('blog', 'blog_category_id'); }` (sınıflandırması yoksa — örn. Sayfa — ikinci parametre `null`). Update Request Create'i extend ettiği için tekrar yazılmaz. İzni olmayan alan **iki kademede** engellenir: Blade'de `@can('blog.seo')` ile blok hiç basılmaz, `validated()` override'ı ham bir HTTP isteğiyle gönderilse de o alanı sessizce düşürür — UI'yi atlayan istek de kaydedilmez. Şu an blog/page/service/project'te kurulu; Sayfa'da sınıflandırma yok |
| SEO alanları | `App\Models\Concerns\HasSeo` + `<x-admin::form.seo>` (polymorphic `seo` tablosu) |
| Etiketler | `App\Models\Concerns\HasTags` + `<x-admin::form.tags>` (`tags` + `taggables`) |
| Zengin metin | `<x-admin::form.editor>` — TinyMCE 7, `core/editor.js` |
| Yapay zeka | `App\Services\Ai\AiService` + `core/ai-generator.js`, `/admin/ai-provider`, `/admin/ai-prompt` |
| Alan adı dönüşümü | `App\Support\Field` — `seo.meta_title` → `name="seo[meta_title]"` |
| CSV indirme | `App\Support\Csv::download($dosyaAdi, $satirlar)` — satırları akıtır (generator), BOM + noktalı virgül ayırıcı (Türkçe Excel). Servis HTTP bilmez: satırları servis üretir, yanıtı controller kurar |
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
| Schema.org (JSON-LD) | `App\Services\Schema\{SchemaGraphBuilder,SchemaLinter,SchemaInspector}` + `App\Support\SchemaContext`. Ön yüzde `layout/partials/schema.blade.php` her sayfaya bağlı bir `@graph` basar (Organization/ProfessionalService + WebSite + WebPage + BreadcrumbList + sayfa türüne göre Service/BlogPosting/FAQPage). Ayarlar: `setting` grubu `schema` (`setting.schema.update`). Kayıt bazında override: `seo` tablosunda `schema_type`/`schema_json`/`schema_override` + `<x-admin::form.schema>` (Blog/Hizmet/Sayfa formlarında). Doğrulama ekranı `/admin/schema`. Dinamik controller'lar view'e `schemaContext` geçirir; statik route'lar `SchemaContext::fromRoute()` |
| Google service account | `App\Services\Google\{GoogleServiceAccount,GoogleException}` — JSON'dan okunan özel anahtarla JWT'yi `openssl_sign` (RS256) ile kendimiz imzalar, jetonu **kapsam başına** 50 dk cache'ler (`google.token.{sha1(email\|scope)}`), Google'ın hata gövdesinden kullanıcıya gösterilebilir mesaj çıkarır (`errorMessage()`). Paket yok. GA4 ve Search Console istemcileri bunu paylaşır — **yeni bir Google servisi eklenirse JWT imzalama yeniden yazılmaz**, bu sınıf kullanılır. Kimlik tek yerde: `settings` grubu `analytics` > `service_account` (şifreli); `AnalyticsService::serviceAccount()` onu çözüp bu nesneyi verir |
| GA4 panel özeti | `App\Services\Analytics\{GoogleAnalyticsClient,AnalyticsService}` — kimlik `GoogleServiceAccount`'ta, rapor `analyticsdata.googleapis.com/v1beta`. Kimlik: `setting` grubu `analytics` (`property_id`/`client_email` düz, `service_account` JSON `Crypt` ile şifreli) — Ayarlar "Analitik" sekmesi (`setting.analytics.update`). Panel `/admin/analytics` (`analytics.data`/`analytics.realtime`/`analytics.test`), veriler AJAX. Dashboard'ın üstünde özet kart (`admin/pages/dashboard/index.blade.php` — eski statik şablon kaldırıldı). Grafikler global ApexCharts |
| Liste ekranlarında görüntüleme | Blog/Sayfa/Hizmet listelerinde satır başına "son 28 gün" sayfa görüntülemesi. `AnalyticsService::viewsFor($tür, $ids, $gün)` → `analytics.page-views` uç noktası; adres => görüntüleme haritası **tek** GA4 raporundan gelir (`pagePath`, 5.000 satır, 30 dk cache, `analytics.page_views.{gün}`), sorgu dizeli adresler `UrlPath::normalize` ile toplanır. Kayıt adresi `indexNowUrl()` ile çözülür (yayın durumuna bakmaz, yayından kalkan içeriğin geçmiş trafiği de görünsün). **Liste GA4'e bağımlı değildir**: sayılar satırlar basıldıktan sonra ayrı bir istekle dolar (`core/table.js`'in `onLoaded` kancası + `pages/analytics/views.js`), bağlantı yoksa ya da rapor alınamazsa istisna fırlatılmaz — `available: false` döner ve kolon kendiliğinden gizlenir. Kolon Blade'de `@can('analytics.page-views')` ile basılır (`data-views-column`) |
| SEO skorlama (Yoast tarzı) | `App\Services\Seo\{SeoAnalyzer,SeoHealthService}` + `config/seo.php` (eşikler tek kaynak). `HasSeo::syncSeo()` kaydederken `SeoAnalyzer` skoru `seo` tablosuna yazar (`focus_keyword`, `seo_score`, `readability_score`, `score_checks`, `analyzed_at`). Canlı panel: `<x-admin::form.seo>` içindeki odak kelime alanı + `core/seo-analyzer.js` (PHP ile birebir aynı mantık, eşikler `data-seo-rules` ile gelir, TinyMCE içeriğini okur). Rozet: Blog/Hizmet/Sayfa liste ekranları (`toPayload` → `seo_score`/`seo_grade`, `pages/seo/badge.js`) + dashboard özet kartı. `/admin/seo` "SEO Sağlığı" 6 sekmeli rapor (`seo.datatable`) + "yeniden puanla" (`seo.rescore`). İçeriği işlenmiş modeller (`Service` yer tutucu) `seoAnalysisInput()`'i ezer |
| Site haritası (sitemap.xml) | `App\Services\Sitemap\SitemapService` — `storage/app/sitemaps/` altına `XMLWriter` ile akış halinde yazar; `sitemap.xml` bir indeks, her kaynak (`static`/`pages`/`blog`/`services`/`regions`/`extra`) kendi dosyasında, 50.000 URL'yi aşan kaynak otomatik `-1.xml`, `-2.xml`'e bölünür. Kapak görselleri `<image:image>`. Ayarlar (aktif kaynaklar, hariç tutulan/ek adresler) `settings` grubu `sitemap`'te. Ön yüzde `GET /sitemap.xml` ve `/sitemap-{name}.xml` (`routes/web.php`, dosya yoksa 404). Panel `/admin/sitemap` (`sitemap.index`/`.update`/`.generate`) — üç sekme: Site Haritası, Hızlı İndeksleme (IndexNow), robots.txt. Yeniden üretim üç yoldan: `sitemap:generate` komutu (günlük `withSchedule`, sunucuda `schedule:run` cron'u gerekir), `GenerateSitemapJob` (`ShouldBeUnique`, panel butonu), `SitemapObserver` (`Page`/`Blog`/`Service`/`ServiceRegion` kaydedilince 1 dk gecikmeli, `config('sitemap.observed_models')`). **robots.txt dinamiktir**: `GET /robots.txt` → `App\Http\Controllers\Sitemap\RobotsController`, gövde `settings.sitemap.robots_txt` (boşsa `config('sitemap.robots_default')`), sonuna `Sitemap: {route('sitemap.index')}` satırı kendiliğinden eklenir (gövdede zaten varsa eklenmez) — alan adı hiçbir yere sabitlenmez. `public/robots.txt` statik dosyası **silindi**, geri eklenmemeli: web sunucusu onu Laravel'e hiç uğramadan döndürür |
| Gelen talepler (lead) | `App\Models\Lead\Lead` + `App\Services\Lead\LeadService`, `/admin/lead`. **Tablo `contact_submissions` iken `leads`'e taşındı** (veri korundu) — `source` kolonu hangi formdan geldiğini tutar, ileride açılır pencere/teklif formları da aynı gelen kutusuna düşecek. İletişim formu akışı (`ContactService`) artık bu modele yazar ve `page_url`'e gönderildiği sayfayı işler. Durum/kaynak etiketleri `config/leads.php` (tek kaynak). Özellikler: okunmamış vurgusu + `read_at` (detay açılınca okundu, `saveQuietly` ile log şişmesin), durum/atama (`assigned_to` → users), müşteriye gitmeyen iç not, **panelden e-posta yanıtı** (`App\Mail\Lead\LeadReply`, Ayarlar → Posta'daki SMTP ile gider; `replied_at` işlenir, durum "Yeni" ise "İşlemde"ye geçer), toplu işlem (okundu/okunmadı/durum/sil/geri al — model olayı yok, log `Activity::record` ile), `SoftDeletes` + çöp kutusu görünümü ve geri alma, filtrelerle CSV dışa aktarım (`Csv::download`). Dashboard'da okunmamış sayısı kartı. Log modülü `lead` (`replied`/`bulk_update` olayları `config/activity-log.php`'de) |
| Hızlı İndeksleme (IndexNow) | `App\Services\IndexNow\IndexNowService` — içerik değişince adresi Bing/Yandex/Seznam/Naver/Yep'e tek JSON POST ile bildirir (`config/indexnow.php` > `endpoint`, ortak uç nokta; motor başına istek yok). Paket yok. **Google IndexNow'ı desteklemez** — Google tarafı Search Console'dan yürür, ikisi birbirinin yerine geçmez. Ayarlar `settings` grubu `indexnow` (`enabled`, `auto_submit`, `key`). Doğrulama anahtarı dosyası **dinamik**: `GET /{key}.txt` → `App\Http\Controllers\IndexNow\IndexNowController`, ayardaki anahtarla `hash_equals` eşleşirse içeriği döner — anahtar panelden yenilenince sunucuya dosya koymak gerekmez. Tetikleyiciler: `IndexNowObserver` (`config('indexnow.observed_models')` → Page/Blog/Service; 20 sn gecikmeli `SubmitToIndexNowJob`) + panelden elle adres + "tüm adresleri bildir" (site haritası dosyalarındaki `<loc>`'ları okur). Modeller `App\Contracts\SubmitsToIndexNow::indexNowUrl()` uygular — **yayın durumuna bakmadan** adres döner, çünkü yayından çıkan/silinen adresin de bildirilmesi gerekir (motor 404'ü görüp dizinden düşürsün). Gönderim geçmişi cache'te (`indexnow.history`, ISO metin olarak — Carbon nesnesi değil), ayrıca log kaydına (`indexnow` modülü, `notified`/`failed`) yazılır. **Kendi sayfası yoktur**: arayüzü `/admin/sitemap` ekranının "Hızlı İndeksleme" sekmesidir (`indexnow.index` izni/route'u kaldırıldı, menüde ayrı öğe yok) — ikisi de aynı işin parçası. Not: Google ve Bing'in eski `ping?sitemap=` uçları kaldırıldı, o yüzden ping yok |
| Search Console | `App\Services\SearchConsole\{SearchConsoleClient,SearchConsoleService}` — kimlik GA4 ile **ortak** (`GoogleServiceAccount`), ikinci bir JSON istenmez; kapsam `auth/webmasters` (site haritası göndermek yazma ister). Kendine ait tek ayar: `settings` grubu `search_console` > `site_url` (`sc-domain:siteniz.com` ya da `https://siteniz.com/`). Sabitler + Google kodlarının Türkçe karşılıkları `config/search-console.php`'de (`labels.verdict/coverage/robots/indexing/fetch/device/country` — listede olmayan değer ham geçer). Panel `/admin/search-console`: kurulum adımları, mülk listeleme (`sites`), arama performansı (`performance` — KPI/eğilim/sorgu/sayfa/ülke/cihaz, `cache_minutes` kadar cache), Search Console'a bildirilmiş site haritaları + "gönder" (`sitemaps`/`submit`), URL denetimi (`inspect`, sonucu 1 saat cache — Google'ın günlük kotası sınırlı). **Veri ~2 gün gecikmeli**: rapor aralığının bitişi `lag_days` kadar geriye çekilir, yoksa son günler boş görünür ve önceki dönem kıyası yanlış çıkar. Kurulumda kullanıcı tarafında iki şart var: service account e-postası Search Console'a "Tam" yetkiyle kullanıcı olarak eklenmeli ve Cloud projesinde "Google Search Console API" etkinleştirilmeli |
| Kırık link denetimi | `App\Models\BrokenLink\BrokenLink` + `App\Services\BrokenLink\{LinkExtractor,LinkChecker,BrokenLinkService}`, `/admin/broken-link`. Taranan kaynaklar: Sayfa/Blog/Hizmet `content` alanlarındaki `<a href>` + `<img src>`, menü öğelerinin çözülen adresleri, tanıtım alanının `button_url`'ü (`config/broken-links.php` > `sources`). **İç adres için ağ isteği yoktur**: sırayla `public/` dosyası → `storage` diski → yönlendirme yöneticisi → route tablosu bakılır, dinamik route'ta (`sayfa.show`/`blog.show`/`hizmetler.show(-region)`) kaydın varlığı ve yayın durumu sorgulanır — catch-all `/{path}` her şeye uyduğu için route eşleşmesi tek başına "çalışıyor" demek değildir. Yalnızca dış adreslere HTTP gider (HEAD, reddedilirse GET); bot koruması yüzünden yanlış uyarı üretecek kodlar `ignored_statuses`, siteler `skipped_hosts` listesinde. Tarama kuyrukta (`ScanBrokenLinksJob`, `ShouldBeUnique`), haftada bir `broken-links:scan` (bkz. `bootstrap/app.php`) ya da panel butonuyla. Satır kaynak + adres çiftiyle benzersiz, tekrar taramada güncellenir; **o taramada dokunulmayan satır silinir** (link düzeltilmiş ya da kaynak kalkmış). Tarama yüzlerce satır yazdığı için `saveQuietly` kullanılır, denetim kaydına taramanın özeti `Activity::record` ile tek satır düşer (`broken-link` modülü, `scan` olayı). Panelde kırık bir **iç bağlantıdan** tek tuşla 301: satırdaki "Yönlendir" Yönlendirme modülünün kayıt modalını `?from=` ile açar (eksik görselde çıkmaz — çözüm yönlendirme değil, görseli yeniden yüklemek). Ayrıca "kaynağı düzenle", "yok say" (bilinçli linkler, taramada korunur) ve CSV dışa aktarım |
| Revizyon geçmişi | `App\Models\Concerns\HasRevisions` + `Revision` + `RevisionService`, `/admin/revision`. Denetim kaydı "ne değişti"yi tutar; revizyon kaydın **değiştirilmeden önceki tam halini** saklar (alanlar + SEO + etiket + medya + SSS). Geri yükleme modülün kendi `update()` metoduna gider (slug/301/ağaç kuralları kopyalanmaz). Kayıt başına son 25 sürüm (`config/revisions.php`). Formda `<x-admin::revision-button>`, listede `revisionButton()`. |
| Toplu işlemler | Tek motor `BulkService` + `config/bulk-actions.php`. Liste: `<x-admin::bulk-bar module="blog" />` + `bindBulk` / `bulkCell`. Yayınla / taslağa al / kategori / etiket / sil. `save()` ile yazar ki revizyon ve gözlemciler çalışsın. |
| Duyuru şeridi + popup | Ayrı modüller (`Announcement`, `Popup`), ortak `HasAudience` (tüm site / anasayfa / seçili sayfa-yazı-hizmet + `starts_at`/`ends_at`). Ön yüz `NoticeResolver` o isteğe uyan en yeni kaydı verir. Kapatma `localStorage`'da kalıcı. Popup bülten formu açabilir (`collect_email`). |
| Bülten aboneleri | `Subscriber` + `/admin/subscriber` + CSV. Ön yüz `POST /bulten` (honeypot + KVKK onayı, `throttle:newsletter`), ayrılma `GET /bulten/ayril/{token}`. Çift onay yok. Footer formu + popup'tan kayıt. |
| Haftalık e-posta özeti | `WeeklyReportService` + `report:weekly`, pazartesi 08:30. Alıcı iletişim formununki (`contact.to_email`, yoksa `company.email`). İçerik: son 7 gün GA4 KPI + yeni lead + yeni abone. GA4 yoksa o bölüm atlanır. |
| Sistem sağlığı | `App\Services\Health\{HealthService,QueueHealth,SystemHealth,ConnectivityHealth}` + `Check` değer nesnesi, eşikler `config/health.php` (tek kaynak), panel `/admin/health`. On bir kontrol: kuyruk işçisi, başarısız işler, cron, disk, veritabanı, yazma izinleri, APP_DEBUG/ortam, SSL bitişi, SMTP, GA4 + Search Console jetonu. **Kuyruk işçisi sinyali** `Looping` + `JobProcessed` olaylarına bağlı `App\Listeners\RecordQueueHeartbeat` ile cache'e yazılır (30 sn'de bir) — işçi boşta dönerken de sinyal verdiği için "iş yokken durmuş sanılma" sorunu olmaz. **Cron sinyali** `bootstrap/app.php`'de her dakika çalışan bir `$schedule->call(...)`; sinyal yoksa cron ölmüş demektir. Rapor `health.report` anahtarında `Cache::forever` ile durur (`checked_at` üzerinden bayatlar), saatlik `health:check` komutu tazeler, panelin "Yeniden Tara" butonu `?fresh=1` ile zorlar — SSL/SMTP/Google ağa çıktığı için **her istekte çalıştırılmaz**. Sidebar rozeti ve dashboard uyarı kartı yalnızca cache'i okur (`menuBadge()`). Rozet altyapısı geneldir: `App\Contracts\ProvidesMenuBadge` + `config/admin-menu.php`'de `'badge' => Sınıf::class` (closure değil — config cache'lenebilsin diye), `MenuService` çözer, `admin/layout/partials/menu-badge.blade.php` basar. Başarısız işler panelden yeniden denenir/silinir (`queue:retry` / `failed_jobs` kaydı), log modülü `health` (`job_retry`/`job_delete`/`alert`). Kritik sorun sürerken günde bir `App\Mail\Health\HealthAlert` — alıcı iletişim formuyla aynı (`contact.to_email`, yoksa `company.email`), **kuyruğa alınmaz** çünkü sorun kuyruğun kendisi olabilir |
| Neler Yaptık (projeler) | `App\Models\Project\Project` + `ProjectCategory` + `App\Services\Project\ProjectService`, `/admin/project` ve `/admin/project-category`. Vaka çalışması kaydı: künye (müşteri/sektör/tarih/süre/yayındaki adres), `technologies` ve `results` **JSON** kolonları (kendi başlarına sorgulanmadıkları ve sırası kullanıcının verdiği sıra olduğu için ayrı tablo açılmadı — revizyon diff'ine de alan olarak kendiliğinden girer), galeri (`form.image multiple`), video (gömülü adres **ya da** kütüphaneden mp4, ikisi birlikte değil), SSS, SEO, etiket, revizyon, toplu işlem. Projeler hizmetlere çoktan-çoğa bağlanır (`project_service`) — hizmet sayfasında "bu hizmette yaptığımız işler" bloğu kurulabilsin diye. Müşteri yorumu `testimonial_id` ile bağlanır. Kırık link taraması ve global arama kaynakları arasında. **Ön yüzü henüz yok**: `LinksToPublicPage` / `SubmitsToIndexNow` / site haritası kaydı bilinçli olarak EKLENMEDİ — ön yüz route'u doğduğunda eklenecek, şimdi eklenirse `publicUrl()` olmayan bir route'a gider. |
| Dashboard | `App\Services\Dashboard\DashboardService` — GA4 DIŞINDAKİ her şey (uyarılar, sayaçlar, içerik envanteri, medya özeti, son talepler, son etkinlikler, en zayıf SEO skorları, 12 aylık üretim grafiği, talep durum dağılımı). Sunucu render'ında basılır, hiçbiri ağa çıkmaz. GA4 tarafı `AnalyticsService::summary()`'den AJAX ile gelir (`pages/dashboard/index.js`): trafik grafiği + KPI + "en çok görüntülenen" **aynı isteği paylaşır**, aralık (7/28/90 gün) değişince tek istek atılır. Grafikler global ApexCharts; tema değişince `redraws` dizisindeki her grafik yeniden çizilir. Bağlantı yoksa grafik iskeleti değil, "bağlantıyı kur" kartı basılır |
| Global arama (Ctrl+K) | `App\Services\Search\GlobalSearchService` + `config/global-search.php` + `core/global-search.js`. Header'daki kutu; modüllere göre gruplanmış sonuç, ↑↓/Enter/Esc ile gezinme. Ayar sekmeleri `config/settings.php` > groups'tan **türetilir**, elle yazılmaz — yeni bir sekme kendiliğinden aranabilir olur. **Route yetki ara katmanından muaf** (`withoutMiddleware`), bu yüzden filtre servisin içinde: izni olmayan kullanıcı için o kaynak hiç sorgulanmaz |
| Bildirim merkezi | `App\Services\Notification\NotificationService` + `core/notifications.js`. Bildirimler **saklanmaz**, canlı durumdan türetilir (okunmamış talep, kritik/uyarı sistem kontrolü, başarısız kuyruk işi, kırık link, yeni abone, kendi başarısız AI üretimi) — sorun çözülünce bildirim de kaybolur. Saklanan tek şey kullanıcının ne gördüğü: `notification_reads` (user_id + kararlı `key`, örn. `lead:42`, `health:ssl:critical`). Sağlık raporu **cache'ten** okunur, menüyü açmak ağa çıkmaz. `NotificationRead` projede `LogsActivity` kullanmayan tek modeldir: "bildirimi okudum" denetim olayı değil arayüz durumudur |
| Profil | `/admin/profile` (`App\Services\Profile\ProfileService`) — ad, e-posta, avatar (`user.avatar` preset, `HasMedia` koleksiyonu `avatar`) ve şifre. Controller route parametresi ALMAZ, her zaman `auth()->user()` üzerinde çalışır; bu yüzden yetki ara katmanından muaf tutulabiliyor. Mevcut şifre kontrolü FormRequest'te değil **serviste**: yanlış deneme `password_failed` olarak denetim kaydına düşsün diye. Header'daki ad/avatar buradan gelir, avatar yoksa `User::initials()` baş harfleri basılır |


Yeni modülün izinlerini `config/permissions.php` içindeki `permissions` dizisine
ekle, kategorisini `categories`'e yaz ve seeder'ı tekrar çalıştır. Seeder
tekrar çalıştırılabilir: mevcut kayıtlar ve rol atamaları korunur, ama
config'ten kaldırılan izinler **veritabanından tamamen silinir** (önce bir
uyarı basılır) — bir izni kaldırmadan önce ona sahip roller olup olmadığını
kontrol et, aksi halde o rollerin erişimi sessizce düşer.

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
