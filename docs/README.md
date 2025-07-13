# KaelyAuth Documentation

## 📚 Documentación del Paquete

Bienvenido a la documentación completa del paquete **KaelyAuth**. Este paquete proporciona un sistema avanzado de autenticación y autorización para Laravel con soporte para múltiples bases de datos y OAuth.

## 📖 Documentación Disponible

### 🇪🇸 Documentación en Español
- **[README_ES.md](README_ES.md)** - Documentación completa en español
  - Instalación y configuración
  - Uso del paquete
  - Soporte multi-base de datos
  - Integración OAuth/Socialite
  - Compatibilidad con sistemas de autenticación
  - Solución de problemas

### 🇺🇸 English Documentation
- **[README_EN.md](README_EN.md)** - Complete documentation in English
  - Installation and configuration
  - Package usage
  - Multi-database support
  - OAuth/Socialite integration
  - Authentication system compatibility
  - Troubleshooting

## 🚀 Características Principales / Main Features

### ✅ Funcionalidades / Features
- **Soporte Multi-Base de Datos** / **Multi-Database Support**
- **Integración OAuth/Socialite** / **OAuth/Socialite Integration**
- **Control de Acceso Basado en Roles (RBAC)** / **Role-Based Access Control (RBAC)**
- **Generación de Menús Dinámicos** / **Dynamic Menu Generation**
- **Caché Integrado** / **Built-in Caching**
- **Middleware Listo para Usar** / **Ready-to-Use Middleware**
- **Directivas Blade** / **Blade Directives**
- **API Completa** / **Complete API**
- **Transacciones Cross-Database** / **Cross-Database Transactions**

### 🔧 Sistemas de Autenticación Soportados / Supported Authentication Systems
- **Laravel Sanctum** - Para APIs (recomendado) / For APIs (recommended)
- **Laravel Breeze** - Para aplicaciones web / For web applications
- **Laravel Jetstream** - Para aplicaciones complejas / For complex applications

### 🗄️ Modos de Base de Datos / Database Modes
- **Base de Datos Única** / **Single Database** (por defecto / default)
- **Múltiples Bases de Datos** / **Multiple Databases** (configurable)

### 🔐 Proveedores OAuth Soportados / Supported OAuth Providers
- **Google** - OAuth 2.0
- **Facebook** - OAuth 2.0
- **GitHub** - OAuth 2.0
- **LinkedIn** - OAuth 2.0
- **Twitter** - OAuth 2.0

## 📦 Instalación Rápida / Quick Installation

```bash
# 1. Instalar el paquete / Install the package
composer require kaely/auth

# 2. Verificar dependencias / Check dependencies
php artisan kaely:check-dependencies

# 3. Instalación completa / Complete installation
php artisan kaely:install

# 4. Configurar sistema de autenticación / Configure auth system
php artisan kaely:configure-auth
```

## 🔧 Comandos Principales / Main Commands

```bash
# Verificar dependencias / Check dependencies
php artisan kaely:check-dependencies

# Configurar múltiples bases de datos / Configure multiple databases
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory

# Configurar OAuth / Configure OAuth
php artisan kaely:configure-oauth

# Sembrar datos iniciales / Seed initial data
php artisan kaely:seed
```

## 📖 Ejemplos de Uso / Usage Examples

### Verificación de Permisos / Permission Checks
```php
// En controladores / In controllers
if ($user->hasPermission('manage-users')) {
    // Usuario puede gestionar usuarios / User can manage users
}

// En plantillas Blade / In Blade templates
@permission('manage-users')
    <button>Gestionar Usuarios</button>
@endpermission
```

### Middleware / Middleware
```php
// En rutas / In routes
Route::middleware(['auth:sanctum', 'kaely.permission:manage-users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

## 🔗 Enlaces Útiles / Useful Links

- **[Documentación en Español](README_ES.md)** - Guía completa en español
- **[English Documentation](README_EN.md)** - Complete guide in English
- **[Configuración](README_ES.md#⚙️-configuración)** - Variables de entorno
- **[API Endpoints](README_ES.md#api-endpoints)** - Endpoints disponibles
- **[Solución de Problemas](README_ES.md#🔧-solución-de-problemas)** - Troubleshooting

## 📞 Soporte / Support

Para soporte técnico, por favor abre un issue en el repositorio de GitHub o contacta al equipo de desarrollo.

For technical support, please open an issue in the GitHub repository or contact the development team.

---

**KaelyAuth** - Sistema Avanzado de Autenticación y Autorización para Laravel
**KaelyAuth** - Advanced Authentication & Authorization System for Laravel 