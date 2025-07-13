---
id: intro
title: Introducción
sidebar_label: Introducción
---

# KaelyAuth

**KaelyAuth** es un paquete completo de Laravel que proporciona funcionalidades avanzadas de autenticación y autorización con soporte para múltiples bases de datos. Encapsula toda la lógica de permisos de tu proyecto en un paquete reutilizable y configurable.

## 🚀 Características Principales

<div className="row">
  <div className="col col--6">
    <h3>🔐 Autenticación Avanzada</h3>
    <ul>
      <li>Soporte para múltiples sistemas de autenticación</li>
      <li>Integración OAuth/Socialite completa</li>
      <li>Tokens JWT y API keys</li>
      <li>Autenticación multi-tenant</li>
    </ul>
  </div>
  <div className="col col--6">
    <h3>🎭 Control de Acceso</h3>
    <ul>
      <li>Sistema RBAC (Role-Based Access Control)</li>
      <li>Permisos granulares</li>
      <li>Middleware listo para usar</li>
      <li>Directivas Blade</li>
    </ul>
  </div>
</div>

<div className="row">
  <div className="col col--6">
    <h3>🗄️ Multi-Base de Datos</h3>
    <ul>
      <li>Soporte para múltiples conexiones</li>
      <li>Transacciones cross-database</li>
      <li>Sincronización automática</li>
      <li>Configuración flexible</li>
    </ul>
  </div>
  <div className="col col--6">
    <h3>🍽️ Menús Dinámicos</h3>
    <ul>
      <li>Generación automática de menús</li>
      <li>Basado en permisos de usuario</li>
      <li>Soporte para iconos y rutas</li>
      <li>Ordenamiento personalizable</li>
    </ul>
  </div>
</div>

## 📦 Instalación Rápida

```bash
# 1. Instalar el paquete
composer require kaely/auth

# 2. Verificar dependencias
php artisan kaely:check-dependencies

# 3. Instalación completa
php artisan kaely:install

# 4. Configurar sistema de autenticación
php artisan kaely:configure-auth
```

## 🔧 Configuración Básica

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
```

## 💡 Ejemplo de Uso

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
        $user = Auth::user();
        
        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }
}
```

## 🎯 Casos de Uso

### E-commerce
- Gestión de roles de cliente, vendedor, administrador
- Permisos granulares para productos, pedidos, pagos
- Menús dinámicos según el tipo de usuario

### CRM
- Roles por departamento (ventas, soporte, marketing)
- Permisos específicos por módulo
- Acceso controlado a datos sensibles

### Panel de Administración
- Roles de super admin, admin, moderador
- Gestión completa de usuarios y permisos
- Auditoría de acciones

### Multi-tenant
- Aislamiento de datos por tenant
- Roles específicos por organización
- Configuración independiente

## 🔗 Compatibilidad

KaelyAuth es compatible con:

- **Laravel Sanctum** - Para APIs (recomendado)
- **Laravel Breeze** - Para aplicaciones web
- **Laravel Jetstream** - Para aplicaciones complejas
- **Laravel Framework** - 8.x o superior

## 🚀 Próximos Pasos

1. **[Instalación](/docs/installation)** - Guía completa de instalación
2. **[Configuración](/docs/configuration)** - Configurar el paquete
3. **[Guía de Migración](/docs/migration-guide)** - Migrar desde sistemas existentes
4. **[API Reference](/api/)** - Documentación completa de la API
5. **[Ejemplos](/examples/)** - Casos de uso prácticos

## 🤝 Contribuir

¿Te gustaría contribuir al proyecto?

- 📖 [Guía de Contribución](/docs/reference/contributing)
- 🐛 [Reportar Bugs](https://github.com/kaely/kaely-auth/issues)
- 💡 [Sugerir Funcionalidades](https://github.com/kaely/kaely-auth/discussions)
- 📝 [Documentación](https://github.com/kaely/kaely-auth/edit/main/docs/)

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](https://github.com/kaely/kaely-auth/blob/main/LICENSE) para más detalles.

---

**¿Listo para comenzar?** 🚀

[Instalar KaelyAuth →](/docs/installation) 