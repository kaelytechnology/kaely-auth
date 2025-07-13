<?php

namespace Kaely\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Kaely\Auth\Models\Role;
use Kaely\Auth\Models\Permission;
use Kaely\Auth\Models\Module;
use Kaely\Auth\Models\RoleCategory;

class KaelyAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedRoleCategories();
        $this->seedRoles();
        $this->seedModules();
        $this->seedPermissions();
        $this->assignPermissionsToRoles();
    }

    /**
     * Seed role categories.
     */
    protected function seedRoleCategories()
    {
        $categories = [
            [
                'name' => 'Administración',
                'slug' => 'administration',
                'description' => 'Roles de administración del sistema',
                'is_active' => true,
            ],
            [
                'name' => 'Operaciones',
                'slug' => 'operations',
                'description' => 'Roles de operaciones del negocio',
                'is_active' => true,
            ],
            [
                'name' => 'Soporte',
                'slug' => 'support',
                'description' => 'Roles de soporte técnico',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            RoleCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    /**
     * Seed roles.
     */
    protected function seedRoles()
    {
        $roles = [
            [
                'name' => 'Super Administrador',
                'slug' => 'super-admin',
                'description' => 'Acceso completo al sistema',
                'role_category_id' => RoleCategory::where('slug', 'administration')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Administrador del sistema',
                'role_category_id' => RoleCategory::where('slug', 'administration')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Usuario',
                'slug' => 'user',
                'description' => 'Usuario estándar',
                'role_category_id' => RoleCategory::where('slug', 'operations')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Soporte',
                'slug' => 'support',
                'description' => 'Usuario de soporte',
                'role_category_id' => RoleCategory::where('slug', 'support')->first()->id,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }

    /**
     * Seed modules.
     */
    protected function seedModules()
    {
        $modules = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'description' => 'Panel principal',
                'icon' => 'fas fa-tachometer-alt',
                'route' => '/dashboard',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Usuarios',
                'slug' => 'users',
                'description' => 'Gestión de usuarios',
                'icon' => 'fas fa-users',
                'route' => '/users',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Roles',
                'slug' => 'roles',
                'description' => 'Gestión de roles',
                'icon' => 'fas fa-user-shield',
                'route' => '/roles',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Permisos',
                'slug' => 'permissions',
                'description' => 'Gestión de permisos',
                'icon' => 'fas fa-key',
                'route' => '/permissions',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Módulos',
                'slug' => 'modules',
                'description' => 'Gestión de módulos',
                'icon' => 'fas fa-cubes',
                'route' => '/modules',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Sucursales',
                'slug' => 'branches',
                'description' => 'Gestión de sucursales',
                'icon' => 'fas fa-building',
                'route' => '/branches',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Departamentos',
                'slug' => 'departments',
                'description' => 'Gestión de departamentos',
                'icon' => 'fas fa-sitemap',
                'route' => '/departments',
                'order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }

    /**
     * Seed permissions.
     */
    protected function seedPermissions()
    {
        $permissions = [
            // Dashboard permissions
            [
                'name' => 'Ver Dashboard',
                'slug' => 'view-dashboard',
                'description' => 'Permite ver el dashboard',
                'module_id' => Module::where('slug', 'dashboard')->first()->id,
                'is_active' => true,
            ],

            // User permissions
            [
                'name' => 'Ver Usuarios',
                'slug' => 'view-users',
                'description' => 'Permite ver la lista de usuarios',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Usuarios',
                'slug' => 'create-users',
                'description' => 'Permite crear usuarios',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Usuarios',
                'slug' => 'edit-users',
                'description' => 'Permite editar usuarios',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Usuarios',
                'slug' => 'delete-users',
                'description' => 'Permite eliminar usuarios',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Usuarios',
                'slug' => 'manage-users',
                'description' => 'Permite gestionar usuarios (CRUD completo)',
                'module_id' => Module::where('slug', 'users')->first()->id,
                'is_active' => true,
            ],

            // Role permissions
            [
                'name' => 'Ver Roles',
                'slug' => 'view-roles',
                'description' => 'Permite ver la lista de roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Roles',
                'slug' => 'create-roles',
                'description' => 'Permite crear roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Roles',
                'slug' => 'edit-roles',
                'description' => 'Permite editar roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Roles',
                'slug' => 'delete-roles',
                'description' => 'Permite eliminar roles',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Roles',
                'slug' => 'manage-roles',
                'description' => 'Permite gestionar roles (CRUD completo)',
                'module_id' => Module::where('slug', 'roles')->first()->id,
                'is_active' => true,
            ],

            // Permission permissions
            [
                'name' => 'Ver Permisos',
                'slug' => 'view-permissions',
                'description' => 'Permite ver la lista de permisos',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Permisos',
                'slug' => 'create-permissions',
                'description' => 'Permite crear permisos',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Permisos',
                'slug' => 'edit-permissions',
                'description' => 'Permite editar permisos',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Permisos',
                'slug' => 'delete-permissions',
                'description' => 'Permite eliminar permisos',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Permisos',
                'slug' => 'manage-permissions',
                'description' => 'Permite gestionar permisos (CRUD completo)',
                'module_id' => Module::where('slug', 'permissions')->first()->id,
                'is_active' => true,
            ],

            // Module permissions
            [
                'name' => 'Ver Módulos',
                'slug' => 'view-modules',
                'description' => 'Permite ver la lista de módulos',
                'module_id' => Module::where('slug', 'modules')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Módulos',
                'slug' => 'create-modules',
                'description' => 'Permite crear módulos',
                'module_id' => Module::where('slug', 'modules')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Módulos',
                'slug' => 'edit-modules',
                'description' => 'Permite editar módulos',
                'module_id' => Module::where('slug', 'modules')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Módulos',
                'slug' => 'delete-modules',
                'description' => 'Permite eliminar módulos',
                'module_id' => Module::where('slug', 'modules')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Módulos',
                'slug' => 'manage-modules',
                'description' => 'Permite gestionar módulos (CRUD completo)',
                'module_id' => Module::where('slug', 'modules')->first()->id,
                'is_active' => true,
            ],

            // Branch permissions
            [
                'name' => 'Ver Sucursales',
                'slug' => 'view-branches',
                'description' => 'Permite ver la lista de sucursales',
                'module_id' => Module::where('slug', 'branches')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Sucursales',
                'slug' => 'create-branches',
                'description' => 'Permite crear sucursales',
                'module_id' => Module::where('slug', 'branches')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Sucursales',
                'slug' => 'edit-branches',
                'description' => 'Permite editar sucursales',
                'module_id' => Module::where('slug', 'branches')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Sucursales',
                'slug' => 'delete-branches',
                'description' => 'Permite eliminar sucursales',
                'module_id' => Module::where('slug', 'branches')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Sucursales',
                'slug' => 'manage-branches',
                'description' => 'Permite gestionar sucursales (CRUD completo)',
                'module_id' => Module::where('slug', 'branches')->first()->id,
                'is_active' => true,
            ],

            // Department permissions
            [
                'name' => 'Ver Departamentos',
                'slug' => 'view-departments',
                'description' => 'Permite ver la lista de departamentos',
                'module_id' => Module::where('slug', 'departments')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Crear Departamentos',
                'slug' => 'create-departments',
                'description' => 'Permite crear departamentos',
                'module_id' => Module::where('slug', 'departments')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Editar Departamentos',
                'slug' => 'edit-departments',
                'description' => 'Permite editar departamentos',
                'module_id' => Module::where('slug', 'departments')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Eliminar Departamentos',
                'slug' => 'delete-departments',
                'description' => 'Permite eliminar departamentos',
                'module_id' => Module::where('slug', 'departments')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Gestionar Departamentos',
                'slug' => 'manage-departments',
                'description' => 'Permite gestionar departamentos (CRUD completo)',
                'module_id' => Module::where('slug', 'departments')->first()->id,
                'is_active' => true,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    /**
     * Assign permissions to roles.
     */
    protected function assignPermissionsToRoles()
    {
        // Super Admin gets all permissions
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions->pluck('id'));
        }

        // Admin gets most permissions
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = Permission::whereNotIn('slug', [
                'delete-users',
                'delete-roles',
                'delete-permissions',
                'delete-modules',
                'delete-branches',
                'delete-departments',
            ])->get();
            $adminRole->syncPermissions($adminPermissions->pluck('id'));
        }

        // User gets basic permissions
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $userPermissions = Permission::whereIn('slug', [
                'view-dashboard',
                'view-users',
                'view-branches',
                'view-departments',
            ])->get();
            $userRole->syncPermissions($userPermissions->pluck('id'));
        }

        // Support gets support permissions
        $supportRole = Role::where('slug', 'support')->first();
        if ($supportRole) {
            $supportPermissions = Permission::whereIn('slug', [
                'view-dashboard',
                'view-users',
                'view-roles',
                'view-permissions',
                'view-modules',
                'view-branches',
                'view-departments',
            ])->get();
            $supportRole->syncPermissions($supportPermissions->pluck('id'));
        }
    }
} 