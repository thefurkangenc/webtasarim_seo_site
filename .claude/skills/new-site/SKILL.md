---
name: new-site
description: Use when standing up a new site on this panel for a different company or sector - resets the panel content, runs the setup wizard, then moves the public front end onto the new HTML theme dropped into resources/views/layout/html/ (app.blade.php, the layout partials, every page template, public/assets) while every panel binding (meta, schema, tracking, menus, settings, notices, cookie banner, forms) keeps working. Also use for a theme change alone, or when a single front-end page must be re-cut against the current theme. Delegates to front-end-module for any active module (existing, like Gallery, or newly named) that has no front-end pages yet.
---

# Yeni Site Kurulumu

Bu panel üzerinde başka bir firma/sektör için site ayağa kaldırır: panel
içeriğini sıfırlar, kurulum sihirbazını tamamlar, sonra ön yüzü yeni HTML
temasına taşır. Yeni tema `resources/views/layout/html/` içine konur (eski
temanın üzerine — eski sürüm git geçmişinde durur).

**Ön yüz kodu değişir, panel kodu değişmez.** `resources/views/admin/**` ve
`public/admin/**` bu iş sırasında açılmaz bile — CLAUDE.md'deki "İki Ayrı
Dünya" kuralı. Değişen tek panel şeyi onun **içeriğidir** (ayarlar, menüler,
sayfalar), o da sihirbazdan.

## Sıfırdan site kuruyorsan — önce panel

Yalnızca temayı değiştiriyorsan bu bölümü atla, Adım 0'dan başla.

1. **İçeriği sıfırla.** `php artisan setup:reset` — onay sorar (betikte
   `--force`). `config/setup.php` > `reset_tables` boşalır, `keep_tables`
   (roller, izinler, ülkeler, iller, modüller, medya preset'leri, AI şablonları,
   menü konumları) durur; `storage` yüklemeleri de silinir. Bittiğinde
   `/kurulum` yeniden açılır ve tüm ön yüz adresleri oraya düşer.

   Tamamen boş bir kurulumda bunun yerine: `php artisan migrate --force` +
   `php artisan db:seed --force` (seeder **yönetici oluşturmaz**, ilk hesap
   sihirbazdan gelir).

2. **Sihirbazı tamamla** — `/kurulum`. Yedi adım: Yönetici, Firma, Modüller,
   İletişim, E-posta, Yasal, Özet. "Kurulumu başlat" görevleri sırayla işler
   (`config/setup.php` > `tasks`). `page` ve `lead` modülleri kilitlidir,
   kapatılamaz.

   **Bu adımı kullanıcı yapar, sen yapmazsın** — firma adı, logo, e-posta,
   hangi modüllerin açılacağı onun kararı. Sihirbaz bitmeden tema işine
   başlama: header/footer panelden veri okuyor, boş veriyle ne yaptığını
   göremezsin.

3. Sihirbaz bittikten sonra **elinde ne var, gör**: hangi modüller açık
   (`/admin/module`), menülerde ne var, kaç sayfa/yazı/hizmet kaydı var.
   Kapalı bir modülün ön yüz sayfasını temaya uyarlamak boşa iştir.

## Temel kural

Tema markup'ı **değişir**, panel bağı **değişmez**.

Her Blade dosyasında iki tür kod vardır:

| Tür | Örnek | Ne olur |
|---|---|---|
| Tema markup'ı | `<div class="vl-header-area14">`, `<section class="cta14-section">` | Yeni temadakiyle **değiştirilir** |
| Panel bağı | `{{ $headerCompany['phone'] }}`, `<x-site.meta />`, `MenuRenderer::render('header')`, `@yield('content')` | **Aynen taşınır** |

İş şudur: yeni temanın HTML'ini al, içindeki **sabit demo içeriğini** mevcut
panel bağlarıyla doldur. Tersi değil — mevcut Blade'e yeni class adları
serpiştirmek değil, yeni markup'a eski bağları yerleştirmek.

Demo metni İngilizce gelir; arayüz metni **Türkçe** yazılır (CLAUDE.md kural 5).
Lang dosyası yok, metin doğrudan Blade'e.

## Adım 0 — Başlamadan

1. `git status` temiz mi? Değilse durdur, kullanıcıya sor.
2. Çalışan halden bir commit al (yoksa geri dönecek nokta kalmaz).
3. Temayı tanı — **soru sormadan önce oku**:

```sh
ls resources/views/layout/html/                      # hangi sayfalar var
ls resources/views/layout/html/assets/               # css / js / img / fonts
sed -n '1,60p' resources/views/layout/html/index.html   # <head> yapısı
grep -n "</main>\|<footer\|<header\|preloader\|offcanvas" resources/views/layout/html/index.html
```

4. Anasayfa hangi dosya? Çoğu temada birden çok varyant olur (`index.html`,
   `index2.html`, …). **Kullanıcıya sor** — varyant seçimi tasarım kararıdır,
   tahmin edilmez. Aynı soruyla iç sayfa varyantlarını da netleştir
   (`blog.html` mi `blog2.html` mi, `service-details3.html` mi).

5. **Tema haritasını yaz** (aşağıdaki bölüm). Kod yazmadan önce.

### Tema haritası — `docs/tema-haritasi.md`

Bu iş tek oturuma sığmaz: 20'den fazla view, yüzlerce sınıf adı. Bağlam
sıkıştığında her şeyi baştan keşfetmek zorunda kalmamak için harita **iş
başlamadan** yazılır ve her adım bitince güncellenir. Sonraki oturum bu dosyayı
okur, kaldığı yerden devam eder — "hangi tema dosyasına bakıyorduk, menü sınıfı
neydi, nerede kalmıştık" soruları burada cevaplı durur.

Dosya git'e girer; iş bitince silinmez (bir sonraki tema değişiminde önceki
temanın haritası karşılaştırma için işe yarar).

```markdown
# Tema Haritası — <tema adı>

Tarih: <tarih> · Kaynak: `resources/views/layout/html/`

## Kararlar
- Anasayfa varyantı: `index7.html` (kullanıcı seçti)
- Blog listesi: `blog2.html` · Blog detay: `blog-details.html`
- Hizmet detay: `service-details3.html` · Portfolyo: `portfolio2.html`

## Sayfa eşlemesi
| Tema dosyası | View | Durum |
|---|---|---|
| index7.html | pages/home/index.blade.php | ✓ |
| about.html | pages/about/index.blade.php | — |
| contact.html | pages/contact/index.blade.php | — |
| ... | ... | |

## Sınıf eşlemesi
| Rol | Eski tema | Yeni tema |
|---|---|---|
| Alt menülü kök `<li>` | `has-dropdown` | ? |
| Aktif menü `<li>` | `current-menu-item` | ? |
| Alt menü `<ul>` (1. seviye) | `sub-menu` | ? |
| Alt menü `<ul>` (2. seviye) | `sub-menu menu1` | ? |
| Birincil buton | `theme-btn27` | ? |
| İç sayfa üst görseli | `inner-hero` | ? |
| Kırılım (breadcrumb) | `breadcrumbs-pages` | ? |
| Sayfalama | `theme-pagination` | ? |
| Kenar çubuğu kutusu | `_sidebar-widget` | ? |

## Kütüphaneler
Yeni temada olanlar: bootstrap, swiper, gsap, ...
**Eski temada olup yenide olmayanlar:** aos, slick, nice-select → bunları
kullanan view'ler (`data-aos`, `.slick-*`, `<select>`) o kütüphane gittiğinde
sessizce bozulur, tek tek gezilir.

## Kalanlar
- [ ] sayfalama view'i
- [ ] mobil menü klonlama kontrolü
- [ ] ...
```

"Kütüphaneler" satırını atlamak pahalıya patlar: eski tema AOS ile geliyordu ve
sayfalarda `data-aos` öznitelikleri var. Yeni temada AOS yoksa öznitelikler
zararsızca durur ama **animasyon hiç çalışmaz** ve hata da vermez; ya kütüphane
korunur ya öznitelikler temizlenir — karar burada yazılır.

## Adım 1 — Asset'ler

Yeni temanın asset'leri `public/assets/`'e kopyalanır. **Panele ait dosyalar
korunur.**

Kural: `public/assets/` içinde, yeni temanın `assets/` klasöründe **karşılığı
olmayan** her dosya projeye aittir ve silinmez. Kopyalamadan önce listeyi çıkar:

```sh
# proje kökünden çalıştır
for d in css js img fonts; do
  for f in $(ls public/assets/$d); do
    [ -e "resources/views/layout/html/assets/$d/$f" ] || echo "KORUNACAK: $d/$f"
  done
done
```

Şu an korunması gerekenler (liste değişebilir, yukarıdaki komut esastır):

| | |
|---|---|
| CSS | `cookie-banner.css`, `notices.css`, `integrations.css`, `references.css`, `contact-form.css`, `legal.css`, `page.css`, `maintenance.css`, `video-player.css`, **`pages/`** (tüm ağaç) |
| JS | `cookie-banner.js`, `notices.js`, `contact-form.js`, `video-player.js`, **`pages/`** (tüm ağaç) |

Kopyalama:

```sh
cp -R resources/views/layout/html/assets/css/. public/assets/css/
cp -R resources/views/layout/html/assets/js/.  public/assets/js/
cp -R resources/views/layout/html/assets/img/. public/assets/img/
cp -R resources/views/layout/html/assets/fonts/. public/assets/fonts/
```

Sonra korunacak listeyi **tek tek doğrula** — biri gitmişse site sessizce bozulur.

> `public/assets/css/pages/**` altındaki dosyalar tema sınıflarına yaslanabilir
> (örn. `project/show.css` içindeki `.details-quote blockquote`). Dosyalar
> korunur ama **Adım 7'de ilgili sayfa elden geçerken bu seçiciler yeniden
> kontrol edilir** — yeni temada karşılığı yoksa seçici günceller ya da düşer.

## Adım 2 — `layout/partials/css.blade.php`

Yeni temanın `<head>`'indeki `<link>`/font satırlarını al, sırayı koru. Şu üç
parça **aynen kalır**:

```blade
@php
    $faviconLogoId = \App\Support\Settings::get('company.logo_media_id');
    $faviconUrl = $faviconLogoId ? \App\Models\Media\Media::query()->find($faviconLogoId)?->url('medium') : null;
@endphp
<link rel="shortcut icon" href="{{ $faviconUrl ?? asset('<yeni temanın favicon yolu>') }}" type="image/x-icon">
```

```blade
{{-- tema css'lerinden SONRA, panele ait olanlar --}}
<link rel="stylesheet" href="{{ asset('assets/css/integrations.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/references.css') }}">
@if (\App\Support\Settings::bool('cookie.enabled'))
    <link rel="stylesheet" href="{{ asset('assets/css/cookie-banner.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('assets/css/notices.css') }}">

@stack('css')
```

Kurallar:
- Her yol `{{ asset('assets/...') }}` olur. Tema HTML'i göreli yazar (`assets/css/x.css`) — hepsi çevrilir.
- Panel css'leri **tema css'lerinden sonra** yüklenir, yoksa tema onları ezer.
- `@stack('css')` en sonda kalır — sayfalar kendi css'ini oraya basıyor.
- jQuery bu temada `css.blade.php`'nin sonunda yükleniyor. Yeni tema jQuery kullanmıyorsa satır **kaldırılır**, ama önce `grep -rl "jQuery\|\$(" public/assets/js/*.js` ile projeye ait js'lerin ona muhtaç olup olmadığına bakılır.

## Adım 3 — `layout/partials/scripts.blade.php`

Yeni temanın `</body>` öncesi script'leri, sırayla. Sonuna eklenenler **aynen kalır**:

```blade
<x-site.tracking placement="foot" />

@if (\App\Support\Settings::bool('cookie.enabled'))
    <script src="{{ asset('assets/js/cookie-banner.js') }}"></script>
@endif

<script src="{{ asset('assets/js/notices.js') }}"></script>

@stack('scripts')
```

## Adım 4 — `layout/app.blade.php`

Yeni temanın `index.html` iskeletini al; `<head>` ve `</body>` arasını şu
yerleşime göre kur. **Aşağıdaki yedi etiketin tamamı `app.blade.php`'de
bulunmak zorunda** ve yerleri önemlidir (`tracking` üç kez geçer — üçüncüsü
`scripts.blade.php`'de):

| Bileşen | Nerede | Neden |
|---|---|---|
| `<x-site.meta />` | `<head>`'in en başı | title/description/og/canonical hepsi burada |
| `<x-site.schema :context="$schemaContext ?? null" />` | meta'dan sonra | JSON-LD; controller `$schemaContext` verir, vermezse route'tan türetilir |
| `<x-site.tracking placement="head" />` | `@include('layout.partials.css')`'ten önce | GA/GTM head parçası |
| `<x-site.tracking placement="body" />` | `<body>` açılır açılmaz | GTM noscript |
| `<x-site.notices />` | header'dan **önce** | duyuru şeridi sayfanın en üstünde |
| `<x-site.cookie-banner />` | footer'dan sonra, script'lerden önce | |
| `<x-site.integrations />` | `</body>`'den hemen önce, script'lerden sonra | WhatsApp/telefon baloncukları |

İskeletin sabit noktaları:

```blade
<html lang="tr">          {{-- tema "en" yazar, "tr" olur --}}
...
@include('layout.partials.css')
...
@include('layout.partials.header')
<main>
    @yield('content')     {{-- adı değişmez, bütün sayfa view'leri buna yaslanıyor --}}
</main>
@include('layout.partials.footer')
...
@include('layout.partials.scripts')
```

Preloader / progress / scroll-top gibi tema kabuğu parçaları yeni temada ne
şekildeyse öyle taşınır; içindeki görsel yolları `asset()`'e çevrilir.

## Adım 5 — `header.blade.php` + `menu-nav.blade.php`

Yeni temanın header markup'ını al, şu bağları içine yerleştir:

```php
$headerCompany = \App\Support\Settings::group('company');   // name, legal_name, phone, email, address, short_description, logo_media_id
$headerLogo    = ... Media::find($headerCompany['logo_media_id']);
$headerSocialLinks = app(\App\Services\SocialLink\SocialLinkService::class)->list();
$headerMenu    = app(\App\Services\Menu\MenuRenderer::class)->render('header');
```

- Logo: `$headerLogo?->url('medium') ?? asset('<tema varsayılan logosu>')`
- Telefon linki: `\App\Support\Phone::href($headerCompany['phone'])`
- Sabit butonlar: `route('anasayfa')`, `route('iletisim')`
- Sosyal linkler: her öğe `['url', 'name', 'icon' => ['url'] | null]`

**Menü ağacı `menu-nav.blade.php`'de** ve tamamen tema sınıflarına bağlıdır
(`has-dropdown`, `sub-menu`, `current-menu-item`, ok ikonu). Yeni temanın
anasayfasında **alt menüsü olan** bir örnek bul, sınıfları oradan eşle ve
eşlemeyi `docs/tema-haritasi.md` > "Sınıf eşlemesi" tablosuna yaz — sonraki
adımlarda oraya bakılacak.

`$items` yapısı değişmez: `['label', 'url', 'target', 'active', 'children']`.
Özyineleme (`$depth`) korunur; tema 3 seviyeden fazlasını açmıyorsa
`config/menus.php` > `max_depth` da güncellenir.

**Mobil menü:** bu temada ayrı kodlanmaz — `main.js` header'daki `<ul>`'yi
klonlayıp offcanvas'a kopyalar. Yeni temanın mekanizması farklıysa
(`grep -n "clone\|offcanvas" public/assets/js/main.js`) header ona göre kurulur;
mobil menü boş kalırsa sebebi budur.

## Adım 6 — `footer.blade.php`

```php
$footerMenus     = app(\App\Services\Menu\MenuRenderer::class);
$footerPrimary   = $footerMenus->render('footer_primary');
$footerSecondary = $footerMenus->render('footer_secondary');
$footerMenus->heading('footer_primary');   // sütun başlığı da panelden
```

İçinde durması gerekenler:
- Logo + `$footerCompany['short_description']`
- Sosyal linkler
- İki menü sütunu (**başlıkları `heading()`'den**, elle yazılmaz)
- `<x-site.subscribe-form source="footer" />` — bülten formu, markup'ı bileşende, sarmalayıcı temadan
- `route('kvkk')` ve `route('cerez-politikasi')` linkleri
- Telif satırı

## KONTROL NOKTASI — devam etmeden önce

```sh
vendor/bin/pint --test
for u in "" hizmetler blog projeler iletisim hakkimizda kvkk; do
  printf "%-22s " "/$u"; curl -s -o /dev/null -w "%{http_code}\n" "https://webtasarim.test/$u"
done
```

Hepsi 200 olmadan Adım 7'ye geçme. Sonra **ekran görüntüsü al ve gerçekten bak**
(aşağıdaki "Doğrulama" bölümü). Header, footer, menü ve mobil menü çalışıyorsa
commit at — sayfalar bunun üstüne gelecek.

## Adım 7 — Sayfa şablonları

Yeni site kurulumunda `resources/views/pages/**` genelde **boştur** — eski
temanın sayfaları silinir, sıfırdan yazılır. O yüzden kaynak eski view değil,
**controller'ın view'a ne geçirdiğidir**. Aşağıdaki tablo sözleşmedir: her
satırdaki dosya var olmak zorunda, yoksa o adres 500 verir.

(Eski view'ler duruyorsa — yalnızca tema değişikliği yapıyorsan — tablo yine
geçerlidir; eski view sadece "hangi bağ nerede kullanılmıştı" için referanstır.)

### 7.0 — Bu sitede hangi modüller ön yüze çıkacak?

Aşağıdaki envanter, ön yüzü **zaten kurulu** modülleri (Blog/Hizmet/Proje/
Sayfa/İletişim/Hakkımızda/Yasal) belgeler — panelde CRUD'u olan **her**
modülün karşılığı değil. Örneğin `Gallery` modeli admin'de tam kurulu ama
hiçbir ön yüz sayfası yok; bu envanterin dışında kalan böyle modüller
sessizce atlanır, sen sormazsan fark edilmez.

Devam etmeden önce:

1. `app(\App\Support\ModuleRegistry::class)->all()` ile (ya da `/admin/module`
   ekranından) hangi modüllerin bu kurulumda **aktif** olduğuna bak.
2. **Kullanıcıya sor**: "Bu site için ön yüzde şunlar olacak: Blog, Hizmet,
   Proje. Ayrıca [aktif ama envanterde olmayan modüller] var — bunlar da ön
   yüze çıksın mı?" Aktif-ama-envanterde-yok listesini tahmin etmeden, gerçek
   `ModuleRegistry::all()` çıktısından çıkar.
3. Envanterde olan modüller bu adımda aşağıdaki gibi kurulur. Envanterde
   **olmayan** ama kullanıcının istediği bir modül için (Gallery gibi, ya da
   kullanıcının yeni tarif ettiği bir modül) **`front-end-module` skill'ini
   çağır** — route/controller/servis/model sözleşmeleri/schema/sitemap/
   IndexNow/menü/view kurulumunun tamamı orada.

### View envanteri

| View dosyası | Çağıran | View'a gelenler |
|---|---|---|
| `pages/home/index.blade.php` | `routes/web.php` (closure) | yalnızca `schemaContext` — **veriyi view kendi çeker**, aşağıya bak |
| `pages/about/index.blade.php` | `AboutController` | `aboutTitle`, `aboutContent`, `testimonials`, `schemaContext` |
| `pages/contact/index.blade.php` | `ContactController` | `ContactService::pageData()` yayılımı: `enabled`, `email`, `phone`, `tel_href`, `address`, `heading`, `intro`, `privacy_required`, `privacy_html`, `map_embed` (lat/lng doluysa gömülü harita adresi, yoksa null) + `schemaContext` |
| `pages/blog/index.blade.php` | `routes/web.php` (closure) | `schemaContext` — listeyi view kendi çeker, aşağıdaki nota bak |
| `pages/blog/show.blade.php` | `BlogController` | `blog`, `related`, `schemaContext` |
| `pages/services/index.blade.php` | `ServiceController@index` | `services`, `schemaContext` |
| `pages/services/show.blade.php` | `ServiceController@show` / `@showForRegion` | `service`, `region` (null olabilir), `rendered`, `regionGroups`, `projects`, `schemaContext` |
| `pages/projects/index.blade.php` | `ProjectController@index` / `@category` | `projects` (paginator), `categories`, `category` (null olabilir), `schemaContext` |
| `pages/projects/show.blade.php` | `ProjectController@show` | `project`, `related`, `schemaContext` |
| `pages/page/layout.blade.php` + `default` / `wide` / `sidebar` | `PageController` (`config/pages.php` > `templates`) | `page`, `ancestors`, `section` (yalnızca sidebar), `schemaContext` |
| `pages/legal/show.blade.php` | `LegalController` (kvkk + çerez) | `title`, `content`, `schemaContext` |
| `pages/subscriber/unsubscribed.blade.php` | `SubscriberController` | `email` |
| `pages/maintenance.blade.php` | `MaintenanceController` + `MaintenanceService` (503 ile de basılır) | `title`, `message`, `company_name`, `logo`, `retry_after` |

**Blog listesi ve anasayfa özeldir**: controller yok, route doğrudan view
döndürür, veriyi view'ın kendisi kapsayıcıdan çeker.

Blog listesi bu projede tarihsel olarak panele **hiç bağlanmamıştı** — ham tema
markup'ı, sabit demo kartlar. Yeniden yazarken bağlanır: `BlogService::active()`
son yazıları verir; **sayfalı** bir liste isteniyorsa servise
`ProjectService::listing()` kalıbında bir metot eklenir (sorgu serviste kalır,
view'a Eloquent girmez) ve sayfalama `vendor.pagination.theme` ile basılır.

Anasayfa bağları — biri unutulursa o blok siteden sessizce düşer, hata vermez:

```php
app(\App\Services\Hero\HeroService::class)->current();          // + $hero->getMedia('gallery')
app(\App\Services\Service\ServiceService::class)->active(6);
app(\App\Services\WhyChooseUs\WhyChooseUsService::class)->active();
app(\App\Services\Testimonial\TestimonialService::class)->active();
app(\App\Services\Blog\BlogService::class)->active(3);
```

`ReferenceService::active()` de vardır (referans/müşteri logoları) ama ön yüzde
henüz hiçbir yerde basılmıyor — yeni temada logo şeridi varsa bağlanacak yer
orasıdır.

### Meta sözleşmesi — her sayfada uyulur

`<title>` ve `<meta>` etiketlerini `<x-site.meta />` basar; view'ın işi ona
doğru `@section`'ları vermektir. Üç kalıp var, sayfanın türüne göre biri seçilir:

**1. Kaydı olan sayfa** (blog yazısı, hizmet, proje, dinamik sayfa) — dördü de
yazılır, kaynak modelin `seoMeta()`'sıdır:

```blade
@php($seo = $blog->seoMeta())

@section('title', $seo['title'] ?: $blog->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])
```

Hizmet sayfasında kaynak `$rendered['seo']`'dur (`Service::renderFor()` bölge
yer tutucularını çözer ve bölge adıyla niteler) — `$service->seoMeta()` **değil**:

```blade
@section('title', $rendered['seo']['title'] ?: $rendered['title'])
```

Proje listesinde kategori varsa onun `seoMeta()`'sı, yoksa liste başlığı.

**2. Sabit liste/bilgi sayfası** (Blog, Hizmetler, Hakkımızda, İletişim, KVKK,
Çerez, bülten) — yalnızca başlık verilir, açıklama/anahtar kelime site geneli
SEO ayarlarına düşer:

```blade
@section('title', 'Hizmetler')
```

**3. Anasayfa** — hiçbir `@section` yazılmaz. `<x-site.meta />` site geneli
`seo.meta_title` / `seo.meta_description` ayarına düşer; panelden yönetilsin
diye böyle.

Kurallar:
- Başlığa site adını **ekleme** — `meta.blade.php` sonuna " | Site Adı" ekliyor zaten.
- `meta_description`/`meta_keywords`/`meta_image` boş geçilebilir; `(string)`
  cast'i durur, `null` basılırsa `filled()` kontrolü onu zaten eler.
- `schemaContext` view'da **kullanılmaz**; controller verir, `layout/app.blade.php`
  bileşene geçirir. View'da ona dokunma.

### Her sayfa için yöntem

1. Tablodan o view'ın **ne aldığını** oku; emin değilsen controller'ı aç.
2. Yeni temanın karşılık gelen HTML'inden bölüm markup'ını al
   (`docs/tema-haritasi.md`'deki eşlemeye göre).
3. Demo içeriğin yerine gelen değişkenleri koy; liste blokları `@foreach`,
   koşullu bloklar `@if (filled(...))` ile sarılır — panelde boş bırakılan bir
   alan sayfada boş bir kutu bırakmamalı.
4. Meta sözleşmesinden doğru kalıbı uygula.
5. `@extends('layout.app')`, `@section('content')`, gerekiyorsa `@push('css')` /
   `@push('scripts')`.
6. Göreli yollar (`src="assets/..."`, `style="background-image: url(assets/...)"`)
   `{{ asset('assets/...') }}` olur.
7. İngilizce demo metni Türkçeye çevrilir.
8. Sayfa açılıyor mu — **aç ve bak**, ekran görüntüsü al.
9. `docs/tema-haritasi.md`'de satırı ✓ yap, öğrendiğin sınıf eşlemesini tabloya
   ekle. Commit'e bu dosya da girer.

### Sıra

Site en hızlı ayağa kalkacak şekilde: **anasayfa → yasal/bülten/bakım (küçük ve
sabit) → hakkımızda → iletişim → blog liste+detay → hizmetler liste+detay →
projeler liste+detay → dinamik sayfa şablonları.**

Sayfa yazılana kadar o adres 500 verir; kısa olanları öne almak siteyi erken
bütünler.

### Partial'lar

Yeni sayfalarda tekrar eden bloklar partial'a çıkar — özellikle **proje kartı**:
liste, proje detayındaki "Benzer İşler" ve hizmet detayındaki "Bu Hizmette
Yaptığımız İşler" aynı dosyayı kullanmalı (`pages/projects/partials/card.blade.php`),
yoksa kart üç yerde ayrı ayrı bakım ister. Aynısı SSS bloğu ve CTA için geçerli.

Sayfalı listelerde sayfalama `{{ $projects->links('vendor.pagination.theme') }}`
ile çağrılır — Laravel'in varsayılan view'i Tailwind olduğu için doğrudan
kullanılamaz (bkz. Adım 8).

## Adım 8 — Sayfalama

`resources/views/vendor/pagination/theme.blade.php` **temanın** sayfalama
markup'ıdır. Yeni temada sayfalı bir listeyi (`blog.html` gibi) aç, markup'ı
oradan al; `$paginator` API'si değişmez (`hasPages`, `onFirstPage`, `previousPageUrl`,
`currentPage`, `hasMorePages`, `nextPageUrl`). Sayfa numarası biçimi (bu temada
`01`, `02`) yeni temada nasılsa öyle.

Bu view değişmezse **her sayfalı liste bozuk görünür** ve kolay gözden kaçar.

## Adım 9 — Son doğrulama

```sh
vendor/bin/pint --test
php artisan sitemap:generate            # adresler hâlâ üretiliyor mu
```

Tüm ön yüz adresleri (dinamik olanlar için DB'den gerçek bir slug al):

```
/  /hakkimizda  /hizmetler  /hizmetler/{slug}  /hizmetler/{slug}/{il}/{ilçe}
/blog  /blog/{slug}  /projeler  /projeler/kategori/{slug}  /projeler/{slug}
/iletisim  /kvkk  /cerez-politikasi  /{dinamik-sayfa}  /sitemap.xml  /robots.txt
```

Ayrıca:
- `<x-site.meta />` çıktısı: `curl -s <url> | grep -c "og:title\|canonical"`
- JSON-LD hâlâ basılıyor mu: `curl -s <url> | grep -c "application/ld+json"`
- Bülten formu, iletişim formu, çerez bandı, duyuru şeridi görünüyor ve kapanıyor mu
- Mobil (390px) menü açılıyor mu

### Ekran görüntüsü alma (bu projeye özel iki tuzak)

```sh
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless --disable-gpu \
  --virtual-time-budget=8000 --screenshot=/tmp/x.png --window-size=1440,1600 "https://webtasarim.test/"
```

- `--virtual-time-budget` **şart**: preloader animasyonu bitmeden çekilen kare siyah çıkar.
- Aktif bir **popup** varsa sayfanın üstünü kaplar. Geçici olarak kapat, çek,
  **hemen geri aç ve doğrula** — kullanıcının verisi:

```sh
php artisan tinker --execute="\App\Models\Popup\Popup::find(<id>)->update(['is_active'=>false]); echo 'off';"
# ... ekran görüntüsü ...
php artisan tinker --execute="\App\Models\Popup\Popup::find(<id>)->update(['is_active'=>true]); echo \App\Models\Popup\Popup::find(<id>)->is_active ? 'restored' : 'FAILED';"
```

## Bozulmaması gereken sözleşmeler

İş bittiğinde hepsi doğru olmalı:

- [ ] `@yield('content')` `app.blade.php`'de duruyor
- [ ] `@stack('css')` css partial'ında, `@stack('scripts')` scripts partial'ında
- [ ] Yedi `x-site.*` etiketi de yerinde (üç `tracking` yerleşimi dahil: head / body / foot)
- [ ] `<x-site.schema :context="$schemaContext ?? null" />` — `:context` prop'u atlanmadı
- [ ] Sayfa view'lerindeki `@section('title'|'meta_description'|'meta_keywords'|'meta_image')` isimleri aynı
- [ ] `MenuRenderer::render('header'|'footer_primary'|'footer_secondary')` + `heading()` çağrılıyor
- [ ] `SocialLinkService::list()` çıktısı hem header hem footer'da
- [ ] `Settings::group('company')` alanları (ad, logo, telefon, e-posta, adres, kısa açıklama) basılıyor
- [ ] `Settings::bool('cookie.enabled')` koşulu css ve scripts partial'larında
- [ ] `<x-site.subscribe-form source="footer" />` footer'da
- [ ] Route adları değişmedi: `anasayfa`, `iletisim`, `kvkk`, `cerez-politikasi`
- [ ] `vendor/pagination/theme.blade.php` yeni temanın markup'ında
- [ ] `public/assets` içindeki panel dosyalarının tamamı duruyor
- [ ] Her tema yolu `asset()` içinden geçiyor — göreli yol kalmadı
- [ ] `html lang="tr"`
- [ ] Arayüz metinleri Türkçe
- [ ] Adım 7 envanterindeki her view dosyası var (eksik olan adres 500 verir)
- [ ] `docs/tema-haritasi.md` güncel — tüm satırlar ✓, kalan madde yok

## Tuzaklar

| Tuzak | Sonuç |
|---|---|
| Göreli yol unutmak (`src="assets/img/x.png"`) | Alt sayfalarda görsel kırılır — `/blog/yazi` altında `/blog/assets/...` aranır |
| `style="background-image: url(assets/...)"` içindeki yolu atlamak | Aynı sorun, ama sessiz — gözle yakalanır, curl'le yakalanmaz |
| Panel css'ini tema css'inden önce yüklemek | Tema ezer, çerez bandı/duyuru şeridi biçimsiz görünür |
| `menu-nav.blade.php`'yi eski sınıflarla bırakmak | Menü düz liste olur, açılır menü çalışmaz |
| Mobil menü klonlama mekanizmasını kontrol etmemek | Mobilde menü tamamen boş |
| `vendor/pagination/theme.blade.php`'yi atlamak | Blog/proje listelerinde sayfalama bozuk |
| Tema demo metnini İngilizce bırakmak | CLAUDE.md kural 5 ihlali |
| `resources/views/admin/**` veya `public/admin/**`'e dokunmak | İki Ayrı Dünya ihlali — panel bozulur |
| Tek dev commit | Bir şey bozulunca geri dönülecek ara nokta kalmaz |

## Adım adım commit

Her adım kendi commit'i: asset'ler → css/scripts → app.blade → header → footer →
(kontrol noktası) → her sayfa ayrı. Bozulan bir şey olursa hangi adımda
olduğu tek `git log` ile görünür.
