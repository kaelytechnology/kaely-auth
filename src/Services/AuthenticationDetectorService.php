<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class AuthenticationDetectorService
{
    /**
     * Detect the authentication package being used.
     */
    public function detectAuthPackage()
    {
        // Check for Laravel Sanctum
        if ($this->isSanctumInstalled()) {
            return 'sanctum';
        }

        // Check for Laravel Passport
        if ($this->isPassportInstalled()) {
            return 'passport';
        }

        // Check for JWT Auth
        if ($this->isJWTAuthInstalled()) {
            return 'jwt';
        }

        // Check for Firebase Auth
        if ($this->isFirebaseAuthInstalled()) {
            return 'firebase';
        }

        // Default to web session
        return 'web';
    }

    /**
     * Check if Laravel Sanctum is installed.
     */
    protected function isSanctumInstalled()
    {
        return class_exists('Laravel\Sanctum\SanctumServiceProvider') ||
               File::exists(base_path('vendor/laravel/sanctum'));
    }

    /**
     * Check if Laravel Passport is installed.
     */
    protected function isPassportInstalled()
    {
        return class_exists('Laravel\Passport\PassportServiceProvider') ||
               File::exists(base_path('vendor/laravel/passport'));
    }

    /**
     * Check if JWT Auth is installed.
     */
    protected function isJWTAuthInstalled()
    {
        return class_exists('PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider') ||
               File::exists(base_path('vendor/php-open-source-saver/jwt-auth'));
    }

    /**
     * Check if Firebase Auth is installed.
     */
    protected function isFirebaseAuthInstalled()
    {
        return class_exists('Kreait\Laravel\Firebase\ServiceProvider') ||
               File::exists(base_path('vendor/kreait/laravel-firebase'));
    }

    /**
     * Get the appropriate guard for the detected package.
     */
    public function getGuard()
    {
        $package = $this->detectAuthPackage();

        switch ($package) {
            case 'sanctum':
                return 'sanctum';
            case 'passport':
                return 'api';
            case 'jwt':
                return 'api';
            case 'firebase':
                return 'api';
            default:
                return 'web';
        }
    }

    /**
     * Get the appropriate middleware for the detected package.
     */
    public function getMiddleware()
    {
        $package = $this->detectAuthPackage();

        switch ($package) {
            case 'sanctum':
                return 'auth:sanctum';
            case 'passport':
                return 'auth:api';
            case 'jwt':
                return 'auth:api';
            case 'firebase':
                return 'auth:api';
            default:
                return 'auth';
        }
    }

    /**
     * Get the users table name based on the detected package.
     */
    public function getUsersTable()
    {
        $package = $this->detectAuthPackage();

        switch ($package) {
            case 'sanctum':
                return 'users';
            case 'passport':
                return 'users';
            case 'jwt':
                return 'users';
            case 'firebase':
                return 'users';
            default:
                return 'users';
        }
    }

    /**
     * Get authentication configuration for the detected package.
     */
    public function getAuthConfig()
    {
        $package = $this->detectAuthPackage();

        return [
            'package' => $package,
            'guard' => $this->getGuard(),
            'middleware' => $this->getMiddleware(),
            'table_name' => $this->getUsersTable(),
        ];
    }

    /**
     * Update configuration based on detected package.
     */
    public function updateConfig()
    {
        $authConfig = $this->getAuthConfig();

        // Update auth configuration
        config([
            'kaely-auth.auth.package' => $authConfig['package'],
            'kaely-auth.auth.guard' => $authConfig['guard'],
            'kaely-auth.database.tables.users' => $authConfig['table_name'],
        ]);

        return $authConfig;
    }

    /**
     * Get package-specific features.
     */
    public function getPackageFeatures()
    {
        $package = $this->detectAuthPackage();

        $features = [
            'sanctum' => [
                'token_management' => true,
                'abilities' => true,
                'expiration' => true,
                'refresh' => false,
            ],
            'passport' => [
                'token_management' => true,
                'abilities' => true,
                'expiration' => true,
                'refresh' => true,
                'scopes' => true,
            ],
            'jwt' => [
                'token_management' => true,
                'abilities' => false,
                'expiration' => true,
                'refresh' => true,
            ],
            'firebase' => [
                'token_management' => false,
                'abilities' => false,
                'expiration' => true,
                'refresh' => false,
            ],
            'web' => [
                'token_management' => false,
                'abilities' => false,
                'expiration' => false,
                'refresh' => false,
            ],
        ];

        return $features[$package] ?? $features['web'];
    }

    /**
     * Check if the detected package supports a specific feature.
     */
    public function supportsFeature($feature)
    {
        $features = $this->getPackageFeatures();
        return $features[$feature] ?? false;
    }

    /**
     * Get recommended configuration for the detected package.
     */
    public function getRecommendedConfig()
    {
        $package = $this->detectAuthPackage();

        $recommendations = [
            'sanctum' => [
                'KAELY_AUTH_PACKAGE' => 'sanctum',
                'KAELY_AUTH_GUARD' => 'sanctum',
                'KAELY_USERS_TABLE' => 'users',
                'KAELY_API_MIDDLEWARE' => 'auth:sanctum',
            ],
            'passport' => [
                'KAELY_AUTH_PACKAGE' => 'passport',
                'KAELY_AUTH_GUARD' => 'api',
                'KAELY_USERS_TABLE' => 'users',
                'KAELY_API_MIDDLEWARE' => 'auth:api',
            ],
            'jwt' => [
                'KAELY_AUTH_PACKAGE' => 'jwt',
                'KAELY_AUTH_GUARD' => 'api',
                'KAELY_USERS_TABLE' => 'users',
                'KAELY_API_MIDDLEWARE' => 'auth:api',
            ],
            'firebase' => [
                'KAELY_AUTH_PACKAGE' => 'firebase',
                'KAELY_AUTH_GUARD' => 'api',
                'KAELY_USERS_TABLE' => 'users',
                'KAELY_API_MIDDLEWARE' => 'auth:api',
            ],
            'web' => [
                'KAELY_AUTH_PACKAGE' => 'web',
                'KAELY_AUTH_GUARD' => 'web',
                'KAELY_USERS_TABLE' => 'users',
                'KAELY_API_MIDDLEWARE' => 'auth',
            ],
        ];

        return $recommendations[$package] ?? $recommendations['web'];
    }
} 