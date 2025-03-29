<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
