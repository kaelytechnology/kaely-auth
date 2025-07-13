# Interactive Installation Wizard

The KaelyAuth package includes an interactive wizard that guides you through the entire installation and configuration process. This is the **recommended** way to install KaelyAuth.

## 🚀 Quick Start

```bash
php artisan kaely:install-wizard
```

## 📋 What the Wizard Does

The wizard will guide you through:

### 1. Welcome & Confirmation
- Displays welcome message
- Asks for confirmation to proceed

### 2. Dependency Checking
- Automatically detects installed packages (Sanctum, Breeze, Jetstream, Socialite)
- Identifies missing dependencies
- Offers to install missing dependencies automatically

### 3. Authentication System Detection
- Detects your current authentication system
- Supports multiple systems if installed
- Allows you to choose which system to use

### 4. Database Configuration
- **Single Database Mode**: Configure prefix for tables
- **Multi-Database Mode**: Configure multiple connections with prefixes
- Handles both simple and complex database setups

### 5. OAuth/Socialite Setup
- Choose which OAuth providers to enable
- Configure client IDs and secrets
- Set up redirect URLs

### 6. Permission Configuration
- Configure permission caching
- Set cache TTL
- Enable/disable auto-sync

### 7. Installation Process
- Publishes configuration files
- Runs migrations
- Configures database mode
- Sets up OAuth (if enabled)
- Updates User model automatically
- Runs seeders (optional)

## 🎯 Example Wizard Session

```bash
$ php artisan kaely:install-wizard

🚀 Bienvenido al Asistente de Instalación de KaelyAuth
==================================================

Este asistente te guiará a través de la instalación y configuración
del paquete KaelyAuth para tu aplicación Laravel.

¿Deseas iniciar el asistente de instalación de KaelyAuth? (yes/no) [yes]:
> yes

📋 Verificando dependencias...
✅ Todas las dependencias están instaladas.

🔍 Detectando sistema de autenticación...
✅ Sistema detectado: Laravel Sanctum (API Tokens)

🗄️  Configuración de Base de Datos
--------------------------------
¿Qué modo de base de datos deseas usar? [single]:
  [0] Base de datos única (recomendado para la mayoría de proyectos)
  [1] Múltiples bases de datos (para proyectos empresariales)
> 0

Configuración de Base de Datos Única
Prefijo para las tablas (ej: "main_", "auth_", "kaely_"): [main_]:
> main_

✅ Configurado: Base de datos única con prefijo 'main_'

🔐 Configuración de OAuth/Socialite
-----------------------------------
¿Deseas configurar autenticación OAuth con redes sociales? (yes/no) [no]:
> no

🔑 Configuración de Permisos
----------------------------
¿Habilitar caché de permisos? (yes/no) [yes]:
> yes

TTL del caché (en minutos): [60]:
> 60

¿Sincronizar permisos automáticamente? (yes/no) [yes]:
> yes

✅ Permisos configurados

💾 Guardando configuración...
✅ Configuración guardada exitosamente

🚀 Iniciando instalación...

📋 Publicando configuración...
🗄️  Ejecutando migraciones...
🔑 Configurando permisos...
👤 Configurando modelo User...
✅ Trait HasPermissions agregado al modelo User

¿Deseas ejecutar los seeders iniciales? (yes/no) [yes]:
> yes

🌱 Ejecutando seeders...
✅ Instalación completada exitosamente!

🎉 ¡Instalación Completada!
========================

📚 Próximos pasos:

1. 📖 Revisa la documentación:
   - Documentación completa: https://kaely-auth.com
   - Documentación local: php artisan serve && visit /docs

2. 🔧 Configuración adicional:
   - Revisa: config/kaely-auth.php
   - Variables de entorno: .env

3. 🧪 Pruebas:
   - Verificar instalación: php artisan kaely:check-dependencies
   - Probar permisos: php artisan tinker

4. 🚀 Comandos útiles:
   - Configurar multi-db: php artisan kaely:configure-multi-db
   - Configurar OAuth: php artisan kaely:configure-oauth
   - Ejecutar seeders: php artisan kaely:seed

5. 📝 Ejemplos de uso:
   - Ver ejemplos: https://kaely-auth.com/examples

¡Gracias por usar KaelyAuth! 🚀
```

## ⚙️ Wizard Options

### Force Installation

Skip all confirmations and use default values:

```bash
php artisan kaely:install-wizard --force
```

### Environment Variables

The wizard automatically adds these environment variables to your `.env` file:

```env
# Database Configuration
KAELY_DATABASE_MODE=single
KAELY_DB_PREFIX=main_

# OAuth Configuration (if enabled)
OAUTH_GOOGLE_CLIENT_ID=your_client_id
OAUTH_GOOGLE_CLIENT_SECRET=your_client_secret
OAUTH_GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# Permissions Configuration
KAELY_CACHE_ENABLED=true
KAELY_CACHE_TTL=60
KAELY_AUTO_SYNC=true
```

## 🔧 Post-Installation

After running the wizard, you can:

### Verify Installation

```bash
# Check dependencies
php artisan kaely:check-dependencies

# Test permissions
php artisan tinker
>>> auth()->user()->hasPermission('manage-users')
```

### Customize Configuration

```bash
# Edit configuration
nano config/kaely-auth.php

# Add more OAuth providers
php artisan kaely:configure-oauth

# Configure multiple databases
php artisan kaely:configure-multi-db
```

### Update User Model

The wizard automatically adds the `HasPermissions` trait to your User model. If you need to do this manually:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kaely\Auth\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasPermissions;
    
    // ... rest of your User model
}
```

## 🚨 Troubleshooting

### Wizard Fails to Start

```bash
# Check if command exists
php artisan list | grep kaely

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Dependencies Missing

```bash
# Install missing dependencies
php artisan kaely:check-dependencies --install

# Or install manually
composer require laravel/sanctum
composer require laravel/socialite
```

### Configuration Issues

```bash
# Re-run wizard
php artisan kaely:install-wizard

# Or configure manually
php artisan kaely:configure-auth
```

## 📚 Next Steps

1. **Read the Documentation**: Visit [https://kaely-auth.com](https://kaely-auth.com)
2. **Explore Examples**: Check out practical examples
3. **Configure OAuth**: Set up social authentication
4. **Set Up Multi-DB**: Configure multiple databases if needed
5. **Customize Permissions**: Create your own roles and permissions

The wizard makes KaelyAuth installation simple and ensures everything is configured correctly for your specific needs! 