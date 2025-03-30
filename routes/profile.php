<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\ProfileController;

Route::middleware('auth')->group(function () {
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
