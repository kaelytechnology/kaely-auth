# Guía de Migración - BrisasHux a KaelyAuth

## 📋 Descripción

Esta guía te ayudará a migrar desde el sistema de autenticación actual de BrisasHux al paquete KaelyAuth. El paquete encapsula toda la lógica de permisos existente y la hace reutilizable.

## 🔄 Proceso de Migración

### Paso 1: Preparación

#### 1.1 Backup de Datos
```bash
# Crear backup de la base de datos actual
php artisan db:backup

# Exportar datos críticos
php artisan tinker
>>> DB::table('users')->get()->toJson();
>>> DB::table('roles')->get()->toJson();
>>> DB::table('permissions')->get()->toJson();
```

#### 1.2 Verificar Dependencias
```bash
# Verificar qué sistema de autenticación tienes instalado
php artisan kaely:check-dependencies
```

### Paso 2: Instalación del Paquete

#### 2.1 Instalar KaelyAuth
```bash
# Instalar el paquete
composer require kaely/auth

# Verificar dependencias
php artisan kaely:check-dependencies --install
```

#### 2.2 Configurar el Paquete
```bash
# Instalación completa
php artisan kaely:install

# Configurar para tu sistema de autenticación
php artisan kaely:configure-auth
```

### Paso 3: Migración de Datos

#### 3.1 Migrar Estructura de Base de Datos
```bash
# Ejecutar migraciones del paquete
php artisan migrate

# Verificar que las tablas se crearon correctamente
php artisan tinker
>>> Schema::hasTable('roles');
>>> Schema::hasTable('permissions');
>>> Schema::hasTable('role_user');
>>> Schema::hasTable('permission_role');
```

#### 3.2 Migrar Datos Existentes

Crea un comando de migración personalizado:

```php
// app/Console/Commands/MigrateBrisasHuxData.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateBrisasHuxData extends Command
{
    protected $signature = 'brisas:migrate-data';
    protected $description = 'Migrate data from BrisasHux to KaelyAuth';

    public function handle()
    {
        $this->info('Starting data migration...');

        // Migrar roles
        $this->migrateRoles();
        
        // Migrar permisos
        $this->migratePermissions();
        
        // Migrar relaciones usuario-rol
        $this->migrateUserRoles();
        
        // Migrar módulos
        $this->migrateModules();

        $this->info('Data migration completed successfully!');
    }

    private function migrateRoles()
    {
        $this->info('Migrating roles...');
        
        // Obtener roles existentes
        $existingRoles = DB::table('roles')->get();
        
        foreach ($existingRoles as $role) {
            DB::table('roles')->insert([
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name ?? $role->name,
                'description' => $role->description ?? null,
                'guard_name' => 'web',
                'created_at' => $role->created_at ?? now(),
                'updated_at' => $role->updated_at ?? now(),
            ]);
        }
        
        $this->info("Migrated " . count($existingRoles) . " roles");
    }

    private function migratePermissions()
    {
        $this->info('Migrating permissions...');
        
        // Obtener permisos existentes
        $existingPermissions = DB::table('permissions')->get();
        
        foreach ($existingPermissions as $permission) {
            DB::table('permissions')->insert([
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name ?? $permission->name,
                'description' => $permission->description ?? null,
                'guard_name' => 'web',
                'created_at' => $permission->created_at ?? now(),
                'updated_at' => $permission->updated_at ?? now(),
            ]);
        }
        
        $this->info("Migrated " . count($existingPermissions) . " permissions");
    }

    private function migrateUserRoles()
    {
        $this->info('Migrating user-role relationships...');
        
        // Obtener relaciones usuario-rol existentes
        $existingUserRoles = DB::table('role_user')->get();
        
        foreach ($existingUserRoles as $userRole) {
            DB::table('model_has_roles')->insert([
                'role_id' => $userRole->role_id,
                'model_type' => 'App\Models\User',
                'model_id' => $userRole->user_id,
            ]);
        }
        
        $this->info("Migrated " . count($existingUserRoles) . " user-role relationships");
    }

    private function migrateModules()
    {
        $this->info('Migrating modules...');
        
        // Obtener módulos existentes
        $existingModules = DB::table('modules')->get();
        
        foreach ($existingModules as $module) {
            DB::table('modules')->insert([
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->display_name ?? $module->name,
                'description' => $module->description ?? null,
                'icon' => $module->icon ?? null,
                'route' => $module->route ?? null,
                'order' => $module->order ?? 0,
                'is_active' => $module->is_active ?? true,
                'created_at' => $module->created_at ?? now(),
                'updated_at' => $module->updated_at ?? now(),
            ]);
        }
        
        $this->info("Migrated " . count($existingModules) . " modules");
    }
}
```

#### 3.3 Ejecutar Migración de Datos
```bash
# Ejecutar migración de datos
php artisan brisas:migrate-data

# Verificar datos migrados
php artisan tinker
>>> DB::table('roles')->count();
>>> DB::table('permissions')->count();
>>> DB::table('model_has_roles')->count();
```

### Paso 4: Actualizar Código

#### 4.1 Actualizar Controladores

**Antes (BrisasHux):**
```php
// app/Http/Controllers/Api/V1/Auth/AuthController.php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Lógica de login existente
        $user = Auth::user();
        
        // Obtener permisos manualmente
        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id');
        
        return response()->json([
            'user' => $user,
            'permissions' => $permissions
        ]);
    }
}
```

**Después (KaelyAuth):**
```php
// app/Http/Controllers/Api/V1/Auth/AuthController.php
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
        // Lógica de login existente
        $user = Auth::user();
        
        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }
}
```

#### 4.2 Actualizar Middleware

**Antes (BrisasHux):**
```php
// app/Http/Middleware/CheckPermission.php
class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Verificar permiso manualmente
        $hasPermission = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->contains('name', $permission);
        
        if (!$hasPermission) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

**Después (KaelyAuth):**
```php
// Usar middleware del paquete
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

#### 4.3 Actualizar Vistas Blade

**Antes (BrisasHux):**
```blade
@php
    $user = auth()->user();
    $hasPermission = $user->roles()
        ->with('permissions')
        ->get()
        ->flatMap(function ($role) {
            return $role->permissions;
        })
        ->contains('name', 'manage-users');
@endphp

@if($hasPermission)
    <button>Gestionar Usuarios</button>
@endif
```

**Después (KaelyAuth):**
```blade
@permission('manage-users')
    <button>Gestionar Usuarios</button>
@endpermission
```

### Paso 5: Configurar Multi-Base de Datos (Opcional)

Si tu proyecto usa múltiples bases de datos:

```bash
# Configurar múltiples bases de datos
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory

# Verificar configuración
php artisan tinker
>>> config('kaely-auth.database.mode');
>>> config('kaely-auth.database.active_connections');
```

### Paso 6: Configurar OAuth (Opcional)

Si quieres agregar autenticación social:

```bash
# Instalar Socialite
composer require laravel/socialite

# Configurar OAuth
php artisan kaely:configure-oauth

# Configurar proveedores en .env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

### Paso 7: Verificación y Testing

#### 7.1 Verificar Funcionalidad
```bash
# Verificar que todo funciona
php artisan route:list | grep kaely

# Probar autenticación
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

#### 7.2 Testing de Permisos
```php
// En tinker
php artisan tinker

>>> $user = App\Models\User::find(1);
>>> $user->hasPermission('manage-users');
>>> $user->hasRole('admin');
>>> $user->getAllPermissions()->pluck('name');
```

## 🔧 Configuración Post-Migración

### Variables de Entorno Requeridas

Agrega estas variables a tu `.env`:

```env
# Configuración de Base de Datos
KAELY_DB_MODE=single
KAELY_ACTIVE_CONNECTIONS=main

# Autenticación
KAELY_AUTH_GUARD=web
KAELY_AUTH_PROVIDER=users

# Permisos
KAELY_PERMISSIONS_CACHE=true
KAELY_PERMISSIONS_CACHE_TTL=3600

# Menú
KAELY_MENU_CACHE=true
KAELY_MENU_CACHE_TTL=1800
```

### Configuración de Cache

```bash
# Limpiar cache después de la migración
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🚨 Problemas Comunes y Soluciones

### 1. Error "Class not found"
```bash
# Regenerar autoload
composer dump-autoload

# Limpiar cache
php artisan cache:clear
```

### 2. Error "Table already exists"
```bash
# Revertir migraciones específicas
php artisan migrate:rollback --step=1

# Ejecutar migraciones nuevamente
php artisan migrate
```

### 3. Permisos no funcionan
```bash
# Verificar que el modelo User tiene el trait
php artisan tinker
>>> class_uses(App\Models\User::class);

# Si falta, agregar manualmente
use Kaely\Auth\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasPermissions;
    // ...
}
```

### 4. Middleware no funciona
```bash
# Verificar que el middleware está registrado
php artisan route:list | grep kaely

# Registrar manualmente si es necesario
// app/Http/Kernel.php
protected $routeMiddleware = [
    'kaely.permission' => \Kaely\Auth\Middleware\CheckPermission::class,
    'kaely.role' => \Kaely\Auth\Middleware\CheckRole::class,
];
```

## 📊 Verificación de Migración

### Checklist de Verificación

- [ ] Paquete instalado correctamente
- [ ] Dependencias verificadas
- [ ] Migraciones ejecutadas
- [ ] Datos migrados
- [ ] Controladores actualizados
- [ ] Middleware funcionando
- [ ] Vistas Blade actualizadas
- [ ] Variables de entorno configuradas
- [ ] Cache limpiado
- [ ] Testing completado

### Comandos de Verificación

```bash
# Verificar estado general
php artisan kaely:check-dependencies

# Verificar configuración
php artisan config:show kaely-auth

# Verificar rutas
php artisan route:list | grep kaely

# Verificar permisos
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->hasPermission('manage-users');
```

## 🎉 Post-Migración

### Optimizaciones Recomendadas

1. **Configurar Cache Redis** (si no está configurado):
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

2. **Configurar Queue** (para operaciones asíncronas):
```env
QUEUE_CONNECTION=redis
```

3. **Optimizar Base de Datos**:
```bash
php artisan kaely:optimize-tables
```

### Monitoreo

```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Verificar estadísticas
php artisan tinker
>>> app('kaely.auth')->getSystemStats();
```

## 📞 Soporte

Si encuentras problemas durante la migración:

1. Revisar logs: `storage/logs/laravel.log`
2. Verificar configuración: `config/kaely-auth.php`
3. Ejecutar verificaciones: `php artisan kaely:check-dependencies`
4. Abrir issue en el repositorio con detalles del error

---

**¡Migración Completada!** 🎉

Tu proyecto BrisasHux ahora usa el paquete KaelyAuth con todas sus funcionalidades avanzadas. 