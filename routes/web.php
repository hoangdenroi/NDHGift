<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('components.pages.app.home.home-index');
})->name('home');

Route::middleware('auth')->group(function () {});

require __DIR__.'/auth.php';
