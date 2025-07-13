<?php

namespace Kaely\Auth\Services\TenantResolvers;

use Illuminate\Http\Request;

interface TenantResolverInterface
{
    /**
     * Resolve tenant from request
     */
    public function resolve(Request $request): ?string;
} 