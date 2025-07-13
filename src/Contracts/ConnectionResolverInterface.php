<?php

namespace Kaely\Auth\Contracts;

interface ConnectionResolverInterface
{
    /**
     * Get current database connection
     */
    public function getCurrentConnection(): string;

    /**
     * Set current database connection
     */
    public function setCurrentConnection(string $connection): void;

    /**
     * Get connection for specific tenant
     */
    public function getTenantConnection(string $tenant): string;

    /**
     * Get database name for specific tenant
     */
    public function getTenantDatabase(string $tenant): string;

    /**
     * Configure connection for tenant
     */
    public function configureTenantConnection(string $tenant, string $database): void;

    /**
     * Get all available connections
     */
    public function getAllConnections(): array;

    /**
     * Check if connection exists
     */
    public function connectionExists(string $connection): bool;

    /**
     * Get connection configuration
     */
    public function getConnectionConfig(string $connection): array;

    /**
     * Set connection configuration
     */
    public function setConnectionConfig(string $connection, array $config): void;
} 