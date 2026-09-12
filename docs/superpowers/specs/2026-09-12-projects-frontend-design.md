# Neler Yaptık (Projeler) — Ön Yüz Tasarımı

## Amaç

"Neler Yaptık" modülünün admin tarafı bitti; ön yüzü bilinçli olarak
bekletiliyordu (CLAUDE.md: "Ön yüzü henüz yok — `LinksToPublicPage` /
`SubmitsToIndexNow` / site haritası kaydı ön yüz route'u doğduğunda
eklenecek"). Bu iş o route'u doğuruyor ve modülü ön yüzde uçtan uca
yayına alıyor: liste, kategori listesi, detay sayfası ve modülün arama
motoru altyapısına (sitemap, Schema.org, IndexNow, 301, kırık link,
menü) bağlanması.

Vaka çalışması sayfası bir blog yazısından üç şeyle ayrılır ve tasarım
bu üçünü öne çıkarır: **künye** (müşteri/sektör/tarih/süre/adres),
**ölçülebilir sonuçlar** ve **bağlı hizmetler**.

## Kararlar

| Konu | Karar | Gerekçe |
|---|---|---|
| Adres | `/projeler`, `/projeler/{slug}` | Kısa ve gerçek bir arama terimi. Sayfa başlığı/menü etiketi "Neler Yaptık" kalabilir; URL'den bağımsız |
| Kategori | Gerçek adres: `/projeler/kategori/{slug}` | `ProjectCategory` zaten `HasSeo` taşıyor → panelden meta girilebilen, indekslenebilir iniş sayfası. Yalnızca yayında projesi olan kategoriler listelenir, böylece boş/zayıf sayfa oluşmaz |
| Modül bağı | Modül pasifse ön yüz 404 | "Bu müşteri projeler bölümünü kullanmayacak" senaryosu tek switch'le çözülür |
| Hizmet sayfası | "Bu hizmette yaptığımız işler" bloğu eklenir | `project_service` pivotu tam bu yüzden var; iç link ağını güçlendirir |
| Teklif formu | Eklenmez | Lead altyapısı tek iletişim formundan yürüyor; ikinci form ayrı `source` + ayrı doğrulama yüzeyi demek. Detayda CTA `/iletisim`'e gider |

## Route'lar

```
GET /projeler                   projeler            ProjectController@index
GET /projeler/kategori/{slug}   projeler.kategori   ProjectController@category
GET /projeler/{slug}            projeler.show       ProjectController@show
```

Üçü tek grupta, `module.active:project,404` middleware'i altında.

`{slug}` tek segment eşleştiği için `kategori/{slug}` ile çakışma yoktur;
okunabilirlik için kategori route'u üstte tanımlanır. `routes/web.php`,
`routes/pages.php` catch-all'ından önce yüklendiği için sıra güvenlidir ve
`ReservedPath` route tablosundan türediği için `projeler` segmenti
kendiliğinden rezerve olur — bir Sayfa artık bu slug'ı alamaz.

## Liste sayfası (`pages/projects/index.blade.php`)

İki route (tümü / kategori) **aynı** view'i kullanır; fark geçilen veridir.

1. `inner-hero` + breadcrumb — kategori sayfasında başlık kategori adı,
   kırılım `Ana Sayfa > Neler Yaptık > {Kategori}`
2. Kategori filtresi: "Tümü" + yayında projesi olan kategoriler. Temanın
   `categories-buttons > nav-pills` markup'ı kullanılır ama `button` yerine
   `a` ile gerçek linkler; aktif olana `active` sınıfı
3. Kart ızgarası: `col-lg-4 col-md-6` + `portfolio-box` — **tek partial**, üç
   yerde kullanılır (liste, benzer işler, hizmet sayfası bloğu). Sözleşmesi:
   `@include('pages.projects.partials.card', ['project' => $project])`. Üst
   etiket sırayla kategori adı → sektör → (ikisi de yoksa hiç basılmaz);
   kapak yoksa görsel alanı atlanır, kart yine de linklidir
4. Sayfalama: 9 kayıt/sayfa, temanın `.theme-pagination` markup'ı
5. Boş durum: "Henüz yayınlanmış bir proje bulunmuyor." / kategori sayfasında
   "Bu kategoride henüz yayınlanmış bir proje yok."
6. Alt CTA: `/iletisim`

## Detay sayfası (`pages/projects/show.blade.php`)

Tema `portfolio-details.html` düzeni: `col-lg-8` içerik + `col-lg-4` sidebar.
Her blok **verisi yoksa hiç basılmaz**.

**Sol kolon**

| Blok | Kaynak | Not |
|---|---|---|
| Kapak | `getFirstMedia('cover')` | |
| Proje Hakkında | `excerpt` (lead) + `content` | |
| Ölçülebilir sonuçlar | `resultRows()` | `details-counter-box`; `direction` yalnızca ok yönünü belirler, renk sabit kalır — "çıkma oranı %60 düştü" iyi bir sonuçtur, yön tek başına iyi/kötü demez |
| Galeri | `getMedia('gallery')` | 2 kolon ızgara + magnific-popup lightbox |
| Video | `videoEmbed()` ya da `getFirstMedia('video')` | Embed varsa iframe (nocookie), yoksa mp4 için `<video controls>` |
| Bağlı hizmetler | `services` | Tik ikonlu liste, `publicUrl()` olanlar link |
| Müşteri yorumu | `testimonial` | Alıntı bloğu |
| SSS | `faqs` | Bootstrap akordeon, id'ler proje kimliğiyle önekli |
| Etiket + paylaş | `tags`, `publicUrl()` | Blog detayındaki paylaş kalıbı |

**Sağ kolon (künye — `_sidebar-widget _portfolio`)**

Müşteri, Sektör, Kategori (link), Tamamlanma (`completedLabel()` → "Mart 2026"),
Süre, Teknolojiler, "Siteyi Görüntüle" (`project_url`, yeni sekme,
`rel="noopener noreferrer"`), ardından "Benzer bir proje mi planlıyorsunuz?"
CTA kutusu.

**Alt:** "Benzer İşler" — aynı kategoriden 3 proje; kategori yoksa ya da
yetmezse en yeni diğer projelerle tamamlanır, kaydın kendisi hariç tutulur.

## Hizmet detayına blok

`ServiceController::show()` ve `showForRegion()` view'e
`ProjectService::active(6, null, $service->id)` sonucunu `projects` olarak
geçirir. `pages/services/show.blade.php` bu listeyi aynı kart partial'ıyla
"Bu hizmette yaptığımız işler" başlığı altında basar; liste boşsa blok hiç
render edilmez.

## Model ve servis

`Project implements LinksToPublicPage, RedirectsOnMove, SubmitsToIndexNow`

```php
public function publicUrl(): ?string      // yayında DEĞİLSE ya da 'project' modülü pasifse null
public function indexNowUrl(): ?string    // yayın durumuna bakmaz
public function publicLinkLabel(): string // title
public function redirectableMove(): ?array // slug değişince projeler/eski -> projeler/yeni
```

`publicUrl()`'ün modül durumuna bakması bilinçlidir: menü öğeleri ve kart
linkleri `publicUrl()` null dönünce kendiliğinden düşer, böylece modül
pasifken ön yüzde 404'e giden bir link kalmaz. `indexNowUrl()` durumdan
bağımsızdır — yayından kalkan adresin motora bildirilmesi gerekir.

`ProjectCategory implements RedirectsOnMove` — kategori sayfaları
indekslenebilir olduğu için slug değişimi ölü URL bırakmamalı.

`ProjectService` eklenecek metotlar:

```php
/** @return array{projects: LengthAwarePaginator, categories: Collection, category: ?ProjectCategory} */
public function listing(?ProjectCategory $category = null, int $perPage = 9): array

/** Aynı kategoriden, kaydın kendisi hariç; yetmezse en yenilerle tamamlanır. */
public function related(Project $project, int $limit = 3): Collection
```

`active()` ve `findBySlug()` zaten ön yüz için hazır, değişmez.

## Arama motoru tarafı

**Meta:** detay `$project->seoMeta()`, kategori `$category->seoMeta()`
(`HasSeo::seoFallbacks()` zaten `name`/`description` alanlarına düşüyor),
ana liste site geneli SEO ayarları — `/hizmetler` ve `/blog` ile aynı
davranış.

**Schema.org:** `SchemaContext::PROJECT` sabiti + `SchemaContext::project()`.
`SchemaGraphBuilder` bir `CreativeWork` düğümü basar: `name`, `description`,
`image` (kapak + galeri), `url`, `datePublished` (`completed_at` ?? oluşturma),
`dateModified`, `creator` → Organization, `about` → müşteri Organization
(varsa), `genre` → kategori adı, `keywords` → etiketler, `inLanguage`.
Kayıt bazlı `schema_type` override'ı — hizmet/blog düğümlerinde olduğu gibi —
WebPage tipini değil bu düğümü değiştirir (`pageNodes()` içindeki istisna
listesine `PROJECT` eklenir). SSS düğümü model-agnostik olduğu için
kendiliğinden gelir. Liste ve kategori sayfaları `CollectionPage`.

`SchemaContext::fromRoute()`'a `projeler` eşlemesi, `SchemaInspector`'a
"Projeler" örnek adres grubu ve `contextFor()` eşlemeleri eklenir — proje
sayfaları `/admin/schema` ekranından denetlenebilir olur.

**Sitemap:** yeni `projects` kaynağı tek dosyada üç tür adres taşır: liste
adresi, kategori adresleri (yayında projesi olanlar) ve proje adresleri
(kapak görselleriyle). `noindex` işaretli kayıtlar atlanır. Modül pasifse
kaynak hiç üretilmez. `observed_models`'a `Project` ve `ProjectCategory`
eklenir (kayıt sonrası 1 dk gecikmeli yeniden üretim).

Liste adresi bilinçli olarak `static_routes`'a **eklenmez**: modül
kapatıldığında tek bir yerden (bu kaynak) düşmesi gerekiyor, `static_routes`
ikinci bir kontrol noktası yaratırdı.

> **Kurulum notu:** `enabledSources()` ayar kaydedilmemişse tüm kaynakları
> açık sayar, ama sitemap ayarları bir kez kaydedilmiş kurulumda yeni
> `projects` anahtarı kayıtlı listede olmadığı için **kapalı** görünür.
> Panelden bir kez işaretlenmesi gerekir.

**IndexNow:** `config/indexnow.php > observed_models`'a `Project`.

**Kırık link:** `LinkChecker`'ın route eşlemesine `projeler.show` ve
`projeler.kategori` eklenir; `checkRecord()` imzası `Project` ve
`ProjectCategory`'yi de kabul eder. Taslak bir projeye verilen link
"yayında değil" sebebiyle raporlanır.

**Menü:** `config/menus.php > routes`'a `projeler`, `linkables`'a `project`
(yayında projeler, etiket `title`). Böylece panelden menüye hem liste
sayfası hem tek bir proje eklenebilir.

## Middleware

`EnsureModuleIsActive` opsiyonel ikinci parametre alır:

```
module.active:project        -> admin, 403 (mevcut davranış, değişmez)
module.active:project,404    -> ön yüz, 404
```

Ön yüzde 403 yanlış sinyaldir ("var ama yasak"); kullanılmayan bir modülün
adresi ziyaretçi için hiç yoktur. 404, `bootstrap/app.php`'deki
`NotFoundHttpException` kancasından geçer — yani önce yönlendirme
yöneticisine sorulur, yoksa 404 kaydına yazılır. Modül kapalıyken bu adrese
iç link kalmadığı için kayıt gürültüsü beklenmiyor.

## Dosyalar

**Yeni**

```
app/Http/Controllers/Project/ProjectController.php
resources/views/pages/projects/index.blade.php
resources/views/pages/projects/show.blade.php
resources/views/pages/projects/partials/card.blade.php
resources/views/pages/projects/partials/faqs.blade.php
resources/views/vendor/pagination/theme.blade.php
public/assets/js/pages/project/show.js
public/assets/css/pages/project/show.css        (yalnızca gerçekten gerekirse)
```

**Değişen**

```
routes/web.php
app/Models/Project/Project.php
app/Models/ProjectCategory/ProjectCategory.php
app/Services/Project/ProjectService.php
app/Http/Controllers/Service/ServiceController.php
resources/views/pages/services/show.blade.php
app/Http/Middleware/EnsureModuleIsActive.php
app/Support/SchemaContext.php
app/Services/Schema/SchemaGraphBuilder.php
app/Services/Schema/SchemaInspector.php
app/Services/Sitemap/SitemapService.php
app/Services/BrokenLink/LinkChecker.php
config/sitemap.php
config/indexnow.php
config/redirects.php
config/menus.php
CLAUDE.md
```

## Doğrulama (proje kuralı: otomatik test yazılmaz, manuel doğrulama)

- [ ] `/projeler` 200, kartlar yayındaki projeleri listeliyor, taslaklar yok
- [ ] Kategori pill'leri yalnızca yayında projesi olan kategorileri gösteriyor;
      tıklanınca `/projeler/kategori/{slug}` 200 ve yalnızca o kategori
- [ ] 9'dan fazla proje varken sayfalama çalışıyor ve tema görünümünde
- [ ] Detay sayfası: künye, sonuç kartları, galeri lightbox, video, bağlı
      hizmetler, müşteri yorumu, SSS, etiket, paylaş, benzer işler —
      her biri verisi varken basılıyor, yokken hiç basılmıyor
- [ ] Taslak projenin adresi 404; yayına alınınca 200
- [ ] Hizmet detayında "bu hizmette yaptığımız işler" bloğu bağlı projeyle
      görünüyor, bağlı proje yoksa hiç basılmıyor
- [ ] Proje slug'ı değişince eski adres 301 ile yenisine gidiyor; kategori
      slug'ı için aynısı
- [ ] `sitemap.xml` içinde `sitemap-projects.xml`, içinde liste + kategori +
      proje adresleri; `noindex` işaretli proje yok
- [ ] `/admin/schema` ekranında proje sayfası denetlenebiliyor, `@graph`
      içinde `CreativeWork` + `BreadcrumbList` (+ SSS varsa `FAQPage`)
- [ ] Modül Yönetimi'nden `project` pasife alınınca `/projeler` ve detay 404,
      site haritasında `projects` kaynağı üretilmiyor, menüdeki kayda bağlı
      öğe düşüyor; tekrar aktif edilince her şey geri geliyor
- [ ] Menü yöneticisinde "Projeler (liste)" hazır bağlantısı ve "Proje"
      kayda bağlama seçeneği çıkıyor
- [ ] Kırık link taraması taslak projeye verilen linki "yayında değil"
      olarak raporluyor
- [ ] `vendor/bin/pint` temiz, mevcut ön yüz sayfaları (ana sayfa, hizmetler,
      blog, iletişim) ve admin ekranları bozulmamış
