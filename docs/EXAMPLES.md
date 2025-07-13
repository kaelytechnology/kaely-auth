# Ejemplos Prácticos - KaelyAuth

## 📚 Casos de Uso Comunes

Esta sección contiene ejemplos prácticos de cómo usar KaelyAuth en diferentes escenarios.

## 🔐 Autenticación Básica

### Login con Permisos y Menú

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\KaelyAuthManager;

class AuthController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'permissions' => $this->authManager->getUserPermissions($user),
                'roles' => $user->getRoleNames(),
                'menu' => $this->authManager->getUserMenu($user),
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales inválidas'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'roles' => $user->getRoleNames(),
            'menu' => $this->authManager->getUserMenu($user),
        ]);
    }
}
```

### Middleware de Autenticación Personalizado

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaelyAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'No autenticado',
                'message' => 'Debe iniciar sesión para acceder a este recurso'
            ], 401);
        }

        $user = Auth::user();
        
        // Verificar si el usuario está activo
        if (!$user->is_active) {
            return response()->json([
                'error' => 'Usuario inactivo',
                'message' => 'Su cuenta ha sido desactivada'
            ], 403);
        }

        // Agregar información del usuario a la request
        $request->merge([
            'current_user' => $user,
            'user_permissions' => $user->getAllPermissions()->pluck('name'),
            'user_roles' => $user->getRoleNames(),
        ]);

        return $next($request);
    }
}
```

## 👥 Gestión de Usuarios

### Controlador de Usuarios con Permisos

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Kaely\Auth\KaelyAuthManager;

class UserController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
        $this->middleware('kaely.permission:view-users');
    }

    public function index(Request $request)
    {
        $users = User::with(['roles', 'permissions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'users' => $users,
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->middleware('kaely.permission:create-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Asignar roles
        if (!empty($validated['roles'])) {
            $user->assignRoles($validated['roles']);
        }

        // Asignar permisos directos
        if (!empty($validated['permissions'])) {
            $user->givePermissionsTo($validated['permissions']);
        }

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user->load(['roles', 'permissions'])
        ], 201);
    }

    public function show(User $user)
    {
        $this->middleware('kaely.permission:view-users');

        return response()->json([
            'user' => $user->load(['roles', 'permissions']),
            'permissions' => $user->getAllPermissions(),
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->middleware('kaely.permission:edit-users');

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'permissions' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        // Actualizar roles
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // Actualizar permisos
        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user->load(['roles', 'permissions'])
        ]);
    }

    public function destroy(User $user)
    {
        $this->middleware('kaely.permission:delete-users');

        // No permitir eliminar el propio usuario
        if ($user->id === auth()->id()) {
            return response()->json([
                'error' => 'No puede eliminar su propia cuenta'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    public function assignRoles(Request $request, User $user)
    {
        $this->middleware('kaely.permission:assign-roles');

        $validated = $request->validate([
            'roles' => 'required|array',
        ]);

        $user->assignRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles asignados exitosamente',
            'user' => $user->load('roles')
        ]);
    }

    public function permissions(User $user)
    {
        $this->middleware('kaely.permission:view-users');

        return response()->json([
            'permissions' => $user->getAllPermissions(),
            'direct_permissions' => $user->getDirectPermissions(),
            'role_permissions' => $user->getPermissionsViaRoles(),
        ]);
    }
}
```

## 🎭 Gestión de Roles

### Controlador de Roles

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Kaely\Auth\KaelyAuthManager;

class RoleController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
        $this->middleware('kaely.permission:view-roles');
    }

    public function index(Request $request)
    {
        $roles = Role::with(['permissions', 'users'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            })
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'roles' => $roles,
            'categories' => $this->authManager->getRoleCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $this->middleware('kaely.permission:create-roles');

        $validated = $request->validate([
            'name' => 'required|string|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'category_id' => 'nullable|exists:role_categories,id',
        ]);

        $role = Role::create($validated);

        if (!empty($validated['permissions'])) {
            $role->givePermissionsTo($validated['permissions']);
        }

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role->load('permissions')
        ], 201);
    }

    public function show(Role $role)
    {
        return response()->json([
            'role' => $role->load(['permissions', 'users']),
            'permissions' => $role->getAllPermissions(),
            'users_count' => $role->users()->count(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->middleware('kaely.permission:edit-roles');

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'sometimes|array',
            'category_id' => 'nullable|exists:role_categories,id',
        ]);

        $role->update($validated);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role->load('permissions')
        ]);
    }

    public function destroy(Role $role)
    {
        $this->middleware('kaely.permission:delete-roles');

        // Verificar que no sea un rol del sistema
        if ($role->is_system) {
            return response()->json([
                'error' => 'No se puede eliminar un rol del sistema'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente'
        ]);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $this->middleware('kaely.permission:assign-permissions');

        $validated = $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'role' => $role->load('permissions')
        ]);
    }

    public function users(Role $role)
    {
        $users = $role->users()->paginate(15);

        return response()->json([
            'role' => $role,
            'users' => $users,
        ]);
    }
}
```

## 🔐 Gestión de Permisos

### Controlador de Permisos

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Kaely\Auth\KaelyAuthManager;

class PermissionController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
        $this->middleware('kaely.permission:view-permissions');
    }

    public function index(Request $request)
    {
        $permissions = Permission::with(['roles', 'module'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%");
            })
            ->when($request->module, function ($query, $module) {
                $query->whereHas('module', function ($q) use ($module) {
                    $q->where('name', $module);
                });
            })
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'permissions' => $permissions,
            'modules' => $this->authManager->getModules(),
        ]);
    }

    public function store(Request $request)
    {
        $this->middleware('kaely.permission:create-permissions');

        $validated = $request->validate([
            'name' => 'required|string|unique:permissions',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ]);

        $permission = Permission::create($validated);

        return response()->json([
            'message' => 'Permiso creado exitosamente',
            'permission' => $permission->load('module')
        ], 201);
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'permission' => $permission->load(['roles', 'module']),
            'roles_count' => $permission->roles()->count(),
            'users_count' => $permission->users()->count(),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->middleware('kaely.permission:edit-permissions');

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ]);

        $permission->update($validated);

        return response()->json([
            'message' => 'Permiso actualizado exitosamente',
            'permission' => $permission->load('module')
        ]);
    }

    public function destroy(Permission $permission)
    {
        $this->middleware('kaely.permission:delete-permissions');

        // Verificar que no sea un permiso del sistema
        if ($permission->is_system) {
            return response()->json([
                'error' => 'No se puede eliminar un permiso del sistema'
            ], 403);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado exitosamente'
        ]);
    }

    public function byModule($module)
    {
        $permissions = Permission::whereHas('module', function ($query) use ($module) {
            $query->where('name', $module);
        })->get();

        return response()->json([
            'module' => $module,
            'permissions' => $permissions,
        ]);
    }

    public function roles(Permission $permission)
    {
        $roles = $permission->roles()->paginate(15);

        return response()->json([
            'permission' => $permission,
            'roles' => $roles,
        ]);
    }
}
```

## 🍽️ Gestión de Menús

### Controlador de Menús

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Kaely\Auth\KaelyAuthManager;

class MenuController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function userMenu(Request $request)
    {
        $user = $request->user();
        
        $menu = $this->authManager->getUserMenu($user);

        return response()->json([
            'menu' => $menu,
            'user' => $user,
        ]);
    }

    public function allModules()
    {
        $modules = Module::with(['permissions'])
            ->orderBy('order')
            ->get();

        return response()->json([
            'modules' => $modules,
        ]);
    }

    public function reorder(Request $request)
    {
        $this->middleware('kaely.permission:manage-menu');

        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['modules'] as $moduleData) {
            Module::where('id', $moduleData['id'])
                ->update(['order' => $moduleData['order']]);
        }

        return response()->json([
            'message' => 'Orden de módulos actualizado exitosamente'
        ]);
    }

    public function store(Request $request)
    {
        $this->middleware('kaely.permission:create-modules');

        $validated = $request->validate([
            'name' => 'required|string|unique:modules',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $module = Module::create($validated);

        return response()->json([
            'message' => 'Módulo creado exitosamente',
            'module' => $module
        ], 201);
    }

    public function update(Request $request, Module $module)
    {
        $this->middleware('kaely.permission:edit-modules');

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $module->update($validated);

        return response()->json([
            'message' => 'Módulo actualizado exitosamente',
            'module' => $module
        ]);
    }
}
```

## 🔄 OAuth/Socialite

### Controlador OAuth

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kaely\Auth\Services\OAuthService;

class OAuthController extends Controller
{
    protected $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function redirect($provider)
    {
        if (!$this->oauthService->isProviderEnabled($provider)) {
            return response()->json([
                'error' => 'Proveedor OAuth no habilitado'
            ], 400);
        }

        $redirectUrl = $this->oauthService->getRedirectUrl($provider);

        return response()->json([
            'redirect_url' => $redirectUrl
        ]);
    }

    public function callback($provider, Request $request)
    {
        try {
            $result = $this->oauthService->handleCallback($provider);

            return response()->json([
                'success' => true,
                'user' => $result['user'],
                'token' => $result['token'],
                'message' => 'Autenticación OAuth exitosa'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function providers()
    {
        $providers = $this->oauthService->getEnabledProviders();

        return response()->json([
            'providers' => $providers
        ]);
    }

    public function stats()
    {
        $stats = $this->oauthService->getStats();

        return response()->json([
            'stats' => $stats
        ]);
    }

    public function syncUser(Request $request)
    {
        $user = $request->user();
        
        $this->oauthService->syncOAuthUser($user);

        return response()->json([
            'message' => 'Usuario sincronizado exitosamente'
        ]);
    }

    public function disconnect(Request $request)
    {
        $user = $request->user();
        
        $this->oauthService->disconnectUser($user);

        return response()->json([
            'message' => 'Cuenta OAuth desconectada exitosamente'
        ]);
    }

    public function linkAccount($provider, Request $request)
    {
        $user = $request->user();
        
        try {
            $this->oauthService->linkAccount($user, $provider);

            return response()->json([
                'message' => 'Cuenta vinculada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

## 🗄️ Multi-Base de Datos

### Servicio Multi-Base de Datos

```php
<?php

namespace App\Services;

use Kaely\Auth\Services\MultiDatabaseService;

class UserSyncService
{
    protected $dbService;

    public function __construct(MultiDatabaseService $dbService)
    {
        $this->dbService = $dbService;
    }

    public function syncUserAcrossDatabases($user)
    {
        $connections = config('kaely-auth.database.active_connections');
        
        $results = $this->dbService->executeOnMultiple($connections, function($db, $connection) use ($user) {
            // Verificar si el usuario existe
            $existingUser = $db->table('users')->where('email', $user->email)->first();
            
            if (!$existingUser) {
                // Crear usuario en esta base de datos
                $userId = $db->table('users')->insertGetId([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                return ['action' => 'created', 'user_id' => $userId];
            } else {
                // Actualizar usuario existente
                $db->table('users')
                    ->where('email', $user->email)
                    ->update([
                        'name' => $user->name,
                        'updated_at' => now(),
                    ]);
                
                return ['action' => 'updated', 'user_id' => $existingUser->id];
            }
        });

        return $results;
    }

    public function getUserStats()
    {
        $connections = config('kaely-auth.database.active_connections');
        
        $stats = $this->dbService->executeOnMultiple($connections, function($db, $connection) {
            return [
                'connection' => $connection,
                'total_users' => $db->table('users')->count(),
                'active_users' => $db->table('users')->where('is_active', true)->count(),
                'inactive_users' => $db->table('users')->where('is_active', false)->count(),
            ];
        });

        return $stats;
    }

    public function cleanupInactiveUsers()
    {
        $connections = config('kaely-auth.database.active_connections');
        
        $results = $this->dbService->executeTransaction(function($connections) {
            $deletedCount = 0;
            
            foreach ($connections as $connection) {
                $deleted = DB::connection($connection)
                    ->table('users')
                    ->where('is_active', false)
                    ->where('last_login_at', '<', now()->subMonths(6))
                    ->delete();
                
                $deletedCount += $deleted;
            }
            
            return $deletedCount;
        }, $connections);

        return $results;
    }
}
```

## 🎨 Vistas Blade

### Componentes de Menú

```blade
{{-- resources/views/components/menu.blade.php --}}
@props(['menu'])

<nav class="sidebar">
    <ul class="menu-list">
        @foreach($menu as $module)
            @if($module['is_active'])
                <li class="menu-item">
                    <a href="{{ $module['route'] ?? '#' }}" 
                       class="menu-link {{ request()->is($module['route'] ?? '') ? 'active' : '' }}">
                        @if($module['icon'])
                            <i class="{{ $module['icon'] }}"></i>
                        @endif
                        <span>{{ $module['display_name'] }}</span>
                    </a>
                    
                    @if(isset($module['children']) && count($module['children']) > 0)
                        <ul class="submenu">
                            @foreach($module['children'] as $child)
                                @permission($child['permission'])
                                    <li>
                                        <a href="{{ $child['route'] }}" 
                                           class="{{ request()->is($child['route']) ? 'active' : '' }}">
                                            {{ $child['display_name'] }}
                                        </a>
                                    </li>
                                @endpermission
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</nav>
```

### Componente de Permisos

```blade
{{-- resources/views/components/permission-check.blade.php --}}
@props(['permission', 'fallback' => null])

@permission($permission)
    {{ $slot }}
@else
    @if($fallback)
        {{ $fallback }}
    @endif
@endpermission
```

### Componente de Roles

```blade
{{-- resources/views/components/role-check.blade.php --}}
@props(['role', 'fallback' => null])

@role($role)
    {{ $slot }}
@else
    @if($fallback)
        {{ $fallback }}
    @endif
@endrole
```

### Dashboard con Permisos

```blade
{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        @permission('view-user-stats')
            <div class="stat-card">
                <h3>Usuarios</h3>
                <p>{{ $userCount }}</p>
            </div>
        @endpermission
        
        @permission('view-sales-stats')
            <div class="stat-card">
                <h3>Ventas</h3>
                <p>{{ $salesCount }}</p>
            </div>
        @endpermission
        
        @permission('view-inventory-stats')
            <div class="stat-card">
                <h3>Inventario</h3>
                <p>{{ $inventoryCount }}</p>
            </div>
        @endpermission
    </div>
    
    <div class="actions">
        @permission('create-users')
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                Crear Usuario
            </a>
        @endpermission
        
        @permission('manage-roles')
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                Gestionar Roles
            </a>
        @endpermission
        
        @role('admin')
            <a href="{{ route('admin.settings') }}" class="btn btn-warning">
                Configuración
            </a>
        @endrole
    </div>
</div>
@endsection
```

## 🔧 Rutas API

### Rutas con Macros

```php
<?php
// routes/api.php

use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Rutas protegidas con permisos
Route::prefix('api/v1')->middleware(['auth:sanctum', 'kaely.auth'])->group(function () {
    
    // Rutas de usuarios
    Route::permission('view-users')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });
    
    Route::permission('create-users')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });
    
    Route::permission('edit-users')->group(function () {
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::post('/users/{user}/roles', [UserController::class, 'assignRoles']);
    });
    
    Route::permission('delete-users')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
    
    // Rutas de roles
    Route::permission('view-roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
    });
    
    Route::permission('create-roles')->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
    });
    
    Route::permission('edit-roles')->group(function () {
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    });
    
    Route::permission('delete-roles')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    });
    
    // Rutas de permisos
    Route::permission('view-permissions')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{permission}', [PermissionController::class, 'show']);
        Route::get('/permissions/by-module/{module}', [PermissionController::class, 'byModule']);
    });
    
    Route::permission('create-permissions')->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
    });
    
    Route::permission('edit-permissions')->group(function () {
        Route::put('/permissions/{permission}', [PermissionController::class, 'update']);
    });
    
    Route::permission('delete-permissions')->group(function () {
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
    });
    
    // Rutas de menú
    Route::get('/menu/user', [MenuController::class, 'userMenu']);
    Route::get('/menu/all', [MenuController::class, 'allModules']);
    
    Route::permission('manage-menu')->group(function () {
        Route::post('/menu/reorder', [MenuController::class, 'reorder']);
        Route::post('/menu', [MenuController::class, 'store']);
        Route::put('/menu/{module}', [MenuController::class, 'update']);
    });
    
    // Rutas de sistema
    Route::role('admin')->group(function () {
        Route::get('/system/stats', [SystemController::class, 'stats']);
        Route::get('/system/database-status', [SystemController::class, 'databaseStatus']);
        Route::post('/system/optimize-tables', [SystemController::class, 'optimizeTables']);
    });
});

// Rutas OAuth
Route::prefix('oauth')->group(function () {
    Route::get('/providers', [OAuthController::class, 'providers']);
    Route::get('/stats', [OAuthController::class, 'stats'])->middleware('auth:sanctum');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sync-user', [OAuthController::class, 'syncUser']);
        Route::post('/disconnect', [OAuthController::class, 'disconnect']);
        Route::post('/link-account/{provider}', [OAuthController::class, 'linkAccount']);
    });
    
    // Rutas públicas
    Route::get('/redirect/{provider}', [OAuthController::class, 'redirect']);
    Route::get('/callback/{provider}', [OAuthController::class, 'callback']);
});
```

## 🧪 Testing

### Tests de Permisos

```php
<?php
// tests/Feature/PermissionTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_with_permission()
    {
        // Crear usuario y permiso
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'view-users']);
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_without_permission()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_role_based_access()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $permission = Permission::create(['name' => 'manage-users']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_blade_directives()
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'edit-users']);
        $user->givePermissionTo($permission);

        $view = $this->blade('
            @permission("edit-users")
                <button>Edit User</button>
            @endpermission
        ');

        $view->assertSee('Edit User');
    }
}
```

### Tests de OAuth

```php
<?php
// tests/Feature/OAuthTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kaely\Auth\Services\OAuthService;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_redirect()
    {
        $response = $this->getJson('/api/v1/oauth/redirect/google');

        $response->assertStatus(200)
                ->assertJsonStructure(['redirect_url']);
    }

    public function test_oauth_providers_list()
    {
        $response = $this->getJson('/api/v1/oauth/providers');

        $response->assertStatus(200)
                ->assertJsonStructure(['providers']);
    }

    public function test_oauth_stats()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/oauth/stats');

        $response->assertStatus(200)
                ->assertJsonStructure(['stats']);
    }
}
```

## 📊 Monitoreo y Logs

### Middleware de Logging

```php
<?php
// app/Http/Middleware/KaelyAuthLogging.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KaelyAuthLogging
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        // Log de autenticación
        if (auth()->check()) {
            $user = auth()->user();
            
            Log::channel('kaely-auth')->info('API Access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'duration' => $duration,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->getRoleNames(),
            ]);
        }
        
        return $response;
    }
}
```

### Comando de Monitoreo

```php
<?php
// app/Console/Commands/MonitorKaelyAuth.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kaely\Auth\KaelyAuthManager;

class MonitorKaelyAuth extends Command
{
    protected $signature = 'kaely:monitor';
    protected $description = 'Monitor KaelyAuth system status';

    public function handle(KaelyAuthManager $authManager)
    {
        $this->info('KaelyAuth System Monitor');
        $this->line('========================');
        
        // Verificar estado del sistema
        $stats = $authManager->getSystemStats();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Users', $stats['total_users']],
                ['Active Users', $stats['active_users']],
                ['Total Roles', $stats['total_roles']],
                ['Total Permissions', $stats['total_permissions']],
                ['Cache Status', $stats['cache_status'] ? 'OK' : 'ERROR'],
            ]
        );
        
        // Verificar base de datos
        $dbStatus = $authManager->getDatabaseStatus();
        
        $this->info('Database Status:');
        foreach ($dbStatus as $connection => $status) {
            $this->line("  {$connection}: " . ($status ? 'OK' : 'ERROR'));
        }
        
        // Verificar permisos críticos
        $criticalPermissions = ['manage-users', 'manage-roles', 'manage-permissions'];
        
        $this->info('Critical Permissions:');
        foreach ($criticalPermissions as $permission) {
            $exists = \App\Models\Permission::where('name', $permission)->exists();
            $this->line("  {$permission}: " . ($exists ? 'EXISTS' : 'MISSING'));
        }
    }
}
```

Estos ejemplos muestran cómo usar KaelyAuth en diferentes escenarios reales. El paquete es muy flexible y se puede adaptar a diferentes necesidades de autenticación y autorización. 