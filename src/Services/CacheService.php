<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache user permissions
     */
    public function cacheUserPermissions(int $userId, array $permissions, int $ttl = 3600): void
    {
        $key = "user_permissions_{$userId}";
        Cache::put($key, $permissions, $ttl);
    }

    /**
     * Get cached user permissions
     */
    public function getCachedUserPermissions(int $userId): ?array
    {
        $key = "user_permissions_{$userId}";
        return Cache::get($key);
    }

    /**
     * Cache user roles
     */
    public function cacheUserRoles(int $userId, array $roles, int $ttl = 3600): void
    {
        $key = "user_roles_{$userId}";
        Cache::put($key, $roles, $ttl);
    }

    /**
     * Get cached user roles
     */
    public function getCachedUserRoles(int $userId): ?array
    {
        $key = "user_roles_{$userId}";
        return Cache::get($key);
    }

    /**
     * Cache OAuth providers
     */
    public function cacheOAuthProviders(array $providers, int $ttl = 1800): void
    {
        Cache::put('oauth_providers', $providers, $ttl);
    }

    /**
     * Get cached OAuth providers
     */
    public function getCachedOAuthProviders(): ?array
    {
        return Cache::get('oauth_providers');
    }

    /**
     * Cache user session data
     */
    public function cacheUserSession(int $userId, array $sessionData, int $ttl = 1800): void
    {
        $key = "user_session_{$userId}";
        Cache::put($key, $sessionData, $ttl);
    }

    /**
     * Get cached user session
     */
    public function getCachedUserSession(int $userId): ?array
    {
        $key = "user_session_{$userId}";
        return Cache::get($key);
    }

    /**
     * Cache audit statistics
     */
    public function cacheAuditStats(array $stats, int $ttl = 3600): void
    {
        Cache::put('audit_stats', $stats, $ttl);
    }

    /**
     * Get cached audit stats
     */
    public function getCachedAuditStats(): ?array
    {
        return Cache::get('audit_stats');
    }

    /**
     * Clear user cache
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget("user_permissions_{$userId}");
        Cache::forget("user_roles_{$userId}");
        Cache::forget("user_session_{$userId}");
    }

    /**
     * Clear all auth cache
     */
    public function clearAllAuthCache(): void
    {
        Cache::forget('oauth_providers');
        Cache::forget('audit_stats');
        
        // Clear user-specific caches
        $users = DB::table('users')->pluck('id');
        foreach ($users as $userId) {
            $this->clearUserCache($userId);
        }
    }

    /**
     * Warm up cache
     */
    public function warmUpCache(): void
    {
        // Cache OAuth providers
        $providers = config('kaely-auth.oauth.providers', []);
        $enabledProviders = array_filter($providers, fn($p) => $p['enabled'] ?? false);
        $this->cacheOAuthProviders($enabledProviders);

        // Cache audit stats
        $stats = $this->generateAuditStats();
        $this->cacheAuditStats($stats);

        Log::info('KaelyAuth cache warmed up successfully');
    }

    /**
     * Generate audit statistics
     */
    protected function generateAuditStats(): array
    {
        $stats = [
            'total_logs' => DB::table('audit_logs')->count(),
            'today_logs' => DB::table('audit_logs')->whereDate('created_at', today())->count(),
            'failed_logins' => DB::table('audit_logs')
                ->where('action', 'user.login_failed')
                ->whereDate('created_at', today())
                ->count(),
            'successful_logins' => DB::table('audit_logs')
                ->where('action', 'user.login')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return $stats;
    }
} 