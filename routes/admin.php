<?php

use App\Http\Controllers\Admin\Ai\AiGenerationController;
use App\Http\Controllers\Admin\AiPrompt\AiPromptController;
use App\Http\Controllers\Admin\AiProvider\AiProviderController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Http\Controllers\Admin\BlogCategory\BlogCategoryController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Faq\FaqController;
use App\Http\Controllers\Admin\Hero\HeroController;
use App\Http\Controllers\Admin\Integration\IntegrationController;
use App\Http\Controllers\Admin\Media\MediaController;
use App\Http\Controllers\Admin\Media\MediaFolderController;
use App\Http\Controllers\Admin\Reference\ReferenceController;
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
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('setting')->name('setting.')->controller(SettingController::class)->group(function () {
        Route::get('/', 'edit')->name('index')->defaults('group', 'company')->middleware('permission:setting.view');
        Route::put('company', 'updateCompany')->name('company.update');
        Route::put('seo', 'updateSeo')->name('seo.update');
        Route::put('mail', 'updateMail')->name('mail.update');
        Route::post('mail/test', 'testMail')->name('mail.test');
        Route::put('tracking', 'updateTracking')->name('tracking.update');
        Route::put('contents', 'updateContents')->name('contents.update');
        Route::put('contact', 'updateContact')->name('contact.update');
        Route::put('cookie', 'updateCookie')->name('cookie.update');
        Route::put('maintenance', 'updateMaintenance')->name('maintenance.update');
        Route::get('{group}', 'edit')->name('edit')
            ->whereIn('group', array_keys(config('settings.groups', [])))
            ->middleware('permission:setting.view');
    });

    Route::prefix('integration')->name('integration.')->controller(IntegrationController::class)->group(function () {
        $keys = array_keys(config('integrations', []));

        Route::get('{key}/form', 'form')->name('form')->whereIn('key', $keys)->middleware('permission:setting.view');
        Route::put('{key}/toggle', 'toggle')->name('toggle')->whereIn('key', $keys);
        Route::put('{key}', 'update')->name('update')->whereIn('key', $keys);
    });

    Route::prefix('social-link')->name('social-link.')->controller(SocialLinkController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:setting.view');
        Route::get('form/{social_link?}', 'form')->name('form')->middleware('permission:setting.view');
        Route::post('/', 'store')->name('store');
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:setting.update');
        Route::put('{social_link}', 'update')->name('update');
        Route::delete('{social_link}', 'destroy')->name('destroy')->middleware('permission:setting.update');
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::controller(MediaController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('permission:media.view');
            Route::get('datatable', 'datatable')->name('datatable');
            Route::get('stats', 'stats')->name('stats')->middleware('permission:media.view');
            Route::get('picker', 'picker')->name('picker')->middleware('permission:media.view');
            Route::post('upload', 'upload')->name('upload');
            Route::post('bulk-move', 'bulkMove')->name('bulk-move')->middleware('permission:media.update');
            Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('permission:media.delete');
            Route::get('{media}/form', 'form')->name('form')->middleware('permission:media.view');
            Route::put('{media}', 'update')->name('update');
            Route::post('{media}/recrop', 'recrop')->name('recrop');
            Route::delete('{media}', 'destroy')->name('destroy')->middleware('permission:media.delete');
        });

        Route::controller(MediaFolderController::class)->prefix('folders')->name('folders.')->group(function () {
            Route::get('/', 'index')->name('index')->middleware('permission:media.view');
            Route::get('tree', 'tree')->name('tree')->middleware('permission:media.view');
            Route::post('/', 'store')->name('store');
            Route::put('{folder}', 'update')->name('update');
            Route::delete('{folder}', 'destroy')->name('destroy')->middleware('permission:media.delete');
        });
    });

    // Etiket alanının öneri listesi. Etiketler modül formlarından yönetilir,
    // ayrı bir yönetim ekranı yoktur.
    Route::get('tags/search', [TagController::class, 'search'])->name('tags.search');

    Route::prefix('ai-provider')->name('ai-provider.')->controller(AiProviderController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:ai-provider.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:ai-provider.view');
        Route::get('form/{provider?}', 'form')->name('form')->middleware('permission:ai-provider.view');
        Route::post('/', 'store')->name('store')->middleware('permission:ai-provider.create');
        Route::put('{provider}', 'update')->name('update')->middleware('permission:ai-provider.update');
        Route::post('{provider}/test', 'test')->name('test')->middleware('permission:ai-provider.update');
        Route::delete('{provider}', 'destroy')->name('destroy')->middleware('permission:ai-provider.delete');
    });

    Route::prefix('ai-prompt')->name('ai-prompt.')->controller(AiPromptController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:ai-prompt.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:ai-prompt.view');
        Route::get('form/{prompt?}', 'form')->name('form')->middleware('permission:ai-prompt.view');
        Route::post('/', 'store')->name('store')->middleware('permission:ai-prompt.create');
        Route::put('{prompt}', 'update')->name('update')->middleware('permission:ai-prompt.update');
        Route::delete('{prompt}', 'destroy')->name('destroy')->middleware('permission:ai-prompt.delete');
    });

    // İçerik üretimi: modül formlarından çağrılır, kuyruğa atar ve durum döner.
    Route::prefix('ai')->name('ai.')->controller(AiGenerationController::class)
        ->middleware('permission:ai.generate')->group(function () {
            Route::get('generate/{key}/form', 'form')->name('generate.form');
            Route::post('generate', 'store')->name('generate.store');
            Route::get('generate/{generation}', 'show')->name('generate.show');
        });

    Route::prefix('blog-category')->name('blog-category.')->controller(BlogCategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:blog-category.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:blog-category.view');
        Route::get('form/{category?}', 'form')->name('form')->middleware('permission:blog-category.view');
        Route::post('/', 'store')->name('store')->middleware('permission:blog-category.create');
        // 'reorder' sabit segmenti, aşağıdaki {category} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kategori kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:blog-category.update');
        Route::put('{category}', 'update')->name('update')->middleware('permission:blog-category.update');
        Route::delete('{category}', 'destroy')->name('destroy')->middleware('permission:blog-category.delete');
    });

    Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:blog.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:blog.view');
        Route::get('create', 'create')->name('create')->middleware('permission:blog.create');
        Route::post('/', 'store')->name('store')->middleware('permission:blog.create');
        Route::get('{blog}/edit', 'edit')->name('edit')->middleware('permission:blog.update');
        Route::put('{blog}', 'update')->name('update')->middleware('permission:blog.update');
        Route::delete('{blog}', 'destroy')->name('destroy')->middleware('permission:blog.delete');
    });

    // Bölge ağacı: liste kırılımlı çalışır, datatable parent_id filtresiyle
    // yalnızca o seviyeyi döndürür.
    Route::prefix('service-region')->name('service-region.')->controller(ServiceRegionController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:service-region.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:service-region.view');
        // Kırılım başlığı için kökten seçili bölgeye kadarki zincir.
        Route::get('breadcrumb/{region}', 'breadcrumb')->name('breadcrumb')->middleware('permission:service-region.view');
        Route::get('form/{region?}', 'form')->name('form')->middleware('permission:service-region.view');
        Route::post('/', 'store')->name('store')->middleware('permission:service-region.create');
        // 'reorder' sabit segmenti, aşağıdaki {region} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir bölge kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:service-region.update');
        Route::put('{region}', 'update')->name('update')->middleware('permission:service-region.update');
        Route::delete('{region}', 'destroy')->name('destroy')->middleware('permission:service-region.delete');
    });

    Route::prefix('service')->name('service.')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:service.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:service.view');
        Route::get('create', 'create')->name('create')->middleware('permission:service.create');
        Route::post('/', 'store')->name('store')->middleware('permission:service.create');
        // 'reorder' sabit segmenti {service} joker'ından ÖNCE tanımlanmalı.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:service.update');
        Route::get('{service}/edit', 'edit')->name('edit')->middleware('permission:service.update');
        Route::put('{service}', 'update')->name('update')->middleware('permission:service.update');
        Route::delete('{service}', 'destroy')->name('destroy')->middleware('permission:service.delete');
    });

    // Tekil kayıt modülü: liste, ekleme ve silme yok — tek form.
    Route::prefix('hero')->name('hero.')->controller(HeroController::class)->group(function () {
        Route::get('/', 'edit')->name('index')->middleware('permission:hero.view');
        Route::put('/', 'update')->name('update')->middleware('permission:hero.update');
    });

    Route::prefix('testimonial')->name('testimonial.')->controller(TestimonialController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:testimonial.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:testimonial.view');
        Route::get('form/{testimonial?}', 'form')->name('form')->middleware('permission:testimonial.view');
        Route::post('/', 'store')->name('store')->middleware('permission:testimonial.create');
        // 'reorder' sabit segmenti, aşağıdaki {testimonial} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:testimonial.update');
        Route::put('{testimonial}', 'update')->name('update')->middleware('permission:testimonial.update');
        Route::delete('{testimonial}', 'destroy')->name('destroy')->middleware('permission:testimonial.delete');
    });

    Route::prefix('reference')->name('reference.')->controller(ReferenceController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:reference.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:reference.view');
        Route::get('form/{reference?}', 'form')->name('form')->middleware('permission:reference.view');
        Route::post('/', 'store')->name('store')->middleware('permission:reference.create');
        // 'reorder' sabit segmenti, aşağıdaki {reference} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:reference.update');
        Route::put('{reference}', 'update')->name('update')->middleware('permission:reference.update');
        Route::delete('{reference}', 'destroy')->name('destroy')->middleware('permission:reference.delete');
    });

    Route::prefix('faq')->name('faq.')->controller(FaqController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:faq.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:faq.view');
        Route::get('form/{faq?}', 'form')->name('form')->middleware('permission:faq.view');
        Route::post('/', 'store')->name('store')->middleware('permission:faq.create');
        // 'reorder' sabit segmenti, aşağıdaki {faq} joker'ından ÖNCE
        // tanımlanmalı — aksi halde 'reorder' bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:faq.update');
        Route::put('{faq}', 'update')->name('update')->middleware('permission:faq.update');
        Route::delete('{faq}', 'destroy')->name('destroy')->middleware('permission:faq.delete');
    });

    Route::prefix('why-choose-us')->name('why-choose-us.')->controller(WhyChooseUsController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('permission:why-choose-us.view');
        Route::get('datatable', 'datatable')->name('datatable')->middleware('permission:why-choose-us.view');
        Route::get('form/{why_choose_us?}', 'form')->name('form')->middleware('permission:why-choose-us.view');
        Route::post('/', 'store')->name('store')->middleware('permission:why-choose-us.create');
        // 'reorder' ve 'heading' sabit segmentleri, aşağıdaki {why_choose_us}
        // joker'ından ÖNCE tanımlanmalı — aksi halde bir kayıt kimliği sanılır.
        Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:why-choose-us.update');
        Route::put('heading', 'updateHeading')->name('heading')->middleware('permission:why-choose-us.update');
        Route::put('{why_choose_us}', 'update')->name('update')->middleware('permission:why-choose-us.update');
        Route::delete('{why_choose_us}', 'destroy')->name('destroy')->middleware('permission:why-choose-us.delete');
    });

    /*
    | Modül route'ları buraya eklenir. Kalıp:
    |
    | Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
    |     Route::get('/', 'index')->name('index')->middleware('permission:blog.view');
    |     ...
    | });
    */
});
