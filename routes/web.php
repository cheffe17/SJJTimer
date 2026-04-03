<?php

use Illuminate\Support\Facades\Route;

// Scavenger Hunt Gate
Route::get('/gate', function () {
    if (session('scavenger_hunt_passed')) {
        return redirect('/');
    }
    return view('gate');
})->name('gate');

Route::post('/gate', function () {
    $password = request('password');

    if (mb_strtolower(trim($password)) === 'leb deinen traum') {
        session(['scavenger_hunt_passed' => true]);
        return redirect('/dashboard');
    }

    return back()->withErrors(['password' => 'Das ist nicht das richtige Passwort.']);
});

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
