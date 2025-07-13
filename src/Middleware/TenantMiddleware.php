<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kaely\Auth\Services\MultitenancyService;

class TenantMiddleware
{
    protected $multitenancyService;

    public function __construct(MultitenancyService $multitenancyService)
    {
        $this->multitenancyService = $multitenancyService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if multitenancy is enabled
        if (!$this->multitenancyService->isEnabled()) {
            return $next($request);
        }

        // Get current tenant
        $tenant = $this->multitenancyService->getCurrentTenant();
        
        if (!$tenant) {
            // No tenant found, continue with default
            return $next($request);
        }

        // Switch to tenant
        $this->multitenancyService->setCurrentTenant($tenant);

        // Add tenant to request for easy access
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
} 