<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Kaely\Auth\Services\KaelyTenantManager;
use Kaely\Auth\Contracts\TenantManagerInterface;

class KaelyTenantMiddleware
{
    protected $tenantManager;

    public function __construct(TenantManagerInterface $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Resolve tenant from request
        $tenant = $this->resolveTenant($request);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Set tenant context
        $this->tenantManager->setCurrentTenant($tenant);

        // Automatically switch database connection based on tenant
        $this->switchDatabaseConnection($tenant);

        // Add tenant info to request for downstream use
        $request->merge(['current_tenant' => $tenant]);

        return $next($request);
    }

    /**
     * Resolve tenant from request
     */
    protected function resolveTenant(Request $request): ?array
    {
        $mode = config('kaely-auth.multitenancy.mode', 'subdomain');
        
        switch ($mode) {
            case 'subdomain':
                return $this->resolveSubdomainTenant($request);
            case 'domain':
                return $this->resolveDomainTenant($request);
            case 'header':
                return $this->resolveHeaderTenant($request);
            case 'parameter':
                return $this->resolveParameterTenant($request);
            default:
                return null;
        }
    }

    /**
     * Resolve tenant from subdomain
     */
    protected function resolveSubdomainTenant(Request $request): ?array
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        // Skip common subdomains
        if (in_array($subdomain, ['www', 'api', 'admin', 'app'])) {
            return null;
        }

        return $this->tenantManager->findTenantBySubdomain($subdomain);
    }

    /**
     * Resolve tenant from domain
     */
    protected function resolveDomainTenant(Request $request): ?array
    {
        $host = $request->getHost();
        return $this->tenantManager->findTenantByDomain($host);
    }

    /**
     * Resolve tenant from header
     */
    protected function resolveHeaderTenant(Request $request): ?array
    {
        $tenantId = $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return null;
        }

        return $this->tenantManager->findTenantById($tenantId);
    }

    /**
     * Resolve tenant from parameter
     */
    protected function resolveParameterTenant(Request $request): ?array
    {
        $tenantId = $request->input('tenant_id');
        if (!$tenantId) {
            return null;
        }

        return $this->tenantManager->findTenantById($tenantId);
    }

    /**
     * Switch database connection based on tenant
     */
    protected function switchDatabaseConnection(array $tenant): void
    {
        $dbMode = config('kaely-auth.database.mode', 'single');
        
        if ($dbMode === 'multiple') {
            $this->switchToTenantDatabase($tenant);
        } else {
            $this->setTenantPrefix($tenant);
        }
    }

    /**
     * Switch to tenant-specific database
     */
    protected function switchToTenantDatabase(array $tenant): void
    {
        $defaultConfig = config('database.connections.' . config('database.default'));
        
        // Create tenant-specific database name
        $tenantDbName = $tenant['database_name'] ?? $defaultConfig['database'] . '_' . $tenant['id'];
        
        // Create new connection for this tenant
        $tenantConfig = $defaultConfig;
        $tenantConfig['database'] = $tenantDbName;
        
        // Register tenant connection
        Config::set("database.connections.tenant_{$tenant['id']}", $tenantConfig);
        
        // Set as default for this request
        Config::set('database.default', "tenant_{$tenant['id']}");
        
        // Update auth provider connection
        Config::set('auth.providers.users.connection', "tenant_{$tenant['id']}");
    }

    /**
     * Set tenant prefix for single database mode
     */
    protected function setTenantPrefix(array $tenant): void
    {
        $prefix = $tenant['table_prefix'] ?? 'tenant_' . $tenant['id'] . '_';
        
        // Set table prefix for current request
        Config::set('database.connections.mysql.prefix', $prefix);
        
        // Update auth tables prefix
        Config::set('auth.table', $prefix . 'users');
    }

    /**
     * Get current tenant info
     */
    public static function getCurrentTenant(): ?array
    {
        return app(TenantManagerInterface::class)->getCurrentTenant();
    }
} 