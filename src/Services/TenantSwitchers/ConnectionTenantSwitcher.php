<?php

namespace Kaely\Auth\Services\TenantSwitchers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ConnectionTenantSwitcher implements TenantSwitcherInterface
{
    public function switch(string $tenant): void
    {
        $connection = $this->getTenantConnection($tenant);
        
        // Ensure connection exists
        if (!Config::has("database.connections.{$connection}")) {
            $this->createConnection($tenant);
        }
        
        // Set as current connection
        DB::setDefaultConnection($connection);
    }
    
    protected function getTenantConnection(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_connection_prefix', 'tenant_');
        return $prefix . $tenant;
    }
    
    protected function createConnection(string $tenant): void
    {
        $connection = $this->getTenantConnection($tenant);
        $database = $this->getTenantDatabase($tenant);
        $baseConnection = config('database.default');
        $baseConfig = config("database.connections.{$baseConnection}");
        
        Config::set("database.connections.{$connection}", array_merge($baseConfig, [
            'database' => $database,
        ]));
    }
    
    protected function getTenantDatabase(string $tenant): string
    {
        $prefix = config('kaely-auth.database.multitenancy.tenant_database_prefix', 'tenant_');
        return $prefix . $tenant;
    }
} 