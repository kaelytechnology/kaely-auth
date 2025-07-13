# KaelyAuth - Advanced Authentication & Authorization System

## 📋 Description

**KaelyAuth** is a comprehensive Laravel package that provides advanced authentication and authorization features with support for multiple databases. It encapsulates all the permission logic from your BrisasHux project into a reusable, configurable package.

## 🚀 Features

- **Multi-Database Support**: Handle authentication across multiple databases with flexible configuration
- **OAuth/Socialite Integration**: Complete social authentication with Google, Facebook, GitHub, LinkedIn, Twitter
- **Role-Based Access Control (RBAC)**: Granular permission system
- **Menu Generation**: Dynamic menus based on user permissions
- **Caching**: Built-in caching for performance optimization
- **Middleware**: Ready-to-use middleware for permission and role checks
- **Blade Directives**: Easy-to-use Blade directives for frontend
- **API Resources**: Complete API endpoints for authentication management
- **Cross-Database Transactions**: Handle transactions across multiple databases
- **Validation**: Comprehensive data validation and integrity checks
- **Flexible Database Modes**: Single database (default) or multiple databases

## 📦 Installation

### Prerequisites

KaelyAuth requires at least one of the following authentication systems:

- **Laravel Sanctum** (`laravel/sanctum`) - For API authentication (recommended for APIs)
- **Laravel Breeze** (`laravel/breeze`) - For web applications with views
- **Laravel Jetstream** (`laravel/jetstream`) - For complex applications with teams
- **Laravel Framework** (8.x or higher)

### 1. Install the package

```bash
composer require kaely/auth
```

### 2. Check and install dependencies

The package will automatically check for required dependencies. If any are missing, you can install them:

```bash
# Check dependencies status
php artisan kaely:check-dependencies

# Install missing dependencies automatically
php artisan kaely:check-dependencies --install
```

### 3. Install KaelyAuth

```bash
# Complete installation with dependency check
php artisan kaely:install
```

### 4. Configure for your authentication system

```bash
# Auto-configure for detected authentication system
php artisan kaely:configure-auth
```

Or install components manually:

```bash
# Publish configuration
php artisan vendor:publish --tag=kaely-auth-config

# Publish migrations
php artisan vendor:publish --tag=kaely-auth-migrations

# Run migrations
php artisan migrate

# Publish seeders (optional)
php artisan vendor:publish --tag=kaely-auth-seeders
```

## ⚙️ Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Database Configuration
KAELY_DB_MODE=single                    # single or multiple
KAELY_ACTIVE_CONNECTIONS=main           # comma-separated for multiple mode
KAELY_CROSS_DB_TRANSACTIONS=false       # Enable cross-database transactions

# Database Connections (for multiple mode)
DB_CONNECTION=mysql
DB_POS_CONNECTION=mysql_pos
DB_INVENTORY_CONNECTION=mysql_inventory
DB_EVENTS_CONNECTION=mysql_events
DB_RESTAURANTS_CONNECTION=mysql_restaurants
DB_RESERVAS_CONNECTION=mysql_reservas

# Database Prefixes (for multiple mode)
KAELY_DB_PREFIX=main_
KAELY_POS_DB_PREFIX=pos_
KAELY_INVENTORY_DB_PREFIX=inventory_
KAELY_EVENTS_DB_PREFIX=events_
KAELY_RESTAURANTS_DB_PREFIX=restaurants_
KAELY_RESERVAS_DB_PREFIX=reservas_

# Authentication
KAELY_AUTH_GUARD=web
KAELY_AUTH_PROVIDER=users
KAELY_AUTH_EXPIRE_TOKENS=604800
KAELY_AUTH_REFRESH_TOKENS=true

# OAuth/Socialite Configuration
KAELY_OAUTH_ENABLED=false
KAELY_OAUTH_GOOGLE_ENABLED=false
KAELY_OAUTH_FACEBOOK_ENABLED=false
KAELY_OAUTH_GITHUB_ENABLED=false
KAELY_OAUTH_LINKEDIN_ENABLED=false
KAELY_OAUTH_TWITTER_ENABLED=false
KAELY_OAUTH_AUTO_CREATE_USERS=true
KAELY_OAUTH_AUTO_ASSIGN_ROLES=true
KAELY_OAUTH_DEFAULT_ROLE=user
KAELY_OAUTH_SYNC_AVATAR=true

# OAuth Provider Credentials
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost/auth/github/callback

LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_REDIRECT_URI=http://localhost/auth/linkedin/callback

TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret
TWITTER_REDIRECT_URI=http://localhost/auth/twitter/callback

# Permissions
KAELY_PERMISSIONS_CACHE=true
KAELY_PERMISSIONS_CACHE_TTL=3600
KAELY_SUPER_ADMIN_ROLE=super-admin
KAELY_ADMIN_ROLE=admin

# Menu
KAELY_MENU_CACHE=true
KAELY_MENU_CACHE_TTL=1800
KAELY_MENU_INCLUDE_INACTIVE=false

# Single Database
KAELY_SINGLE_DB_ENABLED=true
KAELY_DB_PREFIX=main_
KAELY_OPTIMIZATION_ENABLED=true
KAELY_INDEXES_ENABLED=true

# API
KAELY_API_PREFIX=api/v1
KAELY_RATE_LIMITING=true
KAELY_RATE_LIMIT_MAX=60
KAELY_RATE_LIMIT_DECAY=1

# Logging
KAELY_LOGGING_ENABLED=true
KAELY_LOGGING_CHANNEL=daily
KAELY_LOGGING_LEVEL=info

# Cache
KAELY_CACHE_PREFIX=kaely_auth
KAELY_CACHE_STORE=redis
```

## 🔧 Usage

### Basic Authentication

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
        // Your login logic
        $user = Auth::user();
        
        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }
}
```

### Permission Checks

```php
// In controllers
if ($this->authManager->hasPermission('manage-users')) {
    // User can manage users
}

// In Blade templates
@permission('manage-users')
    <button>Manage Users</button>
@endpermission

@role('admin')
    <div>Admin Panel</div>
@endrole
```

### Middleware Usage

```php
// In routes
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'kaely.role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### API Endpoints

The package provides these API endpoints:

#### Authentication
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/logout` - Logout user
- `GET /api/v1/auth/me` - Get current user

#### User Management
- `GET /api/v1/users` - List users
- `POST /api/v1/users` - Create user
- `GET /api/v1/users/{id}` - Get user
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Delete user
- `POST /api/v1/users/{id}/roles` - Assign roles to user
- `GET /api/v1/users/{id}/permissions` - Get user permissions

#### Role Management
- `GET /api/v1/roles` - List roles
- `POST /api/v1/roles` - Create role
- `GET /api/v1/roles/{id}` - Get role
- `PUT /api/v1/roles/{id}` - Update role
- `DELETE /api/v1/roles/{id}` - Delete role
- `POST /api/v1/roles/{id}/permissions` - Assign permissions to role
- `GET /api/v1/roles/{id}/users` - Get role users

#### Permission Management
- `GET /api/v1/permissions` - List permissions
- `POST /api/v1/permissions` - Create permission
- `GET /api/v1/permissions/{id}` - Get permission
- `PUT /api/v1/permissions/{id}` - Update permission
- `DELETE /api/v1/permissions/{id}` - Delete permission
- `GET /api/v1/permissions/by-module/{module}` - Get permissions by module

#### Menu Management
- `GET /api/v1/menu/user` - Get user menu
- `GET /api/v1/menu/all` - Get all modules
- `POST /api/v1/menu/reorder` - Reorder modules

#### System Information
- `GET /api/v1/system/stats` - Get system statistics
- `GET /api/v1/system/database-status` - Get database status
- `GET /api/v1/system/table-stats` - Get table statistics
- `POST /api/v1/system/optimize-tables` - Optimize database tables
- `POST /api/v1/system/create-indexes` - Create database indexes
- `GET /api/v1/system/validate-relations` - Validate relations

## 🛠️ Available Commands

### Installation & Setup

```bash
# Complete installation with dependency check
php artisan kaely:install

# Check dependencies status
php artisan kaely:check-dependencies

# Install missing dependencies automatically
php artisan kaely:check-dependencies --install

# Configure for detected authentication system
php artisan kaely:configure-auth

# Configure multiple databases
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory

# Setup package (publish config, migrations, etc.)
php artisan kaely:setup

# Seed initial data
php artisan kaely:seed
```

### Database Management

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Refresh migrations
php artisan migrate:refresh
```

## 🏗️ Architecture

### Service Layer

The package is built around these core services:

- **KaelyAuthManager**: Main manager class
- **PermissionService**: Handles permission logic
- **MenuService**: Builds dynamic menus
- **MultiDatabaseService**: Manages multi-database operations
- **OAuthService**: Handles social authentication
- **DependencyChecker**: Validates required dependencies

### Middleware

- **CheckPermission**: Verifies user permissions
- **CheckRole**: Verifies user roles
- **SingleDatabaseAuth**: Handles single database authentication
- **DependencyCheck**: Validates dependencies at runtime

### Models

The package uses your existing models:
- `User` (from Laravel/Sanctum)
- `Role`
- `Permission`
- `Module`
- `RoleCategory`
- `Branch`
- `Department`
- `Person`

## 🗄️ Multiple Database Support

### Database Modes

KaelyAuth supports two database modes:

#### Single Database Mode (Default)
```bash
# Configure single database
php artisan kaely:configure-multi-db --mode=single
```

#### Multiple Database Mode
```bash
# Configure multiple databases
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory
```

### Database Configuration

```php
// config/kaely-auth.php
'database' => [
    'mode' => env('KAELY_DB_MODE', 'single'),
    'active_connections' => env('KAELY_ACTIVE_CONNECTIONS', 'main'),
    'connections' => [
        'main' => [
            'connection' => 'mysql',
            'prefix' => 'main_',
            'tables' => [...],
        ],
        'pos' => [
            'connection' => 'mysql_pos',
            'prefix' => 'pos_',
            'tables' => [...],
        ],
        // ... more connections
    ],
],
```

### Cross-Database Operations

```php
use Kaely\Auth\Services\MultiDatabaseService;

$dbService = app(MultiDatabaseService::class);

// Execute on multiple databases
$results = $dbService->executeOnMultiple(['main', 'pos'], function($db, $connection) {
    return $db->table('users')->count();
});

// Cross-database transactions
$dbService->executeTransaction(function($connections) {
    // Your transaction logic
}, ['main', 'pos']);
```

## 🔐 OAuth/Socialite Integration

### Supported Providers

- **Google** - OAuth 2.0
- **Facebook** - OAuth 2.0
- **GitHub** - OAuth 2.0
- **LinkedIn** - OAuth 2.0
- **Twitter** - OAuth 2.0

### Installation

```bash
# Install Socialite
composer require laravel/socialite

# Configure OAuth
php artisan kaely:configure-oauth
```

### Configuration

```php
// config/kaely-auth.php
'oauth' => [
    'enabled' => env('KAELY_OAUTH_ENABLED', false),
    'providers' => [
        'google' => [
            'enabled' => env('KAELY_OAUTH_GOOGLE_ENABLED', false),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
            'scopes' => ['email', 'profile'],
        ],
        // ... other providers
    ],
],
```

### Usage

#### API Routes
```php
// OAuth routes are automatically registered
GET /api/v1/oauth/providers          // Get available providers
GET /api/v1/oauth/stats              // Get OAuth statistics
GET /api/v1/oauth/validate-config    // Validate OAuth configuration
POST /api/v1/oauth/sync-user         // Sync OAuth user across databases
POST /api/v1/oauth/disconnect        // Disconnect OAuth account
POST /api/v1/oauth/link-account/{provider}  // Link OAuth account

// Public routes
GET /api/v1/oauth/redirect/{provider}  // Redirect to OAuth provider
GET /api/v1/oauth/callback/{provider}  // Handle OAuth callback
```

#### Frontend Integration
```javascript
// Redirect to OAuth provider
window.location.href = '/api/v1/oauth/redirect/google';

// Handle callback
fetch('/api/v1/oauth/callback/google')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store token and redirect
            localStorage.setItem('token', data.token);
            window.location.href = '/dashboard';
        }
    });
```

#### Service Usage
```php
use Kaely\Auth\Services\OAuthService;

$oauthService = app(OAuthService::class);

// Check if OAuth is enabled
if ($oauthService->isEnabled()) {
    $providers = $oauthService->getEnabledProviders();
}

// Handle OAuth callback
$result = $oauthService->handleCallback('google');
```

### OAuth with Multiple Databases

When using OAuth with multiple databases, users are automatically synced across all active connections:

```php
// User will be created/updated in all active databases
$result = $oauthService->handleCallback('google');

// Manual sync
$oauthService->syncOAuthUser($user);
```

## 🔐 Authentication System Compatibility

KaelyAuth is compatible with multiple Laravel authentication systems:

### Laravel Sanctum (Recommended for APIs)
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Features:**
- API token authentication
- Stateless authentication
- Mobile application support
- SPA authentication

### Laravel Breeze (For Web Applications)
```bash
composer require laravel/breeze --dev
php artisan breeze:install
php artisan migrate
npm install && npm run dev
```

**Features:**
- Session-based authentication
- Web views included
- Simple setup
- Traditional web apps

### Laravel Jetstream (For Complex Applications)
```bash
composer require laravel/jetstream
php artisan jetstream:install
php artisan migrate
npm install && npm run dev
```

**Features:**
- Both API and web authentication
- Team management
- Profile management
- Advanced features

### Auto-Detection and Configuration

KaelyAuth automatically detects your authentication system and configures itself accordingly:

```bash
# Check what's installed
php artisan kaely:check-dependencies

# Auto-configure for detected system
php artisan kaely:configure-auth
```

## 🔧 Troubleshooting

### Missing Dependencies

If you encounter dependency-related errors:

```bash
# Check what's missing
php artisan kaely:check-dependencies

# Install missing dependencies
php artisan kaely:check-dependencies --install

# Or install manually
composer require laravel/sanctum
```

### Common Issues

1. **"Dependencies missing" error**: Run `php artisan kaely:check-dependencies --install`
2. **"Class not found" errors**: Make sure all dependencies are installed
3. **Database connection errors**: Check your database configuration
4. **Permission denied errors**: Ensure the User model has the HasPermissions trait

### Getting Help

- Check the dependency status: `php artisan kaely:check-dependencies`
- Review the configuration: `config/kaely-auth.php`
- Check the logs: `storage/logs/laravel.log`

## 📚 Examples

### Controller Example with Different Authentication Systems

```php
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
```

### Middleware Usage with Different Systems

```php
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
```

### Blade Directives Work with Any Authentication System

```blade
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
```

### Route Macros Work with Any Authentication System

```php
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
```

### Configuration Differences

```php
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
```

## 📄 License

This package is open source and available under the MIT license.

## 🤝 Contributing

Contributions are welcome. Please open an issue or pull request for suggestions or improvements.

## 📞 Support

For technical support, please open an issue in the GitHub repository or contact the development team. 