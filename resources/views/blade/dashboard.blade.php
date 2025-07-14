@extends('kaely-auth::blade.layouts.app')

@section('content')
<div style="min-height: 100vh; background-color: #f8fafc;">
    <!-- Navigation -->
    <nav style="background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1rem;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #374151;">
                {{ config('app.name', 'Laravel') }}
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="color: #6b7280;">Bienvenido, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.375rem; cursor: pointer;">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 700; color: #374151; margin-bottom: 1rem;">
                Dashboard
            </h1>
            
            <p style="color: #6b7280; margin-bottom: 2rem;">
                Bienvenido a tu panel de control. Aquí puedes gestionar tu cuenta y acceder a las funciones disponibles.
            </p>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div style="background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">
                        Información del Usuario
                    </h3>
                    <p style="color: #374151; margin-bottom: 0.5rem;">
                        <strong>Nombre:</strong> {{ auth()->user()->name }}
                    </p>
                    <p style="color: #374151; margin-bottom: 0.5rem;">
                        <strong>Email:</strong> {{ auth()->user()->email }}
                    </p>
                    <p style="color: #374151;">
                        <strong>Miembro desde:</strong> {{ auth()->user()->created_at->format('d/m/Y') }}
                    </p>
                </div>

                <div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 0.5rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #065f46; margin-bottom: 0.5rem;">
                        Acciones Rápidas
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="{{ route('profile') }}" style="color: #065f46; text-decoration: none; padding: 0.5rem; border-radius: 0.25rem; background: rgba(16, 185, 129, 0.1);">
                            Editar Perfil
                        </a>
                        <a href="{{ route('password.change') }}" style="color: #065f46; text-decoration: none; padding: 0.5rem; border-radius: 0.25rem; background: rgba(16, 185, 129, 0.1);">
                            Cambiar Contraseña
                        </a>
                    </div>
                </div>

                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #92400e; margin-bottom: 0.5rem;">
                        Estado de la Cuenta
                    </h3>
                    <p style="color: #374151; margin-bottom: 0.5rem;">
                        <strong>Email verificado:</strong> 
                        @if(auth()->user()->email_verified_at)
                            <span style="color: #059669;">✓ Sí</span>
                        @else
                            <span style="color: #dc2626;">✗ No</span>
                        @endif
                    </p>
                    <p style="color: #374151;">
                        <strong>Último acceso:</strong> {{ auth()->user()->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div style="background: #f9fafb; border-radius: 0.5rem; padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; color: #374151; margin-bottom: 1rem;">
                    Actividad Reciente
                </h3>
                <p style="color: #6b7280;">
                    No hay actividad reciente para mostrar.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 