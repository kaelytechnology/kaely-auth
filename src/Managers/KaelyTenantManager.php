<?php

namespace Kaely\Auth\Managers;

use Kaely\Auth\Contracts\TenantManagerInterface;
use Kaely\Auth\Contracts\ConnectionResolverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class KaelyTenantManager implements TenantManagerInterface
{
    protected $config;
    protected $currentTenant = null;
    protected $resolvers = [];
    protected $switchers = [];
    protected $connectionResolver;

    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->config = config('kaely-auth.database.multitenancy');
        $this->connectionResolver = $connectionResolver;
        $this->initializeResolvers();
        $this->initializeSwitchers();
    }

    /**
     * Check if multitenancy is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Get current tenant
     */
    public function getCurrentTenant(): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($this->currentTenant !== null) {
            return $this->currentTenant;
        }

        $this->currentTenant = $this->resolveTenant();
        return $this->currentTenant;
    }

    /**
     * Set current tenant
     */
    public function setCurrentTenant(string $tenant): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->currentTenant = $tenant;
        $this->switchToTenant($tenant);
    }

    /**
     * Switch to specific tenant
     */
    public function switchToTenant(string $tenant): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Switch database connection
        $connection = $this->connectionResolver->getTenantConnection($tenant);
        $this->connectionResolver->setCurrentConnection($connection);

        // Switch other components
        foreach ($this->switchers as $switcher) {
            app($switcher)->switch($tenant);
        }

        // Update cache prefix
        $cachePrefix = $this->config['tenant_cache_prefix'] ?? 'tenant_';
        Cache::setPrefix($cachePrefix . $tenant . '_');

        // Update session prefix
        $sessionPrefix = $this->config['tenant_session_prefix'] ?? 'tenant_';
        Session::setPrefix($sessionPrefix . $tenant . '_');
    }

    /**
     * Get all available tenants
     */
    public function getAllTenants(): array
    {
        if (!$this->isEnabled()) {
            return ['main'];
        }

        // This could be enhanced to read from a tenants table
        return ['main', 'tenant1', 'tenant2', 'tenant3'];
    }

    /**
     * Check if tenant exists
     */
    public function tenantExists(string $tenant): bool
    {
        return in_array($tenant, $this->getAllTenants());
    }

    /**
     * Create tenant database
     */
    public function createTenantDatabase(string $tenant): bool
    {
        if (!$this->config['auto_create_tenant_db'] ?? true) {
            return true;
        }

        try {
            $database = $this->connectionResolver->getTenantDatabase($tenant);
            $connection = $this->connectionResolver->getTenantConnection($tenant);
            
            // Create database if it doesn't exist
            \Illuminate\Support\Facades\DB::statement("CREATE DATABASE IF NOT EXISTS {$database}");
            
            // Configure connection
            $this->connectionResolver->configureTenantConnection($tenant, $database);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStats(string $tenant): array
    {
        if (!$this->tenantExists($tenant)) {
            return [];
        }

        $connection = $this->connectionResolver->getTenantConnection($tenant);
        
        try {
            $userCount = \Illuminate\Support\Facades\DB::connection($connection)->table('users')->count();
            $roleCount = \Illuminate\Support\Facades\DB::connection($connection)->table('roles')->count();
            $permissionCount = \Illuminate\Support\Facades\DB::connection($connection)->table('permissions')->count();
            
            return [
                'users' => $userCount,
                'roles' => $roleCount,
                'permissions' => $permissionCount,
                'database' => $this->connectionResolver->getTenantDatabase($tenant),
                'connection' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'database' => $this->connectionResolver->getTenantDatabase($tenant),
                'connection' => $connection,
            ];
        }
    }

    /**
     * Sync user across tenants
     */
    public function syncUserAcrossTenants($user, array $tenants = []): bool
    {
        if (empty($tenants)) {
            $tenants = $this->getAllTenants();
        }

        foreach ($tenants as $tenant) {
            if (!$this->tenantExists($tenant)) {
                continue;
            }

            try {
                $connection = $this->connectionResolver->getTenantConnection($tenant);
                $userData = $user->toArray();
                
                // Remove auto-increment fields
                unset($userData['id']);
                
                \Illuminate\Support\Facades\DB::connection($connection)->table('users')->updateOrInsert(
                    ['email' => $user->email],
                    $userData
                );
            } catch (\Exception $e) {
                // Log error but continue with other tenants
                continue;
            }
        }

        return true;
    }

    /**
     * Check if current request is for a tenant
     */
    public function isTenantRequest(Request $request): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $tenant = $this->resolveTenant();
        return $tenant !== ($this->config['default_tenant'] ?? 'main');
    }

    /**
     * Resolve tenant from request
     */
    protected function resolveTenant(): ?string
    {
        $mode = $this->config['mode'] ?? 'subdomain';
        $resolver = $this->resolvers[$mode] ?? null;

        if (!$resolver) {
            return $this->config['default_tenant'] ?? 'main';
        }

        $tenant = app($resolver)->resolve(request());
        
        if (!$tenant) {
            return $this->config['default_tenant'] ?? 'main';
        }

        return $tenant;
    }

    /**
     * Initialize tenant resolvers
     */
    protected function initializeResolvers(): void
    {
        $resolvers = $this->config['tenant_resolvers'] ?? [];
        
        foreach ($resolvers as $mode => $resolver) {
            if (class_exists($resolver)) {
                $this->resolvers[$mode] = $resolver;
            }
        }
    }

    /**
     * Initialize tenant switchers
     */
    protected function initializeSwitchers(): void
    {
        $switchers = $this->config['tenant_switchers'] ?? [];
        
        foreach ($switchers as $type => $switcher) {
            if (class_exists($switcher)) {
                $this->switchers[$type] = $switcher;
            }
        }
    }
} 