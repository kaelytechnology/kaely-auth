<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class OptimizedQueryService
{
    /**
     * Get user with optimized eager loading
     */
    public function getUserWithRelations(int $userId, array $relations = []): ?object
    {
        $cacheKey = "user_with_relations_{$userId}_" . md5(serialize($relations));
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $relations) {
            $query = DB::table('users')->where('id', $userId);
            
            // Add relations based on what's needed
            if (in_array('roles', $relations)) {
                $query->leftJoin('user_role', 'users.id', '=', 'user_role.user_id')
                      ->leftJoin('roles', 'user_role.role_id', '=', 'roles.id');
            }
            
            if (in_array('permissions', $relations)) {
                $query->leftJoin('user_permissions', 'users.id', '=', 'user_permissions.user_id')
                      ->leftJoin('permissions', 'user_permissions.permission_id', '=', 'permissions.id');
            }
            
            return $query->first();
        });
    }

    /**
     * Get user permissions with caching
     */
    public function getUserPermissions(int $userId): array
    {
        return Cache::remember("user_permissions_{$userId}", 3600, function () use ($userId) {
            return DB::table('permissions')
                ->join('user_permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->where('user_permissions.user_id', $userId)
                ->pluck('permissions.name')
                ->toArray();
        });
    }

    /**
     * Get user roles with caching
     */
    public function getUserRoles(int $userId): array
    {
        return Cache::remember("user_roles_{$userId}", 3600, function () use ($userId) {
            return DB::table('roles')
                ->join('user_role', 'roles.id', '=', 'user_role.role_id')
                ->where('user_role.user_id', $userId)
                ->pluck('roles.name')
                ->toArray();
        });
    }

    /**
     * Get audit logs with pagination and filtering
     */
    public function getAuditLogs(array $filters = [], int $perPage = 50): array
    {
        $cacheKey = "audit_logs_" . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, 1800, function () use ($filters, $perPage) {
            $query = DB::table('audit_logs');
            
            // Apply filters
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (isset($filters['action'])) {
                $query->where('action', $filters['action']);
            }
            
            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            return [
                'data' => $query->orderBy('created_at', 'desc')->paginate($perPage),
                'total' => $query->count(),
            ];
        });
    }

    /**
     * Get session statistics with caching
     */
    public function getSessionStats(): array
    {
        return Cache::remember('session_stats', 1800, function () {
            return [
                'total_sessions' => DB::table('user_sessions')->count(),
                'active_sessions' => DB::table('user_sessions')->where('active', true)->count(),
                'today_sessions' => DB::table('user_sessions')
                    ->whereDate('created_at', today())
                    ->count(),
                'avg_session_duration' => DB::table('user_sessions')
                    ->whereNotNull('last_activity')
                    ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, last_activity)')),
            ];
        });
    }

    /**
     * Bulk insert with chunking for performance
     */
    public function bulkInsertAuditLogs(array $logs): void
    {
        // Process in chunks to avoid memory issues
        $chunks = array_chunk($logs, 1000);
        
        foreach ($chunks as $chunk) {
            DB::table('audit_logs')->insert($chunk);
        }
    }

    /**
     * Optimize database tables
     */
    public function optimizeTables(): array
    {
        $tables = ['audit_logs', 'user_sessions', 'email_verifications', 'password_reset_tokens'];
        $results = [];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                try {
                    DB::statement("OPTIMIZE TABLE {$table}");
                    $results[$table] = 'optimized';
                } catch (\Exception $e) {
                    $results[$table] = 'error: ' . $e->getMessage();
                }
            }
        }
        
        return $results;
    }

    /**
     * Create indexes for better performance
     */
    public function createIndexes(): array
    {
        $indexes = [
            'audit_logs' => [
                'user_id' => 'CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id)',
                'action' => 'CREATE INDEX idx_audit_logs_action ON audit_logs(action)',
                'created_at' => 'CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at)',
            ],
            'user_sessions' => [
                'user_id' => 'CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id)',
                'session_id' => 'CREATE INDEX idx_user_sessions_session_id ON user_sessions(session_id)',
                'active' => 'CREATE INDEX idx_user_sessions_active ON user_sessions(active)',
            ],
        ];
        
        $results = [];
        
        foreach ($indexes as $table => $tableIndexes) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                foreach ($tableIndexes as $indexName => $sql) {
                    try {
                        DB::statement($sql);
                        $results["{$table}.{$indexName}"] = 'created';
                    } catch (\Exception $e) {
                        $results["{$table}.{$indexName}"] = 'error: ' . $e->getMessage();
                    }
                }
            }
        }
        
        return $results;
    }
} 