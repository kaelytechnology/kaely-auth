@extends('kaely-auth::blade.layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Verificar email</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="text-center">
            <p>Gracias por registrarte. Antes de comenzar, ¿podrías verificar tu dirección de email haciendo clic en el enlace que acabamos de enviarte? Si no recibiste el email, te enviaremos otro.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                Se ha enviado un nuevo enlace de verificación a la dirección de email que proporcionaste durante el registro.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="kaely-auth-form">
            @csrf

            <button type="submit" class="form-button">
                Reenviar email de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="kaely-auth-form">
            @csrf
            <button type="submit" class="form-button" style="background-color: #6c757d;">
                Cerrar sesión
            </button>
        </form>
    </div>
</div>
@endsection 