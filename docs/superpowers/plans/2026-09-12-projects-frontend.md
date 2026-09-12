# Projeler (Neler Yaptık) Ön Yüz Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** "Neler Yaptık" modülünün ön yüzünü yayına almak: `/projeler` liste, `/projeler/kategori/{slug}` kategori listesi, `/projeler/{slug}` detay sayfası ve modülün arama motoru altyapısına (sitemap, Schema.org, IndexNow, 301, kırık link, menü) bağlanması.

**Architecture:** Üç public route tek grupta `module.active:project,404` altında; ince bir `ProjectController` veriyi `ProjectService`'ten alır (liste için `listing()`, detay için `findBySlug()` + `related()`). Görünümler temanın `portfolio.html` / `portfolio-details.html` markup'ını kullanır, kart markup'ı tek partial'dan üç yerde paylaşılır. Model üç sözleşmeyi uygular (`LinksToPublicPage`, `RedirectsOnMove`, `SubmitsToIndexNow`) ve `publicUrl()` modül durumuna bakar — böylece modül kapalıyken ön yüzde o adrese giden link kalmaz.

**Tech Stack:** Laravel 13 / PHP 8.3, Blade + Bootstrap 5 tema (`resources/views/layout/html/`), jQuery + magnific-popup (galeri lightbox), Eloquent.

**Spec:** `docs/superpowers/specs/2026-09-12-projects-frontend-design.md`

## Global Constraints

- Proje kuralı: **otomatik test yazılmaz** (CLAUDE.md → "Yapılmayacaklar"). Doğrulama `php artisan tinker`, kimlik doğrulamalı/anonim `curl`, `php -l`, `php artisan route:list` ve `vendor/bin/pint` ile manuel yapılır.
- Ön yüz ve admin **iki ayrı dünya**: ön yüz Bootstrap (`resources/views/pages/`, `public/assets/`), admin Tailwind. Bir taraftan diğerine CSS/JS/markup taşınmaz.
- Tema class'ı uydurulmaz: markup `resources/views/layout/html/portfolio.html` ve `portfolio-details.html`'den alınır.
- Controller ince kalır: Eloquent/iş kuralı/`try-catch` içermez, metotlar 3-4 satırı geçmez. `abort_unless` guard'ı mevcut ön yüz controller'larında (ServiceController, BlogController) kullanılıyor, aynı kalıp izlenir.
- Servis `request()`/`session()`/`redirect()` bilmez. Kısa ve paylaşılmayan bloklar için private metot açılmaz.
- Kod ve DB İngilizce, arayüz metni Türkçe ve doğrudan Blade'e yazılır (lang dosyası yok).
- `validated()` / `?? null`: servis, gelmeyebilecek her alanı `?? null` ile okur.
- Ön yüz 404 gereken yerde `abort(404)`; 403 kullanılmaz (ziyaretçi için modül "yok", "yasak" değil).
- Yeni dosyalarda satır sonu/format `vendor/bin/pint` ile kontrol edilir.
- Test kullanıcısı (oturum gerektiren admin doğrulamaları için): `admin@webtasarim.test` / `password`. Site adresi `https://webtasarim.test` (http 301 ile https'e gider, curl'de `-L` ya da doğrudan https kullan).

---

### Task 1: Middleware'e durum kodu parametresi

**Files:**
- Modify: `app/Http/Middleware/EnsureModuleIsActive.php`

**Interfaces:**
- Produces: `module.active:<key>` (varsayılan 403, mevcut davranış) ve `module.active:<key>,404` — Task 2 route grubunda `module.active:project,404` kullanılır.

- [ ] **Step 1: `handle()` imzasına durum kodu ekle**

`app/Http/Middleware/EnsureModuleIsActive.php` içeriği tamamen şu olur:

```php
<?php

namespace App\Http\Middleware;

use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware'in desenini izler: pasif bir modülün route'una
 * doğrudan adres yazılırsa JSON isteğe JSON, normal isteğe abort().
 *
 * Durum kodu parametreyle verilir çünkü iki tarafın doğru cevabı farklı:
 * panelde 403 ("modül pasif, yöneticinin haberi olsun"), ön yüzde 404
 * (kullanılmayan bir modülün adresi ziyaretçi için hiç yoktur).
 *
 *   module.active:project       -> admin, 403
 *   module.active:project,404   -> ön yüz, 404
 */
class EnsureModuleIsActive
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    public function handle(Request $request, Closure $next, string $key, int|string $status = 403): Response
    {
        if ($this->modules->isActive($key)) {
            return $next($request);
        }

        $status = (int) $status;
        $message = $status === 404 ? 'Sayfa bulunamadı.' : 'Bu modül şu an pasif.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        abort($status, $message);
    }
}
```

- [ ] **Step 2: `php -l` ve admin tarafının bozulmadığını doğrula**

```bash
php -l app/Http/Middleware/EnsureModuleIsActive.php
php artisan route:list --name=admin.blog. -v | grep -c "module.active:blog"
```
Expected: sözdizimi hatası yok; grep 1'den büyük bir sayı (middleware hâlâ bağlı).

- [ ] **Step 3: İki durum kodunu tinker'da doğrula**

```bash
php artisan tinker --execute="
\$m = new App\Http\Middleware\EnsureModuleIsActive(app(App\Support\ModuleRegistry::class));
App\Models\Module\Module::where('key','project')->update(['is_active' => false]);
app(App\Support\ModuleRegistry::class)->flush();
\$req = Illuminate\Http\Request::create('/projeler', 'GET');
\$req->headers->set('Accept', 'application/json');
dump(\$m->handle(\$req, fn() => response('ok'), 'project')->getStatusCode());
dump(\$m->handle(\$req, fn() => response('ok'), 'project', '404')->getStatusCode());
App\Models\Module\Module::where('key','project')->update(['is_active' => true]);
app(App\Support\ModuleRegistry::class)->flush();
"
```
Expected: `403` ve `404`; son satırlar modülü tekrar aktif eder.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Middleware/EnsureModuleIsActive.php
git commit -m "Let module.active middleware answer with a given status code"
```

---

### Task 2: Route'lar, controller, servis metotları ve model sözleşmeleri

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/Project/ProjectController.php`
- Modify: `app/Services/Project/ProjectService.php`
- Modify: `app/Models/Project/Project.php`
- Modify: `app/Models/ProjectCategory/ProjectCategory.php`
- Modify: `config/redirects.php`

**Interfaces:**
- Consumes: `module.active:<key>,<status>` (Task 1).
- Produces:
  - Route adları: `projeler`, `projeler.kategori`, `projeler.show`
  - `ProjectService::listing(?ProjectCategory $category = null, int $perPage = 9): array{projects: LengthAwarePaginator, categories: Collection, category: ?ProjectCategory}`
  - `ProjectService::findCategoryBySlug(string $slug): ?ProjectCategory`
  - `ProjectService::related(Project $project, int $limit = 3): Collection`
  - `Project::publicUrl(): ?string`, `Project::indexNowUrl(): ?string`, `Project::publicLinkLabel(): string`, `Project::redirectableMove(): ?array`
  - `ProjectCategory::redirectableMove(): ?array`
  - View sözleşmesi: `pages.projects.index` → `$projects`, `$categories`, `$category`, `$schemaContext`; `pages.projects.show` → `$project`, `$related`, `$schemaContext` (Task 3 ve 4 bunları kullanır)

- [ ] **Step 1: `ProjectService`'e ön yüz metotlarını ekle**

`app/Services/Project/ProjectService.php` içinde `active()` metodunun hemen ÜSTÜNE ekle (import listesine `use Illuminate\Contracts\Pagination\LengthAwarePaginator;` zaten var):

```php
    /**
     * Ön yüz liste sayfası: sayfalanmış projeler + filtre çubuğu için
     * kategoriler. Kategori verilirse yalnızca o kategori listelenir.
     *
     * Filtre çubuğunda YALNIZCA yayında projesi olan aktif kategoriler yer
     * alır — boş bir kategoriye tıklayıp boş sayfa görmek kullanıcıyı
     * şaşırtır, arama motoru için de zayıf sayfa üretir.
     *
     * @return array{projects: LengthAwarePaginator, categories: Collection<int, ProjectCategory>, category: ?ProjectCategory}
     */
    public function listing(?ProjectCategory $category = null, int $perPage = 9): array
    {
        return [
            'projects' => Project::where('status', Project::STATUS_PUBLISHED)
                ->with(['media', 'category:id,name,slug'])
                ->when($category, fn ($query, ProjectCategory $selected) => $query->where('project_category_id', $selected->id))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate($perPage),
            'categories' => ProjectCategory::where('is_active', true)
                ->whereHas('projects', fn ($query) => $query->where('status', Project::STATUS_PUBLISHED))
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug']),
            'category' => $category,
        ];
    }

    /** Ön yüzde slug ile aktif kategori. Pasif ya da olmayan kategori için null. */
    public function findCategoryBySlug(string $slug): ?ProjectCategory
    {
        return ProjectCategory::where('slug', $slug)
            ->where('is_active', true)
            ->with('seo.ogMedia')
            ->first();
    }

    /**
     * Detay sayfasındaki "Benzer İşler". Aynı kategoriden başlar; kategori
     * yoksa ya da o kategoride yeterli proje yoksa en yeni diğer projelerle
     * tamamlanır — blok ya dolu görünür ya hiç görünmez, yarım kalmaz.
     *
     * @return Collection<int, Project>
     */
    public function related(Project $project, int $limit = 3): Collection
    {
        $base = fn () => Project::where('status', Project::STATUS_PUBLISHED)
            ->whereKeyNot($project->id)
            ->with(['media', 'category:id,name,slug']);

        $same = $project->project_category_id
            ? $base()->where('project_category_id', $project->project_category_id)
                ->orderBy('sort_order')
                ->limit($limit)
                ->get()
            : collect();

        if ($same->count() >= $limit) {
            return $same;
        }

        return $same->merge(
            $base()->whereKeyNot($same->modelKeys())
                ->latest('id')
                ->limit($limit - $same->count())
                ->get(),
        );
    }
```

- [ ] **Step 2: `Project` modeline üç sözleşmeyi ekle**

`app/Models/Project/Project.php` — import bloğuna ekle:

```php
use App\Contracts\LinksToPublicPage;
use App\Contracts\RedirectsOnMove;
use App\Contracts\SubmitsToIndexNow;
use App\Support\ModuleRegistry;
```

Sınıf bildirimini değiştir:

```php
class Project extends Model implements LinksToPublicPage, RedirectsOnMove, SubmitsToIndexNow
```

`statusLabel()` metodunun hemen ALTINA ekle:

```php
    /**
     * Kaydın ön yüz adresi — yayında değilse ya da "project" modülü Modül
     * Yönetimi'nden kapatılmışsa null. Modül kontrolü bilinçli olarak burada:
     * menü öğeleri ve kart linkleri publicUrl() null dönünce kendiliğinden
     * düşer, böylece modül kapalıyken 404'e giden bir link kalmaz.
     */
    public function publicUrl(): ?string
    {
        if (! app(ModuleRegistry::class)->isActive('project')) {
            return null;
        }

        return $this->status === self::STATUS_PUBLISHED
            ? route('projeler.show', $this->slug)
            : null;
    }

    /**
     * IndexNow adresi yayın durumuna ve modül durumuna BAKMAZ: yayından
     * kaldırılan ya da modülü kapatılan bir adresin de motora bildirilmesi
     * gerekir, motor 404'ü görüp dizininden düşürsün.
     */
    public function indexNowUrl(): ?string
    {
        return filled($this->slug) ? route('projeler.show', $this->slug) : null;
    }

    public function publicLinkLabel(): string
    {
        return $this->title;
    }

    /**
     * Slug değiştiyse eski → yeni adres. Kategori değişiminde adres
     * değişmediği için (kategori URL'in parçası değil) yalnızca slug bakılır.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return [
            'from' => 'projeler/'.$this->getOriginal('slug'),
            'to' => 'projeler/'.$this->slug,
        ];
    }
```

- [ ] **Step 3: `ProjectCategory` modeline 301 sözleşmesini ekle**

`app/Models/ProjectCategory/ProjectCategory.php` — import bloğuna `use App\Contracts\RedirectsOnMove;` ekle, sınıf bildirimini `class ProjectCategory extends Model implements RedirectsOnMove` yap ve `projects()` ilişkisinin ALTINA ekle:

```php
    /**
     * Kategori sayfaları (/projeler/kategori/{slug}) indekslenebilir olduğu
     * için slug değişimi ölü URL bırakmamalı.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array
    {
        if (! $this->wasChanged('slug')) {
            return null;
        }

        return [
            'from' => 'projeler/kategori/'.$this->getOriginal('slug'),
            'to' => 'projeler/kategori/'.$this->slug,
        ];
    }
```

- [ ] **Step 4: `config/redirects.php > auto_from` listesine iki modeli ekle**

Dosyanın başındaki import bloğuna ekle:

```php
use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
```

`auto_from` dizisine `Service::class,` satırının altına ekle:

```php
        Project::class,
        ProjectCategory::class,
```

- [ ] **Step 5: `ProjectController`'ı yaz**

`app/Http/Controllers/Project/ProjectController.php`:

```php
<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use App\Support\SchemaContext;
use Illuminate\View\View;

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

    /**
     * Kategori listesi. Kategoride yayında proje yoksa sayfa boş durumla
     * açılır — 404 vermiyoruz, çünkü kategori gerçekten var ve panelden
     * yeniden doldurulabilir.
     */
    public function category(string $slug): View
    {
        $category = $this->service->findCategoryBySlug($slug);
        abort_unless($category, 404);

        return view('pages.projects.index', [
            ...$this->service->listing($category),
            'schemaContext' => SchemaContext::collection($category->name, route('projeler.kategori', $category->slug)),
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

> `SchemaContext::project()` Task 6'da ekleniyor. O zamana kadar `show` sayfası
> hata verir; bu yüzden Task 2'nin doğrulaması tinker + route listesiyle yapılır,
> HTTP doğrulaması Task 3'te (liste) ve Task 6'dan sonra (detay) yapılır.
> Sırayı bozmamak için Task 6'yı Task 4'ten önce uygulamak da mümkündür —
> plan sırası: 3 (liste) → 4 (detay view) → 6 (schema) arasında detay sayfasının
> HTTP doğrulaması Task 6'ya ertelenir, bu bilinçlidir.

- [ ] **Step 6: Route'ları ekle**

`routes/web.php` — import bloğuna (alfabetik olarak `Maintenance` ile `Service` arasına):

```php
use App\Http\Controllers\Project\ProjectController;
```

`Route::get('/blog/{slug}', ...)` satırından SONRA, `/iletisim` route'larından ÖNCE ekle:

```php
/*
| Projeler (Neler Yaptık). Kategori route'u okunabilirlik için üstte; {slug}
| tek segment eşlediği için zaten çakışmazlar. Üçü de Modül Yönetimi'ndeki
| "project" anahtarına bağlı: modül pasifse ziyaretçiye 404 döner.
*/
Route::middleware('module.active:project,404')->group(function () {
    Route::get('/projeler', [ProjectController::class, 'index'])->name('projeler');
    Route::get('/projeler/kategori/{slug}', [ProjectController::class, 'category'])->name('projeler.kategori');
    Route::get('/projeler/{slug}', [ProjectController::class, 'show'])->name('projeler.show');
});
```

- [ ] **Step 7: Sözdizimi + route listesi**

```bash
php -l app/Http/Controllers/Project/ProjectController.php
php -l app/Services/Project/ProjectService.php
php -l app/Models/Project/Project.php
php -l app/Models/ProjectCategory/ProjectCategory.php
php -l routes/web.php
php -l config/redirects.php
php artisan route:list --name=projeler -v
```
Expected: hata yok; üç route listelenir ve her birinde `module.active:project,404` görünür.

- [ ] **Step 8: Servis ve model metotlarını tinker'da doğrula**

```bash
php artisan tinker --execute="
\$data = app(App\Services\Project\ProjectService::class)->listing();
dump(\$data['projects']->total(), \$data['categories']->pluck('name'), \$data['category']);
\$project = App\Models\Project\Project::where('status','published')->first();
dump(\$project?->publicUrl(), \$project?->indexNowUrl(), \$project?->publicLinkLabel());
dump(app(App\Services\Project\ProjectService::class)->related(\$project)->pluck('title'));
"
```
Expected: `total()` yayındaki proje sayısı; `categories` yalnızca yayında projesi olanlar; `publicUrl()` `https://webtasarim.test/projeler/<slug>`; `related` kaydın kendisini içermez.

> Yayında proje yoksa önce panelden (ya da tinker ile) en az iki proje yayına
> alınmalı, biri kategorili olmalı — doğrulamaların hepsi buna dayanıyor.

- [ ] **Step 9: ReservedPath'in `projeler`'i rezerve ettiğini doğrula**

```bash
php artisan tinker --execute="dump(App\Support\ReservedPath::taken('projeler'));"
```
Expected: `true` (artık bir Sayfa bu slug'ı alamaz).

- [ ] **Step 10: Commit**

```bash
git add routes/web.php app/Http/Controllers/Project/ProjectController.php \
        app/Services/Project/ProjectService.php app/Models/Project/Project.php \
        app/Models/ProjectCategory/ProjectCategory.php config/redirects.php
git commit -m "Add public project routes, controller and model contracts"
```

---

### Task 3: Liste sayfası — kart partial, sayfalama view'i, index.blade.php

**Files:**
- Create: `resources/views/pages/projects/partials/card.blade.php`
- Create: `resources/views/vendor/pagination/theme.blade.php`
- Create: `resources/views/pages/projects/index.blade.php`

**Interfaces:**
- Consumes: `$projects` (LengthAwarePaginator), `$categories`, `$category`, route adları (Task 2).
- Produces: `@include('pages.projects.partials.card', ['project' => $project])` — Task 4 (benzer işler) ve Task 5 (hizmet sayfası) aynı partial'ı kullanır; `vendor.pagination.theme` paginator view'i.

- [ ] **Step 1: Kart partial'ını yaz**

Markup kaynağı: `resources/views/layout/html/portfolio.html:660-676` (`portfolio-box`).

`resources/views/pages/projects/partials/card.blade.php`:

```blade
{{--
    Proje kartı — üç yerde kullanılır: liste ızgarası, detaydaki "Benzer
    İşler" ve hizmet detayındaki "bu hizmette yaptığımız işler". Tek kaynak
    olduğu için kart görünümü bir yerde değişince üçü birlikte değişir.

    $project  App\Models\Project\Project
--}}
@php
    $cardUrl = $project->publicUrl() ?? route('projeler.show', $project->slug);
    $cardCover = $project->getFirstMedia('cover');
    $cardLabel = $project->category?->name ?: $project->sector;
@endphp

<div class="portfolio-box">
    @if ($cardCover)
        <div class="image-area">
            <div class="image">
                <img src="{{ $cardCover->url('medium') }}" alt="{{ $project->title }}">
            </div>
            <a href="{{ $cardUrl }}" class="arrow" aria-label="{{ $project->title }}"><i class="fa-solid fa-arrow-right"></i></a>
        </div>
    @endif
    <div class="content-area">
        @if (filled($cardLabel))
            <span>{{ $cardLabel }}</span>
        @endif
        <a href="{{ $cardUrl }}">{{ $project->title }}</a>
    </div>
</div>
```

- [ ] **Step 2: Tema görünümünde paginator view'i yaz**

Markup kaynağı: `resources/views/layout/html/blog.html:825-834` (`theme-pagination`).

`resources/views/vendor/pagination/theme.blade.php`:

```blade
{{--
    Ön yüz sayfalaması — temanın .theme-pagination markup'ı.

    Laravel'in varsayılan paginator view'leri Tailwind içindir; ön yüz
    Bootstrap olduğu için olduğu gibi kullanılsa bozuk görünür. Kullanımı:
    {{ $projects->links('vendor.pagination.theme') }}

    Sayfa numaraları temada iki hanelidir (01, 02) — sprintf onu korur.
--}}
@if ($paginator->hasPages())
    <div class="theme-pagination text-center">
        <ul>
            @if (! $paginator->onFirstPage())
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Önceki sayfa">
                        <i class="fa-solid fa-angle-left"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            <a class="{{ $page == $paginator->currentPage() ? 'active' : '' }}" href="{{ $url }}">
                                {{ sprintf('%02d', $page) }}
                            </a>
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Sonraki sayfa">
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </li>
            @endif
        </ul>
    </div>
@endif
```

- [ ] **Step 3: Liste sayfasını yaz**

Markup kaynağı: hero için `resources/views/pages/services/index.blade.php:1-28`, filtre pill'leri için `portfolio.html:633-657` (`categories-buttons`), ızgara için `portfolio.html:659-760`.

`resources/views/pages/projects/index.blade.php`:

```blade
@extends('layout.app')

@php
    // Kategori sayfasında başlık ve meta kategoriden gelir; ana listede
    // site geneli SEO ayarlarına düşülür (/hizmetler ve /blog ile aynı).
    $listingTitle = $category?->name ?? 'Neler Yaptık';
    $listingSeo = $category?->seoMeta();
@endphp

@section('title', $listingSeo['title'] ?? $listingTitle)
@section('meta_description', (string) ($listingSeo['description'] ?? ''))
@section('meta_keywords', (string) ($listingSeo['keywords'] ?? ''))
@section('meta_image', (string) ($listingSeo['image'] ?? ''))

@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $listingTitle }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                @if ($category)
                                    <li><a href="{{ route('projeler') }}">Neler Yaptık</a></li>
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $category->name }}</li>
                                @else
                                    <li>Neler Yaptık</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA END =====-->

    <!--===== PORTFOLIO AREA START =====-->

    <div class="blog1 sp bg1 _relative">
        <div class="container">
            @if ($categories->isNotEmpty())
                {{-- Filtre: temanın sekme görünümü, ama gerçek linklerle (her kategori indekslenebilir bir adres). --}}
                <div class="row">
                    <div class="col-lg-10 m-auto text-center">
                        <div class="categories-buttons">
                            <ul class="nav nav-pills justify-content-center">
                                <li class="nav-item">
                                    <a class="nav-link {{ $category ? '' : 'active' }}" href="{{ route('projeler') }}">Tümü</a>
                                </li>
                                @foreach ($categories as $item)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $category?->is($item) ? 'active' : '' }}"
                                            href="{{ route('projeler.kategori', $item->slug) }}">{{ $item->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if ($projects->isEmpty())
                <div class="row">
                    <div class="col-lg-8 m-auto text-center">
                        <p class="mt-30">
                            {{ $category
                                ? 'Bu kategoride henüz yayınlanmış bir proje yok.'
                                : 'Henüz yayınlanmış bir proje bulunmuyor.' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="row mt-30">
                    @foreach ($projects as $project)
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                            @include('pages.projects.partials.card', ['project' => $project])
                        </div>
                    @endforeach
                </div>

                <div class="row mt-40">
                    <div class="col-lg-12">
                        {{ $projects->links('vendor.pagination.theme') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!--===== PORTFOLIO AREA END =====-->

    @include('pages.projects.partials.cta')
@endsection
```

- [ ] **Step 4: Alt CTA partial'ını yaz**

`resources/views/pages/projects/partials/cta.blade.php`:

```blade
{{--
    Liste ve detay sayfalarının altındaki çağrı bloğu. Metin firma adıyla
    kişiselleşir, hedef her zaman iletişim sayfasıdır — projeler bölümüne
    ikinci bir form koymuyoruz, lead altyapısı tek formdan yürüyor.
--}}
@php($ctaCompany = \App\Support\Settings::group('company'))

<div class="cta2 sp sec-bg1">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 m-auto text-center">
                <div class="heading2">
                    <h2>Sıradaki proje sizin olsun</h2>
                    <p class="mt-16">
                        Benzer bir işe ihtiyacınız varsa {{ $ctaCompany['name'] ?: config('app.name') }} ekibi
                        hedeflerinizi dinleyip yol haritasını birlikte çıkarır.
                    </p>
                    <div class="button mt-30">
                        <a class="theme-btn3" href="{{ route('iletisim') }}">
                            Bize Ulaşın
                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                            <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Liste sayfasını render ederek doğrula**

```bash
curl -s -o /tmp/projeler.html -w "LISTE: %{http_code}\n" https://webtasarim.test/projeler
grep -c "portfolio-box" /tmp/projeler.html
grep -o 'href="https://webtasarim.test/projeler/kategori/[^"]*"' /tmp/projeler.html | sort -u
```
Expected: `200`; `portfolio-box` sayısı yayındaki proje sayısı kadar (sayfa başına en çok 9); kategori linkleri yalnızca yayında projesi olan kategoriler için basılmış.

- [ ] **Step 6: Kategori sayfasını ve boş/404 durumlarını doğrula**

```bash
SLUG=$(php artisan tinker --execute="echo optional(App\Models\ProjectCategory\ProjectCategory::where('is_active',true)->first())->slug;" | tail -1 | tr -d '\r')
curl -s -o /tmp/kategori.html -w "KATEGORI(%{http_code})\n" "https://webtasarim.test/projeler/kategori/$SLUG"
grep -c "nav-link active" /tmp/kategori.html
curl -s -o /dev/null -w "OLMAYAN KATEGORI: %{http_code}\n" https://webtasarim.test/projeler/kategori/boyle-bir-kategori-yok
```
Expected: kategori sayfası `200`, tam 1 tane `nav-link active`, olmayan kategori `404`.

- [ ] **Step 7: Sayfalamanın tema görünümünde çıktığını doğrula**

```bash
php artisan tinker --execute="
\$p = app(App\Services\Project\ProjectService::class)->listing()['projects'];
dump(\$p->perPage(), \$p->hasPages());
echo \$p->links('vendor.pagination.theme')->toHtml();
"
```
Expected: `perPage = 9`; 9'dan fazla proje varsa `theme-pagination` markup'ı basılır, yoksa çıktı boş (paginator sayfa yoksa hiçbir şey basmaz).

- [ ] **Step 8: Commit**

```bash
git add resources/views/pages/projects resources/views/vendor/pagination/theme.blade.php
git commit -m "Add project listing page with category filter and themed pagination"
```

---

### Task 4: Detay sayfası

**Files:**
- Create: `resources/views/pages/projects/show.blade.php`
- Create: `resources/views/pages/projects/partials/faqs.blade.php`
- Create: `public/assets/js/pages/project/show.js`

**Interfaces:**
- Consumes: `$project`, `$related` (Task 2), kart partial (Task 3).
- Produces: detay sayfası; `#project-gallery` lightbox davranışı.

- [ ] **Step 1: SSS partial'ını yaz**

Kalıp kaynağı: `resources/views/pages/page/partials/faqs.blade.php`.

`resources/views/pages/projects/partials/faqs.blade.php`:

```blade
{{--
    Projeye bağlanmış sorular. Akordeon kimlikleri proje kimliğiyle önekli:
    aynı soru birden fazla içeriğe bağlanabildiği için sayfa içinde tekil
    olmaları gerekiyor.
--}}
@if ($project->faqs->isNotEmpty())
    <div class="research-faq mt-50">
        <h3>Sıkça Sorulan Sorular</h3>

        <div class="accordion accordion1" id="project-faq-{{ $project->id }}">
            @foreach ($project->faqs as $index => $faq)
                @php($target = "project-{$project->id}-faq-{$faq->id}")

                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#{{ $target }}"
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $target }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="{{ $target }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                        data-bs-parent="#project-faq-{{ $project->id }}">
                        <div class="accordion-body">
                            {!! $faq->answer !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
```

- [ ] **Step 2: Galeri lightbox JS'ini yaz**

`public/assets/js/pages/project/show.js`:

```js
/**
 * Proje detay galerisi — magnific-popup lightbox'ı.
 *
 * Eklenti ve jQuery layout'ta global yükleniyor (scripts.blade.php);
 * main.js yalnızca .play-btn'i iframe tipiyle bağlıyor, galeri tipi
 * burada bağlanır. Galeri yoksa sarmalayıcı hiç basılmadığı için
 * each() boş döner, hata olmaz.
 */
jQuery(function ($) {
    $('[data-project-gallery]').magnificPopup({
        delegate: 'a[data-gallery-item]',
        type: 'image',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            tPrev: 'Önceki',
            tNext: 'Sonraki',
            tCounter: '%curr% / %total%',
        },
        image: {
            titleSrc: 'data-title',
            tError: 'Görsel yüklenemedi.',
        },
    });
});
```

- [ ] **Step 3: Detay sayfasını yaz**

Markup kaynağı: `resources/views/layout/html/portfolio-details.html:630-870`. Blok sırası spec'teki sırayla birebir aynıdır; her blok verisi yoksa hiç basılmaz.

`resources/views/pages/projects/show.blade.php`:

```blade
@extends('layout.app')

@php
    $seo = $project->seoMeta();
    $cover = $project->getFirstMedia('cover');
    $gallery = $project->getMedia('gallery');
    $video = $project->videoEmbed();
    $videoFile = $project->getFirstMedia('video');
    $results = $project->resultRows();
    $technologies = $project->technologies ?? [];
    $shareUrl = urlencode((string) ($project->publicUrl() ?? url()->current()));
    // Künye satırları tek yerde kurulur: boş olanlar elenir, sıra sabit kalır.
    $facts = collect([
        ['Müşteri', $project->client_name, null],
        ['Sektör', $project->sector, null],
        ['Kategori', $project->category?->name, $project->category ? route('projeler.kategori', $project->category->slug) : null],
        ['Tamamlanma', $project->completedLabel(), null],
        ['Süre', $project->duration, null],
    ])->filter(fn ($row) => filled($row[1]));
@endphp

@section('title', $seo['title'] ?: $project->title)
@section('meta_description', (string) $seo['description'])
@section('meta_keywords', (string) $seo['keywords'])
@section('meta_image', (string) $seo['image'])

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/project/show.css') }}">
@endpush

@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $project->title }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('projeler') }}">Neler Yaptık</a></li>
                                @if ($project->category)
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li><a href="{{ route('projeler.kategori', $project->category->slug) }}">{{ $project->category->name }}</a></li>
                                @endif
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li>{{ $project->title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA END =====-->

    <!--===== PORTFOLIO DETAILS AREA START =====-->

    <div class="portfolio-details-area sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog-details-content">
                        <article>
                            <div class="details-content">
                                @if ($cover)
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url() }}" alt="{{ $project->title }}">
                                    </div>
                                @endif

                                @if (filled($project->excerpt) || filled($project->content))
                                    <div class="heading2 mt-24">
                                        <h3>Proje Hakkında</h3>
                                        @if (filled($project->excerpt))
                                            <p class="mt-16">{{ $project->excerpt }}</p>
                                        @endif
                                    </div>

                                    @if (filled($project->content))
                                        <div class="details-body mt-16">
                                            {!! $project->content !!}
                                        </div>
                                    @endif
                                @endif

                                @if ($results !== [])
                                    {{--
                                        Ölçülebilir sonuçlar. `direction` yalnızca ok yönünü
                                        belirler, renk sabit kalır: "çıkma oranı %60 düştü" iyi
                                        bir sonuçtur — yön tek başına iyi/kötü demez.
                                    --}}
                                    <div class="heading2 mt-50">
                                        <h3>Sonuçlar</h3>
                                    </div>
                                    <div class="counters-area-details mt-20">
                                        <div class="row">
                                            @foreach ($results as $result)
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="details-counter-box mt-30">
                                                        <h3>
                                                            @if ($result['direction'] === 'up')
                                                                <i class="fa-solid fa-arrow-trend-up"></i>
                                                            @elseif ($result['direction'] === 'down')
                                                                <i class="fa-solid fa-arrow-trend-down"></i>
                                                            @endif
                                                            {{ $result['value'] }}
                                                        </h3>
                                                        <p class="mt-10">{{ $result['label'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($gallery->isNotEmpty())
                                    <div class="heading2 mt-50">
                                        <h3>Proje Görselleri</h3>
                                    </div>
                                    <div class="row" data-project-gallery>
                                        @foreach ($gallery as $media)
                                            <div class="col-md-6">
                                                <div class="image mt-30">
                                                    <a href="{{ $media->url() }}" data-gallery-item
                                                        data-title="{{ $media->alt ?: $project->title }}">
                                                        <img class="w-full" src="{{ $media->url('medium') }}"
                                                            alt="{{ $media->alt ?: $project->title }}">
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($video || $videoFile)
                                    <div class="heading2 mt-50">
                                        <h3>Proje Videosu</h3>
                                    </div>
                                    <div class="mt-20">
                                        @if ($video)
                                            <div class="ratio ratio-16x9">
                                                <iframe src="{{ $video['embed_url'] }}" title="{{ $project->title }}"
                                                    loading="lazy" allowfullscreen
                                                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                            </div>
                                        @else
                                            <video class="w-full" controls preload="metadata"
                                                @if ($cover) poster="{{ $cover->url('medium') }}" @endif>
                                                <source src="{{ $videoFile->url() }}" type="{{ $videoFile->mime_type }}">
                                            </video>
                                        @endif
                                    </div>
                                @endif

                                @if ($project->services->isNotEmpty())
                                    <div class="heading2 mt-50">
                                        <h3>Bu Projede Verdiğimiz Hizmetler</h3>
                                        <div class="details-list-item mt-20">
                                            <ul>
                                                @foreach ($project->services as $service)
                                                    <li>
                                                        <span class="check"><i class="fa-solid fa-check"></i></span>
                                                        @if ($serviceUrl = $service->publicUrl())
                                                            <a href="{{ $serviceUrl }}">{{ $service->publicLinkLabel() }}</a>
                                                        @else
                                                            {{ $service->publicLinkLabel() }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @if ($project->testimonial)
                                    @php($testimonialPhoto = $project->testimonial->getFirstMedia('photo'))
                                    <div class="details-quote mt-50">
                                        <blockquote>
                                            <i class="fa-solid fa-quote-left"></i>
                                            <p>{{ $project->testimonial->content }}</p>
                                            <footer class="mt-20 d-flex align-items-center gap-3">
                                                @if ($testimonialPhoto)
                                                    <img src="{{ $testimonialPhoto->url('thumb') }}"
                                                        alt="{{ $project->testimonial->name }}" width="56" height="56">
                                                @endif
                                                <span>
                                                    <strong>{{ $project->testimonial->name }}</strong>
                                                    @if (filled($project->testimonial->title))
                                                        <br><small>{{ $project->testimonial->title }}</small>
                                                    @endif
                                                </span>
                                            </footer>
                                        </blockquote>
                                    </div>
                                @endif

                                @include('pages.projects.partials.faqs', ['project' => $project])
                            </div>
                        </article>

                        <div class="details-border"></div>
                        <div class="details-content">
                            <div class="details-social-tags">
                                @if ($project->tags->isNotEmpty())
                                    <div class="tags">
                                        <ul>
                                            <li class="text">Etiketler:</li>
                                            @foreach ($project->tags as $tag)
                                                <li class="tag"><a href="{{ route('projeler') }}">#{{ $tag->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="social-icons">
                                    <ul>
                                        <li class="text">Paylaş:</li>
                                        <li class="icon">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="Facebook'ta paylaş">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="icon">
                                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($project->title) }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="X'te paylaş">
                                                <i class="fa-brands fa-x-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="icon">
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                                                target="_blank" rel="noopener noreferrer" aria-label="LinkedIn'de paylaş">
                                                <i class="fa-brands fa-linkedin-in"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="sidebar-area ml-30 md:ml-0 sm:ml-0 md:mt-40 sm:mt-40">
                        @if ($facts->isNotEmpty() || $technologies !== [] || filled($project->project_url))
                            <div class="_sidebar-widget _portfolio">
                                <h3>Proje Künyesi</h3>
                                @if ($facts->isNotEmpty())
                                    <div class="portfolio-list">
                                        <ul>
                                            @foreach ($facts as [$label, $value, $url])
                                                <li>
                                                    {{ $label }}:
                                                    <span>
                                                        @if ($url)
                                                            <a href="{{ $url }}">{{ $value }}</a>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($technologies !== [])
                                    <div class="project-tech mt-20">
                                        <h4>Kullanılan Teknolojiler</h4>
                                        <ul class="project-tech-list mt-10">
                                            @foreach ($technologies as $technology)
                                                <li>{{ $technology }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (filled($project->project_url))
                                    <div class="button mt-20">
                                        <a class="theme-btn3" href="{{ $project->project_url }}" target="_blank"
                                            rel="noopener noreferrer">
                                            Siteyi Görüntüle
                                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                                            <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="_sidebar-widget _contact mt-40">
                            <h3>Benzer bir proje mi planlıyorsunuz?</h3>
                            <p class="mt-10">İhtiyacınızı anlatın, size uygun kurguyu birlikte çıkaralım.</p>
                            <div class="button mt-20">
                                <a class="theme-btn3" href="{{ route('iletisim') }}">
                                    Teklif Alın
                                    <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                                    <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== PORTFOLIO DETAILS AREA END =====-->

    @if ($related->isNotEmpty())
        <!--===== RELATED PROJECTS START =====-->

        <div class="portfolio sp sec-bg1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 m-auto text-center">
                        <div class="heading2">
                            <h2>Benzer İşler</h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($related as $relatedProject)
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                            @include('pages.projects.partials.card', ['project' => $relatedProject])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!--===== RELATED PROJECTS END =====-->
    @endif

    @include('pages.projects.partials.cta')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/pages/project/show.js') }}"></script>
@endpush
```

- [ ] **Step 4: Sayfaya özel CSS'i yaz**

Temada karşılığı olmayan üç küçük blok var: künyedeki teknoloji etiketleri, müşteri yorumu alıntısı ve galeri görsellerinin tıklanabilir oluşu. `public/assets/css/pages/project/show.css`:

```css
/* Proje detayında temada karşılığı olmayan üç blok. Geri kalan her şey
   temanın portfolio-details sınıflarıyla geliyor. */

.project-tech-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.project-tech-list li {
    font-size: 14px;
    line-height: 1;
    padding: 8px 12px;
    border: 1px solid rgba(0, 0, 0, .08);
    border-radius: 4px;
    background: #fff;
}

.details-quote blockquote {
    margin: 0;
    padding: 28px 30px;
    border-radius: 8px;
    background: #f6f7f9;
    border-left: 3px solid var(--ztc-color-primary, #2b59ff);
}

.details-quote blockquote > i {
    font-size: 22px;
    opacity: .35;
}

.details-quote blockquote p {
    margin: 12px 0 0;
    font-size: 18px;
    line-height: 1.6;
}

.details-quote blockquote footer img {
    border-radius: 50%;
    object-fit: cover;
}

[data-project-gallery] a[data-gallery-item] {
    display: block;
    cursor: zoom-in;
}
```

- [ ] **Step 5: Detay sayfasının render edildiğini doğrula**

> `SchemaContext::project()` Task 6'da eklendiği için bu adım Task 6'dan SONRA
> çalıştırılır. Sıra bozulmasın diye Task 4 commit edilir, doğrulama Task 6'nın
> sonunda yapılır. Önce hızlı bir sözdizimi kontrolü:

```bash
php artisan view:clear
php -l public/assets/js/pages/project/show.js 2>/dev/null || node --check public/assets/js/pages/project/show.js
```
Expected: JS sözdizimi hatası yok (`php -l` JS'i denetlemez, `node --check` kullanılır).

- [ ] **Step 6: Commit**

```bash
git add resources/views/pages/projects/show.blade.php \
        resources/views/pages/projects/partials/faqs.blade.php \
        public/assets/js/pages/project/show.js \
        public/assets/css/pages/project/show.css
git commit -m "Add project detail page with gallery, results, credits and FAQ"
```

---

### Task 5: Hizmet detayına "bu hizmette yaptığımız işler" bloğu

**Files:**
- Modify: `app/Http/Controllers/Service/ServiceController.php`
- Modify: `resources/views/pages/services/show.blade.php`

**Interfaces:**
- Consumes: `ProjectService::active(?int $limit, ?int $categoryId, ?int $serviceId)` (mevcut), kart partial (Task 3).
- Produces: `pages.services.show` view'ine `$projects` değişkeni.

- [ ] **Step 1: Controller'a projeleri geçir**

`app/Http/Controllers/Service/ServiceController.php` — import ekle:

```php
use App\Services\Project\ProjectService;
```

Constructor'ı iki servis alacak şekilde değiştir:

```php
    public function __construct(
        private readonly ServiceService $service,
        private readonly ProjectService $projects,
    ) {}
```

`show()` ve `showForRegion()` metotlarının view dizisine şu satırı ekle (ikisine de):

```php
            'projects' => $this->projects->active(6, null, $service->id),
```

- [ ] **Step 2: Hizmet detayına bloğu ekle**

`resources/views/pages/services/show.blade.php` — `@endsection`'dan HEMEN ÖNCE ekle:

```blade
    @if ($projects->isNotEmpty())
        <!--===== SERVICE PROJECTS START =====-->

        <div class="portfolio sp sec-bg1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 m-auto text-center">
                        <div class="heading2">
                            <h2>Bu Hizmette Yaptığımız İşler</h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-30">
                    @foreach ($projects as $serviceProject)
                        <div class="col-lg-4 col-md-6 mt-30" data-aos="fade-up" data-aos-duration="900">
                            @include('pages.projects.partials.card', ['project' => $serviceProject])
                        </div>
                    @endforeach
                </div>
                <div class="row mt-40">
                    <div class="col-lg-12 text-center">
                        <a class="theme-btn3" href="{{ route('projeler') }}">
                            Tüm İşlerimiz
                            <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span>
                            <span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!--===== SERVICE PROJECTS END =====-->
    @endif
```

- [ ] **Step 3: Doğrula**

```bash
php -l app/Http/Controllers/Service/ServiceController.php
php artisan tinker --execute="
\$service = App\Models\Service\Service::where('status','published')->first();
dump(\$service?->slug, app(App\Services\Project\ProjectService::class)->active(6, null, \$service?->id)->pluck('title'));
"
SSLUG=$(php artisan tinker --execute="echo optional(App\Models\Service\Service::where('status','published')->first())->slug;" | tail -1 | tr -d '\r')
curl -s -o /tmp/hizmet.html -w "HIZMET: %{http_code}\n" "https://webtasarim.test/hizmetler/$SSLUG"
grep -c "Bu Hizmette Yaptığımız İşler" /tmp/hizmet.html
```
Expected: hizmet sayfası `200`. O hizmete bağlı proje varsa başlık 1 kez geçer; bağlı proje yoksa `0` (blok hiç basılmaz) — ikisi de doğru sonuçtur, hangisinin beklendiğini tinker çıktısı söyler.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Service/ServiceController.php resources/views/pages/services/show.blade.php
git commit -m "Show related projects on service detail pages"
```

---

### Task 6: Schema.org — context, graph düğümü, panel denetimi

**Files:**
- Modify: `app/Support/SchemaContext.php`
- Modify: `app/Services/Schema/SchemaGraphBuilder.php`
- Modify: `app/Services/Schema/SchemaInspector.php`

**Interfaces:**
- Consumes: `Project`, route adları (Task 2).
- Produces: `SchemaContext::PROJECT`, `SchemaContext::project(Project $project, ?string $url = null): self` — Task 2'deki `ProjectController::show()` bunu çağırıyor.

- [ ] **Step 1: `SchemaContext`'e proje bağlamını ekle**

`app/Support/SchemaContext.php` — import ekle:

```php
use App\Models\Project\Project;
```

`BLOG_POSTING` sabitinin altına ekle:

```php
    public const PROJECT = 'project';
```

`blogPosting()` metodunun altına ekle:

```php
    public static function project(Project $project, ?string $url = null): self
    {
        $url ??= route('projeler.show', $project->slug);

        $trail = [['Neler Yaptık', route('projeler')]];

        if ($category = $project->category) {
            $trail[] = [$category->name, route('projeler.kategori', $category->slug)];
        }

        $trail[] = [$project->title, $url];

        return new self(self::PROJECT, $url, $project->title, self::trail($trail), $project);
    }
```

`fromRoute()` içindeki `match` bloğuna `'blog' => ...` satırının altına ekle:

```php
            'projeler' => self::collection('Neler Yaptık', route('projeler')),
```

- [ ] **Step 2: `SchemaGraphBuilder`'a `CreativeWork` düğümünü ekle**

`app/Services/Schema/SchemaGraphBuilder.php` — import ekle:

```php
use App\Models\Project\Project;
```

`pageNodes()` içindeki `$webPageType` satırını değiştir (PROJECT de istisna listesine girer — kayıt bazlı `schema_type` override'ı WebPage'i değil proje düğümünü değiştirir):

```php
        $webPageType = $overrideType && ! in_array($ctx->kind, [SchemaContext::SERVICE, SchemaContext::BLOG_POSTING, SchemaContext::PROJECT], true)
            ? $overrideType
            : $this->webPageType($ctx->kind);
```

Aynı metotta, `blogPostingNode` çağrısının altına ekle:

```php
        if ($ctx->kind === SchemaContext::PROJECT && $ctx->model instanceof Project) {
            $nodes[] = $this->projectNode($ctx, $overrideType);
        }
```

`webPageType()` içindeki `match`'e ekle:

```php
            SchemaContext::PROJECT => 'ItemPage',
```

`blogPostingNode()` metodunun altına yeni metodu ekle:

```php
    /**
     * Vaka çalışması düğümü. schema.org'da "proje/vaka çalışması" tipi yok;
     * CreativeWork en yakın karşılıktır. Müşteri adı `about` altında bir
     * Organization olarak verilir — CreativeWork'ün müşteri alanı yoktur.
     *
     * @return array<string, mixed>
     */
    private function projectNode(SchemaContext $ctx, ?string $overrideType): array
    {
        /** @var Project $project */
        $project = $ctx->model;
        $meta = $project->seoMeta();

        return [
            '@type' => $overrideType ?: 'CreativeWork',
            '@id' => $this->pageUrl($ctx).'#project',
            'name' => $project->title,
            'description' => $meta['description'] ?? null,
            'url' => $this->pageUrl($ctx),
            // Kapak + galerinin ilk dördü: portföy kaydında birden fazla görsel
            // gerçekten var, tek kapakla sınırlamak bilgi kaybı olur.
            'image' => collect([$project->getFirstMedia('cover')])
                ->concat($project->getMedia('gallery')->take(4))
                ->filter()
                ->map(fn ($media) => $this->absolute($media->url('medium')))
                ->values()
                ->all(),
            'datePublished' => ($project->completed_at ?? $project->created_at)?->toIso8601String(),
            'dateModified' => $project->updated_at?->toIso8601String(),
            'creator' => ['@id' => $this->site.'/#organization'],
            'about' => filled($project->client_name)
                ? ['@type' => 'Organization', 'name' => $project->client_name]
                : null,
            'genre' => $project->category?->name,
            'keywords' => $project->tagNames() !== [] ? implode(', ', $project->tagNames()) : null,
            'mainEntityOfPage' => ['@id' => $this->pageUrl($ctx).'#webpage'],
            'inLanguage' => 'tr-TR',
        ];
    }
```

- [ ] **Step 3: `SchemaInspector`'a proje adreslerini ekle**

`app/Services/Schema/SchemaInspector.php` — import ekle:

```php
use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
```

`samples()` içinde, ilk `$groups` dizisine `['label' => 'Blog', 'url' => route('blog')],` satırının altına ekle:

```php
                ['label' => 'Neler Yaptık', 'url' => route('projeler')],
```

`samples()`'ın `return $groups;` satırından ÖNCE ekle:

```php
        $projects = Project::query()
            ->where('status', Project::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->limit(8)
            ->get(['id', 'title', 'slug']);

        if ($projects->isNotEmpty()) {
            $groups[] = ['label' => 'Projeler', 'items' => $projects
                ->map(fn (Project $p) => ['label' => $p->title, 'url' => route('projeler.show', $p->slug)])
                ->all()];
        }
```

`contextFor()` içindeki `match`'e `'blog' => ...` satırının altına ekle:

```php
            'projeler' => [SchemaContext::collection('Neler Yaptık', route('projeler')), true],
```

ve `'blog.show' => ...` satırının altına ekle:

```php
            'projeler.show' => $this->projectContext($slug),
            'projeler.kategori' => $this->projectCategoryContext($slug),
```

`blogContext()` metodunun altına iki metodu ekle:

```php
    /**
     * @return array{0: SchemaContext, 1: bool}
     */
    private function projectContext(?string $slug): array
    {
        $project = Project::where('slug', $slug)
            ->with(['media', 'tags', 'faqs', 'seo.ogMedia', 'category:id,name,slug'])
            ->first();

        return $project
            ? [SchemaContext::project($project), true]
            : [SchemaContext::generic(null, url('projeler/'.$slug)), false];
    }

    /**
     * @return array{0: SchemaContext, 1: bool}
     */
    private function projectCategoryContext(?string $slug): array
    {
        $category = ProjectCategory::where('slug', $slug)->where('is_active', true)->first();

        return $category
            ? [SchemaContext::collection($category->name, route('projeler.kategori', $category->slug)), true]
            : [SchemaContext::generic(null, url('projeler/kategori/'.$slug)), false];
    }
```

- [ ] **Step 4: Detay sayfasını ve üretilen @graph'ı doğrula**

```bash
php -l app/Support/SchemaContext.php
php -l app/Services/Schema/SchemaGraphBuilder.php
php -l app/Services/Schema/SchemaInspector.php
php artisan view:clear

PSLUG=$(php artisan tinker --execute="echo optional(App\Models\Project\Project::where('status','published')->first())->slug;" | tail -1 | tr -d '\r')
curl -s -o /tmp/proje.html -w "DETAY: %{http_code}\n" "https://webtasarim.test/projeler/$PSLUG"
grep -o '"@type": *"CreativeWork"' /tmp/proje.html | head -1
grep -o '"@type": *"BreadcrumbList"' /tmp/proje.html | head -1
grep -c "Proje Künyesi" /tmp/proje.html
```
Expected: `200`, `CreativeWork` ve `BreadcrumbList` düğümleri basılmış, künye bloğu 1 kez.

- [ ] **Step 5: Panelin Schema denetimi ekranını doğrula**

```bash
php artisan tinker --execute="
\$report = app(App\Services\Schema\SchemaInspector::class)->report('projeler/$(php artisan tinker --execute="echo optional(App\Models\Project\Project::where('status','published')->first())->slug;" | tail -1 | tr -d '\r')');
dump(\$report['target']['kind'], \$report['target']['resolved'], collect(\$report['graph']['@graph'])->pluck('@type'));
dump(collect(app(App\Services\Schema\SchemaInspector::class)->samples())->pluck('label'));
"
```
Expected: `kind = project`, `resolved = true`, `@type` listesinde `CreativeWork`; örnek grup listesinde "Projeler" var.

- [ ] **Step 6: Commit**

```bash
git add app/Support/SchemaContext.php app/Services/Schema/SchemaGraphBuilder.php app/Services/Schema/SchemaInspector.php
git commit -m "Emit CreativeWork schema for project pages and expose them to the schema inspector"
```

---

### Task 7: Site haritası kaynağı

**Files:**
- Modify: `config/sitemap.php`
- Modify: `app/Services/Sitemap/SitemapService.php`

**Interfaces:**
- Consumes: `Project::publicUrl()` (Task 2), `ModuleRegistry::isActive()` (mevcut).
- Produces: `sitemap-projects.xml` — liste + kategori + proje adresleri.

- [ ] **Step 1: Config'e kaynağı ve gözlenen modelleri ekle**

`config/sitemap.php` — import bloğuna ekle:

```php
use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
```

`sources` dizisine `'regions' => ...` satırının altına ekle:

```php
        'projects' => 'Projeler (Neler Yaptık)',
```

`observed_models` dizisine ekle:

```php
        Project::class,
        ProjectCategory::class,
```

> `static_routes`'a `projeler` bilinçli olarak EKLENMEZ: liste adresi
> `projects` kaynağının içinde üretilir, böylece modül kapatıldığında tek bir
> yerden düşer.

- [ ] **Step 2: `SitemapService`'e kaynağı ekle**

`app/Services/Sitemap/SitemapService.php` — import bloğuna ekle:

```php
use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
use App\Support\ModuleRegistry;
```

`generate()` içindeki `match`/dizi bloğuna `'regions' => ...` satırının altına ekle:

```php
            'projects' => $enabled['projects'] ? $this->writeProjects() : $this->disable('projects'),
```

`writeRegions()` metodunun altına ekle:

```php
    /**
     * Projeler: liste adresi + kategori adresleri + proje adresleri tek
     * kaynakta. Kaynağın tamamı Modül Yönetimi'ndeki "project" anahtarına
     * bağlı — modül kapalıysa ön yüz 404 döndüğü için site haritasında da
     * hiç adres olmamalı.
     *
     * @return array{files: array<int, string>, count: int}
     */
    private function writeProjects(): array
    {
        if (! app(ModuleRegistry::class)->isActive('project')) {
            return $this->disable('projects');
        }

        $urls = [['loc' => route('projeler'), 'lastmod' => null, 'image' => null]];

        foreach (ProjectCategory::query()
            ->where('is_active', true)
            ->whereHas('projects', fn ($query) => $query->where('status', Project::STATUS_PUBLISHED))
            ->with('seo')
            ->get() as $category) {
            if ($this->isNoindex($category)) {
                continue;
            }

            $urls[] = [
                'loc' => route('projeler.kategori', $category->slug),
                'lastmod' => $category->updated_at,
                'image' => null,
            ];
        }

        Project::query()->where('status', Project::STATUS_PUBLISHED)->with('seo')
            ->chunk(100, function ($projects) use (&$urls) {
                foreach ($projects as $project) {
                    if ($this->isNoindex($project)) {
                        continue;
                    }

                    $urls[] = ['loc' => $project->publicUrl(), 'lastmod' => $project->updated_at, 'image' => $this->coverImage($project)];
                }
            });

        return $this->writeSource('projects', $urls);
    }
```

- [ ] **Step 3: Yeni kaynağı ayarlarda aç**

Mevcut kurulumda sitemap ayarları kaydedilmişse yeni `projects` anahtarı kayıtlı listede olmadığı için kapalı görünür. Aç:

```bash
php artisan tinker --execute="
\$settings = app(App\Services\Setting\SettingService::class);
\$current = (string) \$settings->get('sitemap', 'sources', implode(',', array_keys(config('sitemap.sources'))));
\$keys = array_values(array_unique(array_filter(array_merge(explode(',', \$current), ['projects']))));
\$settings->putGroup('sitemap', ['sources' => implode(',', \$keys)]);
dump(\$settings->get('sitemap', 'sources'));
"
```
Expected: çıktıda `projects` de listede.

- [ ] **Step 4: Site haritasını üret ve içeriğini doğrula**

```bash
php -l config/sitemap.php
php -l app/Services/Sitemap/SitemapService.php
php artisan sitemap:generate
curl -s https://webtasarim.test/sitemap.xml | grep -c "sitemap-projects.xml"
curl -s https://webtasarim.test/sitemap-projects.xml | grep -c "<loc>"
curl -s https://webtasarim.test/sitemap-projects.xml | grep -o "<loc>[^<]*</loc>" | head -5
```
Expected: indekste `sitemap-projects.xml` 1 kez; dosyada en az `1 + kategori + proje` kadar `<loc>`; ilk satırlarda liste adresi ve kategori adresleri görünür.

- [ ] **Step 5: Commit**

```bash
git add config/sitemap.php app/Services/Sitemap/SitemapService.php
git commit -m "Add projects source to the sitemap generator"
```

---

### Task 8: IndexNow, menü ve kırık link entegrasyonu

**Files:**
- Modify: `config/indexnow.php`
- Modify: `config/menus.php`
- Modify: `app/Services/BrokenLink/LinkChecker.php`

**Interfaces:**
- Consumes: `Project::indexNowUrl()`, `Project::publicUrl()`, `Project::publicLinkLabel()` (Task 2).

- [ ] **Step 1: IndexNow gözlem listesine ekle**

`config/indexnow.php` — import bloğuna `use App\Models\Project\Project;` ekle, `observed_models` dizisine `Project::class,` satırını ekle.

- [ ] **Step 2: Menü yöneticisine ekle**

`config/menus.php` — import bloğuna `use App\Models\Project\Project;` ekle.

`linkables` dizisine `blog` girişinin altına ekle:

```php
        'project' => [
            'label' => 'Proje',
            'model' => Project::class,
            'query' => fn () => Project::query()->where('status', Project::STATUS_PUBLISHED)->orderBy('sort_order'),
            'option_label' => fn (Project $project) => $project->title,
        ],
```

`routes` dizisine `'blog' => 'Blog (liste)',` satırının altına ekle:

```php
        'projeler' => 'Neler Yaptık (liste)',
```

- [ ] **Step 3: Kırık link tarayıcısına route çözümünü ekle**

`app/Services/BrokenLink/LinkChecker.php` — import bloğuna ekle:

```php
use App\Models\Project\Project;
use App\Models\ProjectCategory\ProjectCategory;
```

İç adres `match` bloğuna `'hizmetler.show-region' => ...` satırının altına ekle:

```php
            'projeler.show' => $this->checkRecord(Project::where('slug', $route->parameter('slug'))->first(), 'Proje'),
            'projeler.kategori' => $this->checkCategory($route->parameter('slug')),
```

`checkRecord()` imzasını genişlet:

```php
    private function checkRecord(Page|Blog|Service|Project|null $record, string $label): ?array
```

`checkRegion()` metodunun altına ekle:

```php
    /**
     * Proje kategorisi sayfası. Kategori pasifse ya da yoksa adres 404 döner;
     * ikisi ayrı sebep olarak raporlanmaz çünkü düzeltme yolu aynı: linki
     * değiştir ya da kategoriyi yayına al.
     */
    private function checkCategory(?string $slug): ?array
    {
        return ProjectCategory::where('slug', $slug)->where('is_active', true)->exists()
            ? null
            : $this->broken('internal', 404, 'not_found', 'Böyle bir proje kategorisi yok ya da pasif.');
    }
```

- [ ] **Step 4: Doğrula**

```bash
php -l config/indexnow.php
php -l config/menus.php
php -l app/Services/BrokenLink/LinkChecker.php

php artisan tinker --execute="
dump(in_array(App\Models\Project\Project::class, config('indexnow.observed_models'), true));
dump(array_keys(config('menus.linkables')), config('menus.routes')['projeler'] ?? null);
\$checker = app(App\Services\BrokenLink\LinkChecker::class);
\$project = App\Models\Project\Project::first();
dump(\$checker->check(url('projeler/'.\$project->slug)));
dump(\$checker->check(url('projeler/olmayan-bir-proje')));
"
```
Expected: IndexNow listesinde `true`; linkables içinde `project`, routes içinde "Neler Yaptık (liste)"; yayındaki projenin adresi `null` (kırık değil), olmayan adres `not_found` sebebiyle kırık.

> `LinkChecker`'ın public metot adı `check()` değilse tinker satırını dosyadaki
> gerçek public imzaya göre düzelt (`grep -n "public function" app/Services/BrokenLink/LinkChecker.php`).

- [ ] **Step 5: Menü ekranında göründüğünü doğrula (oturumlu)**

```bash
cd "$(mktemp -d)"
TOKEN=$(curl -s -c jar.txt https://webtasarim.test/admin/login -o login.html; grep -o 'name="_token" value="[^"]*"' login.html | sed 's/.*value="//;s/"//')
curl -s -b jar.txt -c jar.txt -X POST https://webtasarim.test/admin/login -d "_token=$TOKEN" -d "email=admin@webtasarim.test" -d "password=password" -o /dev/null
curl -s -b jar.txt https://webtasarim.test/admin/menu | grep -c "Neler Yaptık (liste)"
```
Expected: `1` veya daha fazla (hazır bağlantı listesinde görünüyor).

- [ ] **Step 6: Commit**

```bash
git add config/indexnow.php config/menus.php app/Services/BrokenLink/LinkChecker.php
git commit -m "Wire projects into IndexNow, menu linkables and broken-link scanning"
```

---

### Task 9: Modül anahtarı uçtan uca, dokümantasyon ve regresyon

**Files:**
- Modify: `CLAUDE.md`

**Interfaces:** Consumes: Task 1–8'in tamamı.

- [ ] **Step 1: Modül pasifken ön yüzün kapandığını doğrula**

```bash
PSLUG=$(php artisan tinker --execute="echo optional(App\Models\Project\Project::where('status','published')->first())->slug;" | tail -1 | tr -d '\r')

php artisan tinker --execute="
App\Models\Module\Module::where('key','project')->update(['is_active' => false]);
app(App\Support\ModuleRegistry::class)->flush();
dump(App\Models\Project\Project::first()->publicUrl());
"
curl -s -o /dev/null -w "LISTE: %{http_code}\n" https://webtasarim.test/projeler
curl -s -o /dev/null -w "DETAY: %{http_code}\n" "https://webtasarim.test/projeler/$PSLUG"
php artisan sitemap:generate >/dev/null && curl -s -o /dev/null -w "SITEMAP DOSYASI: %{http_code}\n" https://webtasarim.test/sitemap-projects.xml
```
Expected: `publicUrl()` null; liste ve detay `404`; `sitemap-projects.xml` `404` (kaynak dosyası silindi).

- [ ] **Step 2: Modülü geri aç ve her şeyin döndüğünü doğrula**

```bash
php artisan tinker --execute="
App\Models\Module\Module::where('key','project')->update(['is_active' => true]);
app(App\Support\ModuleRegistry::class)->flush();
"
php artisan sitemap:generate >/dev/null
curl -s -o /dev/null -w "LISTE: %{http_code}\n" https://webtasarim.test/projeler
curl -s -o /dev/null -w "DETAY: %{http_code}\n" "https://webtasarim.test/projeler/$PSLUG"
curl -s -o /dev/null -w "SITEMAP DOSYASI: %{http_code}\n" https://webtasarim.test/sitemap-projects.xml
```
Expected: üçü de `200`.

- [ ] **Step 3: 301 davranışını doğrula**

```bash
php artisan tinker --execute="
\$project = App\Models\Project\Project::where('status','published')->first();
\$old = \$project->slug;
\$project->update(['slug' => \$old.'-yeni-adres']);
dump(App\Models\Redirect\Redirect::latest('id')->first()?->only(['from_path','to_path','status_code']));
\$project->update(['slug' => \$old]);
"
```
Expected: `projeler/<eski>` → `projeler/<eski>-yeni-adres` 301 kaydı oluşmuş (son satır slug'ı geri alır; oluşan yönlendirme kayıtları panelden temizlenebilir).

- [ ] **Step 4: Taslak projenin 404 döndüğünü doğrula**

```bash
php artisan tinker --execute="
\$project = App\Models\Project\Project::where('status','draft')->first();
echo \$project?->slug ?: 'TASLAK YOK';
"
```
Taslak varsa adresini `curl -s -o /dev/null -w "%{http_code}\n" https://webtasarim.test/projeler/<slug>` ile dene.
Expected: `404`.

- [ ] **Step 5: Ön yüz ve admin regresyonu**

```bash
for path in "" hakkimizda hizmetler blog iletisim projeler kvkk cerez-politikasi; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "https://webtasarim.test/$path")
    echo "/$path -> $code"
done
curl -s -o /dev/null -w "sitemap.xml -> %{http_code}\n" https://webtasarim.test/sitemap.xml
curl -s -o /dev/null -w "robots.txt -> %{http_code}\n" https://webtasarim.test/robots.txt
vendor/bin/pint --test
tail -20 storage/logs/laravel.log | grep -i "ERROR\|Exception" || echo "YENI HATA YOK"
```
Expected: hepsi `200`, pint `passed`, logda yeni hata yok.

- [ ] **Step 6: `CLAUDE.md`'yi güncelle**

"Neler Yaptık (projeler)" satırının sonundaki şu cümle artık yanlış, değiştirilmeli:

> **Ön yüzü henüz yok**: `LinksToPublicPage` / `SubmitsToIndexNow` / site haritası kaydı bilinçli olarak EKLENMEDİ — ön yüz route'u doğduğunda eklenecek, şimdi eklenirse `publicUrl()` olmayan bir route'a gider.

Yerine:

> **Ön yüz**: `/projeler` (liste), `/projeler/kategori/{slug}` (kategori), `/projeler/{slug}` (detay) — üçü de `module.active:project,404` altında, yani Modül Yönetimi'nden kapatılınca ziyaretçiye 404 döner. `publicUrl()` yayın durumuna EK OLARAK modül durumuna da bakar (kapalıyken null döner, menü öğeleri ve kartlar kendiliğinden düşer); `indexNowUrl()` ikisine de bakmaz. Kart markup'ı tek partial (`pages/projects/partials/card.blade.php`) — liste, "benzer işler" ve hizmet detayındaki "bu hizmette yaptığımız işler" bloğu aynı dosyayı kullanır. Schema.org'da `CreativeWork` düğümü basar (müşteri `about` altında Organization). Site haritasında `projects` kaynağı liste + kategori + proje adreslerini birlikte üretir, modül kapalıysa hiç üretmez.

Ayrıca "Kurulu Altyapı" tablosuna, "Ön yüz bağlanabilir kayıt" satırının altına yeni bir satır ekle:

```
| Ön yüz sayfalaması | `resources/views/vendor/pagination/theme.blade.php` — temanın `.theme-pagination` markup'ı, `{{ $paginator->links('vendor.pagination.theme') }}` ile çağrılır. Laravel'in varsayılan paginator view'leri Tailwind olduğu için ön yüzde doğrudan kullanılamaz; yeni bir sayfalı ön yüz listesi bu view'i kullanır |
```

- [ ] **Step 7: Commit**

```bash
git add CLAUDE.md
git commit -m "Document the projects front-end and the themed pagination view"
```
