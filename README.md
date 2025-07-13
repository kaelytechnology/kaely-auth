# KaelyAuth - Advanced Authentication & Authorization Package

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/kaely/auth)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-yellow.svg)](LICENSE)

A comprehensive Laravel package for advanced authentication and authorization with multi-database support, OAuth integration, multitenancy, and audit logging.

## 🌟 Features

- **🔐 Advanced Authentication**: Support for Sanctum, Breeze, and Jetstream
- **🌐 OAuth Integration**: 20+ OAuth providers (Google, Facebook, GitHub, LinkedIn, Microsoft, Twitter, Apple, Discord, Slack, Bitbucket, GitLab, Dropbox, Box, Salesforce, HubSpot, Zoom, Stripe, PayPal, Twitch, Reddit)
- **🏢 Multitenancy**: Domain and subdomain-based tenant management with **automatic database switching**
- **🗄️ Multi-Database Support**: Single or multiple database configurations with **zero configuration required**
- **📧 Email Features**: Password reset and email verification
- **📊 Audit Logging**: Comprehensive activity tracking with **PDF/Excel export**
- **🌍 Multi-Language Installer**: English and Spanish support
- **⚡ Session Management**: Advanced session handling
- **🛡️ Role-Based Access Control**: Granular permissions system
- **📈 Dashboard Analytics**: Real-time logs and statistics
- **🧪 Comprehensive Testing**: Unit, functional, and end-to-end tests
- **🛡️ Security Features**: SQL injection detection, XSS protection, rate limiting, security headers
- **⚡ Performance**: Execution monitoring, memory tracking, query optimization, caching
- **🎨 UI Components**: Blade and Livewire UI options

## 🚀 Quick Installation

### Interactive Installation (Recommended)

```bash
php artisan kaely:install
```

The installer supports multiple languages and will guide you through:
- Language selection (English/Spanish)
- Laravel version compatibility check
- Authentication package detection and installation
- Database configuration
- OAuth provider setup
- Multitenancy configuration
- Additional features setup

### Command Line Options

```bash
# Install with specific language
php artisan kaely:install --language=en
php artisan kaely:install --language=es

# Skip interactive wizard
php artisan kaely:install --skip-wizard

# Force installation without confirmation
php artisan kaely:install --force
```

## 📋 Requirements

- Laravel 8.0 or higher
- PHP 8.0 or higher
- At least one authentication package (Sanctum, Breeze, or Jetstream)

## 🔧 Configuration

### Basic Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=kaely-auth-config
```

Edit `config/kaely-auth.php`:

```php
return [
    'database' => [
        'mode' => env('KAELY_AUTH_DB_MODE', 'single'),
        'prefix' => env('KAELY_AUTH_DB_PREFIX', ''),
    ],
    
    'oauth' => [
        'enabled' => env('KAELY_AUTH_OAUTH_ENABLED', false),
        'providers' => [
            'google' => [
                'enabled' => env('KAELY_AUTH_GOOGLE_ENABLED', false),
                'client_id' => env('KAELY_AUTH_GOOGLE_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_GOOGLE_REDIRECT_URI'),
            ],
            // ... 19 more providers
        ],
    ],
    
    'multitenancy' => [
        'enabled' => env('KAELY_AUTH_MULTITENANCY_ENABLED', false),
        'mode' => env('KAELY_AUTH_TENANT_MODE', 'subdomain'),
        'resolver' => env('KAELY_AUTH_TENANT_RESOLVER', 'subdomain'),
    ],
    
    'password_reset' => [
        'enabled' => env('KAELY_AUTH_PASSWORD_RESET_ENABLED', true),
    ],
    
    'email_verification' => [
        'enabled' => env('KAELY_AUTH_EMAIL_VERIFICATION_ENABLED', true),
    ],
    
    'sessions' => [
        'enabled' => env('KAELY_AUTH_SESSION_MANAGEMENT_ENABLED', true),
    ],
    
    'audit' => [
        'enabled' => env('KAELY_AUTH_AUDIT_ENABLED', true),
    ],
];
```

## 🌐 Multi-Language Support

The installer supports multiple languages:

### English (Default)
```bash
php artisan kaely:install --language=en
```

### Spanish
```bash
php artisan kaely:install --language=es
```

The installer will automatically detect your preference and provide all prompts in the selected language.

## 🎨 UI Options

KaelyAuth provides optional UI components that you can choose during installation:

### Blade UI (Traditional)
- Server-side rendered views
- No additional dependencies required
- Simple and lightweight
- Perfect for traditional applications

### Livewire UI (Interactive)
- Real-time interactive components
- Requires Livewire package
- Modern reactive experience
- Perfect for dynamic applications

### Custom UI
- No UI installed by default
- Create your own custom interface
- Full control over design and functionality
- Perfect for custom applications

### Installation Options

During the installation process, you'll be asked to choose your UI preference:

```bash
🎨 UI Configuration
Would you like to install a UI for authentication?
  [0] Blade UI (Traditional server-side rendering)
  [1] Livewire UI (Interactive with real-time features)
  [2] No UI (I will create my own)
```

### Manual UI Installation

If you choose to install UI later:

```bash
# Install Blade UI
php artisan kaely:install-ui blade

# Install Livewire UI
php artisan kaely:install-ui livewire
```

Or publish views manually:

```bash
# Install Blade UI
php artisan vendor:publish --tag=kaely-auth-blade-views

# Install Livewire UI
composer require livewire/livewire
php artisan vendor:publish --tag=kaely-auth-livewire-views
```

## 🏢 Multibase de Datos Dinámica

### Cambio Automático de Conexión

El middleware `kaely.tenant` maneja **automáticamente** el cambio de conexión entre bases de datos sin requerir personalización adicional.

#### Modo Single Database (Recomendado)
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

#### Modo Multiple Databases
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

### Configuraciones de Tenant

#### Subdomain Mode
```php
// tenant1.example.com -> Base de datos: tenant_1_db
// tenant2.example.com -> Base de datos: tenant_2_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente detecta tenant1 y usa su base de datos
});
```

#### Domain Mode
```php
// example1.com -> Base de datos: example1_db
// example2.com -> Base de datos: example2_db

Route::middleware('kaely.tenant')->group(function () {
    // Automáticamente detecta example1.com y usa su base de datos
});
```

## 📊 Exportación de Logs

### Exportar Logs en Diferentes Formatos

```bash
# Exportar logs de auditoría en Excel
php artisan kaely:export-logs audit --format=excel --days=30

# Exportar logs de sesiones en PDF
php artisan kaely:export-logs sessions --format=pdf --output=/tmp/report.pdf

# Exportar logs OAuth en JSON
php artisan kaely:export-logs oauth --format=json --filters='{"provider":"google"}'

# Exportar todos los logs en CSV
php artisan kaely:export-logs all --format=csv --days=90
```

### Formatos Soportados
- **Excel (.xlsx)**: Con hojas múltiples y estadísticas
- **PDF**: Con formato profesional y gráficos
- **JSON**: Para integración con APIs
- **CSV**: Para análisis en Excel/Google Sheets

### Filtros Avanzados
```bash
# Filtrar por usuario específico
php artisan kaely:export-logs audit --filters='{"user_id":123}'

# Filtrar por proveedor OAuth
php artisan kaely:export-logs oauth --filters='{"provider":"google"}'

# Filtrar por rango de fechas
php artisan kaely:export-logs audit --days=7
```

## 📚 Available Commands

### Installation & Setup
```bash
# Interactive installation
php artisan kaely:install

# Setup OAuth providers
php artisan kaely:setup-oauth

# Setup multitenancy
php artisan kaely:setup-multitenancy

# Create a new tenant
php artisan kaely:create-tenant
```

### Maintenance & Export
```bash
# Clean up expired tokens
php artisan kaely:cleanup-tokens

# Generate audit report
php artisan kaely:audit-report

# Export logs in various formats
php artisan kaely:export-logs audit --format=excel
php artisan kaely:export-logs sessions --format=pdf
php artisan kaely:export-logs oauth --format=json
```

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/login
POST   /api/auth/register
POST   /api/auth/logout
POST   /api/auth/refresh
GET    /api/auth/user
```

### Password Reset
```
POST   /api/auth/password/email
POST   /api/auth/password/reset
POST   /api/auth/password/confirm
```

### Email Verification
```
POST   /api/auth/email/verify
POST   /api/auth/email/resend
```

### OAuth (20+ Providers)
```
GET    /api/oauth/{provider}/redirect
GET    /api/oauth/{provider}/callback
POST   /api/oauth/{provider}/link
DELETE /api/oauth/{provider}/unlink
GET    /api/oauth/accounts
```

### User Management
```
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}
```

### Roles & Permissions
```
GET    /api/roles
POST   /api/roles
GET    /api/permissions
POST   /api/permissions
```

## 🛡️ Middleware

### Available Middleware
```php
// Tenant management (cambio automático de base de datos)
Route::middleware('kaely.tenant')->group(function () {
    // Tenant-specific routes
});

// Permission checking
Route::middleware('kaely.permission:edit-users')->group(function () {
    // Routes requiring specific permission
});

// Role checking
Route::middleware('kaely.role:admin')->group(function () {
    // Routes requiring specific role
});

// Email verification
Route::middleware('kaely.verified')->group(function () {
    // Routes requiring verified email
});

// Session activity tracking
Route::middleware('kaely.session.activity')->group(function () {
    // Routes with session tracking
});

// Audit logging
Route::middleware('kaely.audit')->group(function () {
    // Routes with audit logging
});
```

## 🌐 OAuth Integration (20+ Providers)

### Supported Providers
- **Google OAuth**
- **Facebook OAuth**
- **GitHub OAuth**
- **LinkedIn OAuth**
- **Microsoft OAuth**
- **Twitter OAuth**
- **Apple OAuth**
- **Discord OAuth**
- **Slack OAuth**
- **Bitbucket OAuth**
- **GitLab OAuth**
- **Dropbox OAuth**
- **Box OAuth**
- **Salesforce OAuth**
- **HubSpot OAuth**
- **Zoom OAuth**
- **Stripe OAuth**
- **PayPal OAuth**
- **Twitch OAuth**
- **Reddit OAuth**

### Google OAuth Configuration
```php
// .env configuration
KAELY_AUTH_GOOGLE_ENABLED=true
KAELY_AUTH_GOOGLE_CLIENT_ID=your-client-id
KAELY_AUTH_GOOGLE_CLIENT_SECRET=your-client-secret
KAELY_AUTH_GOOGLE_REDIRECT_URI=https://your-domain.com/api/oauth/google/callback
```

### GitHub OAuth Configuration
```php
// .env configuration
KAELY_AUTH_GITHUB_ENABLED=true
KAELY_AUTH_GITHUB_CLIENT_ID=your-client-id
KAELY_AUTH_GITHUB_CLIENT_SECRET=your-client-secret
KAELY_AUTH_GITHUB_REDIRECT_URI=https://your-domain.com/api/oauth/github/callback
```

## 🗄️ Database Modes

### Single Database (Default)
```php
KAELY_AUTH_DB_MODE=single
KAELY_AUTH_DB_PREFIX=  // No prefix by default
```

### Multiple Databases
```php
KAELY_AUTH_DB_MODE=multiple
KAELY_AUTH_DB_PREFIX=auth_
KAELY_AUTH_DEFAULT_CONNECTION=mysql
KAELY_AUTH_AUTH_CONNECTION=mysql
```

## 📊 Audit Logging Examples

### User Authentication Events
```json
{
  "id": 1,
  "user_id": 123,
  "action": "user.login",
  "description": "User logged in successfully",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "session_id": "abc123def456",
  "tenant_id": 1,
  "metadata": {
    "login_method": "email",
    "two_factor_enabled": false,
    "location": "New York, US"
  },
  "created_at": "2024-01-15T10:30:00Z"
}
```

### OAuth Authentication
```json
{
  "id": 3,
  "user_id": 124,
  "action": "oauth.login",
  "description": "User logged in via Google OAuth",
  "ip_address": "192.168.1.102",
  "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "session_id": "xyz789abc123",
  "tenant_id": 1,
  "metadata": {
    "provider": "google",
    "oauth_id": "google_123456789",
    "email": "user@gmail.com",
    "first_login": true
  },
  "created_at": "2024-01-15T11:00:00Z"
}
```

## 🧪 Testing

### Unit Tests
```bash
# Run unit tests
php artisan test --testsuite=Unit

# Run specific test
php artisan test --filter=InstallCommandTest
```

### Feature Tests
```bash
# Run feature tests
php artisan test --testsuite=Feature

# Run specific test
php artisan test --filter=InstallCommandFeatureTest
```

### Test Examples
```php
/** @test */
public function it_can_detect_installed_packages()
{
    $command = new InstallCommand();
    
    $this->assertTrue($command->isPackageInstalled('laravel/sanctum'));
    $this->assertFalse($command->isPackageInstalled('laravel/breeze'));
}

/** @test */
public function it_can_export_logs_in_excel_format()
{
    $this->artisan('kaely:export-logs', [
        'type' => 'audit',
        '--format' => 'excel',
        '--days' => '7'
    ])->assertExitCode(0);
}
```

## 🔧 Blade Directives

### Permission Directives
```blade
@hasPermission('edit-users')
    <button>Edit Users</button>
@endhasPermission
```

### Role Directives
```blade
@hasRole('admin')
    <div>Admin Panel</div>
@endhasRole
```

## 📝 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

- 📖 Documentation: https://kaely-auth.com/docs
- 🐛 Issues: https://github.com/kaelytechnology/kaely-auth/issues
- 💬 Discussions: https://github.com/kaelytechnology/kaely-auth/discussions

## 🚀 Changelog

### v1.1.0
- ✅ Multi-language installer support (English/Spanish)
- ✅ Automatic database switching with middleware
- ✅ PDF/Excel export for logs
- ✅ 20+ OAuth providers support
- ✅ Comprehensive test suite
- ✅ Real log examples and documentation
- ✅ Dashboard analytics
- ✅ Advanced filtering and export options

### v1.0.0
- Initial release
- OAuth integration (Google/Facebook)
- Multitenancy support
- Audit logging
- Role-based access control
- Session management
- Email verification
- Password reset functionality 

## 🔒 Security Features

### Advanced Security Middleware
```php
// Apply security middleware to routes
Route::middleware('kaely.security:high')->group(function () {
    // High security routes
});

Route::middleware('kaely.security:medium')->group(function () {
    // Medium security routes
});
```

### Security Configuration
```php
// In your .env file
KAELY_AUTH_SECURITY_ENABLED=true
KAELY_AUTH_SECURITY_LEVEL=high
KAELY_AUTH_ALLOWED_ORIGINS=example.com,api.example.com
KAELY_AUTH_BLACKLISTED_IPS=192.168.1.100
KAELY_AUTH_WHITELISTED_IPS=10.0.0.0/8
KAELY_AUTH_CSP="default-src 'self'; script-src 'self' 'unsafe-inline'"
```

### Password Policy
```php
KAELY_AUTH_PASSWORD_MIN_LENGTH=12
KAELY_AUTH_PASSWORD_UPPERCASE=true
KAELY_AUTH_PASSWORD_LOWERCASE=true
KAELY_AUTH_PASSWORD_NUMBERS=true
KAELY_AUTH_PASSWORD_SPECIAL=true
KAELY_AUTH_PASSWORD_PREVENT_COMMON=true
```

### Rate Limiting
```php
KAELY_AUTH_RATE_LIMITING_ENABLED=true
KAELY_AUTH_LOGIN_ATTEMPTS=5
KAELY_AUTH_LOGIN_DECAY=15
KAELY_AUTH_API_REQUESTS=60
KAELY_AUTH_API_DECAY=1
```

## ⚡ Performance Features

### Cache Optimization
```php
// Warm up cache
php artisan kaely:optimize-performance --cache-only

// Cache user permissions and roles automatically
$cacheService = app(\Kaely\Auth\Services\CacheService::class);
$permissions = $cacheService->getCachedUserPermissions($userId);
```

### Query Optimization
```php
// Use optimized queries
$queryService = app(\Kaely\Auth\Services\OptimizedQueryService::class);
$user = $queryService->getUserWithRelations($userId, ['roles', 'permissions']);
```

### Performance Monitoring
```php
// Apply performance middleware
Route::middleware('kaely.performance')->group(function () {
    // Routes with performance monitoring
});

// Check performance metrics
php artisan kaely:optimize-performance
```

### Performance Configuration
```php
// In your .env file
KAELY_AUTH_PERFORMANCE_ENABLED=true
KAELY_AUTH_CACHING_ENABLED=true
KAELY_AUTH_USER_PERMISSIONS_TTL=3600
KAELY_AUTH_QUERY_OPTIMIZATION_ENABLED=true
KAELY_AUTH_SLOW_REQUEST_THRESHOLD=1000
```

## 🛠️ Available Commands

### Security Commands
```bash
# Validate configuration
php artisan kaely:validate-config

# Check security status
php artisan kaely:security-check
```

### Performance Commands
```bash
# Optimize performance
php artisan kaely:optimize-performance

# Warm up cache only
php artisan kaely:optimize-performance --cache-only

# Create indexes only
php artisan kaely:optimize-performance --indexes-only

# Optimize tables only
php artisan kaely:optimize-performance --tables-only
``` 

## 🚀 Pre-Launch Checklist

### ✅ Core Features
- [x] Authentication system (login, register, logout)
- [x] OAuth integration (20+ providers)
- [x] Multitenancy support
- [x] Role and permission system
- [x] Password reset functionality
- [x] Email verification
- [x] Session management
- [x] Audit logging
- [x] API endpoints
- [x] UI components (Blade & Livewire)

### ✅ Security Features
- [x] SQL injection protection
- [x] XSS protection
- [x] Rate limiting
- [x] Password strength validation
- [x] Security headers
- [x] CORS protection
- [x] Input sanitization
- [x] Security logging

### ✅ Performance Features
- [x] Intelligent caching
- [x] Query optimization
- [x] Bulk operations
- [x] Performance monitoring
- [x] Memory optimization
- [x] Database indexing

### ✅ Developer Experience
- [x] Interactive installer
- [x] Multi-language support (EN/ES)
- [x] Comprehensive documentation
- [x] Health check command
- [x] Configuration validation
- [x] Performance optimization tools

### ✅ Testing
- [x] Unit tests
- [x] Feature tests
- [x] Security tests
- [x] Performance tests
- [x] Integration tests

### ✅ Documentation
- [x] Installation guide
- [x] Configuration guide
- [x] API documentation
- [x] Security guide
- [x] Performance guide
- [x] Troubleshooting guide

### ✅ Project Structure
- [x] Proper namespaces
- [x] Service providers
- [x] Middleware
- [x] Commands
- [x] Views and components
- [x] Configuration files
- [x] Language files

## 📦 Ready for Release!

The package is now **production-ready** with enterprise-grade features:

- 🔒 **Advanced Security**: Protection against common attacks
- ⚡ **High Performance**: Optimized queries and caching
- 🎨 **Flexible UI**: Blade and Livewire options
- 🌍 **Multi-language**: English and Spanish support
- 📊 **Comprehensive Monitoring**: Health checks and analytics
- 🛠️ **Developer Friendly**: Easy installation and configuration

### Quick Start
```bash
# Install the package
composer require kaely/auth

# Run the installer
php artisan kaely:install

# Check system health
php artisan kaely:health-check

# Optimize performance
php artisan kaely:optimize-performance
```

### Support
- 📖 Documentation: [https://kaely-auth.com/docs](https://kaely-auth.com/docs)
- 🐛 Issues: [https://github.com/kaelytechnology/kaely-auth/issues](https://github.com/kaelytechnology/kaely-auth/issues)
- 💬 Discussions: [https://github.com/kaelytechnology/kaely-auth/discussions](https://github.com/kaelytechnology/kaely-auth/discussions)

---

**Made with ❤️ by the Kaely Team** 

## 📋 Release Information

### Version 1.0.0 (2024-12-19)

This is the initial release of KaelyAuth with all core features implemented and tested:

- ✅ **Core Authentication**: Sanctum, Breeze, Jetstream support
- ✅ **OAuth Integration**: 20+ providers with automatic configuration
- ✅ **Multitenancy**: Domain/subdomain with automatic database switching
- ✅ **Multi-Database**: Single/multiple database modes
- ✅ **Security**: Advanced middleware, validation, monitoring
- ✅ **Performance**: Optimization, caching, monitoring tools
- ✅ **UI Components**: Blade and Livewire options
- ✅ **Testing**: Comprehensive test suite
- ✅ **Documentation**: Complete guides and examples
- ✅ **Multi-Language**: English and Spanish installer

### 🚀 Ready for Production

The package is fully tested and ready for production use. All features have been implemented with proper error handling, security measures, and performance optimizations.

### 📦 Installation

```bash
# Install via Composer
composer require kaely/auth

# Run the interactive installer
php artisan kaely:install
```

### 🔗 Links

- **GitHub**: https://github.com/kaely/auth
- **Documentation**: https://github.com/kaely/auth/blob/main/README.md
- **Issues**: https://github.com/kaely/auth/issues
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)

### 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

**Made with ❤️ by the Kaely Team** 