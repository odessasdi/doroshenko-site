<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\StatisticsController;
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
        Route::get('/gallery/{work}', [PublicController::class, 'galleryShow'])->name('gallery.show');
        Route::get('/contacts', [PublicController::class, 'contacts'])->name('contacts');
        Route::view('/pending-approval', 'auth.pending-approval')
            ->middleware('auth')
            ->name('pending-approval');
    });

Route::prefix('admin')->middleware(['auth', 'admin', 'adminlocale'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.works.index');
    });

    Route::get('/techniques', AdminTechniquesIndex::class)->name('admin.techniques.index');
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('admin.statistics');
    Route::get('/works', AdminWorksIndex::class)->name('admin.works.index');
    Route::get('/works/create', AdminWorksCreate::class)->name('admin.works.create');
    Route::get('/works/{work}/edit', AdminWorksEdit::class)->name('admin.works.edit');
});

if (!Route::has('logout')) {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/en');
    })->name('logout');
}

require __DIR__.'/auth.php';
