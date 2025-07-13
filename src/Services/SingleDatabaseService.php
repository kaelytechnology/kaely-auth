<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Exceptions\KaelyAuthException;

class SingleDatabaseService
{
    /**
     * Get the database connection.
     */
    public function getConnection()
    {
        $connection = config('kaely-auth.database.connection');
        return DB::connection($connection);
    }

    /**
     * Get table name with prefix.
     */
    public function getTableName($table)
    {
        $tables = config('kaely-auth.database.tables');
        return $tables[$table] ?? $table;
    }

    /**
     * Get user's accessible branches.
     */
    public function getUserBranches($user)
    {
        try {
            // Load user branches if not already loaded
            if (!$user->relationLoaded('branches')) {
                $user->load('branches');
            }

            return $user->branches;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting user branches', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get user's accessible departments.
     */
    public function getUserDepartments($user)
    {
        try {
            // Load user departments if not already loaded
            if (!$user->relationLoaded('departments')) {
                $user->load('departments');
            }

            return $user->departments;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting user departments', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Validate database relationships.
     */
    public function validateRelations()
    {
        try {
            $validationResults = [];

            // Validate user-role relationships
            $validationResults['user_roles'] = $this->validateUserRoleRelations();

            // Validate role-permission relationships
            $validationResults['role_permissions'] = $this->validateRolePermissionRelations();

            // Validate module-permission relationships
            $validationResults['module_permissions'] = $this->validateModulePermissionRelations();

            // Validate branch-user relationships
            $validationResults['branch_users'] = $this->validateBranchUserRelations();

            // Validate department-user relationships
            $validationResults['department_users'] = $this->validateDepartmentUserRelations();

            return $validationResults;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error validating relations', [
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to validate relations: {$e->getMessage()}");
        }
    }

    /**
     * Validate user-role relationships.
     */
    protected function validateUserRoleRelations()
    {
        try {
            $userModel = config('kaely-auth.models.user');
            $roleModel = config('kaely-auth.models.role');

            $orphanedUsers = $userModel::whereDoesntHave('roles')->count();
            $orphanedRoles = $roleModel::whereDoesntHave('users')->count();

            return [
                'status' => 'success',
                'orphaned_users' => $orphanedUsers,
                'orphaned_roles' => $orphanedRoles,
                'message' => 'User-role relationships validated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate role-permission relationships.
     */
    protected function validateRolePermissionRelations()
    {
        try {
            $roleModel = config('kaely-auth.models.role');
            $permissionModel = config('kaely-auth.models.permission');

            $orphanedRoles = $roleModel::whereDoesntHave('permissions')->count();
            $orphanedPermissions = $permissionModel::whereDoesntHave('roles')->count();

            return [
                'status' => 'success',
                'orphaned_roles' => $orphanedRoles,
                'orphaned_permissions' => $orphanedPermissions,
                'message' => 'Role-permission relationships validated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate module-permission relationships.
     */
    protected function validateModulePermissionRelations()
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            $permissionModel = config('kaely-auth.models.permission');

            $orphanedModules = $moduleModel::whereDoesntHave('permissions')->count();
            $orphanedPermissions = $permissionModel::whereDoesntHave('module')->count();

            return [
                'status' => 'success',
                'orphaned_modules' => $orphanedModules,
                'orphaned_permissions' => $orphanedPermissions,
                'message' => 'Module-permission relationships validated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate branch-user relationships.
     */
    protected function validateBranchUserRelations()
    {
        try {
            $branchModel = config('kaely-auth.models.branch');
            $userModel = config('kaely-auth.models.user');

            $orphanedBranches = $branchModel::whereDoesntHave('users')->count();
            $orphanedUsers = $userModel::whereDoesntHave('branches')->count();

            return [
                'status' => 'success',
                'orphaned_branches' => $orphanedBranches,
                'orphaned_users' => $orphanedUsers,
                'message' => 'Branch-user relationships validated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate department-user relationships.
     */
    protected function validateDepartmentUserRelations()
    {
        try {
            $departmentModel = config('kaely-auth.models.department');
            $userModel = config('kaely-auth.models.user');

            $orphanedDepartments = $departmentModel::whereDoesntHave('users')->count();
            $orphanedUsers = $userModel::whereDoesntHave('departments')->count();

            return [
                'status' => 'success',
                'orphaned_departments' => $orphanedDepartments,
                'orphaned_users' => $orphanedUsers,
                'message' => 'Department-user relationships validated'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get database status.
     */
    public function getDatabaseStatus()
    {
        try {
            $connection = $this->getConnection();
            $connection->getPdo();

            return [
                'status' => 'connected',
                'connection' => config('kaely-auth.database.connection'),
                'prefix' => config('kaely-auth.database.prefix'),
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'connection' => config('kaely-auth.database.connection'),
                'prefix' => config('kaely-auth.database.prefix'),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute database transaction.
     */
    public function executeTransaction($callback)
    {
        try {
            $connection = $this->getConnection();
            
            return $connection->transaction(function () use ($callback) {
                return $callback();
            });
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error executing transaction', [
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to execute transaction: {$e->getMessage()}");
        }
    }

    /**
     * Get table statistics.
     */
    public function getTableStats()
    {
        try {
            $connection = $this->getConnection();
            $tables = config('kaely-auth.database.tables');
            $stats = [];

            foreach ($tables as $tableName => $fullTableName) {
                try {
                    $count = $connection->table($fullTableName)->count();
                    $stats[$tableName] = [
                        'table' => $fullTableName,
                        'count' => $count,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $stats[$tableName] = [
                        'table' => $fullTableName,
                        'count' => 0,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting table stats', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Optimize database tables.
     */
    public function optimizeTables()
    {
        try {
            $connection = $this->getConnection();
            $tables = config('kaely-auth.database.tables');
            $results = [];

            foreach ($tables as $tableName => $fullTableName) {
                try {
                    $connection->statement("OPTIMIZE TABLE {$fullTableName}");
                    $results[$tableName] = [
                        'status' => 'success',
                        'message' => 'Table optimized successfully'
                    ];
                } catch (\Exception $e) {
                    $results[$tableName] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error optimizing tables', [
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to optimize tables: {$e->getMessage()}");
        }
    }

    /**
     * Create database indexes for better performance.
     */
    public function createIndexes()
    {
        try {
            $connection = $this->getConnection();
            $results = [];

            // Get users table name from config
            $usersTable = config('kaely-auth.database.tables.users');

            // Create indexes for better performance
            $indexes = [
                $usersTable => [
                    'idx_email' => "CREATE INDEX idx_email ON {$usersTable}(email)",
                    'idx_is_active' => "CREATE INDEX idx_is_active ON {$usersTable}(is_active)"
                ],
                'main_roles' => [
                    'idx_slug' => 'CREATE INDEX idx_slug ON main_roles(slug)',
                    'idx_is_active' => 'CREATE INDEX idx_is_active ON main_roles(is_active)'
                ],
                'main_permissions' => [
                    'idx_slug' => 'CREATE INDEX idx_slug ON main_permissions(slug)',
                    'idx_module_id' => 'CREATE INDEX idx_module_id ON main_permissions(module_id)'
                ],
                'main_modules' => [
                    'idx_slug' => 'CREATE INDEX idx_slug ON main_modules(slug)',
                    'idx_parent_id' => 'CREATE INDEX idx_parent_id ON main_modules(parent_id)'
                ],
                'main_user_role' => [
                    'idx_user_role' => 'CREATE INDEX idx_user_role ON main_user_role(user_id, role_id)'
                ],
                'main_role_permission' => [
                    'idx_role_permission' => 'CREATE INDEX idx_role_permission ON main_role_permission(role_id, permission_id)'
                ]
            ];

            foreach ($indexes as $table => $tableIndexes) {
                foreach ($tableIndexes as $indexName => $sql) {
                    try {
                        $connection->statement($sql);
                        $results[$table][$indexName] = [
                            'status' => 'success',
                            'message' => 'Index created successfully'
                        ];
                    } catch (\Exception $e) {
                        $results[$table][$indexName] = [
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ];
                    }
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error creating indexes', [
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to create indexes: {$e->getMessage()}");
        }
    }
} 