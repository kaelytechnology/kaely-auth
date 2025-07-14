<?php

return [
    'welcome' => [
        'title' => '🚀 ¡Bienvenido al Asistente de Instalación de KaelyAuth!',
        'subtitle' => '==========================================',
    ],

    'language_selection' => [
        'title' => '🌐 Selección de Idioma',
        'question' => 'Selecciona tu idioma preferido para la instalación:',
        'options' => [
            'en' => 'English',
            'es' => 'Español',
        ],
    ],

    'laravel_check' => [
        'title' => '📋 Verificación de Versión de Laravel',
        'version' => 'Versión de Laravel: :version',
        'compatible' => '✅ La versión de Laravel es compatible',
        'incompatible' => '❌ KaelyAuth requiere Laravel 8.0 o superior',
    ],

    'auth_packages' => [
        'title' => '🔐 Verificando Paquetes de Autenticación',
        'installed' => '✅ :description está instalado',
        'not_installed' => '❌ :description no está instalado',
        'no_packages' => '⚠️  ¡No se detectaron paquetes de autenticación!',
        'requires_auth' => 'KaelyAuth requiere al menos un paquete de autenticación.',
        'install_choice' => '¿Qué paquete de autenticación te gustaría instalar?',
        'install_options' => [
            'sanctum' => 'Laravel Sanctum (Recomendado para APIs)',
            'breeze' => 'Laravel Breeze (Autenticación simple)',
            'jetstream' => 'Laravel Jetstream (Características avanzadas)',
            'skip' => 'Omitir por ahora (Instalar manualmente después)',
        ],
        'installing' => '📦 Instalando :package...',
        'installed_success' => '✅ ¡:package instalado exitosamente!',
        'installed_packages' => 'Paquetes instalados:',
        'additional_packages' => '📦 Paquetes adicionales disponibles:',
        'install_additional' => '¿Te gustaría instalar paquetes de autenticación adicionales?',
        'install_specific' => '¿Te gustaría instalar :description?',
    ],

    'database' => [
        'title' => '🗄️  Configuración de Base de Datos',
        'connection_check' => 'Verificando conexión a la base de datos...',
        'connection_success' => '✅ Conexión a la base de datos exitosa',
        'connection_failed' => '❌ Falló la conexión a la base de datos: :error',
        'configure_db' => '¿Te gustaría configurar la conexión a la base de datos?',
        'configuration_title' => '📝 Configuración de Base de Datos:',
        'driver_choice' => 'Selecciona el driver de base de datos:',
        'host' => 'Host de la base de datos:',
        'port' => 'Puerto de la base de datos:',
        'database' => 'Nombre de la base de datos:',
        'username' => 'Usuario de la base de datos:',
        'password' => 'Contraseña de la base de datos:',
        'config_updated' => '✅ Configuración de base de datos actualizada',
        'engine' => [
            'question' => 'Selecciona el motor de base de datos:',
            'options' => [
                'mysql' => 'MySQL (Más popular, buen rendimiento)',
                'postgresql' => 'PostgreSQL (Características avanzadas, cumplimiento ACID)',
                'sqlite' => 'SQLite (Basado en archivo, configuración simple)',
            ],
            'config' => '🔧 Configuración del Motor de Base de Datos',
            'sqlite' => [
                'config' => '📁 Configuración de SQLite',
                'path' => 'Ruta del archivo de base de datos SQLite:',
                'created' => '✅ Archivo de base de datos SQLite creado',
                'configured' => '✅ SQLite configurado exitosamente',
            ],
            'mysql' => [
                'config' => '🐬 Configuración de MySQL',
                'host' => 'Host de MySQL:',
                'port' => 'Puerto de MySQL:',
                'database' => 'Nombre de la base de datos MySQL:',
                'username' => 'Usuario de MySQL:',
                'password' => 'Contraseña de MySQL:',
                'configured' => '✅ MySQL configurado exitosamente',
            ],
            'postgresql' => [
                'config' => '🐘 Configuración de PostgreSQL',
                'host' => 'Host de PostgreSQL:',
                'port' => 'Puerto de PostgreSQL:',
                'database' => 'Nombre de la base de datos PostgreSQL:',
                'username' => 'Usuario de PostgreSQL:',
                'password' => 'Contraseña de PostgreSQL:',
                'configured' => '✅ PostgreSQL configurado exitosamente',
            ],
        ],
        'mode_choice' => 'Selecciona el modo de base de datos:',
        'mode_options' => [
            'single' => 'Base de Datos Única (Recomendado - Configuración simple)',
            'multiple' => 'Múltiples Bases de Datos (Avanzado - Para aplicaciones complejas)',
        ],
        'multiple_config' => '📊 Configuración de Múltiples Bases de Datos:',
        'prefix_question' => 'Prefijo de base de datos para tablas de auth (dejar vacío para sin prefijo):',
        'default_connection' => 'Conexión por defecto:',
        'auth_connection' => 'Conexión de auth:',
    ],

    'oauth' => [
        'title' => '🔐 Configuración de OAuth',
        'enable_question' => '¿Te gustaría habilitar proveedores OAuth?',
        'provider_choice' => 'Selecciona los proveedores OAuth a configurar:',
        'provider_options' => [
            'google' => 'Google OAuth',
            'facebook' => 'Facebook OAuth',
            'both' => 'Ambos Google y Facebook',
        ],
        'google_config' => '🔑 Configuración de Google OAuth:',
        'facebook_config' => '🔑 Configuración de Facebook OAuth:',
        'client_id' => 'ID de Cliente de :provider:',
        'client_secret' => 'Secreto de Cliente de :provider:',
        'redirect_uri' => 'URI de Redirección:',
    ],

    'multitenancy' => [
        'title' => '🏢 Configuración de Multitenancy',
        'enable_question' => '¿Te gustaría habilitar multitenancy? (Característica avanzada - para múltiples organizaciones)',
        'mode_choice' => 'Selecciona el modo de tenant:',
        'mode_options' => [
            'subdomain' => 'Basado en subdominio (tenant1.ejemplo.com)',
            'domain' => 'Basado en dominio (ejemplo1.com)',
        ],
        'enabled_message' => '✅ Multitenancy habilitado. Puedes crear tenants después usando: php artisan kaely:create-tenant',
        'disabled_message' => '✅ Multitenancy deshabilitado. Puedes habilitarlo después usando: php artisan kaely:setup-multitenancy',
    ],

    'features' => [
        'title' => '⚙️  Características Adicionales',
        'password_reset' => '¿Habilitar funcionalidad de restablecimiento de contraseña?',
        'email_verification' => '¿Habilitar verificación de email?',
        'session_management' => '¿Habilitar gestión de sesiones?',
        'audit_logging' => '¿Habilitar registro de auditoría?',
    ],

    'installation' => [
        'title' => '📦 Instalando KaelyAuth...',
        'publishing_config' => '📋 Publicando configuración...',
        'running_migrations' => '🗄️  Ejecutando migraciones...',
        'creating_admin' => '👤 Creando usuario administrador...',
        'setup_oauth' => '🔐 Configurando OAuth...',
        'setup_multitenancy' => '🏢 Configurando multitenancy...',
        'success' => '✅ ¡KaelyAuth instalado exitosamente!',
        'failed' => '❌ Falló la instalación: :error',
    ],

    'admin_user' => [
        'title' => '👤 Creación de Usuario Administrador',
        'create_question' => '¿Te gustaría crear un usuario administrador?',
        'name' => 'Nombre del administrador:',
        'email' => 'Email del administrador:',
        'password' => 'Contraseña del administrador:',
        'success' => '✅ ¡Usuario administrador creado exitosamente!',
    ],

    'next_steps' => [
        'title' => '🎉 ¡Instalación Completada!',
        'subtitle' => '=====================',
        'steps' => [
            'Revisa la configuración en config/kaely-auth.php',
            'Configura tus proveedores OAuth (si están habilitados)',
            'Configura tu frontend para usar los endpoints de la API',
            'Consulta la documentación en: https://kaely-auth.com',
        ],
        'commands' => [
            'Comandos Disponibles:',
            '- php artisan kaely:setup-oauth',
            '- php artisan kaely:setup-multitenancy',
            '- php artisan kaely:create-tenant',
            '- php artisan kaely:cleanup-tokens',
            '- php artisan kaely:audit-report',
        ],
        'documentation' => '📖 Documentación: https://kaely-auth.com/docs',
        'issues' => '🐛 Problemas: https://github.com/kaelytechnology/kaely-auth/issues',
    ],

    'errors' => [
        'env_not_found' => 'Archivo .env no encontrado',
        'invalid_choice' => 'Opción inválida. Por favor intenta de nuevo.',
        'command_failed' => 'Comando falló: :command',
    ],

    'ui' => [
        'title' => '🎨 Configuración de UI',
        'choice' => '¿Te gustaría instalar una UI para autenticación?',
        'options' => [
            'blade' => 'UI Blade (Renderizado tradicional del lado del servidor)',
            'livewire' => 'UI Livewire (Interactiva con características en tiempo real)',
            'none' => 'Sin UI (Crearé mi propia UI)',
        ],
        'installing_blade' => '📦 Instalando UI Blade...',
        'blade_installed' => '✅ ¡UI Blade instalada exitosamente!',
        'installing_livewire' => '📦 Instalando UI Livewire...',
        'installing_livewire_package' => '📦 Instalando paquete Livewire...',
        'livewire_installed' => '✅ ¡UI Livewire instalada exitosamente!',
        'none_selected' => '✅ No se seleccionó UI. Puedes crear tu propia UI después.',
    ],
]; 