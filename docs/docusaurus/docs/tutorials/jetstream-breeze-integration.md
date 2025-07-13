# Tutorial: Integración con Laravel Jetstream y Breeze

Este tutorial te guiará a través de la integración de KaelyAuth con Laravel Jetstream y Breeze, los dos sistemas de autenticación más populares de Laravel.

## 🎯 Objetivo

Integrar KaelyAuth con Jetstream o Breeze para agregar gestión de roles y permisos a estos sistemas de autenticación.

## ⏱️ Tiempo Estimado

20-30 minutos

## 📋 Prerrequisitos

- Laravel 8.x o superior
- Jetstream o Breeze instalado
- Composer instalado

## 🚀 Paso a Paso

### Opción A: Integración con Laravel Jetstream

#### Paso 1: Instalar Jetstream

```bash
# Instalar Jetstream
composer require laravel/jetstream

# Instalar con Livewire (recomendado)
php artisan jetstream:install livewire

# O instalar con Inertia.js
php artisan jetstream:install inertia

# Ejecutar migraciones
php artisan migrate

# Instalar dependencias frontend
npm install && npm run dev
```

#### Paso 2: Instalar KaelyAuth

```bash
# Instalar KaelyAuth
composer require kaely/auth

# Ejecutar el wizard
php artisan kaely:install-wizard
```

Durante el wizard, selecciona:

```
🔍 Detectando sistema de autenticación...
✅ Sistema detectado: Laravel Jetstream (Full-stack)

🔧 Configurando sistema de autenticación...
¿Deseas configurar automáticamente para Jetstream? (yes/no) [yes]:
> yes
```

#### Paso 3: Configurar Modelo User

El wizard automáticamente actualizará tu modelo User. Verifica que `app/Models/User.php` contenga:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Kaely\Auth\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use HasPermissions; // Agregado por KaelyAuth
    use Notifiable;
    use TwoFactorAuthenticatable;

    // ... resto del modelo
}
```

#### Paso 4: Configurar Vistas de Jetstream

Crea `resources/views/navigation-menu.blade.php` (si no existe):

```php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-jet-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-jet-nav-link>
                    
                    @permission('manage-users')
                    <x-jet-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-jet-nav-link>
                    @endpermission
                    
                    @permission('manage-roles')
                    <x-jet-nav-link href="{{ route('roles.index') }}" :active="request()->routeIs('roles.*')">
                        {{ __('Roles') }}
                    </x-jet-nav-link>
                    @endpermission
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-jet-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                            <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-jet-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-jet-dropdown-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('API Tokens') }}
                            </x-jet-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf

                            <x-jet-dropdown-link href="{{ route('logout') }}"
                                     onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-jet-dropdown-link>
                        </form>
                    </x-slot>
                </x-jet-dropdown>
            </div>
        </div>
    </div>
</nav>
```

#### Paso 5: Crear Controladores de Gestión

Crea `app/Http/Controllers/UserController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kaely\Auth\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::with('roles')->paginate(10);
        
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        $roles = Role::all();
        
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'array'
        ]);
        
        $user->update($request->only(['name', 'email']));
        
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }
}
```

#### Paso 6: Configurar Rutas

Edita `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Kaely\Auth\Http\Controllers\RoleController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Rutas de gestión de usuarios
    Route::resource('users', UserController::class);
    
    // Rutas de gestión de roles (proporcionadas por KaelyAuth)
    Route::resource('roles', RoleController::class);
});
```

### Opción B: Integración con Laravel Breeze

#### Paso 1: Instalar Breeze

```bash
# Instalar Breeze
composer require laravel/breeze --dev

# Instalar con Blade (recomendado)
php artisan breeze:install blade

# O instalar con Inertia.js
php artisan breeze:install inertia

# Ejecutar migraciones
php artisan migrate

# Instalar dependencias frontend
npm install && npm run dev
```

#### Paso 2: Instalar KaelyAuth

```bash
# Instalar KaelyAuth
composer require kaely/auth

# Ejecutar el wizard
php artisan kaely:install-wizard
```

Durante el wizard, selecciona:

```
🔍 Detectando sistema de autenticación...
✅ Sistema detectado: Laravel Breeze (Session-based)

🔧 Configurando sistema de autenticación...
¿Deseas configurar automáticamente para Breeze? (yes/no) [yes]:
> yes
```

#### Paso 3: Configurar Modelo User

Verifica que `app/Models/User.php` contenga:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Kaely\Auth\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

#### Paso 4: Configurar Vistas de Breeze

Edita `resources/views/layouts/navigation.blade.php`:

```php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    @permission('manage-users')
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-nav-link>
                    @endpermission
                    
                    @permission('manage-roles')
                    <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                        {{ __('Roles') }}
                    </x-nav-link>
                    @endpermission
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
```

#### Paso 5: Configurar Rutas

Edita `routes/web.php`:

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use Kaely\Auth\Http\Controllers\RoleController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas de gestión de usuarios
    Route::resource('users', UserController::class);
    
    // Rutas de gestión de roles (proporcionadas por KaelyAuth)
    Route::resource('roles', RoleController::class);
});

require __DIR__.'/auth.php';
```

## 🔧 Configuración Manual (Alternativa)

### 1. Configurar Automáticamente

```bash
# Configurar para el sistema detectado
php artisan kaely:configure-auth
```

### 2. Verificar Integración

```bash
# Verificar que el trait esté agregado
php artisan tinker
>>> $user = \App\Models\User::first();
>>> method_exists($user, 'hasPermission');
```

## 🧪 Pruebas

### Verificar Integración con Jetstream

```bash
php artisan tinker
```

```php
// Crear usuario de prueba
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);

// Verificar que el trait funciona
$user->hasPermission('manage-users'); // false (no tiene permisos aún)

// Crear rol y asignar
$role = \Kaely\Auth\Models\Role::create([
    'name' => 'admin',
    'display_name' => 'Administrator'
]);

$user->assignRole('admin');

// Verificar rol
$user->hasRole('admin'); // true
```

### Verificar Integración con Breeze

```php
// Verificar que el trait funciona
$user = \App\Models\User::first();
$user->hasPermission('manage-users');

// Verificar roles
$user->hasRole('admin');
```

## 🔍 Solución de Problemas

### Error: "Trait not found"

```bash
# Verificar que el trait esté agregado al modelo User
php artisan tinker
>>> $user = \App\Models\User::first();
>>> method_exists($user, 'hasPermission');
```

### Error: "Jetstream not detected"

```bash
# Verificar instalación de Jetstream
composer show laravel/jetstream

# Reinstalar si es necesario
composer require laravel/jetstream
php artisan jetstream:install livewire
```

### Error: "Breeze not detected"

```bash
# Verificar instalación de Breeze
composer show laravel/breeze

# Reinstalar si es necesario
composer require laravel/breeze --dev
php artisan breeze:install blade
```

### Error: "Permission denied"

```bash
# Verificar que las tablas existan
php artisan migrate

# Verificar que el usuario tenga roles
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->assignRole('admin');
```

## 📈 Monitoreo

### Verificar Usuarios con Roles

```bash
php artisan tinker
>>> \App\Models\User::with('roles')->get()->each(function($user) {
    echo $user->name . ': ' . $user->roles->pluck('name')->implode(', ') . "\n";
});
```

### Verificar Permisos

```bash
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->getAllPermissions()->pluck('name');
```

## 🚀 Despliegue

### Configuración de Producción

```env
# Producción
KAELY_AUTH_GUARD=web
KAELY_AUTH_PROVIDER=users
KAELY_PERMISSIONS_CACHE=true
```

### Configuración de Jetstream

```env
# Jetstream
JETSTREAM_STACK=livewire
JETSTREAM_TEAMS=true
```

### Configuración de Breeze

```env
# Breeze
BREEZE_STACK=blade
```

## 🎉 ¡Listo!

Tu aplicación ahora tiene:
- ✅ Integración completa con Jetstream/Breeze
- ✅ Gestión de roles y permisos
- ✅ Middleware de permisos
- ✅ Blade directives
- ✅ API endpoints
- ✅ Vistas de gestión

## 📖 Recursos Adicionales

- [Documentación Completa](https://kaely-auth.com)
- [API Reference](https://kaely-auth.com/api)
- [Ejemplos Prácticos](https://kaely-auth.com/examples) 