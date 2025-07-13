<?php

namespace Kaely\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Services\PermissionService;
use Kaely\Auth\Services\MenuService;
use Kaely\Auth\Services\SingleDatabaseService;
use Kaely\Auth\Services\AuthenticationDetectorService;
use Kaely\Auth\Exceptions\KaelyAuthException;

class KaelyAuthManager
{
    protected $app;
    protected $auth;
    protected $permissionService;
    protected $menuService;
    protected $singleDatabaseService;
    protected $authDetectorService;

    public function __construct($app)
    {
        $this->app = $app;
        $this->auth = $app->make(AuthFactory::class);
        $this->permissionService = new PermissionService();
        $this->menuService = new MenuService();
        $this->singleDatabaseService = new SingleDatabaseService();
        $this->authDetectorService = new AuthenticationDetectorService();
        
        // Auto-detect and configure authentication
        $this->authDetectorService->updateConfig();
    }

    /**
     * Get the authenticated user with all relationships loaded.
     */
    public function getUser()
    {
        $user = $this->auth->user();
        
        if (!$user) {
            return null;
        }

        // Load relationships based on configuration
        $user->load([
            'roles.permissions.module',
            'branches',
            'departments',
            'person'
        ]);

        return $user;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        // Check cache if enabled
        if (config('kaely-auth.permissions.cache_enabled')) {
            $cacheKey = "user_permission_{$user->id}_{$permission}";
            return Cache::remember($cacheKey, config('kaely-auth.permissions.cache_ttl'), function () use ($user, $permission) {
                return $this->permissionService->hasPermission($user, $permission);
            });
        }

        return $this->permissionService->hasPermission($user, $permission);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission($permissions, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions($permissions, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission, $user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        return $this->permissionService->hasRole($user, $role);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        return $this->permissionService->hasAnyRole($user, $roles);
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles($roles, $user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        return $this->permissionService->hasAllRoles($user, $roles);
    }

    /**
     * Get user's menu based on permissions.
     */
    public function getUserMenu($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return [];
        }

        // Check cache if enabled
        if (config('kaely-auth.menu.cache_enabled')) {
            $cacheKey = "user_menu_{$user->id}";
            return Cache::remember($cacheKey, config('kaely-auth.menu.cache_ttl'), function () use ($user) {
                return $this->menuService->buildMenu($user);
            });
        }

        return $this->menuService->buildMenu($user);
    }

    /**
     * Get user's permissions.
     */
    public function getUserPermissions($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return collect();
        }

        return $this->permissionService->getUserPermissions($user);
    }

    /**
     * Get user's roles.
     */
    public function getUserRoles($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return collect();
        }

        return $this->permissionService->getUserRoles($user);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        $superAdminRole = config('kaely-auth.permissions.super_admin_role');
        return $this->hasRole($superAdminRole, $user);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return false;
        }

        $adminRole = config('kaely-auth.permissions.admin_role');
        return $this->hasRole($adminRole, $user);
    }

    /**
     * Get user's accessible branches.
     */
    public function getUserBranches($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return collect();
        }

        return $this->singleDatabaseService->getUserBranches($user);
    }

    /**
     * Get user's accessible departments.
     */
    public function getUserDepartments($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return collect();
        }

        return $this->singleDatabaseService->getUserDepartments($user);
    }

    /**
     * Clear user's permission cache.
     */
    public function clearUserCache($user = null)
    {
        $user = $user ?: $this->getUser();
        
        if (!$user) {
            return;
        }

        $cachePrefix = config('kaely-auth.cache.prefix');
        
        // Clear permission cache
        Cache::forget("{$cachePrefix}_user_permission_{$user->id}");
        
        // Clear menu cache
        Cache::forget("{$cachePrefix}_user_menu_{$user->id}");
        
        // Clear all user-related cache
        Cache::flush();
    }

    /**
     * Validate database relationships.
     */
    public function validateDatabaseRelations()
    {
        return $this->singleDatabaseService->validateRelations();
    }

    /**
     * Get database status.
     */
    public function getDatabaseStatus()
    {
        return $this->singleDatabaseService->getDatabaseStatus();
    }

    /**
     * Get table statistics.
     */
    public function getTableStats()
    {
        return $this->singleDatabaseService->getTableStats();
    }

    /**
     * Optimize database tables.
     */
    public function optimizeTables()
    {
        return $this->singleDatabaseService->optimizeTables();
    }

    /**
     * Create database indexes.
     */
    public function createIndexes()
    {
        return $this->singleDatabaseService->createIndexes();
    }

    /**
     * Get authentication statistics.
     */
    public function getAuthStats()
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'active_users' => $this->getActiveUsers(),
            'total_roles' => $this->getTotalRoles(),
            'total_permissions' => $this->getTotalPermissions(),
            'cache_status' => $this->getCacheStatus(),
            'database_status' => $this->getDatabaseStatus(),
            'table_stats' => $this->getTableStats(),
        ];
    }

    /**
     * Get total users count.
     */
    protected function getTotalUsers()
    {
        $userModel = config('kaely-auth.models.user');
        return $userModel::count();
    }

    /**
     * Get active users count.
     */
    protected function getActiveUsers()
    {
        $userModel = config('kaely-auth.models.user');
        return $userModel::where('is_active', true)->count();
    }

    /**
     * Get total roles count.
     */
    protected function getTotalRoles()
    {
        $roleModel = config('kaely-auth.models.role');
        return $roleModel::count();
    }

    /**
     * Get total permissions count.
     */
    protected function getTotalPermissions()
    {
        $permissionModel = config('kaely-auth.models.permission');
        return $permissionModel::count();
    }

    /**
     * Get cache status.
     */
    protected function getCacheStatus()
    {
        return [
            'enabled' => config('kaely-auth.permissions.cache_enabled'),
            'store' => config('kaely-auth.cache.store'),
            'prefix' => config('kaely-auth.cache.prefix'),
        ];
    }
} 