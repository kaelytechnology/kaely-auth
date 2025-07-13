<?php

namespace Kaely\Auth\Traits;

use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Check if the model has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }

        return !! $permission->intersect($this->permissions)->count();
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission($permissions): bool
    {
        if (is_string($permissions)) {
            return $this->hasPermission($permissions);
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions($permissions): bool
    {
        if (is_string($permissions)) {
            return $this->hasPermission($permissions);
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the model.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Get permissions by module.
     */
    public function getPermissionsByModule($moduleId): Collection
    {
        return $this->permissions->where('module_id', $moduleId);
    }

    /**
     * Check if the model has a specific role.
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }

        return !! $role->intersect($this->roles)->count();
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all roles for the model.
     */
    public function getAllRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * Check if the model is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        $superAdminRole = config('kaely-auth.permissions.super_admin_role');
        return $this->hasRole($superAdminRole);
    }

    /**
     * Check if the model is an admin.
     */
    public function isAdmin(): bool
    {
        $adminRole = config('kaely-auth.permissions.admin_role');
        return $this->hasRole($adminRole);
    }

    /**
     * Assign roles to the model.
     */
    public function assignRoles($roles): void
    {
        if (is_array($roles) || $roles instanceof Collection) {
            $this->roles()->sync($roles);
        } else {
            $this->roles()->attach($roles);
        }
    }

    /**
     * Remove roles from the model.
     */
    public function removeRoles($roles): void
    {
        if (is_array($roles) || $roles instanceof Collection) {
            $this->roles()->detach($roles);
        } else {
            $this->roles()->detach($roles);
        }
    }

    /**
     * Sync roles for the model.
     */
    public function syncRoles($roles): void
    {
        $this->roles()->sync($roles);
    }

    /**
     * Get the model's permissions through roles.
     */
    public function getPermissionsThroughRoles(): Collection
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('id');
    }

    /**
     * Check if the model has permission through roles.
     */
    public function hasPermissionThroughRole($permission): bool
    {
        $permissions = $this->getPermissionsThroughRoles();
        
        if (is_string($permission)) {
            return $permissions->contains('slug', $permission);
        }

        return !! $permission->intersect($permissions)->count();
    }

    /**
     * Get the model's branches.
     */
    public function getBranches(): Collection
    {
        return $this->branches ?? collect();
    }

    /**
     * Get the model's departments.
     */
    public function getDepartments(): Collection
    {
        return $this->departments ?? collect();
    }

    /**
     * Check if the model has access to a specific branch.
     */
    public function hasBranch($branch): bool
    {
        if (is_numeric($branch)) {
            return $this->branches->contains('id', $branch);
        }

        return $this->branches->contains('slug', $branch);
    }

    /**
     * Check if the model has access to a specific department.
     */
    public function hasDepartment($department): bool
    {
        if (is_numeric($department)) {
            return $this->departments->contains('id', $department);
        }

        return $this->departments->contains('slug', $department);
    }
} 