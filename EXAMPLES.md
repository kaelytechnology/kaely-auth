# KaelyAuth - Usage Examples

## 🔐 Basic Authentication

### Login Example

```php
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = $this->authManager->getUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'permissions' => $this->authManager->getUserPermissions($user),
            'roles' => $this->authManager->getUserRoles($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }
}
```

## 🛡️ Permission Checks

### In Controllers

```php
class UserController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function index()
    {
        $user = $this->authManager->getUser();

        // Check single permission
        if (!$this->authManager->hasPermission('view-users', $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check multiple permissions (any)
        if (!$this->authManager->hasAnyPermission(['manage-users', 'view-users'], $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check multiple permissions (all)
        if (!$this->authManager->hasAllPermissions(['manage-users', 'delete-users'], $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return User::paginate(10);
    }

    public function store(Request $request)
    {
        $user = $this->authManager->getUser();

        if (!$this->authManager->hasPermission('create-users', $user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Create user logic
    }
}
```

### In Blade Templates

```blade
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Dashboard</h1>

        @permission('view-users')
            <div class="card">
                <h3>User Management</h3>
                <a href="{{ route('users.index') }}">View Users</a>
            </div>
        @endpermission

        @role('admin')
            <div class="card">
                <h3>Admin Panel</h3>
                <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            </div>
        @endrole

        @anyRole(['admin', 'manager'])
            <div class="card">
                <h3>Management Panel</h3>
                <a href="{{ route('management.dashboard') }}">Management Dashboard</a>
            </div>
        @endanyRole

        @allRoles(['admin', 'supervisor'])
            <div class="card">
                <h3>Super Admin Panel</h3>
                <a href="{{ route('super-admin.dashboard') }}">Super Admin Dashboard</a>
            </div>
        @endallRoles
    </div>
@endsection
```

## 🛣️ Route Protection

### Using Middleware

```php
// routes/api.php

// Single permission
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
});

// Multiple permissions (any)
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users|view-users'])->group(function () {
    Route::get('/users/list', [UserController::class, 'list']);
});

// Role-based protection
Route::middleware(['auth:sanctum', 'kaely.role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Multiple roles (any)
Route::middleware(['auth:sanctum', 'kaely.role:admin|manager'])->group(function () {
    Route::get('/management', [ManagementController::class, 'index']);
});
```

### Using Route Macros

```php
// routes/api.php

Route::prefix('api/v1')->middleware(['auth:sanctum', 'kaely.auth'])->group(function () {
    
    // Permission-based routes
    Route::permission('manage-users')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    Route::permission('manage-roles')->group(function () {
        Route::apiResource('roles', RoleController::class);
    });

    // Role-based routes
    Route::role('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/settings', [AdminController::class, 'settings']);
    });

    Route::role('manager')->group(function () {
        Route::get('/manager/reports', [ManagerController::class, 'reports']);
    });

    // Multiple roles
    Route::anyRole(['admin', 'manager'])->group(function () {
        Route::get('/management/overview', [ManagementController::class, 'overview']);
    });
});
```

## 🗄️ Single Database Operations

### Database Transactions

```php
use Kaely\Auth\Services\SingleDatabaseService;

class UserService
{
    protected $singleDatabaseService;

    public function __construct(SingleDatabaseService $singleDatabaseService)
    {
        $this->singleDatabaseService = $singleDatabaseService;
    }

    public function createUserWithRoles($userData, $roleIds)
    {
        return $this->singleDatabaseService->executeTransaction(function () use ($userData, $roleIds) {
            // Create user in database
            $user = User::create($userData);
            
            // Assign roles
            $user->roles()->attach($roleIds);
            
            return $user;
        });
    }
}
```

### Database Validation and Optimization

```php
use Kaely\Auth\KaelyAuthManager;

class SystemController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function validateSystem()
    {
        // Validate database relations
        $validation = $this->authManager->validateDatabaseRelations();
        
        // Get database status
        $databaseStatus = $this->authManager->getDatabaseStatus();
        
        // Get table statistics
        $tableStats = $this->authManager->getTableStats();
        
        return response()->json([
            'validation' => $validation,
            'database_status' => $databaseStatus,
            'table_stats' => $tableStats
        ]);
    }

    public function optimizeDatabase()
    {
        // Optimize tables
        $optimizeResults = $this->authManager->optimizeTables();
        
        // Create indexes
        $indexResults = $this->authManager->createIndexes();
        
        return response()->json([
            'message' => 'Database optimized successfully',
            'optimize_results' => $optimizeResults,
            'index_results' => $indexResults
        ]);
    }
}
```

## 🎯 Advanced Features

### Caching

```php
class PermissionService
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function getUserPermissionsWithCache($user)
    {
        // Cache is automatically handled by the manager
        return $this->authManager->getUserPermissions($user);
    }

    public function clearUserCache($user)
    {
        // Clear user-specific cache
        $this->authManager->clearUserCache($user);
    }
}
```

### Menu Generation

```php
class MenuController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function getUserMenu()
    {
        $user = $this->authManager->getUser();
        $menu = $this->authManager->getUserMenu($user);
        
        return response()->json($menu);
    }

    public function getAllModules()
    {
        $menuService = app(\Kaely\Auth\Services\MenuService::class);
        $modules = $menuService->getAllModules();
        
        return response()->json($modules);
    }
}
```

### Statistics and Monitoring

```php
class SystemController extends Controller
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function getStats()
    {
        $stats = $this->authManager->getAuthStats();
        
        return response()->json([
            'total_users' => $stats['total_users'],
            'active_users' => $stats['active_users'],
            'total_roles' => $stats['total_roles'],
            'total_permissions' => $stats['total_permissions'],
            'cache_status' => $stats['cache_status']
        ]);
    }
}
```

## 🔧 Customization Examples

### Custom Permission Checks

```php
class CustomPermissionService
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function canManageBranch($user, $branchId)
    {
        // Check if user is super admin
        if ($this->authManager->isSuperAdmin($user)) {
            return true;
        }

        // Check if user has branch-specific permission
        if ($this->authManager->hasPermission('manage-branch-' . $branchId, $user)) {
            return true;
        }

        // Check if user has access to the branch
        $userBranches = $this->authManager->getUserBranches($user);
        return $userBranches->contains('id', $branchId);
    }

    public function canAccessModule($user, $moduleSlug)
    {
        $userPermissions = $this->authManager->getUserPermissions($user);
        
        return $userPermissions->contains(function ($permission) use ($moduleSlug) {
            return $permission->module->slug === $moduleSlug;
        });
    }
}
```

### Custom Middleware

```php
class CustomPermissionMiddleware
{
    protected $authManager;

    public function __construct(KaelyAuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function handle($request, Closure $next, $permission, $resource = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check basic permission
        if (!$this->authManager->hasPermission($permission, $user)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // Check resource-specific permission if provided
        if ($resource) {
            $resourcePermission = $permission . '-' . $resource;
            if (!$this->authManager->hasPermission($resourcePermission, $user)) {
                return response()->json(['error' => 'Resource access denied'], 403);
            }
        }

        return $next($request);
    }
}
```

## 📊 API Response Examples

### Login Response

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "roles": [
            {
                "id": 1,
                "name": "Admin",
                "slug": "admin"
            }
        ],
        "person": {
            "first_name": "John",
            "last_name": "Doe",
            "phone": "+1234567890"
        }
    },
    "token": "1|abcdef123456",
    "permissions": [
        {
            "id": 1,
            "name": "Manage Users",
            "slug": "manage-users",
            "module": {
                "id": 1,
                "name": "User Management",
                "slug": "user-management"
            }
        }
    ],
    "roles": [
        {
            "id": 1,
            "name": "Admin",
            "slug": "admin"
        }
    ],
    "menu": {
        "menu": [
            {
                "id": 1,
                "name": "User Management",
                "icon": "users",
                "path": "user-management",
                "permission": "manage-users",
                "children": []
            }
        ],
        "role": "Admin",
        "permissions": ["manage-users", "view-users"]
    }
}
```

### Error Response

```json
{
    "error": "Forbidden",
    "message": "Insufficient permissions",
    "required_permissions": ["manage-users"]
}
```

## 🧪 Testing Examples

### Unit Tests

```php
use Kaely\Auth\KaelyAuthManager;
use Tests\TestCase;

class KaelyAuthTest extends TestCase
{
    protected $authManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authManager = app(KaelyAuthManager::class);
    }

    public function test_user_has_permission()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'admin']);
        $permission = Permission::factory()->create(['slug' => 'manage-users']);
        
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($this->authManager->hasPermission('manage-users', $user));
    }

    public function test_user_has_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'admin']);
        
        $user->roles()->attach($role);

        $this->assertTrue($this->authManager->hasRole('admin', $user));
    }
}
```

### Integration Tests

```php
use Tests\TestCase;

class KaelyAuthIntegrationTest extends TestCase
{
    public function test_login_with_permissions()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $role = Role::factory()->create(['slug' => 'user']);
        $permission = Permission::factory()->create(['slug' => 'view-dashboard']);
        
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token',
                    'permissions',
                    'roles',
                    'menu'
                ]);
    }
}
``` 