<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MultitenancyService
{
    protected $config;
    protected $currentTenant = null;
    protected $resolvers = [];
    protected $switchers = [];

    public function __construct()
    {
        $this->config = config('kaely-auth.database.multitenancy');
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
     * Switch to specific tenant
     */
    public function switchToTenant(string $tenant): void
    {
        if (!$this->isEnabled()) {
            return;
        }

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

    /**
     * Get tenant database name
     */
    public function getTenantDatabase(string $tenant): string
    {
        $prefix = $this->config['tenant_database_prefix'] ?? 'tenant_';
        return $prefix . $tenant;
    }

    /**
     * Get tenant connection name
     */
    public function getTenantConnection(string $tenant): string
    {
        $prefix = $this->config['tenant_connection_prefix'] ?? 'tenant_';
        return $prefix . $tenant;
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
            $database = $this->getTenantDatabase($tenant);
            $connection = $this->getTenantConnection($tenant);
            
            // Create database if it doesn't exist
            DB::statement("CREATE DATABASE IF NOT EXISTS {$database}");
            
            // Configure connection
            $this->configureTenantConnection($tenant, $database);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Configure tenant connection
     */
    protected function configureTenantConnection(string $tenant, string $database): void
    {
        $connection = $this->getTenantConnection($tenant);
        $baseConnection = config('database.default');
        $baseConfig = config("database.connections.{$baseConnection}");

        Config::set("database.connections.{$connection}", array_merge($baseConfig, [
            'database' => $database,
        ]));
    }

    /**
     * Run migrations for tenant
     */
    public function runTenantMigrations(string $tenant): bool
    {
        try {
            $connection = $this->getTenantConnection($tenant);
            $migrationPath = $this->config['tenant_migration_path'] ?? 'database/migrations/tenant';
            
            \Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => $migrationPath,
                '--force' => true,
            ]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run seeders for tenant
     */
    public function runTenantSeeders(string $tenant): bool
    {
        try {
            $connection = $this->getTenantConnection($tenant);
            $seederPath = $this->config['tenant_seeder_path'] ?? 'database/seeders/tenant';
            
            \Artisan::call('db:seed', [
                '--database' => $connection,
                '--class' => "Tenant{$tenant}Seeder",
                '--force' => true,
            ]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all tenants
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
     * Get tenant statistics
     */
    public function getTenantStats(string $tenant): array
    {
        if (!$this->tenantExists($tenant)) {
            return [];
        }

        $connection = $this->getTenantConnection($tenant);
        
        try {
            $userCount = DB::connection($connection)->table('users')->count();
            $roleCount = DB::connection($connection)->table('roles')->count();
            $permissionCount = DB::connection($connection)->table('permissions')->count();
            
            return [
                'users' => $userCount,
                'roles' => $roleCount,
                'permissions' => $permissionCount,
                'database' => $this->getTenantDatabase($tenant),
                'connection' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'database' => $this->getTenantDatabase($tenant),
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
                $connection = $this->getTenantConnection($tenant);
                $userData = $user->toArray();
                
                // Remove auto-increment fields
                unset($userData['id']);
                
                DB::connection($connection)->table('users')->updateOrInsert(
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
     * Get tenant middleware
     */
    public function getTenantMiddleware(): array
    {
        return $this->config['tenant_middleware'] ?? [];
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
     * Get tenant from domain
     */
    public function getTenantFromDomain(string $domain): ?string
    {
        $mode = $this->config['mode'] ?? 'subdomain';
        
        switch ($mode) {
            case 'subdomain':
                return $this->extractSubdomain($domain);
            case 'domain':
                return $this->extractDomain($domain);
            default:
                return null;
        }
    }

    /**
     * Extract subdomain from domain
     */
    protected function extractSubdomain(string $domain): ?string
    {
        $parts = explode('.', $domain);
        
        if (count($parts) < 3) {
            return null;
        }
        
        return $parts[0];
    }

    /**
     * Extract domain tenant
     */
    protected function extractDomain(string $domain): ?string
    {
        // This would need to be customized based on your domain structure
        // For example: tenant1.example.com -> tenant1
        return $this->extractSubdomain($domain);
    }
} 