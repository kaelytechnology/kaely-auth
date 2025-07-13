<?php

/**
 * Example: Multi-Authentication System Support
 * 
 * This example shows how KaelyAuth works with different
 * Laravel authentication systems (Sanctum, Breeze, Jetstream)
 */

use Kaely\Auth\Services\DependencyChecker;
use Kaely\Auth\Services\AuthSystemAdapter;

// Example 1: Check what authentication system is installed
$checker = new DependencyChecker();
$report = $checker->validateAndReport();

echo "Detected authentication system: " . ($report['auth_system'] ?? 'None') . "\n";

// Example 2: Get system-specific configuration
$adapter = new AuthSystemAdapter($checker);
$config = $adapter->adaptConfiguration();

if ($config['success']) {
    echo "System: {$config['auth_system']}\n";
    echo "Guard: {$config['config']['guard']}\n";
    echo "Middleware: {$config['config']['middleware']}\n";
    echo "Features: " . implode(', ', array_keys($config['config']['features'])) . "\n";
}

// Example 3: Check User model compatibility
$compatibility = $adapter->checkUserModelCompatibility();

if (!$compatibility['compatible']) {
    echo "User model issues found:\n";
    foreach ($compatibility['issues'] as $issue) {
        echo "- {$issue}\n";
    }
    echo "Suggestions:\n";
    foreach ($compatibility['suggestions'] as $suggestion) {
        echo "- {$suggestion}\n";
    }
}

// Example 4: System-specific usage examples
switch ($report['auth_system'] ?? 'none') {
    case 'sanctum':
        echo "\n=== Sanctum Configuration ===\n";
        echo "// API Routes\n";
        echo "Route::middleware('auth:sanctum')->group(function () {\n";
        echo "    Route::get('/api/user', function () {\n";
        echo "        return auth()->user();\n";
        echo "    });\n";
        echo "});\n\n";
        
        echo "// Generate API token\n";
        echo "\$token = \$user->createToken('api-token')->plainTextToken;\n\n";
        
        echo "// Check permissions\n";
        echo "if (\$user->hasPermission('manage-users')) {\n";
        echo "    // User can manage users\n";
        echo "}\n";
        break;
        
    case 'breeze':
        echo "\n=== Breeze Configuration ===\n";
        echo "// Web Routes\n";
        echo "Route::middleware('auth')->group(function () {\n";
        echo "    Route::get('/dashboard', function () {\n";
        echo "        return view('dashboard');\n";
        echo "    });\n";
        echo "});\n\n";
        
        echo "// Check permissions in Blade\n";
        echo "@permission('manage-users')\n";
        echo "    <button>Manage Users</button>\n";
        echo "@endpermission\n\n";
        
        echo "// Check permissions in controller\n";
        echo "if (auth()->user()->hasRole('admin')) {\n";
        echo "    // Admin functionality\n";
        echo "}\n";
        break;
        
    case 'jetstream':
        echo "\n=== Jetstream Configuration ===\n";
        echo "// API Routes (using Sanctum)\n";
        echo "Route::middleware('auth:sanctum')->group(function () {\n";
        echo "    Route::get('/api/user', function () {\n";
        echo "        return auth()->user();\n";
        echo "    });\n";
        echo "});\n\n";
        
        echo "// Web Routes (using session)\n";
        echo "Route::middleware('auth')->group(function () {\n";
        echo "    Route::get('/dashboard', function () {\n";
        echo "        return view('dashboard');\n";
        echo "    });\n";
        echo "});\n\n";
        
        echo "// Team-based permissions\n";
        echo "if (\$user->hasPermission('manage-team')) {\n";
        echo "    // Team management functionality\n";
        echo "}\n";
        break;
        
    default:
        echo "\n=== No Authentication System Detected ===\n";
        echo "Please install one of the following:\n";
        echo "- Laravel Sanctum: composer require laravel/sanctum\n";
        echo "- Laravel Breeze: composer require laravel/breeze --dev\n";
        echo "- Laravel Jetstream: composer require laravel/jetstream\n";
}

// Example 5: Controller example with different auth systems
class ExampleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // KaelyAuth works the same regardless of auth system
        if ($user->hasPermission('view-dashboard')) {
            return response()->json([
                'user' => $user,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->getRoleNames(),
                'menu' => app('kaely.auth')->getUserMenu($user)
            ]);
        }
        
        return response()->json(['error' => 'Access denied'], 403);
    }
}

// Example 6: Middleware usage with different systems
class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        // KaelyAuth middleware works with any auth system
        if (!$user->hasPermission('access-api')) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }
        
        return $next($request);
    }
}

// Example 7: Blade directives work with any auth system
/*
@permission('manage-users')
    <div class="admin-panel">
        <h2>User Management</h2>
        <button>Add User</button>
        <button>Edit User</button>
    </div>
@endpermission

@role('admin')
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        <!-- Admin content -->
    </div>
@endrole
*/

// Example 8: Route macros work with any auth system
/*
Route::prefix('api/v1')->middleware(['auth:sanctum', 'kaely.auth'])->group(function () {
    
    // Permission-based routes
    Route::permission('manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
    });
    
    // Role-based routes
    Route::role('admin')->group(function () {
        Route::get('/admin/stats', [AdminController::class, 'stats']);
    });
    
    // Multiple permissions
    Route::permission('view-users|edit-users')->group(function () {
        Route::get('/users/{id}', [UserController::class, 'show']);
    });
});
*/

// Example 9: Configuration differences
$configs = [
    'sanctum' => [
        'middleware' => 'auth:sanctum',
        'token_expiration' => 60 * 24 * 7, // 7 days
        'features' => ['api_tokens', 'mobile_apps']
    ],
    'breeze' => [
        'middleware' => 'auth',
        'token_expiration' => null, // Session-based
        'features' => ['session_auth', 'web_views']
    ],
    'jetstream' => [
        'middleware' => 'auth:sanctum', // For API
        'token_expiration' => 60 * 24 * 7,
        'features' => ['api_tokens', 'session_auth', 'teams', 'profiles']
    ]
];

echo "\n=== Configuration Differences ===\n";
foreach ($configs as $system => $config) {
    echo "{$system}:\n";
    echo "  Middleware: {$config['middleware']}\n";
    echo "  Token Expiration: " . ($config['token_expiration'] ?? 'Session-based') . "\n";
    echo "  Features: " . implode(', ', $config['features']) . "\n\n";
} 