---
id: installation
title: Instalación
sidebar_label: Instalación
---

# Instalación

Esta guía te ayudará a instalar y configurar KaelyAuth en tu proyecto Laravel.

## 📋 Prerrequisitos

KaelyAuth requiere:

- **PHP** 8.1 o superior
- **Laravel** 8.x o superior
- **Composer** 2.0 o superior
- **Node.js** 18.0 o superior (para documentación)

### Sistemas de Autenticación Soportados

KaelyAuth es compatible con múltiples sistemas de autenticación Laravel:

- **Laravel Sanctum** (`laravel/sanctum`) - Para APIs (recomendado)
- **Laravel Breeze** (`laravel/breeze`) - Para aplicaciones web
- **Laravel Jetstream** (`laravel/jetstream`) - Para aplicaciones complejas
- **Laravel Framework** (8.x o superior) - Autenticación básica

## 🚀 Instalación Paso a Paso

### Paso 1: Instalar el Paquete

```bash
composer require kaely/auth
```

### Paso 2: Verificar Dependencias

El paquete verificará automáticamente las dependencias requeridas:

```bash
php artisan kaely:check-dependencies
```

Si faltan dependencias, puedes instalarlas automáticamente:

```bash
php artisan kaely:check-dependencies --install
```

### Paso 3: Instalación Completa

```bash
php artisan kaely:install
```

Este comando realizará:

- ✅ Verificación de dependencias
- ✅ Publicación de configuración
- ✅ Publicación de migraciones
- ✅ Ejecución de migraciones
- ✅ Configuración del modelo User
- ✅ Registro de middleware
- ✅ Configuración de rutas

### Paso 4: Configurar Sistema de Autenticación

```bash
php artisan kaely:configure-auth
```

Este comando detectará automáticamente tu sistema de autenticación y lo configurará.

## ⚙️ Configuración Manual (Opcional)

Si prefieres configurar manualmente:

### Publicar Configuración

```bash
php artisan vendor:publish --tag=kaely-auth-config
```

### Publicar Migraciones

```bash
php artisan vendor:publish --tag=kaely-auth-migrations
```

### Ejecutar Migraciones

```bash
php artisan migrate
```

### Publicar Seeders (Opcional)

```bash
php artisan vendor:publish --tag=kaely-auth-seeders
```

## 🔧 Configuración Inicial

### Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# Configuración de Base de Datos
KAELY_DB_MODE=single
KAELY_ACTIVE_CONNECTIONS=main
KAELY_CROSS_DB_TRANSACTIONS=false

# Autenticación
KAELY_AUTH_GUARD=web
KAELY_AUTH_PROVIDER=users
KAELY_AUTH_EXPIRE_TOKENS=604800
KAELY_AUTH_REFRESH_TOKENS=true

# Permisos
KAELY_PERMISSIONS_CACHE=true
KAELY_PERMISSIONS_CACHE_TTL=3600
KAELY_SUPER_ADMIN_ROLE=super-admin
KAELY_ADMIN_ROLE=admin

# Menú
KAELY_MENU_CACHE=true
KAELY_MENU_CACHE_TTL=1800
KAELY_MENU_INCLUDE_INACTIVE=false

# API
KAELY_API_PREFIX=api/v1
KAELY_RATE_LIMITING=true
KAELY_RATE_LIMIT_MAX=60
KAELY_RATE_LIMIT_DECAY=1
```

### Configurar Modelo User

Asegúrate de que tu modelo `User` tenga el trait `HasPermissions`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kaely\Auth\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasPermissions;

    // ... resto del modelo
}
```

## 🗄️ Configuración de Base de Datos

### Modo Base de Datos Única (Por Defecto)

```bash
php artisan kaely:configure-multi-db --mode=single
```

### Modo Múltiples Bases de Datos

```bash
php artisan kaely:configure-multi-db --mode=multiple --connections=main,pos,inventory
```

## 🔐 Configuración OAuth (Opcional)

Si quieres usar autenticación social:

```bash
# Instalar Socialite
composer require laravel/socialite

# Configurar OAuth
php artisan kaely:configure-oauth
```

## 📊 Verificación de Instalación

### Verificar Estado

```bash
php artisan kaely:check-dependencies
```

### Verificar Rutas

```bash
php artisan route:list | grep kaely
```

### Verificar Configuración

```bash
php artisan config:show kaely-auth
```

### Testing Básico

```bash
php artisan tinker
```

```php
// Verificar que el paquete está funcionando
>>> app('kaely.auth')->getSystemStats();
>>> App\Models\User::first()->hasPermission('view-users');
```

## 🚨 Solución de Problemas

### Error "Class not found"

```bash
composer dump-autoload
php artisan cache:clear
```

### Error "Dependencies missing"

```bash
php artisan kaely:check-dependencies --install
```

### Error "Table already exists"

```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

### Permisos no funcionan

Verifica que el modelo User tenga el trait:

```bash
php artisan tinker
>>> class_uses(App\Models\User::class);
```

## 📚 Próximos Pasos

1. **[Configuración](/docs/configuration)** - Configurar el paquete
2. **[Guía de Migración](/docs/migration-guide)** - Migrar desde sistemas existentes
3. **[API Reference](/api/)** - Documentación de la API
4. **[Ejemplos](/examples/)** - Casos de uso prácticos

## 🎯 Comandos Útiles

```bash
# Verificar estado general
php artisan kaely:check-dependencies

# Configurar múltiples bases de datos
php artisan kaely:configure-multi-db --mode=multiple

# Configurar OAuth
php artisan kaely:configure-oauth

# Sembrar datos iniciales
php artisan kaely:seed

# Optimizar tablas
php artisan kaely:optimize-tables

# Crear índices
php artisan kaely:create-indexes
```

## 🤝 Soporte

Si encuentras problemas durante la instalación:

1. Revisar logs: `storage/logs/laravel.log`
2. Verificar configuración: `config/kaely-auth.php`
3. Ejecutar verificaciones: `php artisan kaely:check-dependencies`
4. Abrir issue en [GitHub](https://github.com/kaely/kaely-auth/issues)

---

**¡Instalación Completada!** 🎉

Tu proyecto Laravel ahora tiene KaelyAuth configurado y listo para usar.

[Configurar KaelyAuth →](/docs/configuration) 