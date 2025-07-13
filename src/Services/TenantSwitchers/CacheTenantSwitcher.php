<?php

namespace Kaely\Auth\Services\TenantSwitchers;

use Illuminate\Support\Facades\Cache;

class CacheTenantSwitcher implements TenantSwitcherInterface
{
    public function switch(string $tenant): void
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_cache_prefix', 'tenant_');
        Cache::setPrefix($prefix . $tenant . '_');
    }
} 