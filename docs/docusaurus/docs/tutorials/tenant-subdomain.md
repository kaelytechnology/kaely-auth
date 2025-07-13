# Tutorial: Multitenancy con Subdominios

Este tutorial te guiará a través de la configuración de KaelyAuth en modo multitenancy usando subdominios, ideal para aplicaciones SaaS donde cada cliente tiene su propio subdominio.

## 🎯 Objetivo

Configurar KaelyAuth para manejar múltiples tenants usando subdominios como `tenant1.tudominio.com`, `tenant2.tudominio.com`, etc.

## ⏱️ Tiempo Estimado

45-60 minutos

## 📋 Prerrequisitos

- Laravel 8.x o superior
- Dominio configurado con wildcard DNS
- Acceso a configuración de DNS
- Base de datos MySQL/PostgreSQL

## 🚀 Paso a Paso

### Paso 1: Configurar DNS Wildcard

Configura tu DNS para manejar subdominios dinámicos:

```
# En tu proveedor de DNS, agrega:
*.tudominio.com -> tu-servidor.com
```

### Paso 2: Instalar KaelyAuth

```bash
# Instalar el paquete
composer require kaely/auth

# Ejecutar el wizard
php artisan kaely:install-wizard
```

Durante el wizard, selecciona:

```
🗄️  Configuración de Base de Datos
--------------------------------
¿Qué modo de base de datos deseas usar? [single]:
  [0] Base de datos única (recomendado para la mayoría de proyectos)
  [1] Múltiples bases de datos (para proyectos empresariales)
  [2] Multitenancy (para aplicaciones SaaS con dominios/subdominios)
> 2

🏢 Configuración de Multitenancy
¿Habilitar multitenancy? (yes/no) [yes]:
> yes

¿Qué modo de tenancy deseas usar? [subdomain]:
  [0] Subdominio (ej: tenant1.tudominio.com)
  [1] Dominio completo (ej: tenant1.com)
  [2] Ruta (ej: tudominio.com/tenant1)
  [3] Header HTTP (X-Tenant)
  [4] Sesión (session tenant)
> 0

Nombre del tenant por defecto: [main]:
> main

¿Crear bases de datos automáticamente? (yes/no) [yes]:
> yes
```

### Paso 3: Configurar Variables de Entorno

Edita tu archivo `.env`:

```env
# Multitenancy Configuration
KAELY_MULTITENANCY_ENABLED=true
KAELY_TENANCY_MODE=subdomain
KAELY_DEFAULT_TENANT=main
KAELY_AUTO_CREATE_TENANT_DB=true

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=main_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Tenant Database Configuration
KAELY_TENANT_DB_PREFIX=tenant_
KAELY_TENANT_CONNECTION_PREFIX=tenant_
KAELY_TENANT_CACHE_PREFIX=tenant_
KAELY_TENANT_SESSION_PREFIX=tenant_
```

### Paso 4: Configurar Multitenancy

```bash
# Configurar multitenancy
php artisan kaely:configure-multitenancy --mode=subdomain --enabled=true
```

### Paso 5: Crear Primer Tenant

```bash
# Crear tenant de prueba
php artisan kaely:create-tenant tenant1 --subdomain=tenant1

# Crear más tenants
php artisan kaely:create-tenant tenant2 --subdomain=tenant2
php artisan kaely:create-tenant tenant3 --subdomain=tenant3
```

### Paso 6: Configurar Middleware

Edita `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... otros middleware
        \Kaely\Auth\Middleware\TenantMiddleware::class,
    ],
    'api' => [
        // ... otros middleware
        \Kaely\Auth\Middleware\TenantMiddleware::class,
    ],
];
```

### Paso 7: Configurar Rutas

Edita `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Rutas que requieren tenant
Route::middleware(['kaely.tenant'])->group(function () {
    Route::get('/dashboard', function () {
        $tenant = app('Kaely\Auth\Contracts\TenantManagerInterface')->getCurrentTenant();
        return view('dashboard', compact('tenant'));
    });
    
    Route::get('/profile', function () {
        return view('profile');
    });
});

// Rutas públicas (sin tenant)
Route::get('/', function () {
    return view('welcome');
});
```

## 🔧 Configuración Manual (Alternativa)

### 1. Configurar Multitenancy Manualmente

```bash
# Publicar configuración
php artisan vendor:publish --tag=kaely-auth-config

# Editar config/kaely-auth.php
```

### 2. Crear Directorios de Tenant

```bash
# Crear directorios para tenants
mkdir -p database/migrations/tenant
mkdir -p database/seeders/tenant
mkdir -p app/Models/Tenant
mkdir -p app/Http/Controllers/Tenant
```

### 3. Configurar Conexiones de Base de Datos

Edita `config/database.php`:

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'main_app'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
    
    // Conexiones de tenant se crean dinámicamente
],
```

## 📊 Estructura de Base de Datos

### Base de Datos Principal (`main_app`)
```
users
personal_access_tokens
tenant_main_roles
tenant_main_permissions
tenant_main_role_permissions
```

### Base de Datos Tenant1 (`tenant_tenant1`)
```
tenant_tenant1_roles
tenant_tenant1_permissions
tenant_tenant1_role_permissions
tenant_tenant1_users
```

### Base de Datos Tenant2 (`tenant_tenant2`)
```
tenant_tenant2_roles
tenant_tenant2_permissions
tenant_tenant2_role_permissions
tenant_tenant2_users
```

## 🧪 Pruebas

### Verificar Detección de Tenant

```bash
php artisan tinker
```

```php
// Verificar tenant actual
$tenantManager = app('Kaely\Auth\Contracts\TenantManagerInterface');
$currentTenant = $tenantManager->getCurrentTenant();
echo "Tenant actual: " . $currentTenant;

// Verificar si multitenancy está habilitado
if ($tenantManager->isEnabled()) {
    echo "Multitenancy habilitado";
}
```

### Crear Usuario en Tenant

```php
// Crear usuario en tenant específico
$tenantManager = app('Kaely\Auth\Contracts\TenantManagerInterface');
$tenantManager->setCurrentTenant('tenant1');

$user = \App\Models\User::create([
    'name' => 'Admin Tenant1',
    'email' => 'admin@tenant1.com',
    'password' => bcrypt('password')
]);

// Sincronizar usuario en otros tenants
$tenantManager->syncUserAcrossTenants($user, ['tenant2', 'tenant3']);
```

### Probar Acceso por Subdominio

```bash
# Configurar hosts locales para pruebas
echo "127.0.0.1 tenant1.localhost" >> /etc/hosts
echo "127.0.0.1 tenant2.localhost" >> /etc/hosts

# Acceder a tenants
curl http://tenant1.localhost:8000/dashboard
curl http://tenant2.localhost:8000/dashboard
```

## 🔍 Solución de Problemas

### Error: "Tenant not detected"

```bash
# Verificar configuración de multitenancy
php artisan tinker
>>> config('kaely-auth.database.multitenancy.enabled')
>>> config('kaely-auth.database.multitenancy.mode')
```

### Error: "Database doesn't exist"

```bash
# Crear base de datos para tenant
php artisan kaely:create-tenant tenant1 --subdomain=tenant1

# Verificar base de datos creada
mysql -u root -p -e "SHOW DATABASES LIKE 'tenant_tenant1';"
```

### Error: "Connection failed"

```bash
# Verificar conexión de tenant
php artisan tinker
>>> DB::connection('tenant_tenant1')->getPdo();
```

### Error: "Middleware not found"

```bash
# Verificar que el middleware esté registrado
php artisan route:list | grep tenant
```

## 📈 Monitoreo

### Verificar Estadísticas de Tenants

```bash
# Ver estadísticas de todos los tenants
php artisan kaely:tenant-stats
```

### Verificar Sincronización

```bash
# Verificar usuarios sincronizados
php artisan kaely:sync-status
```

## 🚀 Despliegue

### Configuración de Producción

```env
# Producción
KAELY_MULTITENANCY_ENABLED=true
KAELY_TENANCY_MODE=subdomain
KAELY_DEFAULT_TENANT=main
KAELY_AUTO_CREATE_TENANT_DB=false  # Deshabilitar en producción
```

### Configuración de DNS

```
# En tu proveedor de DNS
*.tudominio.com -> tu-servidor.com
```

### Configuración de Web Server

#### Nginx
```nginx
server {
    listen 80;
    server_name *.tudominio.com;
    
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

#### Apache
```apache
<VirtualHost *:80>
    ServerName *.tudominio.com
    DocumentRoot /path/to/your/app/public
    
    <Directory /path/to/your/app/public>
        AllowOverride All
    </Directory>
</VirtualHost>
```

## 🎉 ¡Listo!

Tu aplicación SaaS ahora tiene:
- ✅ Multitenancy con subdominios
- ✅ Bases de datos separadas por tenant
- ✅ Detección automática de tenant
- ✅ Sincronización de usuarios
- ✅ Aislamiento completo de datos
- ✅ Middleware automático

## 📖 Recursos Adicionales

- [Documentación Completa](https://kaely-auth.com)
- [API Reference](https://kaely-auth.com/api)
- [Ejemplos Prácticos](https://kaely-auth.com/examples) 