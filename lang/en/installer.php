<?php

return [
    'welcome' => [
        'title' => '🚀 Welcome to KaelyAuth Installation Wizard!',
        'subtitle' => '==========================================',
    ],

    'language_selection' => [
        'title' => '🌐 Language Selection',
        'question' => 'Select your preferred language for the installation:',
        'options' => [
            'en' => 'English',
            'es' => 'Español',
        ],
    ],

    'laravel_check' => [
        'title' => '📋 Laravel Version Check',
        'version' => 'Laravel Version: :version',
        'compatible' => '✅ Laravel version is compatible',
        'incompatible' => '❌ KaelyAuth requires Laravel 8.0 or higher',
    ],

    'auth_packages' => [
        'title' => '🔐 Checking Authentication Packages',
        'installed' => '✅ :description is installed',
        'not_installed' => '❌ :description is not installed',
        'no_packages' => '⚠️  No authentication packages detected!',
        'requires_auth' => 'KaelyAuth requires at least one authentication package.',
        'install_choice' => 'Which authentication package would you like to install?',
        'install_options' => [
            'sanctum' => 'Laravel Sanctum (Recommended for APIs)',
            'breeze' => 'Laravel Breeze (Simple authentication)',
            'jetstream' => 'Laravel Jetstream (Advanced features)',
            'skip' => 'Skip for now (Install manually later)',
        ],
        'installing' => '📦 Installing :package...',
        'installed_success' => '✅ :package installed successfully!',
        'installed_packages' => 'Installed packages:',
        'additional_packages' => '📦 Additional packages available:',
        'install_additional' => 'Would you like to install additional authentication packages?',
        'install_specific' => 'Would you like to install :description?',
    ],

    'database' => [
        'title' => '🗄️  Database Configuration',
        'connection_check' => 'Checking database connection...',
        'connection_success' => '✅ Database connection successful',
        'connection_failed' => '❌ Database connection failed: :error',
        'configure_db' => 'Would you like to configure the database connection?',
        'configuration_title' => '📝 Database Configuration:',
        'driver_choice' => 'Select database driver:',
        'host' => 'Database host:',
        'port' => 'Database port:',
        'database' => 'Database name:',
        'username' => 'Database username:',
        'password' => 'Database password:',
        'config_updated' => '✅ Database configuration updated',
        'engine' => [
            'question' => 'Select database engine:',
            'options' => [
                'mysql' => 'MySQL (Most popular, good performance)',
                'postgresql' => 'PostgreSQL (Advanced features, ACID compliance)',
                'sqlite' => 'SQLite (File-based, simple setup)',
            ],
            'config' => '🔧 Database Engine Configuration',
            'sqlite' => [
                'config' => '📁 SQLite Configuration',
                'path' => 'SQLite database file path:',
                'created' => '✅ SQLite database file created',
                'configured' => '✅ SQLite configured successfully',
            ],
            'mysql' => [
                'config' => '🐬 MySQL Configuration',
                'host' => 'MySQL host:',
                'port' => 'MySQL port:',
                'database' => 'MySQL database name:',
                'username' => 'MySQL username:',
                'password' => 'MySQL password:',
                'configured' => '✅ MySQL configured successfully',
            ],
            'postgresql' => [
                'config' => '🐘 PostgreSQL Configuration',
                'host' => 'PostgreSQL host:',
                'port' => 'PostgreSQL port:',
                'database' => 'PostgreSQL database name:',
                'username' => 'PostgreSQL username:',
                'password' => 'PostgreSQL password:',
                'configured' => '✅ PostgreSQL configured successfully',
            ],
        ],
        'mode_choice' => 'Select database mode:',
        'mode_options' => [
            'single' => 'Single Database (Recommended - Simple setup)',
            'multiple' => 'Multiple Databases (Advanced - For complex applications)',
        ],
        'multiple_config' => '📊 Multiple Database Configuration:',
        'prefix_question' => 'Database prefix for auth tables (leave empty for no prefix):',
        'default_connection' => 'Default connection:',
        'auth_connection' => 'Auth connection:',
    ],

    'oauth' => [
        'title' => '🔐 OAuth Configuration',
        'enable_question' => 'Would you like to enable OAuth providers?',
        'provider_choice' => 'Select OAuth providers to configure:',
        'provider_options' => [
            'google' => 'Google OAuth',
            'facebook' => 'Facebook OAuth',
            'both' => 'Both Google and Facebook',
        ],
        'google_config' => '🔑 Google OAuth Configuration:',
        'facebook_config' => '🔑 Facebook OAuth Configuration:',
        'client_id' => ':provider Client ID:',
        'client_secret' => ':provider Client Secret:',
        'redirect_uri' => 'Redirect URI:',
    ],

    'multitenancy' => [
        'title' => '🏢 Multitenancy Configuration',
        'enable_question' => 'Would you like to enable multitenancy? (Advanced feature - for multiple organizations)',
        'mode_choice' => 'Select tenant mode:',
        'mode_options' => [
            'subdomain' => 'Subdomain-based (tenant1.example.com)',
            'domain' => 'Domain-based (example1.com)',
        ],
        'enabled_message' => '✅ Multitenancy enabled. You can create tenants later using: php artisan kaely:create-tenant',
        'disabled_message' => '✅ Multitenancy disabled. You can enable it later using: php artisan kaely:setup-multitenancy',
    ],

    'features' => [
        'title' => '⚙️  Additional Features',
        'password_reset' => 'Enable password reset functionality?',
        'email_verification' => 'Enable email verification?',
        'session_management' => 'Enable session management?',
        'audit_logging' => 'Enable audit logging?',
    ],

    'installation' => [
        'title' => '📦 Installing KaelyAuth...',
        'publishing_config' => '📋 Publishing configuration...',
        'running_migrations' => '🗄️  Running migrations...',
        'creating_admin' => '👤 Creating admin user...',
        'setup_oauth' => '🔐 Setting up OAuth...',
        'setup_multitenancy' => '🏢 Setting up multitenancy...',
        'success' => '✅ KaelyAuth installed successfully!',
        'failed' => '❌ Installation failed: :error',
    ],

    'admin_user' => [
        'title' => '👤 Admin User Creation',
        'create_question' => 'Would you like to create an admin user?',
        'name' => 'Admin name:',
        'email' => 'Admin email:',
        'password' => 'Admin password:',
        'success' => '✅ Admin user created successfully!',
    ],

    'next_steps' => [
        'title' => '🎉 Installation Complete!',
        'subtitle' => '=====================',
        'steps' => [
            'Review the configuration in config/kaely-auth.php',
            'Configure your OAuth providers (if enabled)',
            'Set up your frontend to use the API endpoints',
            'Check the documentation at: https://kaely-auth.com',
        ],
        'commands' => [
            'Available Commands:',
            '- php artisan kaely:setup-oauth',
            '- php artisan kaely:setup-multitenancy',
            '- php artisan kaely:create-tenant',
            '- php artisan kaely:cleanup-tokens',
            '- php artisan kaely:audit-report',
        ],
        'documentation' => '📖 Documentation: https://kaely-auth.com/docs',
        'issues' => '🐛 Issues: https://github.com/kaelytechnology/kaely-auth/issues',
    ],

    'errors' => [
        'env_not_found' => '.env file not found',
        'invalid_choice' => 'Invalid choice. Please try again.',
        'command_failed' => 'Command failed: :command',
    ],

    'ui' => [
        'title' => '🎨 UI Configuration',
        'choice' => 'Would you like to install a UI for authentication?',
        'options' => [
            'blade' => 'Blade UI (Traditional server-side rendering)',
            'livewire' => 'Livewire UI (Interactive with real-time features)',
            'none' => 'No UI (I will create my own)',
        ],
        'installing_blade' => '📦 Installing Blade UI...',
        'blade_installed' => '✅ Blade UI installed successfully!',
        'installing_livewire' => '📦 Installing Livewire UI...',
        'installing_livewire_package' => '📦 Installing Livewire package...',
        'livewire_installed' => '✅ Livewire UI installed successfully!',
        'none_selected' => '✅ No UI selected. You can create your own UI later.',
    ],
]; 