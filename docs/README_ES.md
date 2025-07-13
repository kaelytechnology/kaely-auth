# KaelyAuth - Sistema Avanzado de Autenticación y Autorización

## 📋 Descripción

**KaelyAuth** es un paquete completo de Laravel que proporciona funcionalidades avanzadas de autenticación y autorización con soporte para múltiples bases de datos. Encapsula toda la lógica de permisos de tu proyecto BrisasHux en un paquete reutilizable y configurable.

## 🚀 Características

- **Soporte Multi-Base de Datos**: Maneja autenticación a través de múltiples bases de datos con configuración flexible
- **Integración OAuth/Socialite**: Autenticación social completa con Google, Facebook, GitHub, LinkedIn, Twitter
- **Control de Acceso Basado en Roles (RBAC)**: Sistema granular de permisos
- **Generación de Menús**: Menús dinámicos basados en permisos de usuario
- **Caché**: Caché integrado para optimización de rendimiento
- **Middleware**: Middleware listo para usar para verificaciones de permisos y roles
- **Directivas Blade**: Directivas Blade fáciles de usar para frontend
- **Recursos API**: Endpoints API completos para gestión de autenticación
- **Transacciones Cross-Database**: Maneja transacciones a través de múltiples bases de datos
- **Validación**: Validación de datos completa y verificaciones de integridad
- **Modos de Base de Datos Flexibles**: Base de datos única (por defecto) o múltiples bases de datos

## 📦 Instalación

### Prerrequisitos

KaelyAuth requiere al menos uno de los siguientes sistemas de autenticación:

- **Laravel Sanctum** (`laravel/sanctum`) - Para autenticación API (recomendado para APIs)
- **Laravel Breeze** (`laravel/breeze`) - Para aplicaciones web con vistas
- **Laravel Jetstream** (`laravel/jetstream`) - Para aplicaciones complejas con equipos
- **Laravel Framework** (8.x o superior)

### 1. Instalar el paquete

```bash
composer require kaely/auth
```

### 2. Verificar e instalar dependencias

El paquete verificará automáticamente las dependencias requeridas. Si faltan algunas, puedes instalarlas:

```bash
# Verificar estado de dependencias
php artisan kaely:check-dependencies

# Instalar dependencias faltantes automáticamente
php artisan kaely:check-dependencies --install
```

### 3. Instalar KaelyAuth

```bash
# Instalación completa con verificación de dependencias
php artisan kaely:install
```

### 4. Configurar para tu sistema de autenticación

```bash
# Auto-configurar para el sistema de autenticación detectado
php artisan kaely:configure-auth
```

O instalar componentes manualmente:

```bash
# Publicar configuración
php artisan vendor:publish --tag=kaely-auth-config

# Publicar migraciones
php artisan vendor:publish --tag=kaely-auth-migrations

# Ejecutar migraciones
php artisan migrate

# Publicar seeders (opcional)
php artisan vendor:publish --tag=kaely-auth-seeders
```

## ⚙️ Configuración

### Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# Configuración de Base de Datos
KAELY_DB_MODE=single                    # single o multiple
KAELY_ACTIVE_CONNECTIONS=main           # separado por comas para modo múltiple
KAELY_CROSS_DB_TRANSACTIONS=false       # Habilitar transacciones cross-database

# Conexiones de Base de Datos (para modo múltiple)
DB_CONNECTION=mysql
DB_POS_CONNECTION=mysql_pos
DB_INVENTORY_CONNECTION=mysql_inventory
DB_EVENTS_CONNECTION=mysql_events
DB_RESTAURANTS_CONNECTION=mysql_restaurants
DB_RESERVAS_CONNECTION=mysql_reservas

# Prefijos de Base de Datos (para modo múltiple)
KAELY_DB_PREFIX=main_
KAELY_POS_DB_PREFIX=pos_
KAELY_INVENTORY_DB_PREFIX=inventory_
KAELY_EVENTS_DB_PREFIX=events_
KAELY_RESTAURANTS_DB_PREFIX=restaurants_
KAELY_RESERVAS_DB_PREFIX=reservas_

# Autenticación
KAELY_AUTH_GUARD=web
KAELY_AUTH_PROVIDER=users
KAELY_AUTH_EXPIRE_TOKENS=604800
KAELY_AUTH_REFRESH_TOKENS=true

# Configuración OAuth/Socialite
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

# Credenciales de Proveedores OAuth
GOOGLE_CLIENT_ID=tu_google_client_id
GOOGLE_CLIENT_SECRET=tu_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

FACEBOOK_CLIENT_ID=tu_facebook_client_id
FACEBOOK_CLIENT_SECRET=tu_facebook_client_secret
FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback

GITHUB_CLIENT_ID=tu_github_client_id
GITHUB_CLIENT_SECRET=tu_github_client_secret
GITHUB_REDIRECT_URI=http://localhost/auth/github/callback

LINKEDIN_CLIENT_ID=tu_linkedin_client_id
LINKEDIN_CLIENT_SECRET=tu_linkedin_client_secret
LINKEDIN_REDIRECT_URI=http://localhost/auth/linkedin/callback

TWITTER_CLIENT_ID=tu_twitter_client_id
TWITTER_CLIENT_SECRET=tu_twitter_client_secret
TWITTER_REDIRECT_URI=http://localhost/auth/twitter/callback

# Permisos
KAELY_PERMISSIONS_CACHE=true
KAELY_PERMISSIONS_CACHE_TTL=3600
KAELY_SUPER_ADMIN_ROLE=super-admin
KAELY_ADMIN_ROLE=admin

# Menú
KAELY_MENU_CACHE=true
KAELY_MENU_CACHE_TTL=1800
KAELY_MENU_INCLUDE_INACTIVE=false

# Base de Datos Única
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

## 🔧 Uso

### Autenticación Básica

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
        // Tu lógica de login
        $user = Auth::user();
        
        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }
}
```

### Verificación de Permisos

```php
// En controladores
if ($this->authManager->hasPermission('manage-users')) {
    // Usuario puede gestionar usuarios
}

// En plantillas Blade
@permission('manage-users')
    <button>Gestionar Usuarios</button>
@endpermission

@role('admin')
    <div>Panel de Administrador</div>
@endrole
```

### Uso de Middleware

```php
// En rutas
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'kaely.role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### Endpoints API

El paquete proporciona estos endpoints API:

#### Autenticación
- `POST /api/v1/auth/login` - Login de usuario
- `POST /api/v1/auth/logout` - Logout de usuario
- `GET /api/v1/auth/me` - Obtener usuario actual

#### Gestión de Usuarios
- `GET /api/v1/users` - Listar usuarios
- `POST /api/v1/users` - Crear usuario
- `GET /api/v1/users/{id}` - Obtener usuario
- `PUT /api/v1/users/{id}` - Actualizar usuario
- `DELETE /api/v1/users/{id}` - Eliminar usuario
- `POST /api/v1/users/{id}/roles` - Asignar roles a usuario
- `GET /api/v1/users/{id}/permissions` - Obtener permisos de usuario

#### Gestión de Roles
- `GET /api/v1/roles` - Listar roles
- `POST /api/v1/roles` - Crear role
- `GET /api/v1/roles/{id}` - Obtener role
- `PUT /api/v1/roles/{id}` - Actualizar role
- `DELETE /api/v1/roles/{id}` - Eliminar role
- `POST /api/v1/roles/{id}/permissions` - Asignar permisos a role
- `GET /api/v1/roles/{id}/users` - Obtener usuarios del role

#### Gestión de Permisos
- `GET /api/v1/permissions` - Listar permisos
- `POST /api/v1/permissions` - Crear permiso
- `GET /api/v1/permissions/{id}` - Obtener permiso
- `PUT /api/v1/permissions/{id}` - Actualizar permiso
- `DELETE /api/v1/permissions/{id}` - Eliminar permiso
- `GET /api/v1/permissions/by-module/{module}` - Obtener permisos por módulo

#### Gestión de Menús
- `GET /api/v1/menu/user` - Obtener menú de usuario
- `GET /api/v1/menu/all` - Obtener todos los módulos
- `POST /api/v1/menu/reorder` - Reordenar módulos

#### Información del Sistema
- `GET /api/v1/system/stats` - Obtener estadísticas del sistema
- `GET /api/v1/system/database-status` - Obtener estado de base de datos
- `GET /api/v1/system/table-stats` - Obtener estadísticas de tablas
- `POST /api/v1/system/optimize-tables` - Optimizar tablas de base de datos
- `POST /api/v1/system/create-indexes` - Crear índices de base de datos
- `GET /api/v1/system/validate-relations` - Validar relaciones

## 🛠️ Comandos Disponibles

### Instalación y Configuración

```bash
# Instalación completa con verificación de dependencias
php artisan kaely:install

# Verificar estado de dependencias
php artisan kaely:check-dependencies

# Instalar dependencias faltantes automáticamente
php artisan kaely:check-dependencies --install

# Configurar para sistema de autenticación detectado
php artisan kaely:configure-auth

# Configurar múltiples bases de datos
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory

# Configurar paquete (publicar config, migraciones, etc.)
php artisan kaely:setup

# Sembrar datos iniciales
php artisan kaely:seed
```

### Gestión de Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate

# Revertir migraciones
php artisan migrate:rollback

# Refrescar migraciones
php artisan migrate:refresh
```

## 🏗️ Arquitectura

### Capa de Servicios

El paquete está construido alrededor de estos servicios principales:

- **KaelyAuthManager**: Clase manager principal
- **PermissionService**: Maneja lógica de permisos
- **MenuService**: Construye menús dinámicos
- **MultiDatabaseService**: Maneja operaciones multi-base de datos
- **OAuthService**: Maneja autenticación social
- **DependencyChecker**: Valida dependencias requeridas

### Middleware

- **CheckPermission**: Verifica permisos de usuario
- **CheckRole**: Verifica roles de usuario
- **SingleDatabaseAuth**: Maneja autenticación de base de datos única
- **DependencyCheck**: Valida dependencias en tiempo de ejecución

### Modelos

El paquete usa tus modelos existentes:
- `User` (de Laravel/Sanctum)
- `Role`
- `Permission`
- `Module`
- `RoleCategory`
- `Branch`
- `Department`
- `Person`

## 🗄️ Soporte Multi-Base de Datos

### Modos de Base de Datos

KaelyAuth soporta dos modos de base de datos:

#### Modo Base de Datos Única (Por Defecto)
```bash
# Configurar base de datos única
php artisan kaely:configure-multi-db --mode=single
```

#### Modo Múltiples Bases de Datos
```bash
# Configurar múltiples bases de datos
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory
```

### Configuración de Base de Datos

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
        // ... más conexiones
    ],
],
```

### Operaciones Cross-Database

```php
use Kaely\Auth\Services\MultiDatabaseService;

$dbService = app(MultiDatabaseService::class);

// Ejecutar en múltiples bases de datos
$results = $dbService->executeOnMultiple(['main', 'pos'], function($db, $connection) {
    return $db->table('users')->count();
});

// Transacciones cross-database
$dbService->executeTransaction(function($connections) {
    // Tu lógica de transacción
}, ['main', 'pos']);
```

## 🔐 Integración OAuth/Socialite

### Proveedores Soportados

- **Google** - OAuth 2.0
- **Facebook** - OAuth 2.0
- **GitHub** - OAuth 2.0
- **LinkedIn** - OAuth 2.0
- **Twitter** - OAuth 2.0

### Instalación

```bash
# Instalar Socialite
composer require laravel/socialite

# Configurar OAuth
php artisan kaely:configure-oauth
```

### Configuración

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
        // ... otros proveedores
    ],
],
```

### Uso

#### Rutas API
```php
// Las rutas OAuth se registran automáticamente
GET /api/v1/oauth/providers          // Obtener proveedores disponibles
GET /api/v1/oauth/stats              // Obtener estadísticas OAuth
GET /api/v1/oauth/validate-config    // Validar configuración OAuth
POST /api/v1/oauth/sync-user         // Sincronizar usuario OAuth en bases de datos
POST /api/v1/oauth/disconnect        // Desconectar cuenta OAuth
POST /api/v1/oauth/link-account/{provider}  // Vincular cuenta OAuth

// Rutas públicas
GET /api/v1/oauth/redirect/{provider}  // Redirigir a proveedor OAuth
GET /api/v1/oauth/callback/{provider}  // Manejar callback OAuth
```

#### Integración Frontend
```javascript
// Redirigir a proveedor OAuth
window.location.href = '/api/v1/oauth/redirect/google';

// Manejar callback
fetch('/api/v1/oauth/callback/google')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Almacenar token y redirigir
            localStorage.setItem('token', data.token);
            window.location.href = '/dashboard';
        }
    });
```

#### Uso de Servicio
```php
use Kaely\Auth\Services\OAuthService;

$oauthService = app(OAuthService::class);

// Verificar si OAuth está habilitado
if ($oauthService->isEnabled()) {
    $providers = $oauthService->getEnabledProviders();
}

// Manejar callback OAuth
$result = $oauthService->handleCallback('google');
```

### OAuth con Múltiples Bases de Datos

Cuando usas OAuth con múltiples bases de datos, los usuarios se sincronizan automáticamente en todas las conexiones activas:

```php
// El usuario se creará/actualizará en todas las bases de datos activas
$result = $oauthService->handleCallback('google');

// Sincronización manual
$oauthService->syncOAuthUser($user);
```

## 🔐 Compatibilidad de Sistemas de Autenticación

KaelyAuth es compatible con múltiples sistemas de autenticación Laravel:

### Laravel Sanctum (Recomendado para APIs)
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Características:**
- Autenticación API con tokens
- Autenticación stateless
- Soporte para aplicaciones móviles
- Autenticación SPA

### Laravel Breeze (Para Aplicaciones Web)
```bash
composer require laravel/breeze --dev
php artisan breeze:install
php artisan migrate
npm install && npm run dev
```

**Características:**
- Autenticación basada en sesiones
- Vistas web incluidas
- Configuración simple
- Aplicaciones web tradicionales

### Laravel Jetstream (Para Aplicaciones Complejas)
```bash
composer require laravel/jetstream
php artisan jetstream:install
php artisan migrate
npm install && npm run dev
```

**Características:**
- Tanto autenticación API como web
- Gestión de equipos
- Gestión de perfiles
- Características avanzadas

### Detección y Configuración Automática

KaelyAuth detecta automáticamente tu sistema de autenticación y se configura en consecuencia:

```bash
# Verificar qué está instalado
php artisan kaely:check-dependencies

# Auto-configurar para sistema detectado
php artisan kaely:configure-auth
```

## 🔧 Solución de Problemas

### Dependencias Faltantes

Si encuentras errores relacionados con dependencias:

```bash
# Verificar qué falta
php artisan kaely:check-dependencies

# Instalar dependencias faltantes
php artisan kaely:check-dependencies --install

# O instalar manualmente
composer require laravel/sanctum
```

### Problemas Comunes

1. **Error "Dependencies missing"**: Ejecuta `php artisan kaely:check-dependencies --install`
2. **Errores "Class not found"**: Asegúrate de que todas las dependencias estén instaladas
3. **Errores de conexión de base de datos**: Verifica tu configuración de base de datos
4. **Errores de permisos denegados**: Asegúrate de que el modelo User tenga el trait HasPermissions

### Obtener Ayuda

- Verificar estado de dependencias: `php artisan kaely:check-dependencies`
- Revisar configuración: `config/kaely-auth.php`
- Verificar logs: `storage/logs/laravel.log`

## 📚 Ejemplos

### Ejemplo de Controlador con Diferentes Sistemas de Autenticación

```php
class ExampleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // KaelyAuth funciona igual independientemente del sistema de autenticación
        if ($user->hasPermission('view-dashboard')) {
            return response()->json([
                'user' => $user,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->getRoleNames(),
                'menu' => app('kaely.auth')->getUserMenu($user)
            ]);
        }
        
        return response()->json(['error' => 'Acceso denegado'], 403);
    }
}
```

### Ejemplo de Middleware con Diferentes Sistemas

```php
class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        
        // El middleware de KaelyAuth funciona con cualquier sistema de autenticación
        if (!$user->hasPermission('access-api')) {
            return response()->json(['error' => 'Permisos insuficientes'], 403);
        }
        
        return $next($request);
    }
}
```

### Directivas Blade Funcionan con Cualquier Sistema de Autenticación

```blade
@permission('manage-users')
    <div class="admin-panel">
        <h2>Gestión de Usuarios</h2>
        <button>Agregar Usuario</button>
        <button>Editar Usuario</button>
    </div>
@endpermission

@role('admin')
    <div class="admin-dashboard">
        <h1>Panel de Administrador</h1>
        <!-- Contenido de administrador -->
    </div>
@endrole
```

### Macros de Rutas Funcionan con Cualquier Sistema

```php
Route::prefix('api/v1')->middleware(['auth:sanctum', 'kaely.auth'])->group(function () {
    
    // Rutas basadas en permisos
    Route::permission('manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
    });
    
    // Rutas basadas en roles
    Route::role('admin')->group(function () {
        Route::get('/admin/stats', [AdminController::class, 'stats']);
    });
    
    // Múltiples permisos
    Route::permission('view-users|edit-users')->group(function () {
        Route::get('/users/{id}', [UserController::class, 'show']);
    });
});
```

### Diferencias de Configuración

```php
$configs = [
    'sanctum' => [
        'middleware' => 'auth:sanctum',
        'token_expiration' => 60 * 24 * 7, // 7 días
        'features' => ['api_tokens', 'mobile_apps']
    ],
    'breeze' => [
        'middleware' => 'auth',
        'token_expiration' => null, // Basado en sesión
        'features' => ['session_auth', 'web_views']
    ],
    'jetstream' => [
        'middleware' => 'auth:sanctum', // Para API
        'token_expiration' => 60 * 24 * 7,
        'features' => ['api_tokens', 'session_auth', 'teams', 'profiles']
    ]
];
```

## 📄 Licencia

Este paquete es de código abierto y está disponible bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un issue o pull request para sugerencias o mejoras.

## 📞 Soporte

Para soporte técnico, por favor abre un issue en el repositorio de GitHub o contacta al equipo de desarrollo. 