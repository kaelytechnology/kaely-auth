<?php

use Illuminate\Support\Facades\Route;
use Kaely\Auth\Controllers\{
    AuthController,
    OAuthController
};

/*
|--------------------------------------------------------------------------
| KaelyAuth Web Routes
|--------------------------------------------------------------------------
|
| Here are the web routes for the KaelyAuth package UI components.
| These routes are automatically loaded by the service provider.
|
*/

// Public routes
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', function () {
        return view('kaely-auth::blade.auth.login');
    })->name('login');
    
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Register routes
    Route::get('/register', function () {
        return view('kaely-auth::blade.auth.register');
    })->name('register');
    
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Password reset routes
    Route::get('/forgot-password', function () {
        return view('kaely-auth::blade.auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    
    Route::get('/reset-password/{token}', function ($token) {
        return view('kaely-auth::blade.auth.reset-password', ['token' => $token]);
    })->name('password.reset');
    
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    
    // Email verification routes
    Route::get('/verify-email', function () {
        return view('kaely-auth::blade.auth.verify-email');
    })->name('verification.notice');
    
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.send');
});

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('kaely-auth::blade.dashboard');
    })->name('dashboard');
    
    // Profile
    Route::get('/profile', function () {
        return view('kaely-auth::blade.profile');
    })->name('profile');
    
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    
    // Password change
    Route::get('/change-password', function () {
        return view('kaely-auth::blade.auth.change-password');
    })->name('password.change');
    
    Route::put('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // OAuth routes (if enabled)
    if (config('kaely-auth.oauth.enabled', false)) {
        Route::prefix('oauth')->group(function () {
            Route::get('/{provider}/redirect', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
            Route::get('/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
            Route::post('/disconnect/{provider}', [OAuthController::class, 'disconnect'])->name('oauth.disconnect');
        });
    }
});

// Admin routes (require additional permissions)
Route::middleware(['auth', 'kaely.permission:admin_access'])->prefix('admin')->group(function () {
    Route::get('/users', function () {
        return view('kaely-auth::blade.admin.users');
    })->name('admin.users');
    
    Route::get('/roles', function () {
        return view('kaely-auth::blade.admin.roles');
    })->name('admin.roles');
    
    Route::get('/permissions', function () {
        return view('kaely-auth::blade.admin.permissions');
    })->name('admin.permissions');
    
    Route::get('/audit', function () {
        return view('kaely-auth::blade.admin.audit');
    })->name('admin.audit');
}); 