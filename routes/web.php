<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en'); // стартовая

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'ru', 'ua'])
    ->middleware(['setlocale'])
    ->group(function () {
        Route::get('/', [PublicController::class, 'home'])->name('home');
        Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
        Route::get('/about', [PublicController::class, 'about'])->name('about');
        Route::get('/contacts', [PublicController::class, 'contacts'])->name('contacts');
    });
//Route::view('/', 'welcome');

//Route::view('dashboard', 'dashboard')
  //  ->middleware(['auth', 'verified'])
    //->name('dashboard');

//Route::view('profile', 'profile')
  //  ->middleware(['auth'])
    //->name('profile');

require __DIR__.'/auth.php';



