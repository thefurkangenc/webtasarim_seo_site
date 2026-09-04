<?php

use App\Http\Controllers\Admin\Ai\AiGenerationController;
use App\Http\Controllers\Admin\AiPrompt\AiPromptController;
use App\Http\Controllers\Admin\AiProvider\AiProviderController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Http\Controllers\Admin\BlogCategory\BlogCategoryController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Media\MediaController;
use App\Http\Controllers\Admin\Media\MediaFolderController;
use App\Http\Controllers\Admin\Tag\TagController;
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

    /*
    | Modül route'ları buraya eklenir. Kalıp:
    |
    | Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
    |     Route::get('/', 'index')->name('index')->middleware('permission:blog.view');
    |     ...
    | });
    */
});
