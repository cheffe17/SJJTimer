<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Invitation join link — redirects to dashboard with token in session
Route::get('/join/{token}', function (string $token) {
    session(['join_token' => $token]);

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('register');
})->name('join');

require __DIR__.'/auth.php';
