@extends('kaely-auth::blade.layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Inicia sesión en tu cuenta</p>
        </div>

        @php
            $errors = $errors ?? session('errors');
            $hasErrors = $errors && $errors->any();
        @endphp

        @if ($hasErrors)
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="kaely-auth-form">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-input {{ $hasErrors && $errors->has('email') ? 'is-invalid' : '' }}">
                @if ($hasErrors && $errors->has('email'))
                    <span class="error-message">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" type="password" name="password" required class="form-input {{ $hasErrors && $errors->has('password') ? 'is-invalid' : '' }}">
                @if ($hasErrors && $errors->has('password'))
                    <span class="error-message">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                <label style="display: flex; align-items: center; margin: 0;">
                    <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                    <span style="font-size: 0.875rem;">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size: 0.875rem; color: #3b82f6; text-decoration: none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <button type="submit" class="form-button">
                Iniciar Sesión
            </button>
        </form>

        <div class="auth-links">
            <p>¿No tienes una cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
        </div>
    </div>
</div>

<style>
    .error-message {
        color: #dc2626;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .is-invalid {
        border-color: #dc2626 !important;
    }
    
    .is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }
</style>
@endsection 