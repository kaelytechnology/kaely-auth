@extends('kaely-auth::blade.layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Crea tu cuenta</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="kaely-auth-form">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="form-input">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" type="password" name="password" required class="form-input">
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input">
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
@endsection 