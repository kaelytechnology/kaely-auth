<?php

namespace Kaely\Auth\Contracts;

interface TenantManagerInterface
{
    /**
     * Check if multitenancy is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get current tenant
     */
    public function getCurrentTenant(): ?string;

    /**
     * Set current tenant
     */
    public function setCurrentTenant(string $tenant): void;

    /**
     * Switch to specific tenant
     */
    public function switchToTenant(string $tenant): void;

    /**
     * Get all available tenants
     */
    public function getAllTenants(): array;

    /**
     * Check if tenant exists
     */
    public function tenantExists(string $tenant): bool;

    /**
     * Create tenant database
     */
    public function createTenantDatabase(string $tenant): bool;

    /**
     * Get tenant statistics
     */
    public function getTenantStats(string $tenant): array;

    /**
     * Sync user across tenants
     */
    public function syncUserAcrossTenants($user, array $tenants = []): bool;

    /**
     * Check if current request is for a tenant
     */
    public function isTenantRequest(\Illuminate\Http\Request $request): bool;
} 