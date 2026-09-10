<?php

use App\Http\Controllers\Admin\ActivityLog\ActivityLogController;
use App\Http\Controllers\Admin\Ai\AiGenerationController;
use App\Http\Controllers\Admin\AiPrompt\AiPromptController;
use App\Http\Controllers\Admin\AiProvider\AiProviderController;
use App\Http\Controllers\Admin\Analytics\AnalyticsController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Http\Controllers\Admin\BlogCategory\BlogCategoryController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Faq\FaqController;
use App\Http\Controllers\Admin\Hero\HeroController;
use App\Http\Controllers\Admin\Integration\IntegrationController;
use App\Http\Controllers\Admin\Media\MediaController;
use App\Http\Controllers\Admin\Media\MediaFolderController;
use App\Http\Controllers\Admin\Menu\MenuController;
use App\Http\Controllers\Admin\Page\PageController;
use App\Http\Controllers\Admin\Redirect\RedirectController;
use App\Http\Controllers\Admin\Reference\ReferenceController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\Schema\SchemaController;
use App\Http\Controllers\Admin\Service\ServiceController;
use App\Http\Controllers\Admin\ServiceRegion\ServiceRegionController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\SocialLink\SocialLinkController;
use App\Http\Controllers\Admin\Tag\TagController;
use App\Http\Controllers\Admin\Testimonial\TestimonialController;
use App\Http\Controllers\Admin\WhyChooseUs\WhyChooseUsController;
use Illuminate\Support\Facades\Route;

/*
| Bu dosya bootstrap/app.php içinde "admin" prefix ve "admin." isim öneki ile
| yüklenir. Buradaki route'lar tam adıyla admin.<isim> olur.
|
| Yetki: PermissionMiddleware, route adından `admin.` önekini siler
| (admin.blog.store → blog.store) ve config/permissions.php'deki ada bakar.
| Route üzerine permission: yazılmaz.
| Yetki istemeyen uçlar withoutMiddleware('permission_middleware') alır.
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'permission_middleware'])->group(function () {
    Route::withoutMiddleware('permission_middleware')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('tags/search', [TagController::class, 'search'])->name('tags.search');
    });

    Route::prefix('setting')->name('setting.')->controller(SettingController::class)->group(function () {
        Route::get('/', 'edit')->name('index')->defaults('group', 'company');
        Route::put('company', 'updateCompany')->name('company.update');
        Route::put('seo', 'updateSeo')->name('seo.update');
        Route::put('schema', 'updateSchema')->name('schema.update');
        Route::post('analytics', 'updateAnalytics')->name('analytics.update');
        Route::put('mail', 'updateMail')->name('mail.update');
        Route::post('mail/test', 'testMail')->name('mail.test');
        Route::put('tracking', 'updateTracking')->name('tracking.update');
        Route::put('contents', 'updateContents')->name('contents.update');
        Route::put('contact', 'updateContact')->name('contact.update');
        Route::put('cookie', 'updateCookie')->name('cookie.update');
        Route::put('maintenance', 'updateMaintenance')->name('maintenance.update');
        Route::get('{group}', 'edit')->name('edit')
            ->whereIn('group', array_keys(config('settings.groups', [])));
    });

    Route::prefix('integration')->name('integration.')->controller(IntegrationController::class)->group(function () {
        $keys = array_keys(config('integrations', []));

        Route::get('{key}/form', 'form')->name('form')->whereIn('key', $keys);
        Route::put('{key}/toggle', 'toggle')->name('toggle')->whereIn('key', $keys);
        Route::put('{key}', 'update')->name('update')->whereIn('key', $keys);
    });

    Route::prefix('social-link')->name('social-link.')->controller(SocialLinkController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('form/{social_link?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{social_link}', 'update')->name('update');
        Route::delete('{social_link}', 'destroy')->name('destroy');
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::controller(MediaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('datatable', 'datatable')->name('datatable');
            Route::get('stats', 'stats')->name('stats');
            Route::get('picker', 'picker')->name('picker');
            Route::post('upload', 'upload')->name('upload');
            Route::post('bulk-move', 'bulkMove')->name('bulk-move');
            Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::get('{media}/form', 'form')->name('form');
            Route::put('{media}', 'update')->name('update');
            Route::post('{media}/recrop', 'recrop')->name('recrop');
            Route::delete('{media}', 'destroy')->name('destroy');
        });

        Route::controller(MediaFolderController::class)->prefix('folders')->name('folders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tree', 'tree')->name('tree');
            Route::post('/', 'store')->name('store');
            Route::put('{folder}', 'update')->name('update');
            Route::delete('{folder}', 'destroy')->name('destroy');
        });
    });

    Route::prefix('ai-provider')->name('ai-provider.')->controller(AiProviderController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{provider?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        Route::put('{provider}', 'update')->name('update');
        Route::post('{provider}/test', 'test')->name('test');
        Route::delete('{provider}', 'destroy')->name('destroy');
    });

    Route::prefix('ai-prompt')->name('ai-prompt.')->controller(AiPromptController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{prompt?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        Route::put('{prompt}', 'update')->name('update');
        Route::delete('{prompt}', 'destroy')->name('destroy');
    });

    // İçerik üretimi: modül formlarından çağrılır, kuyruğa atar ve durum döner.
    Route::prefix('ai')->name('ai.')->controller(AiGenerationController::class)->group(function () {
        Route::get('generate/{key}/form', 'form')->name('generate.form');
        Route::post('generate', 'store')->name('generate.store');
        Route::get('generate/{generation}', 'show')->name('generate.show');
    });

    Route::prefix('blog-category')->name('blog-category.')->controller(BlogCategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{category?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti, aşağıdaki {category} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kategori kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{category}', 'update')->name('update');
        Route::delete('{category}', 'destroy')->name('destroy');
    });

    Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{blog}/edit', 'edit')->name('edit');
        Route::put('{blog}', 'update')->name('update');
        Route::delete('{blog}', 'destroy')->name('destroy');
    });

    // Sayfa yöneticisi. Ön yüz adresi bir kolonda (`path`) tutulduğu için
    // burada hiyerarşiye özel bir uç yok; ağaç liste ekranında `path`
    // sıralamasından çıkar.
    Route::prefix('page')->name('page.')->controller(PageController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti {page} joker'ından ÖNCE tanımlanmalı —
        // aksi halde 'reorder' bir sayfa kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::get('{page}/edit', 'edit')->name('edit');
        Route::put('{page}', 'update')->name('update');
        Route::delete('{page}', 'destroy')->name('destroy');
    });

    // Menü yöneticisi. Konumlar (header, footer sütunları) sabittir; öğe
    // tekil route'ları menü grubunun DIŞINDA — {menu} joker'ıyla çakışmasın.
    Route::prefix('menu')->name('menu.')->controller(MenuController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{menu}', 'edit')->name('edit');
        Route::get('{menu}/tree', 'tree')->name('tree');
        Route::put('{menu}', 'updateMenu')->name('update');
        Route::put('{menu}/tree', 'saveTree')->name('save-tree');
        Route::post('{menu}/items', 'storeItem')->name('items.store');
    });

    Route::prefix('menu-item')->name('menu-item.')->controller(MenuController::class)->group(function () {
        Route::put('{menuItem}', 'updateItem')->name('update');
        Route::delete('{menuItem}', 'destroyItem')->name('destroy');
    });

    // Yönlendirme yöneticisi + 404 kayıtları. Sabit segmentler {redirect}
    // joker'ından ÖNCE tanımlanmalı.
    Route::prefix('redirect')->name('redirect.')->controller(RedirectController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('stats', 'stats')->name('stats');
        Route::get('analyze', 'analyze')->name('analyze');
        Route::get('export', 'export')->name('export');
        Route::post('import', 'import')->name('import');
        Route::get('form/{redirect?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        Route::put('{redirect}/toggle', 'toggle')->name('toggle');
        Route::put('{redirect}', 'update')->name('update');
        Route::delete('{redirect}', 'destroy')->name('destroy');
    });

    Route::prefix('not-found')->name('not-found.')->controller(RedirectController::class)->group(function () {
        Route::get('datatable', 'notFoundDatatable')->name('datatable');
        Route::delete('{notFoundLog}', 'destroyNotFound')->name('destroy');
    });

    // Schema.org doğrulama ekranı — üretilen JSON-LD'yi gösterir ve denetler.
    // Ayarlar "Schema.org" sekmesinde (setting.schema.update).
    Route::prefix('schema')->name('schema.')->controller(SchemaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('preview', 'preview')->name('preview');
    });

    // GA4 panel özeti. Kimlik bilgisi Ayarlar "Analitik" sekmesinde
    // (setting.analytics.update); veriler burada AJAX ile çekilir.
    Route::prefix('analytics')->name('analytics.')->controller(AnalyticsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('data', 'data')->name('data');
        Route::get('realtime', 'realtime')->name('realtime');
        Route::post('test', 'test')->name('test');
    });

    // Bölge ağacı: liste kırılımlı çalışır, datatable parent_id filtresiyle
    // yalnızca o seviyeyi döndürür.
    Route::prefix('service-region')->name('service-region.')->controller(ServiceRegionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        // Kırılım başlığı için kökten seçili bölgeye kadarki zincir.
        Route::get('breadcrumb/{region}', 'breadcrumb')->name('breadcrumb');
        Route::get('form/{region?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti, aşağıdaki {region} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir bölge kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{region}', 'update')->name('update');
        Route::delete('{region}', 'destroy')->name('destroy');
    });

    Route::prefix('service')->name('service.')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti {service} joker'ından ÖNCE tanımlanmalı.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::get('{service}/edit', 'edit')->name('edit');
        Route::put('{service}', 'update')->name('update');
        Route::delete('{service}', 'destroy')->name('destroy');
    });

    // Tekil kayıt modülü: liste, ekleme ve silme yok — tek form.
    Route::prefix('hero')->name('hero.')->controller(HeroController::class)->group(function () {
        Route::get('/', 'edit')->name('index');
        Route::put('/', 'update')->name('update');
    });

    Route::prefix('testimonial')->name('testimonial.')->controller(TestimonialController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{testimonial?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti, aşağıdaki {testimonial} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{testimonial}', 'update')->name('update');
        Route::delete('{testimonial}', 'destroy')->name('destroy');
    });

    Route::prefix('reference')->name('reference.')->controller(ReferenceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{reference?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti, aşağıdaki {reference} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{reference}', 'update')->name('update');
        Route::delete('{reference}', 'destroy')->name('destroy');
    });

    /*
    | Denetim kayıtları salt okunurdur: yalnızca listeleme ve detay uçları
    | vardır. Log oluşturma/düzenleme/silme HTTP üzerinden yapılamaz;
    | temizlik yalnızca `php artisan activity-log:prune` ile yapılır.
    */
    Route::prefix('activity-log')->name('activity-log.')->controller(ActivityLogController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:activity-log.index');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:activity-log.index');
        Route::get('{activityLog}', 'show')->name('show')->middleware('permission:activity-log.index');
    });

    Route::prefix('faq')->name('faq.')->controller(FaqController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{faq?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' sabit segmenti, aşağıdaki {faq} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('{faq}', 'update')->name('update');
        Route::delete('{faq}', 'destroy')->name('destroy');
    });

    Route::prefix('role')->name('role.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{role}/edit', 'edit')->name('edit');
        Route::put('{role}', 'update')->name('update');
        Route::delete('{role}', 'destroy')->name('destroy');
    });

    Route::prefix('why-choose-us')->name('why-choose-us.')->controller(WhyChooseUsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('datatable', 'datatable')->name('datatable');
        Route::get('form/{why_choose_us?}', 'form')->name('form');
        Route::post('/', 'store')->name('store');
        // 'reorder' ve 'heading' sabit segmentleri, aşağıdaki {why_choose_us}
        // joker'ından ÖNCE tanımlanmalı — aksi halde bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder');
        Route::put('heading', 'updateHeading')->name('heading');
        Route::put('{why_choose_us}', 'update')->name('update');
        Route::delete('{why_choose_us}', 'destroy')->name('destroy');
    });
});
