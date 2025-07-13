<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Exceptions\KaelyAuthException;

class PermissionService
{
    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($user, $permission)
    {
        try {
            // Check if user is super admin
            if ($this->isSuperAdmin($user)) {
                return true;
            }

            // Get user permissions from roles
            $userPermissions = $this->getUserPermissions($user);
            
            return $userPermissions->contains('slug', $permission);
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error checking permission', [
                'user_id' => $user->id,
                'permission' => $permission,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($user, $role)
    {
        try {
            $userRoles = $this->getUserRoles($user);
            
            if (is_string($role)) {
                return $userRoles->contains('slug', $role);
            }

            return !! $role->intersect($userRoles)->count();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error checking role', [
                'user_id' => $user->id,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($user, $roles)
    {
        try {
            $userRoles = $this->getUserRoles($user);
            
            foreach ($roles as $role) {
                if ($userRoles->contains('slug', $role)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error checking any role', [
                'user_id' => $user->id,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles($user, $roles)
    {
        try {
            $userRoles = $this->getUserRoles($user);
            
            foreach ($roles as $role) {
                if (!$userRoles->contains('slug', $role)) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error checking all roles', [
                'user_id' => $user->id,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get user's permissions from roles.
     */
    public function getUserPermissions($user)
    {
        try {
            // Load roles with permissions if not already loaded
            if (!$user->relationLoaded('roles')) {
                $user->load('roles.permissions');
            }

            return $user->roles->flatMap(function ($role) {
                return $role->permissions;
            })->unique('id');
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting user permissions', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get user's roles.
     */
    public function getUserRoles($user)
    {
        try {
            // Load roles if not already loaded
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }

            return $user->roles;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting user roles', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin($user)
    {
        $superAdminRole = config('kaely-auth.permissions.super_admin_role');
        return $this->hasRole($user, $superAdminRole);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin($user)
    {
        $adminRole = config('kaely-auth.permissions.admin_role');
        return $this->hasRole($user, $adminRole);
    }

    /**
     * Get permissions by module.
     */
    public function getPermissionsByModule($moduleId)
    {
        try {
            $permissionModel = config('kaely-auth.models.permission');
            return $permissionModel::where('module_id', $moduleId)
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting permissions by module', [
                'module_id' => $moduleId,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get all permissions with modules.
     */
    public function getAllPermissions()
    {
        try {
            $permissionModel = config('kaely-auth.models.permission');
            return $permissionModel::with('module')
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting all permissions', [
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get all roles with permissions.
     */
    public function getAllRoles()
    {
        try {
            $roleModel = config('kaely-auth.models.role');
            return $roleModel::with(['permissions.module', 'roleCategory'])
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting all roles', [
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissionsToRole($roleId, $permissionIds)
    {
        try {
            $roleModel = config('kaely-auth.models.role');
            $role = $roleModel::findOrFail($roleId);
            
            $role->permissions()->sync($permissionIds);
            
            // Clear cache
            $this->clearRoleCache($role);
            
            return $role->load('permissions');
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error assigning permissions to role', [
                'role_id' => $roleId,
                'permission_ids' => $permissionIds,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to assign permissions to role: {$e->getMessage()}");
        }
    }

    /**
     * Assign roles to user.
     */
    public function assignRolesToUser($userId, $roleIds)
    {
        try {
            $userModel = config('kaely-auth.models.user');
            $user = $userModel::findOrFail($userId);
            
            $user->roles()->sync($roleIds);
            
            // Clear cache
            $this->clearUserCache($user);
            
            return $user->load('roles');
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error assigning roles to user', [
                'user_id' => $userId,
                'role_ids' => $roleIds,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to assign roles to user: {$e->getMessage()}");
        }
    }

    /**
     * Create a new permission.
     */
    public function createPermission($data)
    {
        try {
            $permissionModel = config('kaely-auth.models.permission');
            
            // Generate slug if not provided
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            
            $permission = $permissionModel::create($data);
            
            // Clear cache
            $this->clearPermissionCache();
            
            return $permission;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error creating permission', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to create permission: {$e->getMessage()}");
        }
    }

    /**
     * Create a new role.
     */
    public function createRole($data)
    {
        try {
            $roleModel = config('kaely-auth.models.role');
            
            // Generate slug if not provided
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            
            $role = $roleModel::create($data);
            
            // Clear cache
            $this->clearRoleCache($role);
            
            return $role;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error creating role', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to create role: {$e->getMessage()}");
        }
    }

    /**
     * Clear user cache.
     */
    protected function clearUserCache($user)
    {
        $cachePrefix = config('kaely-auth.cache.prefix');
        
        // Clear user-specific cache
        Cache::forget("{$cachePrefix}_user_permissions_{$user->id}");
        Cache::forget("{$cachePrefix}_user_roles_{$user->id}");
        Cache::forget("{$cachePrefix}_user_menu_{$user->id}");
    }

    /**
     * Clear role cache.
     */
    protected function clearRoleCache($role)
    {
        $cachePrefix = config('kaely-auth.cache.prefix');
        
        // Clear role-specific cache
        Cache::forget("{$cachePrefix}_role_permissions_{$role->id}");
        
        // Clear all user caches that might be affected
        Cache::flush();
    }

    /**
     * Clear permission cache.
     */
    protected function clearPermissionCache()
    {
        $cachePrefix = config('kaely-auth.cache.prefix');
        
        // Clear all permission-related cache
        Cache::flush();
    }
} 