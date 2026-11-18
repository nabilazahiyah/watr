<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JelajahiController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/jelajahi', [JelajahiController::class, 'index'])->name('jelajahi');
Route::view('/tentang', 'tentang')->name('tentang');