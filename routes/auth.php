<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Невірні облікові дані.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $locale = app()->getLocale();
        $supportedLocales = ['en', 'de', 'ua'];

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'en';
        }

        $isAdmin = (bool) ($user?->is_admin);
        $target = $isAdmin
            ? '/admin/works'
            : route('pending-approval', ['locale' => $locale], false);

        if (!$isAdmin) {
            $intended = $request->session()->get('url.intended');
            $intendedPath = is_string($intended) ? parse_url($intended, PHP_URL_PATH) : null;

            if (is_string($intendedPath) && str_starts_with($intendedPath, '/admin')) {
                $request->session()->forget('url.intended');
            }
        }

        return redirect()->intended($target);
    })->name('login.attempt');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
