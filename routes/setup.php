<?php

use App\Http\Controllers\Setup\SetupController;
use Illuminate\Support\Facades\Route;

Route::prefix('kurulum')->name('setup.')->controller(SetupController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    Route::middleware('throttle:20,1')->group(function () {
        Route::post('yonetici', 'saveAdmin')->name('admin');
        Route::post('firma', 'saveCompany')->name('company');
        Route::post('firma/logo', 'uploadLogo')->name('logo');
        Route::post('moduller', 'saveModules')->name('modules');
        Route::post('iletisim', 'saveContact')->name('contact');
        Route::post('eposta', 'saveMail')->name('mail');
        Route::post('yasal', 'saveLegal')->name('legal');
        Route::post('kur', 'run')->name('run');
    });
});
