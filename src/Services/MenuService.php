<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\Log;
use Kaely\Auth\Exceptions\KaelyAuthException;

class MenuService
{
    /**
     * Build menu for user based on permissions.
     */
    public function buildMenu($user)
    {
        try {
            // Get user's roles with permissions and modules
            $user->load(['roles.permissions.module']);

            // Create permission map for quick access
            $permissionMap = $user->roles->flatMap(function ($role) {
                return $role->permissions;
            })->keyBy('module_id');

            // Get allowed module IDs
            $allowedModuleIds = $permissionMap->keys();

            // Get all active modules (parents and children)
            $moduleModel = config('kaely-auth.models.module');
            $modules = $moduleModel::where('is_active', true)
                ->where(function ($query) use ($allowedModuleIds) {
                    $query->whereIn('id', $allowedModuleIds)
                        ->orWhereIn('parent_id', $allowedModuleIds);
                })
                ->with(['children' => function ($query) use ($allowedModuleIds) {
                    $query->where('is_active', true)
                        ->whereIn('id', $allowedModuleIds)
                        ->orderBy('order');
                }])
                ->orderBy('order')
                ->get();

            // Build menu recursively
            $menu = $this->buildMenuRecursive($modules, null, $permissionMap);

            return [
                'menu' => $menu,
                'role' => $user->roles->first()?->name ?? 'No Role',
                'permissions' => $permissionMap->pluck('slug')
            ];
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error building menu', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'menu' => [],
                'role' => 'Error',
                'permissions' => []
            ];
        }
    }

    /**
     * Build menu recursively.
     */
    protected function buildMenuRecursive($modules, $parentId = null, $permissionMap)
    {
        return $modules->where('parent_id', $parentId)
            ->filter(function ($module) use ($permissionMap) {
                return $permissionMap->has($module->id);
            })
            ->map(function ($module) use ($modules, $permissionMap) {
                $permission = $permissionMap[$module->id];

                return [
                    'id' => $module->id,
                    'parent_id' => $module->parent_id,
                    'name' => $module->name,
                    'name_es' => $module->name, // Can be translated if needed
                    'icon' => $module->icon,
                    'is_leaf' => $module->is_leaf,
                    'path' => $module->slug,
                    'permission' => $permission->slug,
                    'children' => $module->children->isNotEmpty()
                        ? $this->buildMenuRecursive($modules, $module->id, $permissionMap)
                        : []
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get all modules for admin purposes.
     */
    public function getAllModules()
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            return $moduleModel::where('is_active', true)
                ->orderBy('order')
                ->get();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting all modules', [
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get modules by parent.
     */
    public function getModulesByParent($parentId = null)
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            return $moduleModel::where('parent_id', $parentId)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting modules by parent', [
                'parent_id' => $parentId,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Get module tree.
     */
    public function getModuleTree()
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            $modules = $moduleModel::where('is_active', true)
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('order');
                }])
                ->orderBy('order')
                ->get();

            return $this->buildTreeRecursive($modules);
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error getting module tree', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Build tree recursively.
     */
    protected function buildTreeRecursive($modules, $parentId = null)
    {
        return $modules->where('parent_id', $parentId)
            ->map(function ($module) use ($modules) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'icon' => $module->icon,
                    'is_leaf' => $module->is_leaf,
                    'order' => $module->order,
                    'children' => $this->buildTreeRecursive($modules, $module->id)
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Create a new module.
     */
    public function createModule($data)
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            
            // Generate slug if not provided
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            
            $module = $moduleModel::create($data);
            
            // Clear menu cache
            $this->clearMenuCache();
            
            return $module;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error creating module', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to create module: {$e->getMessage()}");
        }
    }

    /**
     * Update module.
     */
    public function updateModule($moduleId, $data)
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            $module = $moduleModel::findOrFail($moduleId);
            
            // Generate slug if name changed and slug not provided
            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            
            $module->update($data);
            
            // Clear menu cache
            $this->clearMenuCache();
            
            return $module;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error updating module', [
                'module_id' => $moduleId,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to update module: {$e->getMessage()}");
        }
    }

    /**
     * Delete module.
     */
    public function deleteModule($moduleId)
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            $module = $moduleModel::findOrFail($moduleId);
            
            // Check if module has children
            if ($module->children()->exists()) {
                throw new KaelyAuthException("Cannot delete module with children");
            }
            
            // Check if module has permissions
            if ($module->permissions()->exists()) {
                throw new KaelyAuthException("Cannot delete module with permissions");
            }
            
            $module->delete();
            
            // Clear menu cache
            $this->clearMenuCache();
            
            return true;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error deleting module', [
                'module_id' => $moduleId,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to delete module: {$e->getMessage()}");
        }
    }

    /**
     * Reorder modules.
     */
    public function reorderModules($orderData)
    {
        try {
            $moduleModel = config('kaely-auth.models.module');
            
            foreach ($orderData as $item) {
                $moduleModel::where('id', $item['id'])->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null
                ]);
            }
            
            // Clear menu cache
            $this->clearMenuCache();
            
            return true;
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error reordering modules', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);
            
            throw new KaelyAuthException("Failed to reorder modules: {$e->getMessage()}");
        }
    }

    /**
     * Clear menu cache.
     */
    protected function clearMenuCache()
    {
        $cachePrefix = config('kaely-auth.cache.prefix');
        
        // Clear all menu-related cache
        \Illuminate\Support\Facades\Cache::flush();
    }
} 