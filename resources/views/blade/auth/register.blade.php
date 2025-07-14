@extends('kaely-auth::blade.layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Crea tu cuenta</p>
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

        <form method="POST" action="{{ route('register.post') }}" class="kaely-auth-form">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="form-input {{ $hasErrors && $errors->has('name') ? 'is-invalid' : '' }}">
                @if ($hasErrors && $errors->has('name'))
                    <span class="error-message">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input {{ $hasErrors && $errors->has('email') ? 'is-invalid' : '' }}">
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

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input {{ $hasErrors && $errors->has('password_confirmation') ? 'is-invalid' : '' }}">
                @if ($hasErrors && $errors->has('password_confirmation'))
                    <span class="error-message">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>

            <button type="submit" class="form-button">
                Registrarse
            </button>
        </form>

        <div class="auth-links">
            <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>
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