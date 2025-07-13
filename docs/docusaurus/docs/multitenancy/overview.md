# Multitenancy Overview

KaelyAuth includes comprehensive multitenancy support that allows you to run multiple tenants (customers, organizations, etc.) in a single Laravel application with complete data isolation.

## 🏢 What is Multitenancy?

Multitenancy is an architecture where a single application serves multiple tenants (customers/organizations) while keeping their data completely isolated. Each tenant has their own:

- **Database**: Separate database or schema
- **Configuration**: Isolated settings
- **Users**: Independent user management
- **Permissions**: Separate role and permission systems
- **Cache**: Isolated caching
- **Sessions**: Independent session management

## 🎯 Supported Modes

### 1. Subdomain Mode
```
tenant1.yourdomain.com
tenant2.yourdomain.com
tenant3.yourdomain.com
```

### 2. Domain Mode
```
tenant1.com
tenant2.com
tenant3.com
```

### 3. Path Mode
```
yourdomain.com/tenant1
yourdomain.com/tenant2
yourdomain.com/tenant3
```

### 4. Header Mode
```
X-Tenant: tenant1
X-Tenant: tenant2
```

### 5. Session Mode
```
Session: tenant = tenant1
Session: tenant = tenant2
```

## ⚙️ Configuration

### Enable Multitenancy

```bash
# Interactive configuration
php artisan kaely:configure-multitenancy --mode=subdomain --enabled=true

# Or via environment variables
KAELY_MULTITENANCY_ENABLED=true
KAELY_TENANCY_MODE=subdomain
KAELY_DEFAULT_TENANT=main
```

### Environment Variables

```env
# Multitenancy Configuration
KAELY_MULTITENANCY_ENABLED=true
KAELY_TENANCY_MODE=subdomain
KAELY_DEFAULT_TENANT=main
KAELY_AUTO_CREATE_TENANT_DB=true

# Database Configuration
KAELY_TENANT_DB_PREFIX=tenant_
KAELY_TENANT_CONNECTION_PREFIX=tenant_

# Cache and Session
KAELY_TENANT_CACHE_PREFIX=tenant_
KAELY_TENANT_SESSION_PREFIX=tenant_
```

## 🚀 Quick Start

### 1. Enable Multitenancy

```bash
php artisan kaely:configure-multitenancy --mode=subdomain --enabled=true
```

### 2. Create Your First Tenant

```bash
php artisan kaely:create-tenant tenant1 --subdomain=tenant1
```

### 3. Configure DNS

Point your subdomains to your application:
```
tenant1.yourdomain.com -> your-app.com
tenant2.yourdomain.com -> your-app.com
```

### 4. Access Your Tenant

Visit `http://tenant1.yourdomain.com` and the application will automatically:
- Detect the tenant from the subdomain
- Switch to the tenant's database
- Load tenant-specific configuration
- Isolate all data and sessions

## 🔧 Usage Examples

### Get Current Tenant

```php
use Kaely\Auth\Services\MultitenancyService;

$multitenancy = app(MultitenancyService::class);
$currentTenant = $multitenancy->getCurrentTenant();
```

### Switch to Tenant

```php
$multitenancy = app(MultitenancyService::class);
$multitenancy->setCurrentTenant('tenant1');
```

### Check if Multitenancy is Enabled

```php
if ($multitenancy->isEnabled()) {
    // Multitenancy specific logic
}
```

### Get Tenant Statistics

```php
$stats = $multitenancy->getTenantStats('tenant1');
// Returns: ['users' => 10, 'roles' => 5, 'permissions' => 20]
```

### Create New Tenant Programmatically

```php
$multitenancy = app(MultitenancyService::class);

// Create tenant database
$multitenancy->createTenantDatabase('newtenant');

// Run migrations
$multitenancy->runTenantMigrations('newtenant');

// Run seeders
$multitenancy->runTenantSeeders('newtenant');
```

## 🛡️ Middleware

### Automatic Tenant Detection

```php
// Apply to all routes
Route::middleware(['kaely.tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Manual Tenant Switching

```php
Route::get('/switch/{tenant}', function ($tenant) {
    app(MultitenancyService::class)->setCurrentTenant($tenant);
    return redirect('/dashboard');
});
```

## 🗄️ Database Management

### Tenant Database Structure

Each tenant gets its own database:
```
tenant_main (default tenant)
tenant_tenant1
tenant_tenant2
tenant_tenant3
```

### Migrations

Tenant-specific migrations are stored in:
```
database/migrations/tenant/
```

### Seeders

Tenant-specific seeders are stored in:
```
database/seeders/tenant/
```

## 🔐 Security Features

### Data Isolation

- **Complete Database Separation**: Each tenant has its own database
- **Cache Isolation**: Separate cache prefixes prevent data leakage
- **Session Isolation**: Independent session management
- **User Isolation**: Users are isolated per tenant

### Access Control

```php
// Check if user belongs to current tenant
if ($user->tenant_id === $currentTenant) {
    // Allow access
}
```

## 📊 Monitoring

### Tenant Statistics

```php
$stats = $multitenancy->getTenantStats('tenant1');
```

Returns:
```json
{
    "users": 10,
    "roles": 5,
    "permissions": 20,
    "database": "tenant_tenant1",
    "connection": "tenant_tenant1"
}
```

### All Tenants

```php
$tenants = $multitenancy->getAllTenants();
// Returns: ['main', 'tenant1', 'tenant2', 'tenant3']
```

## 🔄 User Synchronization

### Sync User Across Tenants

```php
$user = User::find(1);
$multitenancy->syncUserAcrossTenants($user, ['tenant1', 'tenant2']);
```

### OAuth with Multitenancy

When using OAuth with multitenancy, users are automatically created in the current tenant's database.

## 🚨 Troubleshooting

### Common Issues

1. **Tenant Not Detected**
   ```bash
   # Check tenant detection
   php artisan tinker
   >>> app('Kaely\Auth\Services\MultitenancyService')->getCurrentTenant()
   ```

2. **Database Connection Issues**
   ```bash
   # Test tenant connection
   php artisan kaely:test-tenant tenant1
   ```

3. **Cache Issues**
   ```bash
   # Clear tenant cache
   php artisan cache:clear
   ```

### Debugging

```php
// Enable debug mode
config(['kaely-auth.logging.enabled' => true]);

// Check tenant in logs
tail -f storage/logs/laravel.log
```

## 📚 Best Practices

### 1. Tenant Naming
- Use lowercase, alphanumeric characters
- Avoid special characters
- Keep names short and memorable

### 2. Database Management
- Regular backups per tenant
- Monitor database sizes
- Implement data retention policies

### 3. Performance
- Use database indexes
- Implement caching strategies
- Monitor query performance

### 4. Security
- Validate tenant access
- Implement rate limiting per tenant
- Regular security audits

## 🔗 Related Commands

```bash
# Configure multitenancy
php artisan kaely:configure-multitenancy

# Create new tenant
php artisan kaely:create-tenant tenant1

# List all tenants
php artisan kaely:list-tenants

# Test tenant connection
php artisan kaely:test-tenant tenant1

# Backup tenant data
php artisan kaely:backup-tenant tenant1
```

Multitenancy in KaelyAuth provides enterprise-grade isolation and scalability while maintaining the simplicity of a single application deployment. 