<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubdomainTenantResolver implements TenantResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Need at least 3 parts for subdomain (subdomain.domain.tld)
        if (count($parts) < 3) {
            return null;
        }
        
        $subdomain = $parts[0];
        
        // Skip common subdomains that aren't tenants
        $skipSubdomains = ['www', 'api', 'admin', 'app', 'dev', 'staging', 'test'];
        
        if (in_array($subdomain, $skipSubdomains)) {
            return null;
        }
        
        return $subdomain;
    }
} 