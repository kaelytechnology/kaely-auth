<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HeaderTenantResolver implements TenantResolverInterface
{
    public function resolve(Request $request): ?string
    {
        // Check for tenant in headers
        $tenant = $request->header('X-Tenant') ?? 
                  $request->header('Tenant') ?? 
                  $request->header('X-Tenant-ID');
        
        if (!$tenant) {
            return null;
        }
        
        return $tenant;
    }
} 