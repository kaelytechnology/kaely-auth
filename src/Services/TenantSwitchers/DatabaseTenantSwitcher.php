<?php

namespace Kaely\Auth\Services\TenantSwitchers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DatabaseTenantSwitcher implements TenantSwitcherInterface
{
    public function switch(string $tenant): void
    {
        $connection = $this->getTenantConnection($tenant);
        $database = $this->getTenantDatabase($tenant);
        
        // Configure the tenant connection
        $this->configureConnection($connection, $database);
        
        // Set as default connection
        Config::set('database.default', $connection);
    }
    
    protected function getTenantConnection(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_connection_prefix', 'tenant_');
        return $prefix . $tenant;
    }
    
    protected function getTenantDatabase(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_database_prefix', 'tenant_');
        return $prefix . $tenant;
    }
    
    protected function configureConnection(string $connection, string $database): void
    {
        $baseConnection = config('database.default');
        $baseConfig = config("database.connections.{$baseConnection}");
        
        Config::set("database.connections.{$connection}", array_merge($baseConfig, [
            'database' => $database,
        ]));
    }
} 