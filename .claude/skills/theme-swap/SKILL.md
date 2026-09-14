---
name: theme-swap
description: Use when the public site must be moved onto a new HTML theme dropped into resources/views/layout/html/ - rebuilds layout/app.blade.php, the layout partials, the page templates and public/assets from the new theme while every panel binding (meta, schema, tracking, menus, settings, notices, cookie banner, forms) keeps working. Also use when a single front-end page must be re-cut against the current theme.
---

# Tema Değiştirme

Ön yüzü yeni bir HTML temasına taşır. Yeni tema `resources/views/layout/html/`
içine konur (eski temanın üzerine — eski sürüm git geçmişinde durur).

**Yalnızca ön yüz.** `resources/views/admin/**` ve `public/admin/**` bu iş
sırasında açılmaz bile — CLAUDE.md'deki "İki Ayrı Dünya" kuralı.

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
`index.html`'inde **alt menüsü olan** bir örnek bul, sınıfları oradan eşle:

| Veri | Şu anki tema | Yeni temada |
|---|---|---|
| Alt menüsü olan kök `<li>` | `has-dropdown` | ? |
| Aktif `<li>` | `current-menu-item` | ? |
| Alt menü `<ul>` (1. seviye) | `sub-menu` | ? |
| Alt menü `<ul>` (2. seviye) | `sub-menu menu1` | ? |

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

Her view sırayla elden geçer. Sıra önemlidir: her adımda site ayakta kalır.

| # | View | Yeni temada kaynak | Panel bağı |
|---|---|---|---|
| 1 | `pages/home/index.blade.php` | seçilen `index*.html` | Hero, Service, WhyChooseUs, Testimonial, Blog servisleri |
| 2 | `pages/about/index.blade.php` | `about.html` | (şu an ham tema markup'ı — yenisi de öyle taşınır) |
| 3 | `pages/contact/index.blade.php` | `contact.html` | `route('iletisim.store')`, `data-contact-form` |
| 4 | `pages/blog/index.blade.php` + `show.blade.php` | `blog*.html`, `blog-details*.html` | `BlogService`, `seoMeta()`, etiketler |
| 5 | `pages/services/index.blade.php` + `show.blade.php` | `service*.html`, `service-details*.html` | `ServiceService`, **bölge kenar çubuğu** |
| 6 | `pages/projects/*` | `portfolio*.html`, `portfolio-details.html` | `ProjectService`, kart partial'ı |
| 7 | `pages/page/*` (layout/default/wide/sidebar/hero/faqs) | uygun iç sayfa | Dinamik sayfalar, `config/pages.php` |
| 8 | `pages/legal/show`, `maintenance`, `subscriber/unsubscribed` | herhangi bir iç sayfa | küçük, en sona |

Her sayfada yöntem aynı:

1. Mevcut view'deki `@php` veri bloklarını, `@foreach`/`@if` mantığını ve tüm
   `{{ }}` bağlarını **bir kenara yaz** — bunlar korunacak.
2. Yeni temanın karşılık gelen HTML'inden bölüm markup'ını al.
3. Demo içeriğin yerine korunan bağları koy.
4. `@extends('layout.app')`, `@section('title')` ve kardeşleri, `@push('css')`,
   `@push('scripts')` aynen kalır.
5. Göreli yollar (`assets/...`, `src=`, `style="background-image: url(...)"`)
   `{{ asset('assets/...') }}` olur.
6. İngilizce demo metni Türkçeye çevrilir.
7. Sayfa açılıyor mu, ekran görüntüsü alınıyor mu — bak, sonra commit.

**Paylaşılan partial'lar tek kaynaktır**, üç yerde birden değişir:
`pages/projects/partials/card.blade.php` (liste + benzer işler + hizmet detayı),
`pages/projects/partials/cta.blade.php`, `layout/partials/cta.blade.php`.

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
