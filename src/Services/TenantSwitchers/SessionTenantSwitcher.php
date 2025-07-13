<?php

namespace Kaely\Auth\Services\TenantSwitchers;

use Illuminate\Support\Facades\Session;

class SessionTenantSwitcher implements TenantSwitcherInterface
{
    public function switch(string $tenant): void
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_session_prefix', 'tenant_');
        Session::setPrefix($prefix . $tenant . '_');
        
        // Store tenant in session
        Session::put('current_tenant', $tenant);
    }
} 