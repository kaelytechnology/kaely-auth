<?php

use Illuminate\Support\Facades\Route;
use Kaely\Auth\Controllers\Api\V1\{
    AuthController,
    PasswordResetController,
    EmailVerificationController,
    SessionController,
    AuditController
};

/*
|--------------------------------------------------------------------------
| KaelyAuth API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the KaelyAuth package.
| These routes are automatically loaded by the service provider.
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    
    // Password Reset routes
    Route::prefix('password')->group(function () {
        Route::post('reset', [PasswordResetController::class, 'sendResetLink']);
        Route::post('reset/validate', [PasswordResetController::class, 'validateToken']);
        Route::post('reset/confirm', [PasswordResetController::class, 'resetPassword']);
    });

    // Email Verification routes
    Route::prefix('email')->group(function () {
        Route::post('verify/send', [EmailVerificationController::class, 'sendVerificationEmail'])
            ->middleware('auth:sanctum');
        Route::post('verify/confirm', [EmailVerificationController::class, 'verifyEmail']);
        Route::post('verify/resend', [EmailVerificationController::class, 'resendVerificationEmail'])
            ->middleware('auth:sanctum');
        Route::get('verify/status', [EmailVerificationController::class, 'checkVerificationStatus'])
            ->middleware('auth:sanctum');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User profile
        Route::get('user', [AuthController::class, 'user']);
        Route::put('user', [AuthController::class, 'updateProfile']);
        Route::put('user/password', [AuthController::class, 'updatePassword']);

        // Session Management routes
        Route::prefix('sessions')->group(function () {
            Route::get('/', [SessionController::class, 'getActiveSessions']);
            Route::get('stats', [SessionController::class, 'getSessionStats']);
            Route::get('security-report', [SessionController::class, 'getSecurityReport']);
            Route::get('timeline', [SessionController::class, 'getSessionTimeline']);
            Route::delete('revoke/{tokenId}', [SessionController::class, 'revokeSession']);
            Route::delete('revoke-others', [SessionController::class, 'revokeOtherSessions']);
            Route::delete('revoke-all', [SessionController::class, 'revokeAllSessions']);
            Route::post('force-logout-all', [SessionController::class, 'forceLogoutAllDevices']);
        });

        // Audit routes
        Route::prefix('audit')->group(function () {
            Route::get('timeline', [AuditController::class, 'getUserTimeline']);
            Route::get('summary', [AuditController::class, 'getUserActivitySummary']);
            Route::get('stats', [AuditController::class, 'getAuditStats']);
            Route::get('heatmap', [AuditController::class, 'getActivityHeatmap']);
            Route::get('top-actions', [AuditController::class, 'getTopActions']);
            Route::get('error-trends', [AuditController::class, 'getErrorTrends']);
            Route::get('security-alerts', [AuditController::class, 'getSecurityAlerts']);
            Route::get('security-threats', [AuditController::class, 'getSecurityThreats']);
            Route::get('report', [AuditController::class, 'generateAuditReport']);
            Route::get('export', [AuditController::class, 'exportAuditLogs']);
        });

        // Admin routes (require additional permissions)
        Route::middleware('kaely.permission:view_audit_logs')->prefix('admin')->group(function () {
            Route::get('audit/user-logs', [AuditController::class, 'getUserLogs']);
            Route::get('audit/action-logs', [AuditController::class, 'getLogsByAction']);
        });
    });

    // OAuth routes (if enabled)
    if (config('kaely-auth.oauth.enabled', false)) {
        Route::prefix('oauth')->group(function () {
            Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider']);
            Route::get('{provider}/callback', [AuthController::class, 'handleProviderCallback']);
        });
    }
}); 