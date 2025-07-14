<?php

namespace Kaely\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Kaely\Auth\Services\{
    KaelyTenantManager,
    KaelyConnectionResolver,
    PasswordResetService,
    EmailVerificationService,
    SessionManagementService,
    AuditService
};
use Kaely\Auth\Contracts\{
    TenantManagerInterface,
    ConnectionResolverInterface
};
use Kaely\Auth\Middleware\{
    KaelyTenantMiddleware,
    KaelyPermissionMiddleware,
    EmailVerificationMiddleware,
    SessionActivityMiddleware,
    AuditLoggingMiddleware,
    KaelyRoleMiddleware,
    ShareErrors
};

class KaelyAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/kaely-auth.php', 'kaely-auth');
        $this->mergeConfigFrom(__DIR__ . '/../config/security.php', 'kaely-auth-security');

        // Register services
        $this->registerServices();

        // Register middleware
        $this->registerMiddleware();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Kaely\Auth\Commands\InstallCommand::class,
                \Kaely\Auth\Commands\InstallApiCommand::class,
                \Kaely\Auth\Commands\ExportLogsCommand::class,
                \Kaely\Auth\Commands\InstallUICommand::class,
                \Kaely\Auth\Commands\SetupOAuthCommand::class,
                \Kaely\Auth\Commands\SetupMultitenancyCommand::class,
                \Kaely\Auth\Commands\CreateTenantCommand::class,
                \Kaely\Auth\Commands\CleanupExpiredTokens::class,
                \Kaely\Auth\Commands\GenerateAuditReport::class,
                \Kaely\Auth\Commands\SeedKaelyAuth::class,
                \Kaely\Auth\Commands\ValidateConfigCommand::class,
                \Kaely\Auth\Commands\OptimizePerformanceCommand::class,
                \Kaely\Auth\Commands\HealthCheckCommand::class,
            ]);
        }

        // Register languages
        $this->registerLanguages();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api_simple.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/simple_test.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kaely-auth');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/kaely-auth.php' => config_path('kaely-auth.php'),
            __DIR__ . '/../config/security.php' => config_path('kaely-auth-security.php'),
        ], 'kaely-auth-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'kaely-auth-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/kaely-auth'),
        ], 'kaely-auth-views');

        // Publish Blade UI views
        $this->publishes([
            __DIR__ . '/../resources/views/blade' => resource_path('views/vendor/kaely-auth/blade'),
        ], 'kaely-auth-blade-views');

        // Publish Livewire UI views
        $this->publishes([
            __DIR__ . '/../resources/views/livewire' => resource_path('views/vendor/kaely-auth/livewire'),
        ], 'kaely-auth-livewire-views');

        // Publish Livewire components (combined tag)
        $this->publishes([
            __DIR__ . '/../resources/views/livewire' => resource_path('views/vendor/kaely-auth/livewire'),
            __DIR__ . '/../src/Livewire' => app_path('Http/Livewire/KaelyAuth'),
        ], 'kaely-auth-livewire');

        // Publish assets (CSS, JS, images)
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/kaely-auth/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/kaely-auth/js'),
            __DIR__ . '/../resources/images' => public_path('vendor/kaely-auth/images'),
        ], 'kaely-auth-assets');

        // Register gates and policies
        $this->registerGates();

        // Register blade directives
        $this->registerBladeDirectives();

        // Setup database connections
        $this->setupDatabaseConnections();

        // Register scheduled tasks
        $this->registerScheduledTasks();
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        // Register tenant manager
        $this->app->singleton(TenantManagerInterface::class, function ($app) {
            return new KaelyTenantManager();
        });

        // Register connection resolver
        $this->app->singleton(ConnectionResolverInterface::class, function ($app) {
            return new KaelyConnectionResolver();
        });

        // Register service aliases
        $this->app->alias(TenantManagerInterface::class, 'kaely.tenant');
        $this->app->alias(ConnectionResolverInterface::class, 'kaely.connection');

        // Register KaelyAuthManager
        $this->app->singleton(\Kaely\Auth\KaelyAuthManager::class, function ($app) {
            return new \Kaely\Auth\KaelyAuthManager($app);
        });

        // Register additional services
        $this->app->singleton(PasswordResetService::class);
        $this->app->singleton(EmailVerificationService::class);
        $this->app->singleton(SessionManagementService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(\Kaely\Auth\Services\SecurityValidationService::class);
        $this->app->singleton(\Kaely\Auth\Services\CacheService::class);
        $this->app->singleton(\Kaely\Auth\Services\OptimizedQueryService::class);
    }

    /**
     * Register middleware
     */
    protected function registerMiddleware(): void
    {
        // Register tenant middleware
        $this->app['router']->aliasMiddleware('kaely.tenant', KaelyTenantMiddleware::class);

        // Register permission middleware
        $this->app['router']->aliasMiddleware('kaely.permission', KaelyPermissionMiddleware::class);

        // Register role middleware
        $this->app['router']->aliasMiddleware('kaely.role', KaelyRoleMiddleware::class);

        // Register email verification middleware
        $this->app['router']->aliasMiddleware('kaely.verified', EmailVerificationMiddleware::class);

        // Register session activity middleware
        $this->app['router']->aliasMiddleware('kaely.session.activity', SessionActivityMiddleware::class);

        // Register audit logging middleware
        $this->app['router']->aliasMiddleware('kaely.audit', AuditLoggingMiddleware::class);

        // Register rate limiting middleware
        $this->app['router']->aliasMiddleware('kaely.rate.limit', \Kaely\Auth\Middleware\RateLimitMiddleware::class);

        // Register security middleware
        
        // Register share errors middleware
        $this->app['router']->aliasMiddleware('kaely.share.errors', ShareErrors::class);
        $this->app['router']->aliasMiddleware('kaely.security', \Kaely\Auth\Middleware\SecurityMiddleware::class);

        // Register performance middleware
        $this->app['router']->aliasMiddleware('kaely.performance', \Kaely\Auth\Middleware\PerformanceMiddleware::class);
    }



    /**
     * Register the package's language files.
     */
    protected function registerLanguages(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'kaely-auth');
    }

    /**
     * Register gates and policies
     */
    protected function registerGates(): void
    {
        // Register permission gate
        Gate::define('kaely-permission', function ($user, $permission) {
            return $user->hasPermission($permission);
        });

        // Register role gate
        Gate::define('kaely-role', function ($user, $role) {
            return $user->hasRole($role);
        });
    }

    /**
     * Register blade directives
     */
    protected function registerBladeDirectives(): void
    {
        // @hasPermission directive
        Blade::directive('hasPermission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$expression})): ?>";
        });

        // @endhasPermission directive
        Blade::directive('endhasPermission', function () {
            return "<?php endif; ?>";
        });

        // @hasRole directive
        Blade::directive('hasRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });

        // @endhasRole directive
        Blade::directive('endhasRole', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Setup database connections
     */
    protected function setupDatabaseConnections(): void
    {
        $mode = config('kaely-auth.database.mode', 'single');
        $prefix = config('kaely-auth.database.prefix', ''); // No prefix by default

        if ($mode === 'multiple') {
            // Setup multiple database connections
            $this->setupMultipleConnections($prefix);
        } else {
            // Setup single database with prefix (if any)
            $this->setupSingleConnection($prefix);
        }
    }

    /**
     * Setup multiple database connections
     */
    protected function setupMultipleConnections(string $prefix): void
    {
        // Get the default connection configuration
        $defaultConfig = config('database.connections.' . config('database.default'));

        // Create auth connection
        $authConfig = $defaultConfig;
        $authConfig['database'] = $prefix . $defaultConfig['database'];
        
        config(['database.connections.kaely_auth' => $authConfig]);

        // Set the auth connection as default for auth operations
        config(['auth.providers.users.connection' => 'kaely_auth']);
    }

    /**
     * Setup single database connection with prefix
     */
    protected function setupSingleConnection(string $prefix): void
    {
        // Set table prefix for auth tables
        $this->app['config']->set('database.connections.mysql.prefix', $prefix);
    }

    /**
     * Register scheduled tasks
     */
    protected function registerScheduledTasks(): void
    {
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // Clean up expired tokens daily
            if (config('kaely-auth.password_reset.enabled', true) || 
                config('kaely-auth.email_verification.enabled', true) ||
                config('kaely-auth.sessions.enabled', true)) {
                $schedule->command('kaely:cleanup-tokens')
                    ->daily()
                    ->at('02:00');
            }

            // Clean up old audit logs weekly
            if (config('kaely-auth.audit.enabled', true)) {
                $schedule->command('kaely:audit-report --days=90')
                    ->weekly()
                    ->at('03:00');
            }
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            TenantManagerInterface::class,
            ConnectionResolverInterface::class,
            \Kaely\Auth\KaelyAuthManager::class,
            PasswordResetService::class,
            EmailVerificationService::class,
            SessionManagementService::class,
            AuditService::class,
        ];
    }
} 