<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JelajahiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/tentang', 'tentang')->name('tentang');

Route::get('/jelajahi', [JelajahiController::class, 'index'])->name('jelajahi');
Route::get('/peta', [JelajahiController::class, 'peta'])->name('peta');