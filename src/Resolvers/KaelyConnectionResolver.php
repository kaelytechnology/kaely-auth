<?php

namespace Kaely\Auth\Resolvers;

use Kaely\Auth\Contracts\ConnectionResolverInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class KaelyConnectionResolver implements ConnectionResolverInterface
{
    protected $config;
    protected $currentConnection;

    public function __construct()
    {
        $this->config = config('kaely-auth.database');
        $this->currentConnection = config('database.default');
    }

    /**
     * Get current database connection
     */
    public function getCurrentConnection(): string
    {
        return $this->currentConnection;
    }

    /**
     * Set current database connection
     */
    public function setCurrentConnection(string $connection): void
    {
        $this->currentConnection = $connection;
        Config::set('database.default', $connection);
    }

    /**
     * Get connection for specific tenant
     */
    public function getTenantConnection(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_connection_prefix', 'tenant_');
        return $prefix . $tenant;
    }

    /**
     * Get database name for specific tenant
     */
    public function getTenantDatabase(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_database_prefix', 'tenant_');
        return $prefix . $tenant;
    }

    /**
     * Configure connection for tenant
     */
    public function configureTenantConnection(string $tenant, string $database): void
    {
        $connection = $this->getTenantConnection($tenant);
        $baseConnection = config('database.default');
        $baseConfig = config("database.connections.{$baseConnection}");

        Config::set("database.connections.{$connection}", array_merge($baseConfig, [
            'database' => $database,
        ]));
    }

    /**
     * Get all available connections
     */
    public function getAllConnections(): array
    {
        $mode = $this->config['mode'] ?? 'single';
        
        if ($mode === 'single') {
            return [$this->currentConnection];
        }
        
        if ($mode === 'multi') {
            return array_keys($this->config['connections'] ?? []);
        }
        
        if ($mode === 'tenant') {
            // Return tenant connections
            $tenants = ['main', 'tenant1', 'tenant2', 'tenant3']; // This could be dynamic
            $connections = [];
            
            foreach ($tenants as $tenant) {
                $connections[] = $this->getTenantConnection($tenant);
            }
            
            return $connections;
        }
        
        return [$this->currentConnection];
    }

    /**
     * Check if connection exists
     */
    public function connectionExists(string $connection): bool
    {
        return Config::has("database.connections.{$connection}");
    }

    /**
     * Get connection configuration
     */
    public function getConnectionConfig(string $connection): array
    {
        return Config::get("database.connections.{$connection}", []);
    }

    /**
     * Set connection configuration
     */
    public function setConnectionConfig(string $connection, array $config): void
    {
        Config::set("database.connections.{$connection}", $config);
    }

    /**
     * Get connection for multi-database mode
     */
    public function getMultiDatabaseConnection(string $name): string
    {
        $connections = $this->config['connections'] ?? [];
        return $connections[$name]['connection'] ?? $name;
    }

    /**
     * Get prefix for multi-database mode
     */
    public function getMultiDatabasePrefix(string $name): string
    {
        $connections = $this->config['connections'] ?? [];
        return $connections[$name]['prefix'] ?? $name . '_';
    }

    /**
     * Test connection
     */
    public function testConnection(string $connection): bool
    {
        try {
            DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get connection statistics
     */
    public function getConnectionStats(string $connection): array
    {
        if (!$this->testConnection($connection)) {
            return ['error' => 'Connection failed'];
        }

        try {
            $tables = DB::connection($connection)->select('SHOW TABLES');
            $tableCount = count($tables);
            
            return [
                'connection' => $connection,
                'tables' => $tableCount,
                'status' => 'connected',
            ];
        } catch (\Exception $e) {
            return [
                'connection' => $connection,
                'error' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }
} 