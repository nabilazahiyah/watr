<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JelajahiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/jelajahi', [JelajahiController::class, 'index'])->name('jelajahi');

Route::view('/tentang', 'tentang')->name('tentang');
