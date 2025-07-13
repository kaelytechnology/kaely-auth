# Tutorial: Modo Múltiples Bases de Datos

Este tutorial te guiará a través de la configuración de KaelyAuth con múltiples bases de datos, ideal para aplicaciones empresariales que necesitan separar datos por módulos o departamentos.

## 🎯 Objetivo

Configurar KaelyAuth para usar múltiples bases de datos con conexiones separadas y prefijos únicos.

## ⏱️ Tiempo Estimado

30-45 minutos

## 📋 Prerrequisitos

- Laravel 8.x o superior
- Múltiples bases de datos configuradas
- Acceso a todas las bases de datos

## 🚀 Paso a Paso

### Paso 1: Preparar Bases de Datos

Crea las bases de datos que necesitas:

```sql
-- Base de datos principal
CREATE DATABASE main_app;

-- Base de datos para POS
CREATE DATABASE pos_system;

-- Base de datos para inventario
CREATE DATABASE inventory_management;

-- Base de datos para eventos
CREATE DATABASE events_system;

-- Base de datos para restaurantes
CREATE DATABASE restaurants_management;
```

### Paso 2: Configurar Conexiones

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

    'mysql_pos' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_POS_DATABASE', 'pos_system'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    'mysql_inventory' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_INVENTORY_DATABASE', 'inventory_management'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    'mysql_events' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_EVENTS_DATABASE', 'events_system'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    'mysql_restaurants' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_RESTAURANTS_DATABASE', 'restaurants_management'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
],
```

### Paso 3: Configurar Variables de Entorno

Edita tu archivo `.env`:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=main_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Multiple Database Configuration
KAELY_DATABASE_MODE=multi
KAELY_ACTIVE_CONNECTIONS=main,pos,inventory,events,restaurants

# Database Connections
DB_POS_DATABASE=pos_system
DB_INVENTORY_DATABASE=inventory_management
DB_EVENTS_DATABASE=events_system
DB_RESTAURANTS_DATABASE=restaurants_management

# Database Prefixes
KAELY_DB_PREFIX=main_
KAELY_POS_DB_PREFIX=pos_
KAELY_INVENTORY_DB_PREFIX=inventory_
KAELY_EVENTS_DB_PREFIX=events_
KAELY_RESTAURANTS_DB_PREFIX=restaurants_
```

### Paso 4: Instalar KaelyAuth

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
> 1

Configuración de Múltiples Bases de Datos
Configura las conexiones de base de datos:
Nombre de la conexión (ej: "tenant1", "branch1"): [main]:
> main
Prefijo para main (ej: 'main_'): [main_]:
> main_

¿Agregar otra conexión? (yes/no) [no]:
> yes

Nombre de la conexión (ej: "tenant1", "branch1"): [pos]:
> pos
Prefijo para pos (ej: 'pos_'): [pos_]:
> pos_

¿Agregar otra conexión? (yes/no) [no]:
> yes

Nombre de la conexión (ej: "tenant1", "branch1"): [inventory]:
> inventory
Prefijo para inventory (ej: 'inventory_'): [inventory_]:
> inventory_

¿Agregar otra conexión? (yes/no) [no]:
> yes

Nombre de la conexión (ej: "tenant1", "branch1"): [events]:
> events
Prefijo para events (ej: 'events_'): [events_]:
> events_

¿Agregar otra conexión? (yes/no) [no]:
> yes

Nombre de la conexión (ej: "tenant1", "branch1"): [restaurants]:
> restaurants
Prefijo para restaurants (ej: 'restaurants_'): [restaurants_]:
> restaurants_

¿Agregar otra conexión? (yes/no) [no]:
> no
```

### Paso 5: Configurar Múltiples Bases de Datos

```bash
# Configurar modo multi-database
php artisan kaely:configure-multi-db --connections=main,pos,inventory,events,restaurants
```

### Paso 6: Ejecutar Migraciones

```bash
# Ejecutar migraciones en todas las bases de datos
php artisan kaely:migrate
```

### Paso 7: Ejecutar Seeders

```bash
# Ejecutar seeders en todas las bases de datos
php artisan kaely:seed
```

## 🔧 Configuración Manual (Alternativa)

### 1. Configurar Conexiones Manualmente

```bash
# Publicar configuración
php artisan vendor:publish --tag=kaely-auth-config

# Editar config/kaely-auth.php
```

### 2. Ejecutar Migraciones por Conexión

```bash
# Migración para base de datos principal
php artisan migrate --database=mysql

# Migración para POS
php artisan migrate --database=mysql_pos

# Migración para inventario
php artisan migrate --database=mysql_inventory

# Migración para eventos
php artisan migrate --database=mysql_events

# Migración para restaurantes
php artisan migrate --database=mysql_restaurants
```

## 📊 Estructura de Bases de Datos

### Base de Datos Principal (`main_app`)
```
main_roles
main_permissions
main_role_permissions
main_modules
main_users
```

### Base de Datos POS (`pos_system`)
```
pos_sales
pos_sale_items
pos_clients
pos_payment_methods
pos_cash_registers
```

### Base de Datos Inventario (`inventory_management`)
```
inventory_products
inventory_stock_movements
inventory_suppliers
inventory_warehouses
inventory_purchase_orders
```

### Base de Datos Eventos (`events_system`)
```
events_events
events_bookings
events_speakers
events_services
```

### Base de Datos Restaurantes (`restaurants_management`)
```
restaurants_restaurants
restaurants_reservations
restaurants_menus
restaurants_tables
```

## 🧪 Pruebas

### Verificar Conexiones

```bash
php artisan tinker
```

```php
// Verificar conexión principal
DB::connection('mysql')->table('main_roles')->count();

// Verificar conexión POS
DB::connection('mysql_pos')->table('pos_sales')->count();

// Verificar conexión inventario
DB::connection('mysql_inventory')->table('inventory_products')->count();
```

### Crear Usuario en Múltiples Bases

```php
// Crear usuario en base principal
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);

// Sincronizar usuario en otras bases
app('kaely.auth')->syncUserAcrossDatabases($user);
```

## 🔍 Solución de Problemas

### Error: "Connection refused"

```bash
# Verificar conexiones
php artisan tinker
>>> DB::connection('mysql_pos')->getPdo();
```

### Error: "Database doesn't exist"

```sql
-- Crear bases de datos faltantes
CREATE DATABASE pos_system;
CREATE DATABASE inventory_management;
CREATE DATABASE events_system;
CREATE DATABASE restaurants_management;
```

### Error: "Table doesn't exist"

```bash
# Ejecutar migraciones en conexión específica
php artisan migrate --database=mysql_pos
php artisan migrate --database=mysql_inventory
```

## 📈 Monitoreo

### Verificar Estadísticas

```bash
# Ver estadísticas de todas las bases de datos
php artisan kaely:stats
```

### Verificar Sincronización

```bash
# Verificar usuarios sincronizados
php artisan kaely:sync-status
```

## 🎉 ¡Listo!

Tu aplicación ahora tiene:
- ✅ Múltiples bases de datos configuradas
- ✅ Sincronización automática de usuarios
- ✅ Prefijos únicos por base de datos
- ✅ Migraciones independientes
- ✅ Gestión centralizada de permisos

## 📖 Recursos Adicionales

- [Documentación Completa](https://kaely-auth.com)
- [API Reference](https://kaely-auth.com/api)
- [Ejemplos Prácticos](https://kaely-auth.com/examples) 