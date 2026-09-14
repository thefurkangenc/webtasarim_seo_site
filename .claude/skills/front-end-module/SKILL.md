---
name: front-end-module
description: Use when an admin module (built via admin-module) needs its public-facing pages wired up - list/detail routes, controller, views, and every place the project's front-end infrastructure expects a content model to register itself (LinksToPublicPage, SchemaContext, sitemap, IndexNow, menus). Use for a module that has full admin CRUD but no front end yet, or a brand-new module the user names while planning a new site.
---

# Modülü Ön Yüze Bağlama

`admin-module` bir modülün **panel tarafını** uçtan uca kurar. Bu skill onun
devamı: panelde tam kurulu ama ön yüzü eksik/hiç olmayan bir modülü
(Blog/Hizmet/Proje kalıbındaki gibi) ön yüze bağlar.

**Örnek şu an gerçek bir boşluk:** `Gallery` modeli, `GalleryService`,
admin CRUD'u ve route'ları var — ama `LinksToPublicPage` uygulamıyor,
`config/menus.php`'de yok, sitemap'e girmiyor, hiçbir `pages.*.blade.php`'i
yok. Panelde "Foto Galeri" diye bir modül görünüyor ama ziyaretçi onu hiçbir
yerden göremiyor. Bu skill tam olarak bu boşluğu kapatır.

## Ne zaman

- Kullanıcı "şu modülün ön yüzünü de yap" dediğinde.
- `new-site` akışında Adım 7 öncesi kontrolde admin'de aktif ama ön yüz
  envanterinde karşılığı olmayan bir modül bulunduğunda.
- Kullanıcının yeni bir site için isim verdiği, henüz admin tarafı da
  olmayan bir modül olduğunda — önce `admin-module` ile panel kurulur,
  sonra bu skill devreye girer.

## Önce sor, sonra kur

Kod yazmadan önce netleşmesi gereken üç şey (tahmin edilmez):

1. **Liste mi, sadece detay mı?** Blog/Hizmet/Proje gibi üçü de liste + detay.
   Kimi modülde (Referanslar, Duyuru Şeridi) liste sayfası hiç olmaz, kayıtlar
   başka sayfalara gömülür.
2. **URL şeması** — `/{çoğul-kelime}` mi, `/{çoğul-kelime}/{slug}` mi, kategori
   var mı (`/projeler/kategori/{slug}` gibi)?
3. **Modül kapatılabilir mi olsun?** Kapatılınca ön yüz adresleri 404 mü
   dönsün (Proje bunu yapıyor), yoksa modül hep açık mı kalsın (Sayfa gibi)?

## Referans uygulama: Proje modülü

Aşağıdaki her adımda **gerçek dosya**, Proje modülünün karşılığı. Yeni modül
için `Project` yerine kendi model/servis/controller adın gelir, geri kalan
iskelet aynıdır.

### 1. Route

```php
// routes/web.php — pages.php'nin ÜSTÜNE, kapatılabilir modülse module.active ile
Route::middleware('module.active:project,404')->group(function () {
    Route::get('/projeler', [ProjectController::class, 'index'])->name('projeler');
    Route::get('/projeler/kategori/{slug}', [ProjectController::class, 'category'])->name('projeler.kategori');
    Route::get('/projeler/{slug}', [ProjectController::class, 'show'])->name('projeler.show');
});
```

`module.active:{key},404` — ikinci parametre **ön yüz için şart**, yoksa
kapalı modülde varsayılan 403 döner (admin'e uygun, ziyaretçiye değil).
Modül kapatılamıyorsa (`config/setup.php` > `locked_modules` gibi) middleware
hiç konmaz.

Yeni route grubu eklendiğinde `App\Support\ReservedPath` ilk segmenti
(`projeler`) kendiliğinden rezerve eder — Sayfa modülü aynı adla çakışmaz,
elle bir şey eklemene gerek yok.

### 2. Controller — ince kalır

```php
class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(): View
    {
        return view('pages.projects.index', [
            ...$this->service->listing(),
            'schemaContext' => SchemaContext::collection('Neler Yaptık', route('projeler')),
        ]);
    }

    public function show(string $slug): View
    {
        $project = $this->service->findBySlug($slug);
        abort_unless($project, 404);

        return view('pages.projects.show', [
            'project' => $project,
            'related' => $this->service->related($project),
            'schemaContext' => SchemaContext::project($project),
        ]);
    }
}
```

Sorgu, `with()`, `paginate()` — hepsi serviste. Controller `Eloquent` görmez
(CLAUDE.md sert kural 3).

### 3. Servis metotları — mevcut serviste, `Admin/` segmenti yok

```php
public function listing(?ProjectCategory $category = null, int $perPage = 9): array
{
    return [
        'projects' => Project::where('status', Project::STATUS_PUBLISHED)
            ->with(['media', 'category:id,name,slug'])
            ->when($category, fn ($q, $c) => $q->where('project_category_id', $c->id))
            ->paginate($perPage),
        'categories' => ProjectCategory::where('is_active', true)->get(),
        'category' => $category,
    ];
}

public function findBySlug(string $slug): ?Project
{
    return Project::where('slug', $slug)->where('status', Project::STATUS_PUBLISHED)
        ->with(['media', 'category', 'seo.ogMedia', 'faqs'])
        ->first();
}
```

Modülün panel servisi zaten `list()`/`formData()`/`create()`/`update()`/`delete()`
taşıyor (`admin-module` bunları kurdu) — ön yüz metotları **aynı sınıfa**
eklenir, ayrı bir `Admin/` servisi açılmaz.

### 4. Model — üç sözleşme

```php
class Project extends Model implements LinksToPublicPage, RedirectsOnMove, SubmitsToIndexNow
{
    public function publicUrl(): ?string
    {
        // Kapatılabilir modülse ModuleRegistry kontrolü BURADA — publicUrl()
        // null dönünce menü öğeleri ve kart linkleri kendiliğinden düşer.
        if (! app(ModuleRegistry::class)->isActive('project')) {
            return null;
        }

        return $this->status === self::STATUS_PUBLISHED
            ? route('projeler.show', $this->slug)
            : null;
    }

    public function indexNowUrl(): ?string
    {
        // Yayın durumuna BAKMAZ — yayından kalkan adres de bildirilmeli.
        return filled($this->slug) ? route('projeler.show', $this->slug) : null;
    }

    public function publicLinkLabel(): string
    {
        return $this->title;
    }

    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return ['from' => 'projeler/'.$this->getOriginal('slug'), 'to' => 'projeler/'.$this->slug];
    }
}
```

Üçü de opsiyoneldir ama **birbirinden bağımsız değil**: `RedirectsOnMove`
yoksa slug değişince eski adres sessizce 404 olur; `SubmitsToIndexNow` yoksa
yeni/silinen kayıt arama motoruna hiç bildirilmez. Detay sayfası olan her
modülde üçü de uygulanır — bunlardan birini atlamak bilinçli bir karar
olmalı, unutkanlık değil.

### 5. Schema.org — üç parça birden

**a) `App\Support\SchemaContext`** — sabit + factory:

```php
public const PROJECT = 'project';   // diğer KIND sabitlerinin yanına

public static function project(Project $project, ?string $url = null): self
{
    $url ??= route('projeler.show', $project->slug);
    $trail = [['Neler Yaptık', route('projeler')], [$project->title, $url]];

    return new self(self::PROJECT, $url, $project->title, self::trail($trail), $project);
}
```

**b) `App\Services\Schema\SchemaGraphBuilder`** — düğüm üreten metot + iki
bağlantı noktası:

```php
private function projectNode(SchemaContext $ctx, ?string $overrideType): array
{
    /** @var Project $project */
    $project = $ctx->model;
    $meta = $project->seoMeta();
    // ... CreativeWork/Product/uygun tür, meta'dan title/description/image
}
```

Sonra `build()` içindeki `match`/`if` zincirine iki yerde giriş:
`webPageType()`'da `self::PROJECT => 'ItemPage'` gibi bir eşleme, ve
düğüm listesine `$ctx->kind === SchemaContext::PROJECT` koşuluyla
`projectNode()` çağrısı. İkisi de mevcut Proje/Hizmet/Blog satırlarının
hemen yanına eklenir — deseni kopyala.

**c) `App\Services\Schema\SchemaInspector`** — `/admin/schema` doğrulama
ekranının örnek listesine ve `fromRoute()`'a (statik route'lar için) bir satır.

### 6. Site haritası

```php
// config/sitemap.php > sources
'projects' => 'Projeler (Neler Yaptık)',

// config/sitemap.php > observed_models
Project::class, ProjectCategory::class,   // kaydedilince otomatik yeniden üretim
```

```php
// SitemapService — writeAll()'daki dispatch listesine bir satır, sonra:
private function writeProjects(): array
{
    if (! app(ModuleRegistry::class)->isActive('project')) {
        return $this->disable('projects');
    }

    $urls = [['loc' => route('projeler'), 'lastmod' => null, 'image' => null]];

    Project::where('status', Project::STATUS_PUBLISHED)->with('seo')
        ->chunk(100, function ($projects) use (&$urls) {
            foreach ($projects as $project) {
                if ($this->isNoindex($project)) continue;
                $urls[] = ['loc' => $project->publicUrl(), 'lastmod' => $project->updated_at, 'image' => $this->coverImage($project)];
            }
        });

    return $this->writeSource('projects', $urls);
}
```

Liste adresi (`route('projeler')`) **`static_routes`'a eklenmez** —
bilinçli: modül kapanınca liste + kayıtlar tek yerden (`writeProjects()`'in
başındaki `isActive` kontrolünden) birlikte düşsün.

### 7. IndexNow

Genelde **ekstra kod gerekmez** — `SubmitsToIndexNow` (Adım 4) zaten uygulandıysa
tek satır: `config/indexnow.php` > `observed_models`'a modeli ekle. Gözlemci
(`IndexNowObserver`) kaydedilince/silinince otomatik tetikler.

### 8. Menü

Kayıt tek tek menüden seçilebilsin istiyorsan (`Project` böyle — "Proje" diye
bir bağlantı türü var):

```php
// config/menus.php > linkables
'project' => [
    'label' => 'Proje',
    'model' => Project::class,
    'query' => fn () => Project::query()->where('status', Project::STATUS_PUBLISHED)->orderBy('sort_order'),
    'option_label' => fn (Project $p) => $p->title,
],
```

Yalnızca liste sayfasına sabit bir menü linki yeterliyse (`config/menus.php`
> `routes`'a `'projeler' => 'Neler Yaptık (liste)'`) bu adım gerekmez.

### 9. Kırık link denetimi (opsiyonel ama ucuz)

`App\Services\BrokenLink\LinkChecker::checkInternal()`'daki `match` bilinmeyen
route adlarını sessizce geçerli sayar (`default => null`) — modülü hiç
eklemesen de site çökmez. Ama eklersen kayıt varlığı/yayın durumu gerçekten
doğrulanır:

```php
'projeler.show' => $this->checkRecord(Project::where('slug', $route->parameter('slug'))->first(), 'Proje'),
```

### 10. View'lar

```
resources/views/pages/{çoğul}/index.blade.php
resources/views/pages/{çoğul}/show.blade.php
resources/views/pages/{çoğul}/partials/card.blade.php   -- listede tekrar eden kart varsa
```

Meta ve sayfalama sözleşmesi **`new-site` skill'inin "Meta sözleşmesi" ve
Adım 8 bölümlerinde** — burada tekrar edilmez, oradaki kalıp birebir uygulanır:
kaydı olan sayfa → `seoMeta()`'dan dört `@section`; liste sayfası pagination
gerekiyorsa `{{ $items->links('vendor.pagination.theme') }}`.

## Kısa kontrol listesi

Yeni bir modülü ön yüze bağlarken:

- [ ] Route (liste/detay/kategori — hangisi gerekiyorsa), `module.active:...,404` gerekiyorsa eklendi
- [ ] Controller ince — servis çağırıyor, Eloquent yok
- [ ] Servis metodu (`listing()`/`active()`/`findBySlug()` kalıbı) mevcut servise eklendi, `Admin/` segmenti açılmadı
- [ ] Model: `LinksToPublicPage` (+ `RedirectsOnMove`, `SubmitsToIndexNow` — atlanıyorsa bilinçli)
- [ ] `SchemaContext`: sabit + factory metodu
- [ ] `SchemaGraphBuilder`: düğüm metodu + `webPageType()` + düğüm listesi girişi
- [ ] `SchemaInspector`: örnek liste + `fromRoute()` (statik route ise)
- [ ] `config/sitemap.php`: `sources` + `observed_models`; `SitemapService::writeXxx()`
- [ ] `config/indexnow.php` > `observed_models` (model `SubmitsToIndexNow` uyguluyorsa)
- [ ] `config/menus.php`: `linkables` (tek tek seçilebilecekse) ya da `routes` (sabit liste linkiyse)
- [ ] View'lar: liste + detay (+ paylaşılan kart partial'ı varsa)
- [ ] Meta sözleşmesi uygulandı (`new-site` skill'ine bak)
- [ ] Sayfalama varsa `vendor.pagination.theme`
- [ ] `vendor/bin/pint --test`, gerçek tarayıcıda aç ve bak

## CLAUDE.md'ye satır

İş bitince modülün CLAUDE.md tablosundaki satırına ön yüz bilgisini ekle —
Neler Yaptık (projeler) satırındaki "**Ön yüz**: ..." cümlesi kalıptır, aynı
biçimde yaz.
