<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contacts', [PublicController::class, 'contacts'])->name('contacts');

//Route::view('/', 'welcome');

//Route::view('dashboard', 'dashboard')
  //  ->middleware(['auth', 'verified'])
    //->name('dashboard');

//Route::view('profile', 'profile')
  //  ->middleware(['auth'])
    //->name('profile');

require __DIR__.'/auth.php';
