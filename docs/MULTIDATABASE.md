# KaelyAuth Multi-Database Management

## 🔄 Cambio Automático de Conexión

El middleware `kaely.tenant` maneja **automáticamente** el cambio de conexión entre bases de datos sin requerir personalización adicional.

### Modo Single Database (Recomendado)

```php
// Configuración
KAELY_AUTH_DB_MODE=single
KAELY_AUTH_DB_PREFIX=tenant_1_  // Prefijo automático por tenant

// Uso del middleware
Route::middleware('kaely.tenant')->group(function () {
    // Las consultas automáticamente usan el prefijo del tenant
    $users = User::all(); // Consulta: SELECT * FROM tenant_1_users
});
```

### Modo Multiple Databases

```php
// Configuración
KAELY_AUTH_DB_MODE=multiple
KAELY_AUTH_DEFAULT_CONNECTION=mysql
KAELY_AUTH_AUTH_CONNECTION=mysql

// Uso del middleware
Route::middleware('kaely.tenant')->group(function () {
    // Las consultas automáticamente usan la base de datos del tenant
    $users = User::all(); // Consulta en tenant_1_db.users
});
```

## 🏢 Configuraciones de Tenant

### Subdomain Mode
```php
// tenant1.example.com -> Base de datos: tenant_1_db
// tenant2.example.com -> Base de datos: tenant_2_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente detecta tenant1 y usa su base de datos
});
```

### Domain Mode
```php
// example1.com -> Base de datos: example1_db
// example2.com -> Base de datos: example2_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente detecta example1.com y usa su base de datos
});
```

### Header Mode
```php
// X-Tenant-ID: 123 -> Base de datos: tenant_123_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente usa la base de datos del tenant ID 123
});
```

### Parameter Mode
```php
// ?tenant_id=456 -> Base de datos: tenant_456_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente usa la base de datos del tenant ID 456
});
```

## 🔧 Configuración Automática

### Single Database Mode
```php
// El middleware automáticamente:
// 1. Detecta el tenant
// 2. Establece el prefijo de tabla
// 3. Actualiza la configuración de conexión

// Ejemplo de configuración automática:
Config::set('database.connections.mysql.prefix', 'tenant_1_');
Config::set('auth.table', 'tenant_1_users');
```

### Multiple Database Mode
```php
// El middleware automáticamente:
// 1. Detecta el tenant
// 2. Crea una nueva conexión específica del tenant
// 3. Cambia la conexión por defecto

// Ejemplo de configuración automática:
Config::set("database.connections.tenant_1", [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'tenant_1_db',
    'username' => 'root',
    'password' => 'password',
]);
Config::set('database.default', 'tenant_1');
```

## 📊 Ejemplos de Uso

### Crear Tenant
```bash
php artisan kaely:create-tenant --name="Mi Empresa" --domain="miempresa.com"
```

### Configurar Base de Datos
```php
// El middleware automáticamente configura:
// - Prefijo de tablas (single mode)
// - Conexión específica (multiple mode)
// - Configuración de autenticación

Route::middleware('kaely.tenant')->group(function () {
    // Todo funciona automáticamente
    $users = User::all();
    $products = Product::all();
    $orders = Order::all();
});
```

### Obtener Información del Tenant
```php
// Obtener tenant actual
$tenant = KaelyTenantMiddleware::getCurrentTenant();

// Información disponible:
// - id: ID del tenant
// - name: Nombre del tenant
// - domain: Dominio del tenant
// - database_name: Nombre de la base de datos
// - table_prefix: Prefijo de tablas
```

## 🔍 Debugging

### Verificar Conexión Actual
```php
Route::middleware('kaely.tenant')->group(function () {
    $connection = DB::connection()->getDatabaseName();
    $prefix = config('database.connections.mysql.prefix');
    
    dd([
        'database' => $connection,
        'prefix' => $prefix,
        'tenant' => KaelyTenantMiddleware::getCurrentTenant()
    ]);
});
```

### Logs de Conexión
```php
// Los cambios de conexión se registran automáticamente
DB::listen(function ($query) {
    Log::info('Database Query', [
        'connection' => $query->connection->getDatabaseName(),
        'sql' => $query->sql,
        'bindings' => $query->bindings
    ]);
});
```

## ⚡ Optimizaciones

### Cache de Conexiones
```php
// Las conexiones se cachean automáticamente
// No se crean nuevas conexiones para cada request
// Mejora significativa en performance
```

### Pool de Conexiones
```php
// Configuración automática de pool de conexiones
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
    'pool' => [
        'min' => 5,
        'max' => 20,
    ],
],
```

## 🛡️ Seguridad

### Aislamiento de Datos
```php
// Cada tenant tiene acceso solo a sus datos
// No hay posibilidad de acceso cruzado entre tenants
// Aislamiento completo a nivel de base de datos
```

### Validación de Tenant
```php
// El middleware valida automáticamente:
// - Existencia del tenant
// - Permisos de acceso
// - Configuración de base de datos
```

## 📈 Performance

### Métricas de Rendimiento
```php
// Monitoreo automático de:
// - Tiempo de cambio de conexión
// - Uso de memoria por tenant
// - Número de conexiones activas
// - Queries por tenant
```

### Optimizaciones Automáticas
```php
// El sistema automáticamente:
// - Reutiliza conexiones existentes
// - Cachea configuraciones de tenant
// - Optimiza queries por tenant
// - Limpia conexiones inactivas
```

## 🔧 Configuración Avanzada

### Personalización de Conexiones
```php
// En config/kaely-auth.php
'database' => [
    'mode' => 'multiple',
    'connections' => [
        'tenant_1' => [
            'driver' => 'mysql',
            'host' => 'tenant1-db.example.com',
            'database' => 'tenant_1_db',
            'username' => 'tenant1_user',
            'password' => 'tenant1_pass',
        ],
        'tenant_2' => [
            'driver' => 'postgresql',
            'host' => 'tenant2-db.example.com',
            'database' => 'tenant_2_db',
            'username' => 'tenant2_user',
            'password' => 'tenant2_pass',
        ],
    ],
],
```

### Migraciones por Tenant
```bash
# Ejecutar migraciones para un tenant específico
php artisan migrate --database=tenant_1

# Ejecutar migraciones para todos los tenants
php artisan kaely:migrate-all-tenants
```

## 🚀 Migración desde Sistemas Existentes

### Migración Automática
```bash
# Migrar datos existentes a estructura multi-tenant
php artisan kaely:migrate-to-multitenancy

# Validar integridad de datos
php artisan kaely:validate-tenant-data
```

### Rollback
```bash
# Revertir cambios si es necesario
php artisan kaely:rollback-multitenancy
```

## 📚 Casos de Uso

### SaaS Multi-Tenant
```php
// Cada cliente tiene su propia base de datos
// Aislamiento completo de datos
// Escalabilidad horizontal
```

### Aplicaciones Empresariales
```php
// Diferentes departamentos en la misma empresa
// Compartir configuración pero aislar datos
// Control granular de permisos
```

### Marketplaces
```php
// Cada vendedor tiene su espacio aislado
// Datos de clientes separados
// Métricas individuales por vendedor
``` 