<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('profile', function () {
        $user = Auth::user()->fresh()->load('profile');
        return Inertia::render('Profile', [
            'profile' => $user->profile,
        ]);
    })->name('profile');
    Route::get('experience', function () {
        return Inertia::render('Experience');
    })->name('experience');
    Route::get('education', function () {
        return Inertia::render('Education');
    })->name('education');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/profile.php';
