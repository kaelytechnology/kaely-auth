# Tutorial: OAuth con Google y Facebook

Este tutorial te guiará a través de la configuración de autenticación OAuth con Google y Facebook usando KaelyAuth.

## 🎯 Objetivo

Configurar autenticación social con Google y Facebook para permitir que los usuarios inicien sesión usando sus cuentas de redes sociales.

## ⏱️ Tiempo Estimado

30-45 minutos

## 📋 Prerrequisitos

- Laravel 8.x o superior
- Cuentas de desarrollador en Google y Facebook
- Dominio configurado (para URLs de redirección)

## 🚀 Paso a Paso

### Paso 1: Crear Aplicaciones OAuth

#### Google OAuth

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la API de Google+ 
4. Ve a "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Configura:
   - **Application type**: Web application
   - **Name**: Tu aplicación
   - **Authorized redirect URIs**: `http://localhost:8000/auth/google/callback` (desarrollo)
6. Guarda el **Client ID** y **Client Secret**

#### Facebook OAuth

1. Ve a [Facebook Developers](https://developers.facebook.com/)
2. Crea una nueva aplicación
3. Ve a "Settings" → "Basic"
4. Configura:
   - **App Domains**: `localhost` (desarrollo)
   - **Privacy Policy URL**: Tu política de privacidad
5. Ve a "Facebook Login" → "Settings"
6. Configura:
   - **Valid OAuth Redirect URIs**: `http://localhost:8000/auth/facebook/callback`
7. Guarda el **App ID** y **App Secret**

### Paso 2: Instalar KaelyAuth

```bash
# Instalar el paquete
composer require kaely/auth

# Ejecutar el wizard
php artisan kaely:install-wizard
```

Durante el wizard, selecciona:

```
🔐 Configuración de OAuth/Socialite
-----------------------------------
¿Deseas configurar autenticación OAuth con redes sociales? (yes/no) [no]:
> yes

Selecciona los proveedores OAuth (usa espacio para seleccionar múltiples):
  [0] Google
  [1] Facebook
  [2] GitHub
  [3] Twitter
  [4] LinkedIn
  [5] Microsoft
> 0 1

Configurando Google:
Client ID para Google:
> tu_google_client_id

Client Secret para Google:
> tu_google_client_secret

URL de redirección para Google (dejar vacío para usar la predeterminada): [http://localhost:8000/auth/google/callback]:
> 

Configurando Facebook:
Client ID para Facebook:
> tu_facebook_app_id

Client Secret para Facebook:
> tu_facebook_app_secret

URL de redirección para Facebook (dejar vacío para usar la predeterminada): [http://localhost:8000/auth/facebook/callback]:
> 
```

### Paso 3: Configurar Variables de Entorno

Edita tu archivo `.env`:

```env
# OAuth Configuration
KAELY_OAUTH_ENABLED=true
KAELY_OAUTH_GOOGLE_ENABLED=true
KAELY_OAUTH_FACEBOOK_ENABLED=true
KAELY_OAUTH_AUTO_CREATE_USERS=true
KAELY_OAUTH_AUTO_ASSIGN_ROLES=true
KAELY_OAUTH_DEFAULT_ROLE=user
KAELY_OAUTH_SYNC_AVATAR=true

# Google OAuth
GOOGLE_CLIENT_ID=tu_google_client_id
GOOGLE_CLIENT_SECRET=tu_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=tu_facebook_app_id
FACEBOOK_CLIENT_SECRET=tu_facebook_app_secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback
```

### Paso 4: Configurar Socialite

Edita `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
],
```

### Paso 5: Configurar OAuth

```bash
# Configurar OAuth
php artisan kaely:configure-oauth
```

### Paso 6: Crear Controlador de OAuth

Crea `app/Http/Controllers/Auth/OAuthController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Kaely\Auth\Services\OAuthService;

class OAuthController extends Controller
{
    protected $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            
            $result = $this->oauthService->handleCallback('google', $user);
            
            if ($result['success']) {
                auth()->login($result['user']);
                return redirect('/dashboard');
            }
            
            return redirect('/login')->with('error', 'Error en autenticación con Google');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error en autenticación con Google');
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();
            
            $result = $this->oauthService->handleCallback('facebook', $user);
            
            if ($result['success']) {
                auth()->login($result['user']);
                return redirect('/dashboard');
            }
            
            return redirect('/login')->with('error', 'Error en autenticación con Facebook');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error en autenticación con Facebook');
        }
    }
}
```

### Paso 7: Configurar Rutas

Edita `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;

// OAuth Routes
Route::get('/auth/google', [OAuthController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [OAuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

Route::get('/auth/facebook', [OAuthController::class, 'redirectToFacebook'])
    ->name('auth.facebook');

Route::get('/auth/facebook/callback', [OAuthController::class, 'handleFacebookCallback'])
    ->name('auth.facebook.callback');
```

### Paso 8: Crear Vista de Login

Crea `resources/views/auth/login.blade.php`:

```php
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- OAuth Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-8 offset-md-4">
                            <a href="{{ route('auth.google') }}" class="btn btn-danger">
                                <i class="fab fa-google"></i> Login con Google
                            </a>
                            
                            <a href="{{ route('auth.facebook') }}" class="btn btn-primary">
                                <i class="fab fa-facebook"></i> Login con Facebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 🔧 Configuración Manual (Alternativa)

### 1. Instalar Socialite

```bash
composer require laravel/socialite
```

### 2. Publicar Configuración

```bash
php artisan vendor:publish --tag=kaely-auth-config
```

### 3. Configurar OAuth Manualmente

Edita `config/kaely-auth.php`:

```php
'oauth' => [
    'enabled' => true,
    'providers' => [
        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
        ],
        'facebook' => [
            'client_id' => env('FACEBOOK_CLIENT_ID'),
            'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'redirect' => env('FACEBOOK_REDIRECT_URI'),
        ],
    ],
    'auto_create_users' => true,
    'auto_assign_roles' => true,
    'default_role' => 'user',
    'sync_avatar' => true,
],
```

## 🧪 Pruebas

### Verificar Configuración OAuth

```bash
php artisan tinker
```

```php
// Verificar si OAuth está habilitado
$oauthService = app('Kaely\Auth\Services\OAuthService');
$oauthService->isEnabled(); // true

// Verificar proveedores habilitados
$oauthService->getEnabledProviders(); // ['google', 'facebook']
```

### Probar Login OAuth

1. Ve a `http://localhost:8000/login`
2. Haz clic en "Login con Google" o "Login con Facebook"
3. Completa el flujo de autenticación
4. Verifica que el usuario se cree automáticamente

### Verificar Usuario Creado

```php
// Verificar usuario creado por OAuth
$user = \App\Models\User::where('email', 'tu_email@gmail.com')->first();
$user->oauth_provider; // 'google'
$user->oauth_id; // ID de Google
```

## 🔍 Solución de Problemas

### Error: "Invalid redirect URI"

```bash
# Verificar URLs de redirección en Google/Facebook
# Asegúrate de que coincidan exactamente con las configuradas
```

### Error: "Client ID not found"

```bash
# Verificar variables de entorno
php artisan tinker
>>> env('GOOGLE_CLIENT_ID')
>>> env('FACEBOOK_CLIENT_ID')
```

### Error: "Socialite driver not found"

```bash
# Verificar que Socialite esté instalado
composer require laravel/socialite

# Limpiar caché
php artisan config:clear
```

### Error: "User not found after OAuth"

```bash
# Verificar configuración de auto-creación
php artisan tinker
>>> config('kaely-auth.oauth.auto_create_users')
```

## 📈 Monitoreo

### Verificar Estadísticas OAuth

```bash
# Ver estadísticas de OAuth
php artisan kaely:oauth-stats
```

### Verificar Usuarios OAuth

```bash
# Ver usuarios creados por OAuth
php artisan tinker
>>> \App\Models\User::whereNotNull('oauth_provider')->count();
```

## 🚀 Despliegue

### Configuración de Producción

```env
# Producción
GOOGLE_REDIRECT_URI=https://tudominio.com/auth/google/callback
FACEBOOK_REDIRECT_URI=https://tudominio.com/auth/facebook/callback
```

### Configuración de Google/Facebook

1. **Google**: Agrega tu dominio de producción a las URIs autorizadas
2. **Facebook**: Agrega tu dominio de producción a los dominios de la app

## 🎉 ¡Listo!

Tu aplicación ahora tiene:
- ✅ Autenticación OAuth con Google
- ✅ Autenticación OAuth con Facebook
- ✅ Creación automática de usuarios
- ✅ Asignación automática de roles
- ✅ Sincronización de avatares
- ✅ Manejo de errores

## 📖 Recursos Adicionales

- [Documentación Completa](https://kaely-auth.com)
- [API Reference](https://kaely-auth.com/api)
- [Ejemplos Prácticos](https://kaely-auth.com/examples) 