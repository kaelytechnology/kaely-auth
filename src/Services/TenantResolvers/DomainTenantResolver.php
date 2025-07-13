<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DomainTenantResolver implements TenantResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $host = $request->getHost();
        
        // For domain-based tenancy, the entire domain is the tenant
        // Example: tenant1.example.com -> tenant1
        $parts = explode('.', $host);
        
        if (count($parts) < 2) {
            return null;
        }
        
        // Remove TLD and get the main part
        $domain = $parts[0];
        
        // Skip common domains that aren't tenants
        $skipDomains = ['www', 'api', 'admin', 'app', 'dev', 'staging', 'test'];
        
        if (in_array($domain, $skipDomains)) {
            return null;
        }
        
        return $domain;
    }
} 