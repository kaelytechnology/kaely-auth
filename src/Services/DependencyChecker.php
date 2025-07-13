<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class DependencyChecker
{
    /**
     * Lista de dependencias requeridas (al menos una debe estar instalada)
     */
    protected array $requiredDependencies = [
        'laravel/sanctum' => [
            'name' => 'Laravel Sanctum',
            'description' => 'Sistema de autenticación API',
            'composer_package' => 'laravel/sanctum',
            'config_file' => 'sanctum.php',
            'provider' => 'Laravel\Sanctum\SanctumServiceProvider',
            'type' => 'auth',
        ],
        'laravel/breeze' => [
            'name' => 'Laravel Breeze',
            'description' => 'Sistema de autenticación con vistas',
            'composer_package' => 'laravel/breeze',
            'config_file' => null,
            'provider' => null,
            'type' => 'auth',
        ],
        'laravel/jetstream' => [
            'name' => 'Laravel Jetstream',
            'description' => 'Sistema de autenticación completo',
            'composer_package' => 'laravel/jetstream',
            'config_file' => null,
            'provider' => null,
            'type' => 'auth',
        ]
    ];

    /**
     * Lista de dependencias opcionales
     */
    protected array $optionalDependencies = [
        'spatie/laravel-permission' => [
            'name' => 'Spatie Laravel Permission',
            'description' => 'Sistema de permisos avanzado (opcional)',
            'composer_package' => 'spatie/laravel-permission',
        ],
        'laravel/socialite' => [
            'name' => 'Laravel Socialite',
            'description' => 'OAuth/Social authentication (opcional)',
            'composer_package' => 'laravel/socialite',
            'config_file' => 'services.php',
        ]
    ];

    /**
     * Verificar si una dependencia está instalada
     */
    public function isInstalled(string $package): bool
    {
        $composerLockPath = base_path('composer.lock');
        
        if (!File::exists($composerLockPath)) {
            return false;
        }

        $composerLock = json_decode(File::get($composerLockPath), true);
        
        if (!$composerLock || !isset($composerLock['packages'])) {
            return false;
        }

        foreach ($composerLock['packages'] as $installedPackage) {
            if ($installedPackage['name'] === $package) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si un archivo de configuración existe
     */
    public function hasConfigFile(string $configFile): bool
    {
        return File::exists(config_path($configFile));
    }

    /**
     * Verificar si un provider está registrado
     */
    public function isProviderRegistered(string $provider): bool
    {
        $config = config('app.providers', []);
        return in_array($provider, $config);
    }

    /**
     * Obtener el estado de todas las dependencias
     */
    public function getDependenciesStatus(): array
    {
        $status = [];

        // Verificar dependencias requeridas
        foreach ($this->requiredDependencies as $package => $info) {
            $status[$package] = [
                'name' => $info['name'],
                'description' => $info['description'],
                'required' => true,
                'installed' => $this->isInstalled($package),
                'config_exists' => isset($info['config_file']) ? $this->hasConfigFile($info['config_file']) : true,
                'provider_registered' => isset($info['provider']) ? $this->isProviderRegistered($info['provider']) : true,
                'composer_package' => $info['composer_package'],
            ];
        }

        // Verificar dependencias opcionales
        foreach ($this->optionalDependencies as $package => $info) {
            $status[$package] = [
                'name' => $info['name'],
                'description' => $info['description'],
                'required' => false,
                'installed' => $this->isInstalled($package),
                'config_exists' => isset($info['config_file']) ? $this->hasConfigFile($info['config_file']) : true,
                'provider_registered' => isset($info['provider']) ? $this->isProviderRegistered($info['provider']) : true,
                'composer_package' => $info['composer_package'],
            ];
        }

        return $status;
    }

    /**
     * Verificar si todas las dependencias requeridas están instaladas
     */
    public function allRequiredDependenciesInstalled(): bool
    {
        $status = $this->getDependenciesStatus();
        
        foreach ($status as $package => $info) {
            if ($info['required'] && !$info['installed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener dependencias faltantes
     */
    public function getMissingDependencies(): array
    {
        $status = $this->getDependenciesStatus();
        $missing = [];

        // Para sistemas de autenticación, solo reportar como faltante si no hay ninguno instalado
        $authSystems = ['laravel/sanctum', 'laravel/breeze', 'laravel/jetstream'];
        $hasAnyAuthSystem = false;

        foreach ($authSystems as $authPackage) {
            if (isset($status[$authPackage]) && $status[$authPackage]['installed']) {
                $hasAnyAuthSystem = true;
                break;
            }
        }

        foreach ($status as $package => $info) {
            if ($info['required'] && !$info['installed']) {
                // Para sistemas de autenticación, solo reportar si no hay ninguno
                if (in_array($package, $authSystems)) {
                    if (!$hasAnyAuthSystem) {
                        $missing[] = [
                            'package' => $package,
                            'composer_package' => $info['composer_package'],
                            'name' => $info['name'],
                            'description' => $info['description'],
                            'type' => 'auth_system',
                        ];
                    }
                } else {
                    // Para otras dependencias, reportar normalmente
                    $missing[] = [
                        'package' => $package,
                        'composer_package' => $info['composer_package'],
                        'name' => $info['name'],
                        'description' => $info['description'],
                        'type' => 'required',
                    ];
                }
            }
        }

        return $missing;
    }

    /**
     * Generar comando de instalación para dependencias faltantes
     */
    public function getInstallCommand(): string
    {
        $missing = $this->getMissingDependencies();
        
        if (empty($missing)) {
            return '';
        }

        $packages = array_map(function ($dep) {
            return $dep['composer_package'];
        }, $missing);

        return 'composer require ' . implode(' ', $packages);
    }

    /**
     * Generar comandos de configuración
     */
    public function getSetupCommands(): array
    {
        $commands = [];
        $status = $this->getDependenciesStatus();

        foreach ($status as $package => $info) {
            if ($info['installed'] && !$info['config_exists'] && isset($info['config_file'])) {
                switch ($package) {
                    case 'laravel/sanctum':
                        $commands[] = 'php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"';
                        break;
                }
            }
        }

        return $commands;
    }

    /**
     * Detectar qué sistema de autenticación está instalado
     */
    public function detectAuthSystem(): ?string
    {
        $authPackages = [
            'laravel/sanctum' => 'sanctum',
            'laravel/breeze' => 'breeze',
            'laravel/jetstream' => 'jetstream'
        ];

        foreach ($authPackages as $package => $system) {
            if ($this->isInstalled($package)) {
                return $system;
            }
        }

        return null;
    }

    /**
     * Verificar si al menos un sistema de autenticación está instalado
     */
    public function hasAnyAuthSystem(): bool
    {
        return $this->detectAuthSystem() !== null;
    }

    /**
     * Obtener recomendaciones de instalación basadas en el sistema detectado
     */
    public function getInstallationRecommendations(): array
    {
        $authSystem = $this->detectAuthSystem();
        $recommendations = [];

        if (!$authSystem) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'No se detectó ningún sistema de autenticación',
                'suggestions' => [
                    'laravel/sanctum' => 'Para APIs: composer require laravel/sanctum',
                    'laravel/breeze' => 'Para aplicaciones web: composer require laravel/breeze',
                    'laravel/jetstream' => 'Para aplicaciones complejas: composer require laravel/jetstream'
                ]
            ];
        } else {
            $recommendations[] = [
                'type' => 'info',
                'message' => "Sistema de autenticación detectado: {$authSystem}",
                'suggestions' => $this->getAuthSystemSuggestions($authSystem)
            ];
        }

        return $recommendations;
    }

    /**
     * Obtener sugerencias específicas para cada sistema de autenticación
     */
    protected function getAuthSystemSuggestions(string $authSystem): array
    {
        $suggestions = [];

        switch ($authSystem) {
            case 'sanctum':
                $suggestions[] = 'Configurar Sanctum: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"';
                $suggestions[] = 'Ejecutar migraciones: php artisan migrate';
                break;
            
            case 'breeze':
                $suggestions[] = 'Instalar Breeze: php artisan breeze:install';
                $suggestions[] = 'Ejecutar migraciones: php artisan migrate';
                $suggestions[] = 'Compilar assets: npm install && npm run dev';
                break;
            
            case 'jetstream':
                $suggestions[] = 'Instalar Jetstream: php artisan jetstream:install';
                $suggestions[] = 'Ejecutar migraciones: php artisan migrate';
                $suggestions[] = 'Compilar assets: npm install && npm run dev';
                break;
        }

        return $suggestions;
    }

    /**
     * Validar y mostrar reporte de dependencias
     */
    public function validateAndReport(): array
    {
        $status = $this->getDependenciesStatus();
        $missing = $this->getMissingDependencies();
        $installCommand = $this->getInstallCommand();
        $setupCommands = $this->getSetupCommands();
        $authSystem = $this->detectAuthSystem();
        $recommendations = $this->getInstallationRecommendations();

        return [
            'status' => $status,
            'missing_dependencies' => $missing,
            'all_required_installed' => $this->allRequiredDependenciesInstalled(),
            'install_command' => $installCommand,
            'setup_commands' => $setupCommands,
            'can_proceed' => $this->hasAnyAuthSystem(),
            'auth_system' => $authSystem,
            'recommendations' => $recommendations,
        ];
    }
} 