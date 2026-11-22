<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JelajahiController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/tentang', 'tentang')->name('tentang');
Route::get('/jelajahi', [JelajahiController::class, 'index'])->name('jelajahi');
Route::get('/peta', [JelajahiController::class, 'peta'])->name('peta');