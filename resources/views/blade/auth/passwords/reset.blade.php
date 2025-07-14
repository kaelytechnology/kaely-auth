@extends('kaely-auth::blade.layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Restablecer contraseña</p>
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

        <form method="POST" action="{{ route('password.update') }}" class="kaely-auth-form">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus class="form-input">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nueva contraseña</label>
                <input id="password" type="password" name="password" required class="form-input">
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input">
            </div>

            <button type="submit" class="form-button">
                Restablecer contraseña
            </button>
        </form>

        <div class="auth-links">
            <p><a href="{{ route('login') }}">Volver al login</a></p>
        </div>
    </div>
</div>
@endsection 