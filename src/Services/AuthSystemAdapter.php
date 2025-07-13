<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class AuthSystemAdapter
{
    protected DependencyChecker $checker;

    public function __construct(DependencyChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * Adaptar configuración según el sistema de autenticación detectado
     */
    public function adaptConfiguration(): array
    {
        $authSystem = $this->checker->detectAuthSystem();
        
        if (!$authSystem) {
            return [
                'success' => false,
                'message' => 'No se detectó ningún sistema de autenticación',
                'suggestions' => $this->getInstallationSuggestions()
            ];
        }

        $config = $this->getSystemSpecificConfig($authSystem);
        
        return [
            'success' => true,
            'auth_system' => $authSystem,
            'config' => $config,
            'suggestions' => $this->getSystemSuggestions($authSystem)
        ];
    }

    /**
     * Obtener configuración específica para cada sistema
     */
    protected function getSystemSpecificConfig(string $authSystem): array
    {
        switch ($authSystem) {
            case 'sanctum':
                return [
                    'guard' => 'web',
                    'provider' => 'users',
                    'middleware' => 'auth:sanctum',
                    'user_model' => 'App\Models\User',
                    'token_expiration' => 60 * 24 * 7, // 7 days
                    'features' => [
                        'api_tokens' => true,
                        'session_authentication' => false,
                        'mobile_application_tokens' => true,
                    ]
                ];

            case 'breeze':
                return [
                    'guard' => 'web',
                    'provider' => 'users',
                    'middleware' => 'auth',
                    'user_model' => 'App\Models\User',
                    'token_expiration' => null, // Session based
                    'features' => [
                        'api_tokens' => false,
                        'session_authentication' => true,
                        'mobile_application_tokens' => false,
                    ]
                ];

            case 'jetstream':
                return [
                    'guard' => 'web',
                    'provider' => 'users',
                    'middleware' => 'auth:sanctum',
                    'user_model' => 'App\Models\User',
                    'token_expiration' => 60 * 24 * 7, // 7 days
                    'features' => [
                        'api_tokens' => true,
                        'session_authentication' => true,
                        'mobile_application_tokens' => true,
                        'teams' => true,
                        'profile_management' => true,
                    ]
                ];

            default:
                return [
                    'guard' => 'web',
                    'provider' => 'users',
                    'middleware' => 'auth',
                    'user_model' => 'App\Models\User',
                    'token_expiration' => null,
                    'features' => [
                        'api_tokens' => false,
                        'session_authentication' => true,
                        'mobile_application_tokens' => false,
                    ]
                ];
        }
    }

    /**
     * Obtener sugerencias específicas para cada sistema
     */
    protected function getSystemSuggestions(string $authSystem): array
    {
        switch ($authSystem) {
            case 'sanctum':
                return [
                    'setup' => [
                        'php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
                        'php artisan migrate',
                    ],
                    'usage' => [
                        'Use middleware: auth:sanctum',
                        'Generate tokens with: $user->createToken("token-name")',
                        'API routes should use Sanctum middleware',
                    ],
                    'configuration' => [
                        'Check config/sanctum.php for token configuration',
                        'Set up CORS in config/cors.php if needed',
                    ]
                ];

            case 'breeze':
                return [
                    'setup' => [
                        'php artisan breeze:install',
                        'php artisan migrate',
                        'npm install && npm run dev',
                    ],
                    'usage' => [
                        'Use middleware: auth',
                        'Breeze provides web-based authentication',
                        'Use session-based authentication',
                    ],
                    'configuration' => [
                        'Customize views in resources/views/auth',
                        'Modify routes in routes/auth.php',
                    ]
                ];

            case 'jetstream':
                return [
                    'setup' => [
                        'php artisan jetstream:install',
                        'php artisan migrate',
                        'npm install && npm run dev',
                    ],
                    'usage' => [
                        'Use middleware: auth:sanctum for API',
                        'Use middleware: auth for web',
                        'Jetstream provides both API and web auth',
                    ],
                    'configuration' => [
                        'Check config/jetstream.php for features',
                        'Customize views in resources/views',
                        'Configure teams in config/jetstream.php',
                    ]
                ];

            default:
                return [];
        }
    }

    /**
     * Obtener sugerencias de instalación si no hay sistema detectado
     */
    protected function getInstallationSuggestions(): array
    {
        return [
            'sanctum' => [
                'command' => 'composer require laravel/sanctum',
                'description' => 'Para APIs y aplicaciones SPA',
                'setup' => [
                    'php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
                    'php artisan migrate',
                ]
            ],
            'breeze' => [
                'command' => 'composer require laravel/breeze --dev',
                'description' => 'Para aplicaciones web tradicionales',
                'setup' => [
                    'php artisan breeze:install',
                    'php artisan migrate',
                    'npm install && npm run dev',
                ]
            ],
            'jetstream' => [
                'command' => 'composer require laravel/jetstream',
                'description' => 'Para aplicaciones complejas con equipos',
                'setup' => [
                    'php artisan jetstream:install',
                    'php artisan migrate',
                    'npm install && npm run dev',
                ]
            ]
        ];
    }

    /**
     * Verificar si el User model tiene los traits necesarios
     */
    public function checkUserModelCompatibility(): array
    {
        $authSystem = $this->checker->detectAuthSystem();
        $userModelPath = app_path('Models/User.php');
        
        if (!File::exists($userModelPath)) {
            return [
                'compatible' => false,
                'message' => 'User model not found',
                'suggestions' => ['Create App\Models\User model']
            ];
        }

        $userModelContent = File::get($userModelPath);
        $issues = [];
        $suggestions = [];

        // Verificar traits según el sistema de autenticación
        switch ($authSystem) {
            case 'sanctum':
                if (!str_contains($userModelContent, 'HasApiTokens')) {
                    $issues[] = 'Missing HasApiTokens trait';
                    $suggestions[] = 'Add: use Laravel\Sanctum\HasApiTokens;';
                }
                break;

            case 'breeze':
                // Breeze no requiere traits específicos
                break;

            case 'jetstream':
                if (!str_contains($userModelContent, 'HasApiTokens')) {
                    $issues[] = 'Missing HasApiTokens trait';
                    $suggestions[] = 'Add: use Laravel\Sanctum\HasApiTokens;';
                }
                break;
        }

        // Verificar trait de KaelyAuth
        if (!str_contains($userModelContent, 'HasPermissions')) {
            $issues[] = 'Missing HasPermissions trait';
            $suggestions[] = 'Add: use Kaely\Auth\Traits\HasPermissions;';
        }

        return [
            'compatible' => empty($issues),
            'issues' => $issues,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Generar configuración automática para el sistema detectado
     */
    public function generateConfiguration(): array
    {
        $authSystem = $this->checker->detectAuthSystem();
        
        if (!$authSystem) {
            return [
                'success' => false,
                'message' => 'No authentication system detected'
            ];
        }

        $config = $this->getSystemSpecificConfig($authSystem);
        
        // Actualizar configuración de KaelyAuth
        $kaelyConfig = config('kaely-auth', []);
        $kaelyConfig['auth'] = $config;
        $kaelyConfig['auth']['system'] = $authSystem;

        return [
            'success' => true,
            'auth_system' => $authSystem,
            'config' => $kaelyConfig,
            'suggestions' => $this->getSystemSuggestions($authSystem)
        ];
    }
} 