<?php

use Illuminate\Support\Facades\Route;
use Kaely\Auth\Http\Controllers\SimpleLoginController;

// Simple test routes for debugging
Route::prefix('kaely-test')->group(function () {
    Route::get('/test', [SimpleLoginController::class, 'test'])->name('kaely.test');
    Route::get('/login', [SimpleLoginController::class, 'showLoginForm'])->name('kaely.login');
    Route::post('/login', [SimpleLoginController::class, 'login'])->name('kaely.login.post');
}); 