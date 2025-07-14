<?php

use Illuminate\Support\Facades\Route;
use Kaely\Auth\Http\Controllers\TestController;
use Kaely\Auth\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| KaelyAuth Simple API Routes
|--------------------------------------------------------------------------
|
| Simple API routes for testing the package functionality.
|
*/

// Test routes
Route::get('/api/kaely/test', [TestController::class, 'test']);
Route::post('/api/kaely/test/login', [TestController::class, 'testLogin']);

// Auth routes
Route::post('/api/kaely/login', [AuthController::class, 'login']);
Route::post('/api/kaely/register', [AuthController::class, 'register']);
Route::post('/api/kaely/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); 