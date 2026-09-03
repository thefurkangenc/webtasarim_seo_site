<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
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

    /*
    | Modül route'ları buraya eklenir. Kalıp:
    |
    | Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
    |     Route::get('/', 'index')->name('index')->middleware('permission:blog.view');
    |     ...
    | });
    */
});
