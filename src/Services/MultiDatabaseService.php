<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Connection;

class MultiDatabaseService
{
    protected array $connections = [];
    protected string $mode;
    protected array $activeConnections;

    public function __construct()
    {
        $this->mode = config('kaely-auth.database.mode', 'single');
        $this->activeConnections = explode(',', config('kaely-auth.database.active_connections', 'main'));
        $this->initializeConnections();
    }

    /**
     * Initialize database connections
     */
    protected function initializeConnections(): void
    {
        if ($this->mode === 'single') {
            $this->connections['default'] = config('kaely-auth.database.default');
        } else {
            $this->connections = config('kaely-auth.database.connections', []);
        }
    }

    /**
     * Get connection configuration
     */
    public function getConnectionConfig(string $connection = null): array
    {
        $connection = $connection ?: $this->getDefaultConnection();
        
        if ($this->mode === 'single') {
            return $this->connections['default'];
        }

        return $this->connections[$connection] ?? $this->connections['main'];
    }

    /**
     * Get database connection
     */
    public function getConnection(string $connection = null): Connection
    {
        $config = $this->getConnectionConfig($connection);
        return DB::connection($config['connection']);
    }

    /**
     * Get table name with prefix
     */
    public function getTableName(string $table, string $connection = null): string
    {
        $config = $this->getConnectionConfig($connection);
        return $config['tables'][$table] ?? $table;
    }

    /**
     * Get default connection name
     */
    public function getDefaultConnection(): string
    {
        return $this->activeConnections[0] ?? 'main';
    }

    /**
     * Get all active connections
     */
    public function getActiveConnections(): array
    {
        return $this->activeConnections;
    }

    /**
     * Check if connection exists
     */
    public function hasConnection(string $connection): bool
    {
        return isset($this->connections[$connection]);
    }

    /**
     * Execute query on multiple databases
     */
    public function executeOnMultiple(array $connections, callable $callback): array
    {
        $results = [];
        
        foreach ($connections as $connection) {
            if ($this->hasConnection($connection)) {
                try {
                    $results[$connection] = $callback($this->getConnection($connection), $connection);
                } catch (\Exception $e) {
                    $results[$connection] = ['error' => $e->getMessage()];
                }
            }
        }
        
        return $results;
    }

    /**
     * Execute transaction across multiple databases
     */
    public function executeTransaction(callable $callback, array $connections = null): bool
    {
        if (!config('kaely-auth.database.cross_database_transactions', false)) {
            throw new \Exception('Cross-database transactions are disabled');
        }

        $connections = $connections ?: $this->activeConnections;
        $dbConnections = [];
        
        // Get all database connections
        foreach ($connections as $connection) {
            if ($this->hasConnection($connection)) {
                $dbConnections[$connection] = $this->getConnection($connection);
            }
        }

        // Start transactions on all connections
        foreach ($dbConnections as $connection) {
            $connection->beginTransaction();
        }

        try {
            $result = $callback($dbConnections);
            
            // Commit all transactions
            foreach ($dbConnections as $connection) {
                $connection->commit();
            }
            
            return $result;
        } catch (\Exception $e) {
            // Rollback all transactions
            foreach ($dbConnections as $connection) {
                $connection->rollBack();
            }
            
            throw $e;
        }
    }

    /**
     * Get user from multiple databases
     */
    public function getUserFromMultiple(string $email, array $connections = null): ?array
    {
        $connections = $connections ?: $this->activeConnections;
        $userModel = config('kaely-auth.models.user');
        
        foreach ($connections as $connection) {
            if ($this->hasConnection($connection)) {
                $db = $this->getConnection($connection);
                $table = $this->getTableName('users', $connection);
                
                $user = $db->table($table)->where('email', $email)->first();
                
                if ($user) {
                    return [
                        'user' => $user,
                        'connection' => $connection,
                        'table' => $table,
                    ];
                }
            }
        }
        
        return null;
    }

    /**
     * Get user permissions from multiple databases
     */
    public function getUserPermissionsFromMultiple(int $userId, array $connections = null): array
    {
        $connections = $connections ?: $this->activeConnections;
        $permissions = [];
        
        foreach ($connections as $connection) {
            if ($this->hasConnection($connection)) {
                $db = $this->getConnection($connection);
                $userRoleTable = $this->getTableName('user_role', $connection);
                $rolePermissionTable = $this->getTableName('role_permission', $connection);
                $permissionsTable = $this->getTableName('permissions', $connection);
                
                $userPermissions = $db->table($userRoleTable)
                    ->join($rolePermissionTable, 'user_role.role_id', '=', 'role_permission.role_id')
                    ->join($permissionsTable, 'role_permission.permission_id', '=', 'permissions.id')
                    ->where('user_role.user_id', $userId)
                    ->select('permissions.name', 'permissions.slug')
                    ->get();
                
                $permissions[$connection] = $userPermissions;
            }
        }
        
        return $permissions;
    }

    /**
     * Sync user across multiple databases
     */
    public function syncUserAcrossDatabases(array $userData, array $connections = null): array
    {
        $connections = $connections ?: $this->activeConnections;
        $results = [];
        
        foreach ($connections as $connection) {
            if ($this->hasConnection($connection)) {
                $db = $this->getConnection($connection);
                $table = $this->getTableName('users', $connection);
                
                try {
                    $existingUser = $db->table($table)->where('email', $userData['email'])->first();
                    
                    if ($existingUser) {
                        // Update existing user
                        $db->table($table)->where('id', $existingUser->id)->update($userData);
                        $results[$connection] = ['action' => 'updated', 'user_id' => $existingUser->id];
                    } else {
                        // Create new user
                        $userId = $db->table($table)->insertGetId($userData);
                        $results[$connection] = ['action' => 'created', 'user_id' => $userId];
                    }
                } catch (\Exception $e) {
                    $results[$connection] = ['error' => $e->getMessage()];
                }
            }
        }
        
        return $results;
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats(): array
    {
        $stats = [];
        
        foreach ($this->activeConnections as $connection) {
            if ($this->hasConnection($connection)) {
                $db = $this->getConnection($connection);
                $config = $this->getConnectionConfig($connection);
                
                try {
                    $stats[$connection] = [
                        'connection' => $config['connection'],
                        'prefix' => $config['prefix'],
                        'tables' => $this->getTableStats($db, $config),
                        'status' => 'connected',
                    ];
                } catch (\Exception $e) {
                    $stats[$connection] = [
                        'connection' => $config['connection'],
                        'prefix' => $config['prefix'],
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }
        
        return $stats;
    }

    /**
     * Get table statistics for a connection
     */
    protected function getTableStats(Connection $db, array $config): array
    {
        $stats = [];
        
        foreach ($config['tables'] as $tableName => $fullTableName) {
            try {
                $count = $db->table($fullTableName)->count();
                $stats[$tableName] = [
                    'table' => $fullTableName,
                    'count' => $count,
                ];
            } catch (\Exception $e) {
                $stats[$tableName] = [
                    'table' => $fullTableName,
                    'count' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Check if database mode is single
     */
    public function isSingleMode(): bool
    {
        return $this->mode === 'single';
    }

    /**
     * Check if database mode is multiple
     */
    public function isMultipleMode(): bool
    {
        return $this->mode === 'multiple';
    }

    /**
     * Get current database mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }
} 