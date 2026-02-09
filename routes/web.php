<?php

use App\Http\Controllers\PublicController;
use App\Livewire\Admin\Techniques\Index as AdminTechniquesIndex;
use App\Livewire\Admin\Works\Create as AdminWorksCreate;
use App\Livewire\Admin\Works\Edit as AdminWorksEdit;
use App\Livewire\Admin\Works\Index as AdminWorksIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en'); // стартовая

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'de', 'ua'])
    ->middleware(['setlocale'])
    ->group(function () {
        Route::get('/', [PublicController::class, 'home'])->name('home');
        Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
        Route::get('/contacts', [PublicController::class, 'contacts'])->name('contacts');
    });

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/techniques', AdminTechniquesIndex::class)->name('admin.techniques');
    Route::get('/admin/works', AdminWorksIndex::class)->name('admin.works.index');
    Route::get('/admin/works/create', AdminWorksCreate::class)->name('admin.works.create');
    Route::get('/admin/works/{work}/edit', AdminWorksEdit::class)->name('admin.works.edit');
});
//Route::view('/', 'welcome');

//Route::view('dashboard', 'dashboard')
  //  ->middleware(['auth', 'verified'])
    //->name('dashboard');

//Route::view('profile', 'profile')
  //  ->middleware(['auth'])
    //->name('profile');

if (!Route::has('logout')) {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/en');
    })->name('logout');
}

require __DIR__.'/auth.php';
