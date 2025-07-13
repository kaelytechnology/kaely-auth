<?php

use Illuminate\Support\Facades\Route;
use Kaely\Auth\Controllers\{
    AuthController,
    OAuthController
};
use Kaely\Auth\Controllers\Api\V1\{
    UserController,
    RoleController,
    PermissionController,
    MenuController,
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

Route::prefix('api')->group(function () {
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

    // OAuth routes (if enabled)
    if (config('kaely-auth.oauth.enabled', false)) {
        Route::prefix('oauth')->group(function () {
            Route::get('providers', [OAuthController::class, 'getProviders']);
            Route::get('stats', [OAuthController::class, 'getStats']);
            Route::get('validate-config', [OAuthController::class, 'validateConfig']);
            
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('sync-user', [OAuthController::class, 'syncUser']);
                Route::post('disconnect', [OAuthController::class, 'disconnect']);
                Route::post('link-account/{provider}', [OAuthController::class, 'linkAccount']);
                Route::get('connected-accounts', [OAuthController::class, 'getConnectedAccounts']);
                Route::put('update-profile', [OAuthController::class, 'updateProfile']);
            });
            
            // Public OAuth routes
            Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider']);
            Route::get('{provider}/callback', [AuthController::class, 'handleProviderCallback']);
        });
    }

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User profile
        Route::get('user', [AuthController::class, 'user']);
        Route::put('user', [AuthController::class, 'updateProfile']);
        Route::put('user/password', [AuthController::class, 'updatePassword']);

        // Menu routes
        Route::prefix('menu')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('user', [MenuController::class, 'userMenu']);
            Route::get('all', [MenuController::class, 'all']);
            Route::post('reorder', [MenuController::class, 'reorder']);
            Route::get('{id}', [MenuController::class, 'show']);
            Route::post('/', [MenuController::class, 'store']);
            Route::put('{id}', [MenuController::class, 'update']);
            Route::delete('{id}', [MenuController::class, 'destroy']);
            Route::get('{id}/roles', [MenuController::class, 'roles']);
            Route::post('{id}/roles', [MenuController::class, 'assignRoles']);
        });

        // User management routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('{id}', [UserController::class, 'show']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::delete('{id}', [UserController::class, 'destroy']);
            Route::get('{id}/roles', [UserController::class, 'roles']);
            Route::get('{id}/permissions', [UserController::class, 'permissions']);
            Route::post('{id}/roles', [UserController::class, 'assignRoles']);
            Route::post('{id}/permissions', [UserController::class, 'assignPermissions']);
        });

        // Role management routes
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::get('{id}', [RoleController::class, 'show']);
            Route::post('/', [RoleController::class, 'store']);
            Route::put('{id}', [RoleController::class, 'update']);
            Route::delete('{id}', [RoleController::class, 'destroy']);
            Route::get('{id}/permissions', [RoleController::class, 'permissions']);
            Route::get('{id}/users', [RoleController::class, 'users']);
            Route::post('{id}/permissions', [RoleController::class, 'assignPermissions']);
        });

        // Permission management routes
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('{id}', [PermissionController::class, 'show']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::put('{id}', [PermissionController::class, 'update']);
            Route::delete('{id}', [PermissionController::class, 'destroy']);
            Route::get('{id}/roles', [PermissionController::class, 'roles']);
            Route::get('{id}/users', [PermissionController::class, 'users']);
            Route::get('module/{module}', [PermissionController::class, 'byModule']);
            Route::get('modules/all', [PermissionController::class, 'modules']);
        });

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

        // System routes
        Route::prefix('system')->group(function () {
            Route::get('stats', [AuthController::class, 'getStats']);
            Route::get('database-status', [AuthController::class, 'getDatabaseStatus']);
            Route::get('table-stats', [AuthController::class, 'getTableStats']);
            Route::post('optimize-tables', [AuthController::class, 'optimizeTables']);
            Route::post('create-indexes', [AuthController::class, 'createIndexes']);
            Route::get('validate-relations', [AuthController::class, 'validateRelations']);
        });
    });

    // Admin routes (require additional permissions)
    Route::middleware(['auth:sanctum', 'kaely.permission:admin_access'])->prefix('admin')->group(function () {
        Route::get('audit/user-logs', [AuditController::class, 'getUserLogs']);
        Route::get('audit/action-logs', [AuditController::class, 'getLogsByAction']);
    });
}); 