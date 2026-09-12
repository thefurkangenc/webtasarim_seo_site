# Modül Yönetimi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 13 içerik modülünü (blog, service, project, page, testimonial, reference, faq, why-choose-us, hero, announcement, popup, subscriber, lead) admin panelden pasife alınabilir/yeniden adlandırılabilir hale getiren ve medya kırpma boyutlarını config'ten veritabanına taşıyan bir "Modül Yönetimi" ekranı kurmak.

**Architecture:** İki yeni DB tablosu (`modules`, `media_presets`) + config'teki değişmeyen gerçeği (`config/modules.php`, `config/media.php > presets`) seed kaynağı olarak kullanan iki cache'li registry sınıfı (`ModuleRegistry`, `MediaPresetRegistry`). Bu registry'ler dört noktadan okunur: yeni bir route middleware'i (`module.active`), sidebar (`MenuService`), Dashboard (`DashboardService`), global arama (`GlobalSearchService`) ve mevcut iki medya preset okuma noktası (`MediaService::crop()`, `image.blade.php`). Tek bir "Modül Yönetimi" ekranı (Ayarlar ekranıyla aynı desen: tek form, tek submit) her iki tabloyu da günceller.

**Tech Stack:** Laravel 13 / PHP 8.3, Eloquent + Cache facade, Blade + mevcut `x-admin::form.*` bileşenleri, native JS (`core/http.js`, `core/form.js`, `core/toast.js`).

**Spec:** `docs/superpowers/specs/2026-09-12-module-management-design.md`

## Global Constraints

- Proje kuralı: **otomatik test yazılmaz** (CLAUDE.md → "Yapılmayacaklar"). Bu planda "failing test" adımları YOKTUR — her adım yerine `php artisan tinker`, `php -l`, `php artisan route:list`, `vendor/bin/pint` ve kimlik doğrulamalı `curl` ile **manuel doğrulama** kullanılır.
- Controller ince kalır; Eloquent/iş kuralı servise gider (CLAUDE.md).
- Model `#[Fillable([...])]` attribute stili kullanır, `$fillable` property'si yazılmaz (CLAUDE.md → laravel-architecture skill).
- Kod ve DB İngilizce, arayüz metni Türkçe, doğrudan Blade'e yazılır (CLAUDE.md).
- `validated()` çıktısındaki her nullable alan serviste `?? null` ile okunur (CLAUDE.md).
- Yeni izin `config/permissions.php`'ye eklenip `php artisan db:seed --class=RolePermissionSeeder` ile senkronlanır.
- v1 kapsamı sadece admin paneli — ön yüz, sayfa başlıkları/breadcrumb, modül bağımlılık uyarısı, modül ekleme/silme bilinçli olarak KAPSAM DIŞI (spec → "Kapsam dışı").
- Test girişi: seeded admin kullanıcı `admin@webtasarim.test` / `password` (`database/seeders/AdminUserSeeder.php`) — curl ile oturum açmak için kullanılır.

---

### Task 1: Veritabanı ve config temeli — `modules` ve `media_presets` tabloları

**Files:**
- Create: `database/migrations/2026_09_22_100000_create_modules_table.php`
- Create: `database/migrations/2026_09_22_110000_create_media_presets_table.php`
- Create: `app/Models/Module/Module.php`
- Create: `app/Models/Media/MediaPreset.php`
- Create: `config/modules.php`
- Create: `database/seeders/ModuleSeeder.php`
- Create: `database/seeders/MediaPresetSeeder.php`

**Interfaces:**
- Produces: `Module` model (`key` string, `name` ?string, `is_active` bool), `MediaPreset` model (`key` string, `width`/`height` int, `label` string) — Task 2'nin `ModuleRegistry`/`MediaPresetRegistry`'si bunları okur. `config('modules.definitions')` → `[key => ['label','icon','description','routes']]` — Task 3 (middleware uygulama) ve Task 5 (form) bunu okur.

- [ ] **Step 1: `modules` migration'ını yaz**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
```

- [ ] **Step 2: `media_presets` migration'ını yaz**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_presets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_presets');
    }
};
```

- [ ] **Step 3: Migration'ları çalıştır, tabloların oluştuğunu doğrula**

Run: `php artisan migrate`
Expected: `modules` ve `media_presets` tabloları "Done" olarak listelenir.

Run: `php artisan tinker --execute="dump(Schema::hasTable('modules'), Schema::hasTable('media_presets'));"`
Expected: `true, true`

- [ ] **Step 4: `Module` modelini yaz**

```php
<?php

namespace App\Models\Module;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'is_active'])]
class Module extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
```

- [ ] **Step 5: `MediaPreset` modelini yaz**

```php
<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'width', 'height', 'label'])]
class MediaPreset extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
        ];
    }
}
```

- [ ] **Step 6: `config/modules.php`'yi yaz — 13 modülün değişmeyen tanımı**

```php
<?php

/*
| Yönetilebilir 13 içerik modülünün değişmeyen gerçeği. Panelden sadece
| isim (override) ve aktiflik değişir — o `modules` tablosunda durur.
| Yeni bir modül route'u koddan geldiği için bu dosya elle yazılır.
|
| `routes`: bu modülün kapsadığı admin route prefix'leri — module.active
| middleware'i bunlara uygulanır (bkz. routes/admin.php).
*/

return [

    'definitions' => [
        'page' => [
            'label' => 'Sayfalar',
            'icon' => 'description',
            'description' => 'Kurumsal statik sayfalar (Hakkımızda, KVKK vb.).',
            'routes' => ['page'],
        ],
        'blog' => [
            'label' => 'Blog',
            'icon' => 'article',
            'description' => 'Blog yazıları ve kategorileri.',
            'routes' => ['blog', 'blog-category'],
        ],
        'service' => [
            'label' => 'Hizmetler',
            'icon' => 'design_services',
            'description' => 'Sunulan hizmetler ve hizmet bölgeleri.',
            'routes' => ['service', 'service-region'],
        ],
        'project' => [
            'label' => 'Neler Yaptık',
            'icon' => 'workspaces',
            'description' => 'Vaka çalışmaları ve proje kategorileri.',
            'routes' => ['project', 'project-category'],
        ],
        'testimonial' => [
            'label' => 'Müşteri Yorumları',
            'icon' => 'reviews',
            'description' => 'Müşteri yorumları.',
            'routes' => ['testimonial'],
        ],
        'reference' => [
            'label' => 'Referanslar',
            'icon' => 'handshake',
            'description' => 'Referans logoları/listesi.',
            'routes' => ['reference'],
        ],
        'faq' => [
            'label' => 'Sıkça Sorulan Sorular',
            'icon' => 'quiz',
            'description' => 'Sıkça sorulan sorular.',
            'routes' => ['faq'],
        ],
        'why-choose-us' => [
            'label' => 'Neden Biz',
            'icon' => 'verified',
            'description' => '"Neden Biz" maddeleri.',
            'routes' => ['why-choose-us'],
        ],
        'hero' => [
            'label' => 'Tanıtım Alanı',
            'icon' => 'wallpaper',
            'description' => 'Ana sayfa tanıtım alanı (slider/banner).',
            'routes' => ['hero'],
        ],
        'announcement' => [
            'label' => 'Duyuru Şeridi',
            'icon' => 'campaign',
            'description' => 'Üst duyuru şeridi.',
            'routes' => ['announcement'],
        ],
        'popup' => [
            'label' => 'Açılır Pencereler',
            'icon' => 'web_asset',
            'description' => 'Açılır pencere kampanyaları.',
            'routes' => ['popup'],
        ],
        'subscriber' => [
            'label' => 'Bülten Aboneleri',
            'icon' => 'forward_to_inbox',
            'description' => 'Bülten abonelik formu ve listesi.',
            'routes' => ['subscriber'],
        ],
        'lead' => [
            'label' => 'Gelen Talepler',
            'icon' => 'inbox',
            'description' => 'İletişim formundan gelen talepler.',
            'routes' => ['lead'],
        ],
    ],

];
```

- [ ] **Step 7: `ModuleSeeder`'ı yaz — `RolePermissionSeeder` deseniyle aynı**

```php
<?php

namespace Database\Seeders;

use App\Models\Module\Module;
use Illuminate\Database\Seeder;

/**
 * config/modules.php'deki 13 modülü modules tablosuna upsert eder.
 * Tekrar çalıştırılabilir; mevcut name/is_active değerine dokunmaz.
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('modules.definitions', []) as $key => $definition) {
            Module::query()->firstOrCreate(['key' => $key], ['is_active' => true]);
        }
    }
}
```

- [ ] **Step 8: `MediaPresetSeeder`'ı yaz — `config/media.php > presets`'ten kopyalar**

```php
<?php

namespace Database\Seeders;

use App\Models\Media\MediaPreset;
use Illuminate\Database\Seeder;

/**
 * config/media.php > presets'teki her anahtarı media_presets tablosuna
 * kopyalar. Tekrar çalıştırılabilir; DB'de zaten var olan satırın
 * width/height/label'ına dokunmaz (panelden değiştirilmiş olabilir).
 */
class MediaPresetSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('media.presets', []) as $key => $preset) {
            MediaPreset::query()->firstOrCreate(['key' => $key], [
                'width' => $preset['width'],
                'height' => $preset['height'],
                'label' => $preset['label'],
            ]);
        }
    }
}
```

- [ ] **Step 9: Seeder'ları çalıştır ve doğrula**

Run: `php artisan db:seed --class=ModuleSeeder && php artisan db:seed --class=MediaPresetSeeder`

Run: `php artisan tinker --execute="dump(\App\Models\Module\Module::count(), \App\Models\Media\MediaPreset::count());"`
Expected: `13, 14`

Run: `php artisan tinker --execute="dump(\App\Models\Module\Module::where('key','blog')->first()->is_active);"`
Expected: `true`

- [ ] **Step 10: `php -l` ile tüm yeni dosyaları kontrol et, commit**

```bash
php -l app/Models/Module/Module.php
php -l app/Models/Media/MediaPreset.php
php -l database/seeders/ModuleSeeder.php
php -l database/seeders/MediaPresetSeeder.php
php -l database/migrations/2026_09_22_100000_create_modules_table.php
php -l database/migrations/2026_09_22_110000_create_media_presets_table.php
```

```bash
git add database/migrations/2026_09_22_100000_create_modules_table.php \
        database/migrations/2026_09_22_110000_create_media_presets_table.php \
        app/Models/Module/Module.php app/Models/Media/MediaPreset.php \
        config/modules.php \
        database/seeders/ModuleSeeder.php database/seeders/MediaPresetSeeder.php
git commit -m "Add modules and media_presets tables with seeders"
```

---

### Task 2: Tek kaynak sınıfları — `ModuleRegistry`, `MediaPresetRegistry` + izin/log kaydı

**Files:**
- Create: `app/Support/ModuleRegistry.php`
- Create: `app/Support/MediaPresetRegistry.php`
- Modify: `config/permissions.php`
- Modify: `config/activity-log.php`

**Interfaces:**
- Consumes: `App\Models\Module\Module` (Task 1), `App\Models\Media\MediaPreset` (Task 1), `config('modules.definitions')` (Task 1).
- Produces: `ModuleRegistry::isActive(string $key): bool`, `ModuleRegistry::label(string $key): string`, `ModuleRegistry::all(): Collection` (her öğe `['key','label','icon','description','name','is_active']`), `ModuleRegistry::flush(): void` — Task 3 (middleware), Task 4 (controller/service), Task 6 (sidebar/dashboard/arama) bunları kullanır. `MediaPresetRegistry::get(string $key): ?array` (`['width','height','label']`), `MediaPresetRegistry::all(): Collection<string,array>`, `MediaPresetRegistry::flush(): void` — Task 4 (servis) ve Task 7 (preset okuma noktaları) bunları kullanır.

- [ ] **Step 1: `ModuleRegistry`'yi yaz**

```php
<?php

namespace App\Support;

use App\Models\Module\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Modüllerin aktif/pasif durumu ve görünen adı için TEK kaynak.
 *
 * DB'de satırı olmayan bir modül (henüz seed edilmemiş, ya da bilinmeyen
 * bir anahtar) varsayılan olarak AKTİF sayılır — "fail open": eksik veri
 * bir modülü yanlışlıkla kapatmasın.
 */
class ModuleRegistry
{
    public function isActive(string $key): bool
    {
        return $this->state()[$key]['is_active'] ?? true;
    }

    public function label(string $key): string
    {
        $name = $this->state()[$key]['name'] ?? null;

        return filled($name) ? $name : config("modules.definitions.{$key}.label", $key);
    }

    /** @return Collection<string, array{key: string, label: string, icon: string, description: string, name: ?string, is_active: bool}> */
    public function all(): Collection
    {
        $state = $this->state();

        return collect(config('modules.definitions', []))->map(fn (array $definition, string $key) => [
            'key' => $key,
            'label' => $definition['label'],
            'icon' => $definition['icon'],
            'description' => $definition['description'],
            'name' => $state[$key]['name'] ?? null,
            'is_active' => $state[$key]['is_active'] ?? true,
        ]);
    }

    public function flush(): void
    {
        Cache::forget('modules.state');
    }

    /**
     * DİKKAT: Collection değil düz array döner ve cache'lenir. Bu projenin
     * database cache sürücüsü `serialize` config'i varsayılan `false`
     * olduğu için DatabaseStore::unserialize() her nesneyi
     * `allowed_classes => false` ile açar — cache'lenen HER Collection/obje
     * okunduğunda sessizce `__PHP_Incomplete_Class`'a döner (TypeError'a
     * kadar gider). Aynı kısıt yüzünden SettingService::getGroup() de
     * `->all()` ile düz array döndürüyor — aynı kurala uyulur.
     *
     * @return array<string, array{name: ?string, is_active: bool}>
     */
    private function state(): array
    {
        return Cache::rememberForever('modules.state', fn () => Module::query()
            ->get(['key', 'name', 'is_active'])
            ->mapWithKeys(fn (Module $module) => [
                $module->key => ['name' => $module->name, 'is_active' => $module->is_active],
            ])
            ->all());
    }
}
```

- [ ] **Step 2: `MediaPresetRegistry`'yi yaz**

```php
<?php

namespace App\Support;

use App\Models\Media\MediaPreset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Medya kırpma preset'leri için TEK kaynak. DB boşsa (migration henüz
 * seed edilmemiş kurulum) config('media.presets')'e düşer — geriye dönük
 * güvenlik ağı.
 */
class MediaPresetRegistry
{
    public function get(string $key): ?array
    {
        return $this->state()[$key] ?? null;
    }

    /** @return Collection<string, array{width: int, height: int, label: string}> */
    public function all(): Collection
    {
        return collect($this->state());
    }

    public function flush(): void
    {
        Cache::forget('media.presets');
    }

    /**
     * Database cache sürücüsü nesne cache'lemeyi reddeder (bkz.
     * ModuleRegistry::state() yorumu) — Collection değil düz array cache'lenir.
     *
     * @return array<string, array{width: int, height: int, label: string}>
     */
    private function state(): array
    {
        return Cache::rememberForever('media.presets', function () {
            $rows = MediaPreset::query()->get(['key', 'width', 'height', 'label']);

            if ($rows->isEmpty()) {
                return config('media.presets', []);
            }

            return $rows->mapWithKeys(fn (MediaPreset $preset) => [
                $preset->key => ['width' => $preset->width, 'height' => $preset->height, 'label' => $preset->label],
            ])->all();
        });
    }
}
```

- [ ] **Step 3: Tinker ile doğrula (ayrı satırlar halinde — aynı process içinde iki kez okuma yapıldığını kanıtlamak için)**

Run:
```
php artisan tinker --execute="
dump(app(App\Support\ModuleRegistry::class)->isActive('blog'));
dump(app(App\Support\ModuleRegistry::class)->label('blog'));
dump(app(App\Support\MediaPresetRegistry::class)->get('blog.cover'));
"
```
Expected: `true`, `"Blog"`, `['width' => 1200, 'height' => 630, 'label' => 'Blog Kapak Görseli']`

- [ ] **Step 4: `config/permissions.php`'ye `module` kategorisi ve izinlerini ekle**

`'categories'` dizisine, `'menu' => 'Menüler',` satırının altına ekle:

```php
        'module' => 'Modül Yönetimi',
```

`'permissions'` dizisine (herhangi bir mantıklı yere, örn. `menu.*` izinlerinin yakınına) ekle:

```php
        ['name' => 'module.index', 'label' => 'Modül Yönetimi - Görüntüle', 'category' => 'module', 'guard_name' => 'web'],
        ['name' => 'module.update', 'label' => 'Modül Yönetimi - Güncelle', 'category' => 'module', 'guard_name' => 'web'],
```

Bu iki izin `'roles'` dizisindeki hiçbir desene eklenmez (`super-admin` dışında) — `setting.maintenance.update` gibi hassas izinler de otomatik atanmıyor, aynı kural.

- [ ] **Step 5: `config/activity-log.php`'ye `module` log adını ekle**

`'setting' => ['label' => 'Site Ayarları', 'icon' => 'settings'],` satırının altına:

```php
        'module' => ['label' => 'Modül Yönetimi', 'icon' => 'widgets'],
```

- [ ] **Step 6: Seeder'ı çalıştır, izinleri doğrula**

Run: `php artisan db:seed --class=RolePermissionSeeder`

Run: `php artisan tinker --execute="dump(\App\Models\Permission\Permission::where('name','like','module.%')->pluck('name'));"`
Expected: `['module.index', 'module.update']`

- [ ] **Step 7: `php -l`, commit**

```bash
php -l app/Support/ModuleRegistry.php
php -l app/Support/MediaPresetRegistry.php
php -l config/permissions.php
php -l config/activity-log.php
```

```bash
git add app/Support/ModuleRegistry.php app/Support/MediaPresetRegistry.php config/permissions.php config/activity-log.php
git commit -m "Add ModuleRegistry and MediaPresetRegistry support classes"
```

---

### Task 3: Route koruması — `EnsureModuleIsActive` middleware

**Files:**
- Create: `app/Http/Middleware/EnsureModuleIsActive.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/admin.php` (16 route grubunun açılış satırı)

**Interfaces:**
- Consumes: `App\Support\ModuleRegistry::isActive(string $key): bool` (Task 2).
- Produces: `module.active:<key>` middleware alias — başka task bunu tüketmiyor, bu görevin kendi doğrulaması route listesi üzerinden yapılır.

- [ ] **Step 1: `EnsureModuleIsActive` middleware'ini yaz**

```php
<?php

namespace App\Http\Middleware;

use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware'in desenini izler: pasif bir modülün route'una
 * doğrudan adres yazılırsa JSON isteğe 403 JSON, normal isteğe abort(403).
 */
class EnsureModuleIsActive
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    public function handle(Request $request, Closure $next, string $key): Response
    {
        if ($this->modules->isActive($key)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Bu modül şu an pasif.'], 403);
        }

        abort(403, 'Bu modül şu an pasif.');
    }
}
```

- [ ] **Step 2: `bootstrap/app.php`'ye middleware alias'ını ekle**

`use App\Http\Middleware\EnsureSiteIsLive;` satırının altına import ekle:

```php
use App\Http\Middleware\EnsureModuleIsActive;
```

`$middleware->alias([...])` dizisine ekle:

```php
            'module.active' => EnsureModuleIsActive::class,
```

(Sonuç: `'permission_middleware' => MiddlewarePermissionMiddleware::class,` satırının altına eklenmiş olur.)

- [ ] **Step 3: `routes/admin.php`'deki 16 route grubunun açılış satırını güncelle**

Her satırın başına `Route::middleware('module.active:<key>')->` eklenir, `prefix(...)` zinciri öncesine. Aşağıdaki 16 değişikliğin hepsi birebir bu kalıpta:

```php
// blog-category (key: blog)
Route::middleware('module.active:blog')->prefix('blog-category')->name('blog-category.')->controller(BlogCategoryController::class)->group(function () {

// blog (key: blog)
Route::middleware('module.active:blog')->prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {

// page (key: page)
Route::middleware('module.active:page')->prefix('page')->name('page.')->controller(PageController::class)->group(function () {

// announcement (key: announcement)
Route::middleware('module.active:announcement')->prefix('announcement')->name('announcement.')->controller(AnnouncementController::class)->group(function () {

// popup (key: popup)
Route::middleware('module.active:popup')->prefix('popup')->name('popup.')->controller(PopupController::class)->group(function () {

// subscriber (key: subscriber)
Route::middleware('module.active:subscriber')->prefix('subscriber')->name('subscriber.')->controller(SubscriberController::class)->group(function () {

// lead (key: lead)
Route::middleware('module.active:lead')->prefix('lead')->name('lead.')->controller(LeadController::class)->group(function () {

// service-region (key: service)
Route::middleware('module.active:service')->prefix('service-region')->name('service-region.')->controller(ServiceRegionController::class)->group(function () {

// service (key: service)
Route::middleware('module.active:service')->prefix('service')->name('service.')->controller(ServiceController::class)->group(function () {

// project-category (key: project)
Route::middleware('module.active:project')->prefix('project-category')->name('project-category.')->controller(ProjectCategoryController::class)->group(function () {

// project (key: project)
Route::middleware('module.active:project')->prefix('project')->name('project.')->controller(ProjectController::class)->group(function () {

// hero (key: hero)
Route::middleware('module.active:hero')->prefix('hero')->name('hero.')->controller(HeroController::class)->group(function () {

// testimonial (key: testimonial)
Route::middleware('module.active:testimonial')->prefix('testimonial')->name('testimonial.')->controller(TestimonialController::class)->group(function () {

// reference (key: reference)
Route::middleware('module.active:reference')->prefix('reference')->name('reference.')->controller(ReferenceController::class)->group(function () {

// faq (key: faq)
Route::middleware('module.active:faq')->prefix('faq')->name('faq.')->controller(FaqController::class)->group(function () {

// why-choose-us (key: why-choose-us)
Route::middleware('module.active:why-choose-us')->prefix('why-choose-us')->name('why-choose-us.')->controller(WhyChooseUsController::class)->group(function () {
```

- [ ] **Step 4: Route listesinde middleware'in uygulandığını doğrula**

Run: `php artisan route:list --name=admin.blog. -v | grep -i module`
Expected: `module.active:blog` middleware satırda görünür.

Run: `php artisan route:list --name=admin.service-region. -v | grep -i module`
Expected: `module.active:service`

- [ ] **Step 5: Middleware mantığını tinker'da fonksiyonel olarak doğrula**

Run:
```
php artisan tinker --execute="
\$m = new App\Http\Middleware\EnsureModuleIsActive(app(App\Support\ModuleRegistry::class));
\App\Models\Module\Module::where('key','blog')->update(['is_active' => false]);
app(App\Support\ModuleRegistry::class)->flush();
\$req = Illuminate\Http\Request::create('/admin/blog', 'GET');
\$req->headers->set('Accept', 'application/json');
\$res = \$m->handle(\$req, fn(\$r) => response('ok'), 'blog');
dump(\$res->getStatusCode(), \$res->getContent());
\App\Models\Module\Module::where('key','blog')->update(['is_active' => true]);
app(App\Support\ModuleRegistry::class)->flush();
"
```
Expected: `403` ve `{"success":false,"message":"Bu modül şu an pasif."}` — son satırda Blog tekrar aktif edilir (tinker oturumu dağılsa da DB'de kalıcı olmasın).

- [ ] **Step 6: `php -l`, commit**

```bash
php -l app/Http/Middleware/EnsureModuleIsActive.php
php -l bootstrap/app.php
php -l routes/admin.php
```

```bash
git add app/Http/Middleware/EnsureModuleIsActive.php bootstrap/app.php routes/admin.php
git commit -m "Block admin routes of inactive modules with module.active middleware"
```

---

### Task 4: Servis, Request, Controller, route — Modül Yönetimi ekranının backend'i

**Files:**
- Create: `app/Services/Module/ModuleService.php`
- Create: `app/Http/Requests/Admin/Module/ModuleUpdateRequest.php`
- Create: `app/Http/Controllers/Admin/Module/ModuleController.php`
- Modify: `routes/admin.php` (yeni `module` prefix grubu + controller import)
- Modify: `config/admin-menu.php` (sidebar girişi)

**Interfaces:**
- Consumes: `App\Support\ModuleRegistry` (Task 2), `App\Support\MediaPresetRegistry` (Task 2).
- Produces: `ModuleService::formData(): array` (`['modules' => Collection, 'generalPresets' => Collection]`) ve `ModuleService::update(array $data): void` (`$data = ['modules' => [...], 'presets' => [...]]`) — Task 5 (Blade) bu diziyi render eder; her `$modules` öğesi `['key','label','icon','description','name','is_active','presets' => [['key','field','width','height','label'], ...]]` şeklindedir (`field` = preset key'deki `.`'ın `__` ile değiştirilmiş hali, form alan adı için).

- [ ] **Step 1: `ModuleService`'i yaz**

```php
<?php

namespace App\Services\Module;

use App\Models\Media\MediaPreset;
use App\Models\Module\Module;
use App\Support\Activity;
use App\Support\MediaPresetRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    public function __construct(
        private readonly ModuleRegistry $modules,
        private readonly MediaPresetRegistry $presets,
    ) {}

    /** @return array{modules: Collection, generalPresets: Collection} */
    public function formData(): array
    {
        $moduleKeys = $this->modules->all()->keys();
        // İKİNCİ parametre (preserveKeys) ŞART: groupBy varsayılan olarak her
        // grubun içindeki anahtarları da yeniden indeksler (0,1,2...) — preset
        // anahtarı ('blog.cover') kaybolur. Bu projede aynı hata settings
        // sidebar'ında yaşandı, burada bilinçli olarak true geçiliyor.
        $presetsByModule = $this->presets->all()->groupBy(
            fn (array $preset, string $key) => $moduleKeys->contains(explode('.', $key)[0]) ? explode('.', $key)[0] : '_general',
            true
        );

        $modules = $this->modules->all()->map(function (array $module) use ($presetsByModule) {
            $module['presets'] = $this->presetRows($presetsByModule->get($module['key'], collect()));

            return $module;
        });

        $generalPresets = $this->presetRows($presetsByModule->get('_general', collect()));

        return ['modules' => $modules, 'generalPresets' => $generalPresets];
    }

    /** @param  array<string, mixed>  $data */
    public function update(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data['modules'] ?? [] as $key => $attributes) {
                Module::query()->where('key', $key)->update([
                    'name' => ($attributes['name'] ?? null) ?: null,
                    'is_active' => (bool) ($attributes['is_active'] ?? false),
                ]);
            }

            foreach ($data['presets'] ?? [] as $field => $attributes) {
                $key = str_replace('__', '.', $field);

                MediaPreset::query()->where('key', $key)->update([
                    'width' => (int) $attributes['width'],
                    'height' => (int) $attributes['height'],
                ]);
            }
        });

        $this->modules->flush();
        $this->presets->flush();

        Activity::record('module', 'bulk_update', 'Modül Yönetimi ayarları güncellendi.');
    }

    /** @return list<array{key: string, field: string, width: int, height: int, label: string}> */
    private function presetRows(Collection $presets): array
    {
        return $presets
            ->map(fn (array $preset, string $key) => [
                'key' => $key,
                'field' => str_replace('.', '__', $key),
                'width' => $preset['width'],
                'height' => $preset['height'],
                'label' => $preset['label'],
            ])
            ->values()
            ->all();
    }
}
```

- [ ] **Step 2: `ModuleUpdateRequest`'i yaz**

```php
<?php

namespace App\Http\Requests\Admin\Module;

use Illuminate\Foundation\Http\FormRequest;

class ModuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('module.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'modules' => ['required', 'array'],
            'modules.*.name' => ['nullable', 'string', 'max:255'],
            'modules.*.is_active' => ['required', 'boolean'],

            'presets' => ['nullable', 'array'],
            'presets.*.width' => ['required', 'integer', 'min:16', 'max:4000'],
            'presets.*.height' => ['required', 'integer', 'min:16', 'max:4000'],
        ];
    }
}
```

- [ ] **Step 3: `ModuleController`'ı yaz**

```php
<?php

namespace App\Http\Controllers\Admin\Module;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Module\ModuleUpdateRequest;
use App\Services\Module\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ModuleController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ModuleService $service) {}

    public function index(): View
    {
        return view('admin.pages.module.index', $this->service->formData());
    }

    public function update(ModuleUpdateRequest $request): JsonResponse
    {
        $this->service->update($request->validated());

        return $this->success('Modül ayarları güncellendi.');
    }
}
```

- [ ] **Step 4: `routes/admin.php`'ye import ve route grubunu ekle**

`use App\Http\Controllers\Admin\Menu\MenuController;` satırının altına import:

```php
use App\Http\Controllers\Admin\Module\ModuleController;
```

`Route::prefix('setting')->name('setting.')->...->group(function () {...});` bloğunun hemen üstüne yeni grup:

```php
    Route::prefix('module')->name('module.')->controller(ModuleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });

```

- [ ] **Step 5: `config/admin-menu.php`'ye sidebar girişini ekle**

`'Genel'` grubunun `'items'` dizisinin başına (veya "Site Ayarları" girişinin yakınına):

```php
            [
                'title' => 'Modül Yönetimi',
                'icon' => 'widgets',
                'route' => 'admin.module.index',
                'active' => 'admin.module.*',
                'permission' => 'module.index',
            ],
```

- [ ] **Step 6: Route listesini ve yetkiyi doğrula**

Run: `php artisan route:list --name=admin.module`
Expected: `GET admin/module ... admin.module.index`, `PUT admin/module ... admin.module.update`

Run: `php artisan tinker --execute="dump(app(App\Services\Module\ModuleService::class)->formData()['modules']->count());"`
Expected: `13`

- [ ] **Step 7: `php -l`, commit**

```bash
php -l app/Services/Module/ModuleService.php
php -l app/Http/Requests/Admin/Module/ModuleUpdateRequest.php
php -l app/Http/Controllers/Admin/Module/ModuleController.php
php -l routes/admin.php
php -l config/admin-menu.php
```

```bash
git add app/Services/Module/ModuleService.php app/Http/Requests/Admin/Module/ModuleUpdateRequest.php \
        app/Http/Controllers/Admin/Module/ModuleController.php routes/admin.php config/admin-menu.php
git commit -m "Add Module Management controller, service and route"
```

---

### Task 5: Görünüm ve sayfa JS — Modül Yönetimi ekranı

**Files:**
- Create: `resources/views/admin/pages/module/index.blade.php`
- Create: `public/admin/assets/js/pages/module/form.js`

**Interfaces:**
- Consumes: `$modules` (Collection, her öğe Task 4'teki `formData()` şekli), `$generalPresets` (list) — controller'dan view'e geçer.

- [ ] **Step 1: `index.blade.php`'yi yaz**

```blade
@extends('admin.layout.app')
@section('admin.title', 'Modül Yönetimi')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Modül Yönetimi</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Modül Yönetimi
            </li>
        </ol>
    </div>

    <form id="module-form" action="{{ route('admin.module.update') }}">
        @foreach ($modules as $module)
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title flex items-center gap-[10px]">
                        <i class="material-symbols-outlined !text-[22px] text-primary-500">{{ $module['icon'] }}</i>
                        <div>
                            <h5 class="!mb-0">{{ $module['label'] }}</h5>
                            <p class="text-gray-500 dark:text-gray-400 text-xs !mb-0">{{ $module['description'] }}</p>
                        </div>
                    </div>
                    <x-admin::form.switch :name="'modules.'.$module['key'].'.is_active'" label="Aktif"
                        :checked="$module['is_active']" wrapper="" bare="false" />
                </div>
                <div class="trezo-card-content">
                    <x-admin::form.input :name="'modules.'.$module['key'].'.name'" label="Görünen Ad (opsiyonel)"
                        :value="$module['name']" :placeholder="$module['label']" />

                    @foreach ($module['presets'] as $preset)
                        <div class="grid grid-cols-2 gap-[15px]">
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.width'"
                                :label="$preset['label'].' — Genişlik (px)'" :value="$preset['width']" />
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.height'"
                                :label="$preset['label'].' — Yükseklik (px)'" :value="$preset['height']" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($generalPresets !== [])
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Genel Boyutlar</h5>
                        <p class="text-gray-500 dark:text-gray-400 text-xs !mb-0">Belirli bir modüle bağlı olmayan görsel boyutları.</p>
                    </div>
                </div>
                <div class="trezo-card-content">
                    @foreach ($generalPresets as $preset)
                        <div class="grid grid-cols-2 gap-[15px]">
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.width'"
                                :label="$preset['label'].' — Genişlik (px)'" :value="$preset['width']" />
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.height'"
                                :label="$preset['label'].' — Yükseklik (px)'" :value="$preset['height']" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-end">
            <button type="submit"
                class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                Kaydet
            </button>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/module/form.js') }}"></script>
@endpush
```

- [ ] **Step 2: `public/admin/assets/js/pages/module/form.js`'i yaz — `setting/form.js` ile aynı desen**

```js
/**
 * Modül Yönetimi — tek form, tüm modüller ve preset'ler bir istekte kaydedilir.
 */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('module-form');

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message } = await http.put(form.action, new FormData(form));
        toast.success(message);
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(form, error.errors);
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        }
    } finally {
        setLoading(button, false);
    }
});
```

- [ ] **Step 3: Çakışmayan checkbox ismi kontrolü — `is_active` switch'inin gizli input'u**

`x-admin::form.switch` bileşeni her checkbox için bir gizli `<input type="hidden">` basar (kapalıyken `0` gitmesi için) — bu yüzden `ModuleUpdateRequest`'teki `'modules.*.is_active' => ['required', 'boolean']` kuralı her koşulda bir değer bulur, ekstra kontrol gerekmez. Bu adımda sadece component kaynağını tekrar oku ve bunu doğrula:

Run: `grep -n "input type=.hidden." resources/views/admin/components/form/switch.blade.php`
Expected: `<input type="hidden" name="{{ $field }}" value="0">` satırı görünür.

- [ ] **Step 4: `php -l` ve Pint, commit**

```bash
php -l app/Http/Controllers/Admin/Module/ModuleController.php
vendor/bin/pint app/Services/Module app/Http/Requests/Admin/Module app/Http/Controllers/Admin/Module
```

```bash
git add resources/views/admin/pages/module/index.blade.php public/admin/assets/js/pages/module/form.js
git commit -m "Add Module Management screen (view + JS)"
```

---

### Task 6: Sidebar, Dashboard ve global arama filtrelemesi

**Files:**
- Modify: `app/Services/Admin/MenuService.php`
- Modify: `config/admin-menu.php` (13 mevcut öğeye `'module'` anahtarı)
- Modify: `app/Services/Dashboard/DashboardService.php`
- Modify: `app/Services/Search/GlobalSearchService.php`

**Interfaces:**
- Consumes: `App\Support\ModuleRegistry::isActive()` / `::label()` (Task 2).

- [ ] **Step 1: `config/admin-menu.php`'deki 13 öğeye `'module'` anahtarı ekle**

Aşağıdaki öğelerin her birine, `'permission' => '...'` satırının yanına (children'ı olanlarda üst öğenin kendisine, `'children'` anahtarından ÖNCE) bir `'module' => '<key>'` satırı eklenir:

```php
// Sayfalar
'module' => 'page',

// Blog (üst öğe — children'dan önce)
'module' => 'blog',

// Hizmetler (üst öğe)
'module' => 'service',

// Neler Yaptık (üst öğe)
'module' => 'project',

// Müşteri Yorumları
'module' => 'testimonial',

// Referanslar
'module' => 'reference',

// Sıkça Sorulan Sorular
'module' => 'faq',

// Neden Biz
'module' => 'why-choose-us',

// Tanıtım Alanı — NOT: bu menü öğesi şu an config/admin-menu.php'de YOK
// (hero CRUD route'u var ama sidebar'da girişi yok); bu adımda eklenmez,
// sadece var olan 12 öğeye modül anahtarı eklenir. Hero'nun pasife alınması
// yalnızca route middleware (Task 3) ile çalışır, sidebar'da gösterilecek
// bir öğe olmadığı için sidebar filtresi devre dışı kalır — bu kabul
// edilebilir bir tutarsızlık değildir, çünkü zaten gösterilen bir şey yok.

// Duyuru Şeridi
'module' => 'announcement',

// Açılır Pencereler
'module' => 'popup',

// Bülten Aboneleri
'module' => 'subscriber',

// Gelen Talepler
'module' => 'lead',
```

- [ ] **Step 2: `MenuService::filter()`'a modül kontrolü ve başlık override'ı ekle**

`use Illuminate\Support\Facades\Auth;` satırının altına import ekle:

```php
use App\Support\ModuleRegistry;
```

`private function filter(array $items): array` metodunun tamamını şununla değiştir:

```php
    private function filter(array $items): array
    {
        $allowed = [];
        $modules = app(ModuleRegistry::class);

        foreach ($items as $item) {
            if (isset($item['permission']) && ! Auth::user()?->can($item['permission'])) {
                continue;
            }

            if (isset($item['module']) && ! $modules->isActive($item['module'])) {
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = $this->filter($item['children']);

                if ($item['children'] === []) {
                    continue;
                }
            }

            if (isset($item['module'])) {
                $item['title'] = $modules->label($item['module']);
            }

            if (isset($item['badge'])) {
                $item['badge'] = $this->badge($item['badge']);
            }

            $allowed[] = $item;
        }

        return $allowed;
    }
```

- [ ] **Step 3: `DashboardService::content()`'i modül bazlı filtrele**

Constructor'a `ModuleRegistry` ekle:

```php
    public function __construct(
        private readonly HealthService $health,
        private readonly SeoHealthService $seo,
        private readonly \App\Support\ModuleRegistry $modules,
    ) {}
```

`content()` metodunu şununla değiştir:

```php
    public function content(): array
    {
        return collect([
            $this->contentRow('Sayfalar', 'description', Page::class, 'page.index', route('admin.page.index'), 'status', Page::STATUS_PUBLISHED, 'page'),
            $this->contentRow('Blog Yazıları', 'article', Blog::class, 'blog.index', route('admin.blog.index'), 'status', Blog::STATUS_PUBLISHED, 'blog'),
            $this->contentRow('Hizmetler', 'design_services', Service::class, 'service.index', route('admin.service.index'), 'status', Service::STATUS_PUBLISHED, 'service'),
            $this->contentRow('Neler Yaptık', 'workspaces', Project::class, 'project.index', route('admin.project.index'), 'status', Project::STATUS_PUBLISHED, 'project'),
            $this->contentRow('Müşteri Yorumları', 'reviews', Testimonial::class, 'testimonial.index', route('admin.testimonial.index'), 'is_active', true, 'testimonial'),
            $this->contentRow('Sıkça Sorulan Sorular', 'quiz', Faq::class, 'faq.index', route('admin.faq.index'), 'is_active', true, 'faq'),
        ])
            ->filter(fn (array $row) => $this->modules->isActive($row['module']))
            ->values()
            ->all();
    }
```

`contentRow()` metodunu şununla değiştir (yeni `string $module` parametresi ve dönen diziye `'module'` anahtarı):

```php
    private function contentRow(
        string $label,
        string $icon,
        string $model,
        string $permission,
        string $route,
        string $column,
        mixed $liveValue,
        string $module,
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'total' => $model::count(),
            'live' => $model::where($column, $liveValue)->count(),
            'permission' => $permission,
            'route' => $route,
            'module' => $module,
        ];
    }
```

- [ ] **Step 4: `GlobalSearchService::allows()` çağrısının yanına modül kontrolü ekle**

Constructor ekle (şu an hiç constructor yok, class'a ekle):

```php
class GlobalSearchService
{
    public function __construct(private readonly \App\Support\ModuleRegistry $modules) {}

    // ... mevcut metotlar
```

`search()` metodundaki kaynak döngüsünü güncelle:

```php
        foreach (config('global-search.sources', []) as $key => $source) {
            if (! $this->allows($user, $source['permission'] ?? null)) {
                continue;
            }

            if (! $this->modules->isActive($key)) {
                continue;
            }

            $items = $this->query($source, $term);
```

- [ ] **Step 5: Sidebar, Dashboard, arama filtrelerini tinker ile doğrula**

Run:
```
php artisan tinker --execute="
\App\Models\Module\Module::where('key','faq')->update(['is_active' => false, 'name' => null]);
app(App\Support\ModuleRegistry::class)->flush();
dump(app(App\Services\Admin\MenuService::class)->build());
"
```
Expected: Çıktıda "Sıkça Sorulan Sorular" öğesi sidebar dizisinde **yer almaz**.

Run:
```
php artisan tinker --execute="
dump(app(App\Services\Dashboard\DashboardService::class)->content());
"
```
Expected: Dönen dizide `label` değeri "Sıkça Sorulan Sorular" olan satır **yoktur** (5 satır kalır).

Run:
```
php artisan tinker --execute="
\$user = \App\Models\User::first();
dump(app(App\Services\Search\GlobalSearchService::class)->search('soru', \$user));
\App\Models\Module\Module::where('key','faq')->update(['is_active' => true]);
app(App\Support\ModuleRegistry::class)->flush();
"
```
Expected: `groups` içinde `key === 'faq'` olan bir grup **yoktur**; son satırda FAQ tekrar aktif edilir.

- [ ] **Step 6: `php -l`, commit**

```bash
php -l app/Services/Admin/MenuService.php
php -l config/admin-menu.php
php -l app/Services/Dashboard/DashboardService.php
php -l app/Services/Search/GlobalSearchService.php
```

```bash
git add app/Services/Admin/MenuService.php config/admin-menu.php \
        app/Services/Dashboard/DashboardService.php app/Services/Search/GlobalSearchService.php
git commit -m "Filter sidebar, dashboard and global search by module active state"
```

---

### Task 7: Medya preset okuma noktalarını registry'ye yönlendirme

**Files:**
- Modify: `app/Services/Media/MediaService.php:258`
- Modify: `resources/views/admin/components/form/image.blade.php:21`

**Interfaces:**
- Consumes: `App\Support\MediaPresetRegistry::get(string $key): ?array` (Task 2).

- [ ] **Step 1: `MediaService::crop()`'taki preset okumasını değiştir**

Eski:

```php
        // Dizi erişimi bilinçli: preset anahtarları nokta içerir ('blog.cover'),
        // config() bunu iç içe dizi sanıp bulamaz.
        $size = $preset ? (config('media.presets', [])[$preset] ?? null) : null;
```

Yeni:

```php
        $size = $preset ? app(\App\Support\MediaPresetRegistry::class)->get($preset) : null;
```

- [ ] **Step 2: `image.blade.php`'teki preset okumasını değiştir**

Eski:

```php
    // Preset anahtarları nokta içerir ('blog.cover'), config() nokta notasyonunu
    // iç içe dizi sanacağı için doğrudan dizi erişimi kullanılıyor.
    $size = $preset ? (config('media.presets', [])[$preset] ?? null) : null;
```

Yeni:

```php
    $size = $preset ? app(\App\Support\MediaPresetRegistry::class)->get($preset) : null;
```

- [ ] **Step 3: Preset boyutu değiştirince kırpma modalının yeni orana kilitlendiğini doğrula**

Run:
```
php artisan tinker --execute="
\App\Models\Media\MediaPreset::where('key','blog.cover')->update(['width' => 1300, 'height' => 700]);
app(App\Support\MediaPresetRegistry::class)->flush();
dump(app(App\Support\MediaPresetRegistry::class)->get('blog.cover'));
\App\Models\Media\MediaPreset::where('key','blog.cover')->update(['width' => 1200, 'height' => 630]);
app(App\Support\MediaPresetRegistry::class)->flush();
"
```
Expected: İlk dump `['width' => 1300, 'height' => 700, 'label' => 'Blog Kapak Görseli']`, son satırda orijinal boyuta geri dönülür.

Run: `curl -s http://webtasarim.test/admin/blog/form | grep -o 'data-preset-width="[0-9]*" data-preset-height="[0-9]*"' | head -1`
(Giriş yapılmamışsa 302 login'e döner — bu adımın asıl doğrulaması Task 8'deki oturumlu curl ile yapılacak; burada sadece registry'nin doğru değeri verdiği zaten Tinker ile kanıtlandı.)

- [ ] **Step 4: `php -l`, commit**

```bash
php -l app/Services/Media/MediaService.php
```

```bash
git add app/Services/Media/MediaService.php resources/views/admin/components/form/image.blade.php
git commit -m "Read media crop presets from MediaPresetRegistry instead of config"
```

---

### Task 8: Uçtan uca doğrulama

**Files:** Yok (sadece doğrulama — değişiklik yapılmaz).

**Interfaces:** Consumes: Task 1–7'nin tüm uçları.

- [ ] **Step 1: Oturum açan bir curl cookie jar kur**

```bash
cd /private/tmp/claude-501/-Users-umayhome-Herd-webtasarim/*/scratchpad 2>/dev/null || cd /tmp
COOKIES=module-test-cookies.txt
rm -f "$COOKIES"
TOKEN=$(curl -s -c "$COOKIES" http://webtasarim.test/admin/login | grep -o 'name="_token" value="[^"]*"' | sed 's/.*value="//;s/"//')
curl -s -b "$COOKIES" -c "$COOKIES" -X POST http://webtasarim.test/admin/login \
    -d "_token=$TOKEN" -d "email=admin@webtasarim.test" -d "password=password" -o /dev/null -w "%{http_code}\n"
```
Expected: `302` (başarılı girişte dashboard'a yönlendirme).

- [ ] **Step 2: Modül Yönetimi ekranının 200 döndüğünü ve 13 kartı bastığını doğrula**

```bash
curl -s -b "$COOKIES" http://webtasarim.test/admin/module -o module-page.html -w "%{http_code}\n"
grep -c "modules\[" module-page.html
```
Expected: `200`, ardından `modules[` geçen satır sayısı (her modül için `name`/`is_active` olmak üzere en az 26).

- [ ] **Step 3: Bir modülü ekran üzerinden pasife al, sidebar'dan kaybolduğunu doğrula**

```bash
FAQ_TOKEN=$(grep -o 'name="csrf-token" content="[^"]*"' module-page.html | sed 's/.*content="//;s/"//')
curl -s -b "$COOKIES" -X PUT http://webtasarim.test/admin/module \
    -H "X-CSRF-TOKEN: $FAQ_TOKEN" -H "Accept: application/json" \
    --data-urlencode "modules[page][is_active]=1" \
    --data-urlencode "modules[blog][is_active]=1" \
    --data-urlencode "modules[service][is_active]=1" \
    --data-urlencode "modules[project][is_active]=1" \
    --data-urlencode "modules[testimonial][is_active]=1" \
    --data-urlencode "modules[reference][is_active]=1" \
    --data-urlencode "modules[faq][is_active]=0" \
    --data-urlencode "modules[why-choose-us][is_active]=1" \
    --data-urlencode "modules[hero][is_active]=1" \
    --data-urlencode "modules[announcement][is_active]=1" \
    --data-urlencode "modules[popup][is_active]=1" \
    --data-urlencode "modules[subscriber][is_active]=1" \
    --data-urlencode "modules[lead][is_active]=1"
```
Expected: `{"success":true,"message":"Modül ayarları güncellendi.","data":null}`

```bash
curl -s -b "$COOKIES" http://webtasarim.test/admin/dashboard | grep -c "Sıkça Sorulan Sorular"
curl -s -b "$COOKIES" -o /dev/null -w "%{http_code}\n" http://webtasarim.test/admin/faq
```
Expected: İlk komut `0` (sidebar'da FAQ artık yok), ikinci komut `403`.

- [ ] **Step 4: Modülü tekrar aktif et, her şeyin eski haline döndüğünü doğrula**

```bash
curl -s -b "$COOKIES" -X PUT http://webtasarim.test/admin/module \
    -H "X-CSRF-TOKEN: $FAQ_TOKEN" -H "Accept: application/json" \
    --data-urlencode "modules[page][is_active]=1" \
    --data-urlencode "modules[blog][is_active]=1" \
    --data-urlencode "modules[service][is_active]=1" \
    --data-urlencode "modules[project][is_active]=1" \
    --data-urlencode "modules[testimonial][is_active]=1" \
    --data-urlencode "modules[reference][is_active]=1" \
    --data-urlencode "modules[faq][is_active]=1" \
    --data-urlencode "modules[why-choose-us][is_active]=1" \
    --data-urlencode "modules[hero][is_active]=1" \
    --data-urlencode "modules[announcement][is_active]=1" \
    --data-urlencode "modules[popup][is_active]=1" \
    --data-urlencode "modules[subscriber][is_active]=1" \
    --data-urlencode "modules[lead][is_active]=1"

curl -s -b "$COOKIES" -o /dev/null -w "%{http_code}\n" http://webtasarim.test/admin/faq
```
Expected: İkinci komut `200`.

- [ ] **Step 5: Regresyon — diğer ekranların hâlâ çalıştığını doğrula**

```bash
for path in dashboard blog page service project testimonial reference faq why-choose-us hero announcement popup subscriber lead setting module; do
    code=$(curl -s -b "$COOKIES" -o /dev/null -w "%{http_code}" "http://webtasarim.test/admin/$path")
    echo "$path -> $code"
done
```
Expected: Hepsi `200`.

- [ ] **Step 6: Temizlik**

```bash
rm -f "$COOKIES" module-page.html
```

- [ ] **Step 7: Kullanıcıya tarayıcıda görsel kontrol için not düş**

Bu adımda otomatik bir komut yok — curl ile akışın çalıştığı kanıtlandı, ama anahtar/değer formlarının görsel hizası, switch animasyonu ve dark mode yalnızca tarayıcıda doğrulanabilir. Uygulama bittiğinde kullanıcıya `http://webtasarim.test/admin/module` adresini tarayıcıda açıp göz atmasını öner.
