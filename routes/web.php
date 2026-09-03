<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home.index');
})->name('anasayfa');

Route::get('/hakkimizda', function () {
    return view('pages.about.index');
})->name('hakkimizda');

Route::get('/hizmetler', function () {
    return view('pages.services.index');
})->name('hizmetler');

Route::get('/hizmetler/{id}', function ($id) {
    return view('pages.services.show');
})->name('hizmetler.show');

Route::get('/blog', function () {
    return view('pages.blog.index');
})->name('blog');

Route::get('/blog/{id}', function ($id) {
    return view('pages.blog.show');
})->name('blog.show');

Route::get('/iletisim', function () {
    return view('pages.contact.index');
})->name('iletisim');
