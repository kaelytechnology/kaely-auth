<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PathTenantResolver implements TenantResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        if (empty($segments[0])) {
            return null;
        }
        
        $tenant = $segments[0];
        
        // Skip common paths that aren't tenants
        $skipPaths = ['api', 'admin', 'app', 'dev', 'staging', 'test', 'www'];
        
        if (in_array($tenant, $skipPaths)) {
            return null;
        }
        
        return $tenant;
    }
} 