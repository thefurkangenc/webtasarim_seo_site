# Modül Yönetimi — Tasarım

## Amaç

Bu admin panel kod tabanı farklı sektörlerdeki müşteriler için yeniden
kullanılacak bir ürün gibi ele alınıyor. Her kurulumda o müşteriye gerekmeyen
içerik modülleri (örn. bir mobilya üreticisine "Müşteri Yorumları" gerekirken
"Hizmet Bölgeleri" gerekmeyebilir) panelden pasife alınabilmeli; ayrıca bugün
`config/media.php`'de kod içinde sabit duran kırpma boyutları, artık
veritabanından panelden düzenlenebilmeli.

Bu sürümde modül **ekleme/silme yok** — sadece var olan modüllerin
pasife alınması, adının değiştirilmesi ve görsel boyutlarının düzenlenmesi.

## Kapsam

**Yönetilebilir 13 içerik modülü** (her biri bir kayıt, alt kaynaklarını
kapsar):

| Modül anahtarı | Kapsadığı route prefix'leri |
|---|---|
| `page` | `page` |
| `blog` | `blog`, `blog-category` |
| `service` | `service`, `service-region` |
| `project` | `project`, `project-category` |
| `testimonial` | `testimonial` |
| `reference` | `reference` |
| `faq` | `faq` |
| `why-choose-us` | `why-choose-us` |
| `hero` | `hero` |
| `announcement` | `announcement` |
| `popup` | `popup` |
| `subscriber` | `subscriber` |
| `lead` | `lead` |

Kullanıcı/Rol/Medya/Ayarlar/Sistem Sağlığı/Log/AI/Analitik/SEO/Sitemap/
Search Console/Kırık Link/Revizyon/Menü/Yönlendirme gibi panelin kendi
altyapısı bu listede **yer almaz** — sektör değişse de her kurulumda gerekir,
pasife alınamaz ve Modül Yönetimi ekranında görünmez.

**Pasif bir modülün etkisi** (v1 — sadece admin tarafı):
- Sidebar'dan kaybolur.
- Admin route'larına doğrudan adres yazılırsa 403 döner (middleware).
- Dashboard içerik envanteri kartlarından düşer.
- Ctrl+K global aramada aranmaz.

**Kapsam dışı (v1'de yapılmayacak, ileride ayrı bir iş):**
- Ön yüz (Bootstrap) route'larının, menü bağlantılarının, sitemap
  kayıtlarının kapatılması.
- Sayfa başlığı/breadcrumb/kart metinleri gibi Blade'e gömülü diğer sabit
  Türkçe metinlerin modül adına göre dinamikleşmesi — sadece sidebar
  başlığı değişir.
- Modüller arası bağımlılık uyarısı (örn. Hizmet pasifken Proje'nin
  "bu hizmette yaptıklarımız" bloğunun bozulacağı uyarısı).
- Yeni modül ekleme/silme.

## Veri Modeli

### `modules` tablosu

```
id
key         string, unique     — 'blog', 'service' ...
name        string, nullable   — override; null ise config'teki varsayılan etiket kullanılır
is_active   boolean, default true
timestamps
```

### `config/modules.php`

Kodun değişmeyen gerçeği — yeni bir modül route'u/izni koddan geldiği için
bu dosya elle yazılır, panelden değişmez:

```php
return [
    'definitions' => [
        'blog' => [
            'label' => 'Blog',
            'icon' => 'article',
            'description' => 'Blog yazıları ve kategorileri.',
            'routes' => ['blog', 'blog-category'],   // route prefix'leri
        ],
        // ... diğer 12 modül
    ],
];
```

### `ModuleSeeder`

`RolePermissionSeeder` deseniyle aynı: config'teki her `key` için DB'de
`updateOrCreate(['key' => ...], [])` — isim/aktiflik alanlarına elle
dokunmaz (sadece eksik satırı açar). Config'ten kalkan bir `key` DB'de
**silinmez** — v1'de modül silme özelliği olmadığı için bu senaryo zaten
oluşmaz, ileri bir tedbir olarak bırakılıyor.

### `media_presets` tablosu

```
id
key      string, unique   — 'blog.cover', 'social.icon' ...
width    unsignedInteger
height   unsignedInteger
label    string
timestamps
```

`config/media.php > presets` dizisi **seed kaynağı** olarak kalır — yeni bir
preset anahtarı hâlâ kodda (belirli bir `<x-admin::form.image preset="...">`
çağrısına bağlı olarak) açılır, DB sadece var olanların genişlik/yükseklik/
etiketini taşır. `MediaPresetSeeder`, ilk kurulumda (ve sonradan config'e
eklenen yeni anahtarlarda) config'ten DB'ye `updateOrCreate` ile kopyalar,
var olan DB satırının `width`/`height`/`label`'ına dokunmaz.

## Tek Kaynak Sınıfları

### `App\Support\ModuleRegistry`

```php
class ModuleRegistry
{
    public function isActive(string $key): bool;
    public function label(string $key): string;      // DB override ?? config label
    public function all(): Collection;                // liste ekranı için birleşik veri (key, label, icon, description, is_active, presets)
    public function flush(): void;                     // Cache::forget
}
```

`Cache::rememberForever('modules.state', ...)` ile DB'den `key => [name,
is_active]` haritasını okur. `ModuleService::update()` kaydettikten sonra
`flush()` çağırır.

### `App\Support\MediaPresetRegistry` (veya `MediaService` içine metot)

```php
public function get(string $key): ?array;   // ['width' => ..., 'height' => ..., 'label' => ...]
```

`Cache::rememberForever('media.presets', ...)` ile DB'den okur; DB'de
yoksa `config('media.presets')`'e düşer (geriye dönük güvenlik ağı).
`MediaService::crop()` (şu an `config('media.presets')` okuyan satır) ve
`resources/views/admin/components/form/image.blade.php` (aynı config
okumasını yapan tek diğer nokta) bu registry'ye yönlendirilir — projede
preset okuyan yer bu ikisinden ibaret.

## Route Koruması

Yeni middleware, `app/Http/Middleware/PermissionMiddleware`'in desenini
birebir izler:

```php
class EnsureModuleIsActive
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        if (app(ModuleRegistry::class)->isActive($key)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Bu modül şu an pasif.'], 403);
        }

        abort(403, 'Bu modül şu an pasif.');
    }
}
```

`bootstrap/app.php`'de `module.active` alias'ı olarak kaydedilir.
`routes/admin.php`'deki ilgili prefix gruplarına eklenir — aynı modüle ait
birden fazla grup varsa (örn. `blog` + `blog-category`) ikisine de aynı
anahtarla uygulanır:

```php
Route::middleware('module.active:blog')->prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {...});
Route::middleware('module.active:blog')->prefix('blog-category')->name('blog-category.')->controller(BlogCategoryController::class)->group(function () {...});
```

## Sidebar

`config/admin-menu.php`'deki ilgili öğelere `'module' => 'blog'` eklenir
(13 modülün karşılık geldiği üst/çocuk öğelere). `MenuService::filter()`'a
izin kontrolünün yanına bir satır eklenir: `module` anahtarı varsa ve
`ModuleRegistry::isActive()` false ise öğe (children dahil) elenir. Başlık
basılırken `$item['title']` yerine, `module` anahtarı varsa
`ModuleRegistry::label($item['module'])` kullanılır (override varsa o
görünür, yoksa config'teki varsayılan).

## Dashboard & Global Arama

`DashboardService::content()`'teki her `contentRow(...)` çağrısına ilgili
modül anahtarı eklenir; `ModuleRegistry::isActive()` false olan satır
diziden çıkarılır (6 satırdan 6'sı da bir modüle karşılık geliyor: page,
blog, service, project, testimonial, faq).

`GlobalSearchService::allows()`'taki mevcut izin kontrolüne ek olarak, kaynak
anahtarı (`page`, `blog`, `service`, `project`, `testimonial`, `faq`,
`subscriber`, `lead`) bir modül anahtarıyla eşleşiyorsa `ModuleRegistry::isActive()`
da kontrol edilir. `media` kaynağı hiçbir modüle bağlı değildir, dokunulmaz.

## Modül Yönetimi Ekranı

Datatable'lı bir liste değil — `config/settings.php` tab'larına benzer
**tek sayfa, tek form** deseni:

- Route: `GET /admin/module` (`module.index`), `PUT /admin/module`
  (`module.update`) — tek controller metodu, FilterRequest yok.
- Görünüm: 13 modül satırı, her biri bir kart: isim input'u (placeholder
  config'teki varsayılan etiket), aktif/pasif switch. Altında, o modüle
  ait preset varsa (örn. Blog → `blog.cover`) genişlik/yükseklik input
  çifti + etiket (salt okunur, config'ten).
- En altta, hiçbir modüle bağlı olmayan preset'ler (`social.icon`,
  `user.avatar`, `seo.og`, `slider.image`) için "Genel Boyutlar" başlıklı
  ayrı bir blok.
- Form alan adları: `modules[blog][name]`, `modules[blog][is_active]`,
  `presets[blog.cover][width]`, `presets[blog.cover][height]`.
- Tek `ModuleUpdateRequest`, tek `ModuleService::update(array $data)` —
  tüm satırları tek transaction'da günceller, sonunda
  `ModuleRegistry::flush()` + preset cache flush çağırır.

İzinler `config/permissions.php`'ye `module` kategorisiyle eklenir:
`module.index`, `module.update`. Sidebar'a "Genel" grubuna "Modül Yönetimi"
girişi eklenir — bu ekranın kendisi 13 modül listesinde yer almadığı için
hiçbir koşulda pasife alınamaz.

## Test Planı (proje kuralı: otomatik test yazılmıyor, manuel doğrulama)

- [ ] 13 modülün hepsi Modül Yönetimi ekranında listeleniyor, isim/switch
  kaydediliyor.
- [ ] Bir modülü pasife alınca: sidebar'dan kayboluyor, `/admin/<route>`
  adresine gidince 403 dönüyor, Dashboard kartından düşüyor, Ctrl+K'de
  aranmıyor.
- [ ] Modül adı değiştirilince sidebar başlığı değişiyor, sayfa içindeki
  diğer metinler (breadcrumb, buton) değişmiyor (bilinçli kapsam dışı).
- [ ] Preset genişlik/yükseklik değiştirilince o modülün görsel alanı
  (örn. Blog kapak görseli kırpma modalı) yeni orana kilitleniyor.
- [ ] Lead/Announcement/Popup/Subscriber gibi dashboard kartı olmayan
  modüller pasifken de route/sidebar kontrolü doğru çalışıyor.
- [ ] Pasif bir modülün DB verisi silinmiyor/bozulmuyor — sadece erişim
  kapanıyor, tekrar aktif edilince veriler eskisi gibi duruyor.
- [ ] `super-admin` rolü `module.*` izinlerine `Gate::before` ile zaten
  sahip. `config/permissions.php > roles` içinde `super-admin` dışında
  hiçbir rol deseni `module.*` almaz (tıpkı `setting.maintenance.update`
  gibi hassas izinler de otomatik atanmıyor) — panelden oluşturulmuş
  `admin`/`editor` rolleri bu izni isterse Rol ekranından elle eklenir.
