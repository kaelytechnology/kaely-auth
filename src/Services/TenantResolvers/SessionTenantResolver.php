<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SessionTenantResolver implements TenantResolverInterface
{
    public function resolve(Request $request): ?string
    {
        return Session::get('tenant');
    }
} 