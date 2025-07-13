<?php

namespace Kaely\Auth\Services\TenantSwitchers;

interface TenantSwitcherInterface
{
    /**
     * Switch to specific tenant
     */
    public function switch(string $tenant): void;
} 