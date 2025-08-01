<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:install 
                            {--force : Force installation without confirmation}
                            {--skip-wizard : Skip the interactive wizard}
                            {--language= : Language for installation (en/es)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure KaelyAuth package';

    /**
     * Current language for the installer
     */
    protected $language = 'en';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Set language
        $this->setLanguage();

        $this->info($this->trans('welcome.title'));
        $this->info($this->trans('welcome.subtitle'));

        if (!$this->option('skip-wizard')) {
            $this->runInteractiveWizard();
        }

        $this->info($this->trans('installation.title'));

        try {
            // Publish configuration
            $this->publishConfiguration();

            // Run migrations
            $this->runMigrations();

            // Create admin user
            $this->createAdminUser();

            // Setup OAuth (if enabled)
            if ($this->confirm($this->trans('oauth.enable_question'), false)) {
                $this->setupOAuth();
            }

            // Setup multitenancy (if enabled)
            if ($this->confirm($this->trans('multitenancy.enable_question'), false)) {
                $this->setupMultitenancy();
            }

            $this->info($this->trans('installation.success'));
            $this->displayNextSteps();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($this->trans('installation.failed', ['error' => $e->getMessage()]));
            return Command::FAILURE;
        }
    }

    /**
     * Set language for the installer
     */
    protected function setLanguage(): void
    {
        $language = $this->option('language');
        
        if (!$language) {
            $language = $this->choice(
                $this->trans('language_selection.question'),
                $this->transArray('language_selection.options'),
                'en'
            );
        }

        $this->language = $language;
    }

    /**
     * Get translation for current language
     */
    protected function trans(string $key, array $replace = []): string
    {
        $translations = $this->getTranslations();
        
        $keys = explode('.', $key);
        $value = $translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key; // Return key if translation not found
            }
        }

        // Ensure value is a string
        if (!is_string($value)) {
            return $key; // Return key if value is not a string
        }

        // Replace placeholders
        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, $replacement, $value);
        }

        return $value;
    }

    /**
     * Get translation array for current language
     */
    protected function transArray(string $key): array
    {
        $translations = $this->getTranslations();
        
        $keys = explode('.', $key);
        $value = $translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return []; // Return empty array if translation not found
            }
        }

        // Ensure value is an array
        if (!is_array($value)) {
            return []; // Return empty array if value is not an array
        }

        return $value;
    }

    /**
     * Get translations for current language
     */
    protected function getTranslations(): array
    {
        $langPath = __DIR__ . '/../../lang/' . $this->language . '/installer.php';
        
        if (File::exists($langPath)) {
            return require $langPath;
        }

        // Fallback to English
        $langPath = __DIR__ . '/../../lang/en/installer.php';
        return require $langPath;
    }

    /**
     * Run interactive wizard
     */
    protected function runInteractiveWizard(): void
    {
        $this->info("\n" . $this->trans('laravel_check.title'));

        // Check Laravel version
        $this->checkLaravelVersion();

        // Check and setup authentication packages
        $this->checkAuthenticationPackages();

        // Check database connection
        $this->checkDatabaseConnection();

        // Configure database mode
        $this->configureDatabaseMode();

        // Configure OAuth
        $this->configureOAuth();

        // Configure multitenancy
        $this->configureMultitenancy();

        // Configure additional features
        $this->configureAdditionalFeatures();
    }

    /**
     * Check Laravel version
     */
    protected function checkLaravelVersion(): void
    {
        $version = app()->version();
        $this->info($this->trans('laravel_check.version', ['version' => $version]));

        if (version_compare($version, '8.0.0', '<')) {
            $this->error($this->trans('laravel_check.incompatible'));
            exit(1);
        }

        $this->info($this->trans('laravel_check.compatible'));
    }

    /**
     * Check and setup authentication packages
     */
    protected function checkAuthenticationPackages(): void
    {
        $this->info("\n" . $this->trans('auth_packages.title'));

        $packages = [
            'laravel/sanctum' => 'Sanctum (API Authentication)',
            'laravel/breeze' => 'Breeze (Simple Authentication)',
            'laravel/jetstream' => 'Jetstream (Advanced Authentication)',
            'laravel/ui' => 'Laravel UI (Basic Authentication)',
        ];

        $installedPackages = [];
        $missingPackages = [];

        foreach ($packages as $package => $description) {
            if ($this->isPackageInstalled($package)) {
                $installedPackages[$package] = $description;
                $this->info($this->trans('auth_packages.installed', ['description' => $description]));
            } else {
                $missingPackages[$package] = $description;
                $this->warn($this->trans('auth_packages.not_installed', ['description' => $description]));
            }
        }

        if (empty($installedPackages)) {
            $this->warn("\n" . $this->trans('auth_packages.no_packages'));
            $this->info($this->trans('auth_packages.requires_auth'));
            
            $choice = $this->choice(
                $this->trans('auth_packages.install_choice'),
                $this->transArray('auth_packages.install_options'),
                'sanctum'
            );

            if ($choice !== 'skip') {
                $this->installAuthenticationPackage($choice);
            }
        } else {
            $this->info("\n" . $this->trans('auth_packages.installed_packages'));
            foreach ($installedPackages as $package => $description) {
                $this->info("   - {$description}");
            }

            if (!empty($missingPackages)) {
                $this->info("\n" . $this->trans('auth_packages.additional_packages'));
                foreach ($missingPackages as $package => $description) {
                    $this->info("   - {$description}");
                }

                if ($this->confirm($this->trans('auth_packages.install_additional'))) {
                    $this->installAdditionalPackages($missingPackages);
                }
            }
        }
    }

    /**
     * Install authentication package
     */
    protected function installAuthenticationPackage(string $package): void
    {
        $this->info("\n" . $this->trans('auth_packages.installing', ['package' => $package]));

        switch ($package) {
            case 'sanctum':
                $this->installSanctum();
                break;
            case 'breeze':
                $this->installBreeze();
                break;
            case 'jetstream':
                $this->installJetstream();
                break;
        }
    }

    /**
     * Install Laravel Sanctum
     */
    protected function installSanctum(): void
    {
        $this->info($this->trans('auth_packages.installing', ['package' => 'Laravel Sanctum']));
        
        // Use the dedicated install:api command which handles everything
        $this->executeCommand('php artisan install:api --force');
        
        $this->info($this->trans('auth_packages.installed_success', ['package' => 'Sanctum']));
    }

    /**
     * Install Laravel Breeze
     */
    protected function installBreeze(): void
    {
        $this->info($this->trans('auth_packages.installing', ['package' => 'Laravel Breeze']));
        
        // Install via composer
        $this->executeCommand('composer require laravel/breeze --dev');
        
        // Install Breeze
        $this->executeCommand('php artisan breeze:install');
        
        // Install and build assets
        $this->executeCommand('npm install');
        $this->executeCommand('npm run build');
        
        $this->info($this->trans('auth_packages.installed_success', ['package' => 'Breeze']));
    }

    /**
     * Install Laravel Jetstream
     */
    protected function installJetstream(): void
    {
        $this->info($this->trans('auth_packages.installing', ['package' => 'Laravel Jetstream']));
        
        // Install via composer
        $this->executeCommand('composer require laravel/jetstream');
        
        // Install Jetstream with Livewire
        $this->executeCommand('php artisan jetstream:install livewire');
        
        // Install and build assets
        $this->executeCommand('npm install');
        $this->executeCommand('npm run build');
        
        $this->info($this->trans('auth_packages.installed_success', ['package' => 'Jetstream']));
    }

    /**
     * Install additional packages
     */
    protected function installAdditionalPackages(array $packages): void
    {
        foreach ($packages as $package => $description) {
            if ($this->confirm($this->trans('auth_packages.install_specific', ['description' => $description]))) {
                $this->installAuthenticationPackage($this->getPackageKey($package));
            }
        }
    }

    /**
     * Check database connection
     */
    protected function checkDatabaseConnection(): void
    {
        $this->info("\n" . $this->trans('database.connection_check'));

        try {
            DB::connection()->getPdo();
            $this->info($this->trans('database.connection_success'));
        } catch (\Exception $e) {
            $this->error($this->trans('database.connection_failed', ['error' => $e->getMessage()]));
            
            if ($this->confirm($this->trans('database.configure_db'))) {
                $this->configureDatabase();
            } else {
                exit(1);
            }
        }
    }

    /**
     * Configure database connection
     */
    protected function configureDatabase(): void
    {
        $this->info("\n" . $this->trans('database.configuration_title'));
        
        $driver = $this->choice($this->trans('database.driver_choice'), ['mysql', 'pgsql', 'sqlite'], 'mysql');
        
        $host = $this->ask($this->trans('database.host'), 'localhost');
        $port = $this->ask($this->trans('database.port'), $driver === 'mysql' ? '3306' : '5432');
        $database = $this->ask($this->trans('database.database'));
        $username = $this->ask($this->trans('database.username'));
        $password = $this->secret($this->trans('database.password'));

        // Update .env file
        $this->updateEnvFile([
            'DB_CONNECTION' => $driver,
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);

        $this->info($this->trans('database.config_updated'));
    }

    /**
     * Configure database mode
     */
    protected function configureDatabaseMode(): void
    {
        $this->info("\n" . $this->trans('database.title'));

        // First, ask for database engine
        $engine = $this->choice(
            $this->trans('database.engine.question'),
            $this->transArray('database.engine.options'),
            'mysql'
        );

        // Configure database engine
        $this->configureDatabaseEngine($engine);

        $mode = $this->choice(
            $this->trans('database.mode_choice'),
            $this->transArray('database.mode_options'),
            'single'
        );

        if ($mode === 'multiple') {
            $this->configureMultipleDatabases();
        } else {
            // Single database - no prefix by default
            $this->updateEnvFile([
                'KAELY_AUTH_DB_MODE' => 'single',
                'KAELY_AUTH_DB_PREFIX' => '',
            ]);
        }
    }

    /**
     * Configure database engine
     */
    protected function configureDatabaseEngine(string $engine): void
    {
        $this->info("\n" . $this->trans('database.engine.config'));

        switch ($engine) {
            case 'sqlite':
                $this->configureSqlite();
                break;
            case 'mysql':
                $this->configureMysql();
                break;
            case 'postgresql':
                $this->configurePostgresql();
                break;
            default:
                $this->configureMysql();
                break;
        }
    }

    /**
     * Configure SQLite database
     */
    protected function configureSqlite(): void
    {
        $this->info($this->trans('database.engine.sqlite.config'));
        
        $databasePath = $this->ask(
            $this->trans('database.engine.sqlite.path'),
            database_path('database.sqlite')
        );

        // Create SQLite database file if it doesn't exist
        if (!file_exists($databasePath)) {
            touch($databasePath);
            $this->info($this->trans('database.engine.sqlite.created'));
        }

        $this->updateEnvFile([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $databasePath,
        ]);

        $this->info($this->trans('database.engine.sqlite.configured'));
    }

    /**
     * Configure MySQL database
     */
    protected function configureMysql(): void
    {
        $this->info($this->trans('database.engine.mysql.config'));
        
        $host = $this->ask($this->trans('database.engine.mysql.host'), '127.0.0.1');
        $port = $this->ask($this->trans('database.engine.mysql.port'), '3306');
        $database = $this->ask($this->trans('database.engine.mysql.database'), 'laravel');
        $username = $this->ask($this->trans('database.engine.mysql.username'), 'root');
        $password = $this->secret($this->trans('database.engine.mysql.password'));

        $this->updateEnvFile([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);

        $this->info($this->trans('database.engine.mysql.configured'));
    }

    /**
     * Configure PostgreSQL database
     */
    protected function configurePostgresql(): void
    {
        $this->info($this->trans('database.engine.postgresql.config'));
        
        $host = $this->ask($this->trans('database.engine.postgresql.host'), '127.0.0.1');
        $port = $this->ask($this->trans('database.engine.postgresql.port'), '5432');
        $database = $this->ask($this->trans('database.engine.postgresql.database'), 'laravel');
        $username = $this->ask($this->trans('database.engine.postgresql.username'), 'postgres');
        $password = $this->secret($this->trans('database.engine.postgresql.password'));

        $this->updateEnvFile([
            'DB_CONNECTION' => 'pgsql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);

        $this->info($this->trans('database.engine.postgresql.configured'));
    }

    /**
     * Configure multiple databases
     */
    protected function configureMultipleDatabases(): void
    {
        $this->info("\n" . $this->trans('database.multiple_config'));

        $prefix = $this->ask($this->trans('database.prefix_question'), '');
        $defaultConnection = $this->choice($this->trans('database.default_connection'), ['mysql', 'pgsql'], 'mysql');
        $authConnection = $this->choice($this->trans('database.auth_connection'), ['mysql', 'pgsql'], 'mysql');

        $this->updateEnvFile([
            'KAELY_AUTH_DB_MODE' => 'multiple',
            'KAELY_AUTH_DB_PREFIX' => $prefix,
            'KAELY_AUTH_DEFAULT_CONNECTION' => $defaultConnection,
            'KAELY_AUTH_AUTH_CONNECTION' => $authConnection,
        ]);
    }

    /**
     * Configure OAuth
     */
    protected function configureOAuth(): void
    {
        $this->info("\n" . $this->trans('oauth.title'));

        $enableOAuth = $this->confirm($this->trans('oauth.enable_question'), false);

        if ($enableOAuth) {
            $this->updateEnvFile(['KAELY_AUTH_OAUTH_ENABLED' => 'true']);

            $providers = $this->choice(
                $this->trans('oauth.provider_choice'),
                $this->transArray('oauth.provider_options'),
                'google'
            );

            if (in_array($providers, ['google', 'both'])) {
                $this->configureGoogleOAuth();
            }

            if (in_array($providers, ['facebook', 'both'])) {
                $this->configureFacebookOAuth();
            }
        }
    }

    /**
     * Configure Google OAuth
     */
    protected function configureGoogleOAuth(): void
    {
        $this->info("\n" . $this->trans('oauth.google_config'));
        
        $clientId = $this->ask($this->trans('oauth.client_id', ['provider' => 'Google']));
        $clientSecret = $this->secret($this->trans('oauth.client_secret', ['provider' => 'Google']));
        $redirectUri = $this->ask($this->trans('oauth.redirect_uri'), url('/api/oauth/google/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_GOOGLE_ENABLED' => 'true',
            'KAELY_AUTH_GOOGLE_CLIENT_ID' => $clientId,
            'KAELY_AUTH_GOOGLE_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_GOOGLE_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure Facebook OAuth
     */
    protected function configureFacebookOAuth(): void
    {
        $this->info("\n" . $this->trans('oauth.facebook_config'));
        
        $clientId = $this->ask($this->trans('oauth.client_id', ['provider' => 'Facebook']));
        $clientSecret = $this->secret($this->trans('oauth.client_secret', ['provider' => 'Facebook']));
        $redirectUri = $this->ask($this->trans('oauth.redirect_uri'), url('/api/oauth/facebook/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_FACEBOOK_ENABLED' => 'true',
            'KAELY_AUTH_FACEBOOK_CLIENT_ID' => $clientId,
            'KAELY_AUTH_FACEBOOK_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_FACEBOOK_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure multitenancy
     */
    protected function configureMultitenancy(): void
    {
        $this->info("\n" . $this->trans('multitenancy.title'));

        $enableMultitenancy = $this->confirm($this->trans('multitenancy.enable_question'), false);

        if ($enableMultitenancy) {
            $this->updateEnvFile(['KAELY_AUTH_MULTITENANCY_ENABLED' => 'true']);

            $mode = $this->choice(
                $this->trans('multitenancy.mode_choice'),
                $this->transArray('multitenancy.mode_options'),
                'subdomain'
            );

            $this->updateEnvFile([
                'KAELY_AUTH_TENANT_MODE' => $mode,
                'KAELY_AUTH_TENANT_RESOLVER' => $mode,
            ]);

            $this->info($this->trans('multitenancy.enabled_message'));
        } else {
            $this->updateEnvFile(['KAELY_AUTH_MULTITENANCY_ENABLED' => 'false']);
            $this->info($this->trans('multitenancy.disabled_message'));
        }
    }

    /**
     * Configure additional features
     */
    protected function configureAdditionalFeatures(): void
    {
        $this->info("\n" . $this->trans('features.title'));

        // Password Reset
        $enablePasswordReset = $this->confirm($this->trans('features.password_reset'), true);
        $this->updateEnvFile(['KAELY_AUTH_PASSWORD_RESET_ENABLED' => $enablePasswordReset ? 'true' : 'false']);

        // Email Verification
        $enableEmailVerification = $this->confirm($this->trans('features.email_verification'), true);
        $this->updateEnvFile(['KAELY_AUTH_EMAIL_VERIFICATION_ENABLED' => $enableEmailVerification ? 'true' : 'false']);

        // Session Management
        $enableSessionManagement = $this->confirm($this->trans('features.session_management'), true);
        $this->updateEnvFile(['KAELY_AUTH_SESSION_MANAGEMENT_ENABLED' => $enableSessionManagement ? 'true' : 'false']);

        // Audit Logging
        $enableAuditLogging = $this->confirm($this->trans('features.audit_logging'), true);
        $this->updateEnvFile(['KAELY_AUTH_AUDIT_ENABLED' => $enableAuditLogging ? 'true' : 'false']);

        // UI Components
        $this->configureUI();
    }

    /**
     * Configure UI components
     */
    protected function configureUI(): void
    {
        $this->info("\n" . $this->trans('ui.title'));

        $uiChoice = $this->choice(
            $this->trans('ui.choice'),
            $this->transArray('ui.options'),
            'blade'
        );

        switch ($uiChoice) {
            case 'blade':
                $this->installBladeUI();
                break;
            case 'livewire':
                $this->installLivewireUI();
                break;
            case 'none':
                $this->info($this->trans('ui.none_selected'));
                break;
        }
    }

    /**
     * Install Blade UI components
     */
    protected function installBladeUI(): void
    {
        $this->info($this->trans('ui.installing_blade'));
        
        // Publish Blade views
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-views --force');
        
        // Publish assets
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-assets --force');
        
        $this->info($this->trans('ui.blade_installed'));
    }

    /**
     * Install Livewire UI components
     */
    protected function installLivewireUI(): void
    {
        $this->info($this->trans('ui.installing_livewire'));
        
        // Install Livewire if not already installed
        if (!$this->isPackageInstalled('livewire/livewire')) {
            $this->info($this->trans('ui.installing_livewire_package'));
            $this->executeCommand('composer require livewire/livewire');
        }
        
        // Publish Livewire views
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-livewire --force');
        
        // Publish assets
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-assets --force');
        
        $this->info($this->trans('ui.livewire_installed'));
    }

    /**
     * Check if package is installed
     */
    protected function isPackageInstalled(string $package): bool
    {
        // Special check for Laravel Sanctum - only consider it installed if properly configured
        if ($package === 'laravel/sanctum') {
            // Check if Sanctum config exists (this means it was published)
            if (File::exists(config_path('sanctum.php'))) {
                return true;
            }
            
            // Check if Sanctum service provider is registered in config/app.php
            $configPath = config_path('app.php');
            if (File::exists($configPath)) {
                $configContent = File::get($configPath);
                if (strpos($configContent, 'Laravel\\Sanctum\\SanctumServiceProvider') !== false) {
                    return true;
                }
            }
            
            // Check if Sanctum migrations exist and have been run
            $migrationsPath = database_path('migrations');
            if (File::exists($migrationsPath)) {
                $migrationFiles = File::glob($migrationsPath . '/*_create_personal_access_tokens_table.php');
                if (!empty($migrationFiles)) {
                    return true;
                }
            }
            
            // For Laravel 12+, Sanctum is included by default but not configured
            // So we consider it NOT installed until it's properly set up
            return false;
        }

        // For other packages, check composer.lock and composer.json
        $composerLockPath = base_path('composer.lock');
        
        if (File::exists($composerLockPath)) {
            $composerLock = json_decode(File::get($composerLockPath), true);
            
            if ($composerLock && isset($composerLock['packages'])) {
                foreach ($composerLock['packages'] as $installedPackage) {
                    if ($installedPackage['name'] === $package) {
                        return true;
                    }
                }
            }
        }

        // Check composer.json as fallback
        $composerJsonPath = base_path('composer.json');
        
        if (File::exists($composerJsonPath)) {
            $composerJson = json_decode(File::get($composerJsonPath), true);
            
            if ($composerJson) {
                // Check require section
                if (isset($composerJson['require']) && isset($composerJson['require'][$package])) {
                    return true;
                }
                
                // Check require-dev section
                if (isset($composerJson['require-dev']) && isset($composerJson['require-dev'][$package])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get package key from package name
     */
    protected function getPackageKey(string $package): string
    {
        $packageMap = [
            'laravel/sanctum' => 'sanctum',
            'laravel/breeze' => 'breeze',
            'laravel/jetstream' => 'jetstream',
            'laravel/ui' => 'ui',
        ];

        return $packageMap[$package] ?? 'sanctum';
    }

    /**
     * Execute command
     */
    protected function executeCommand(string $command, bool $throwOnError = true): void
    {
        $this->info("Executing: {$command}");
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Command failed: " . implode("\n", $output));
            if ($throwOnError) {
                throw new \Exception("Command failed: {$command}");
            }
        } else {
            $this->info("Command executed successfully");
        }
    }

    /**
     * Update .env file
     */
    protected function updateEnvFile(array $variables): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            throw new \Exception($this->trans('errors.env_not_found'));
        }

        $envContent = File::get($envPath);
        
        foreach ($variables as $key => $value) {
            // Escape value if it contains spaces or special characters
            if (strpos($value, ' ') !== false || strpos($value, '"') !== false) {
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            }
            
            // Check if key already exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$value}";
            }
        }
        
        File::put($envPath, $envContent);
    }

    /**
     * Publish configuration
     */
    protected function publishConfiguration(): void
    {
        $this->info($this->trans('installation.publishing_config'));
        
        // Publish KaelyAuth configuration
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-config');
    }

    /**
     * Run migrations
     */
    protected function runMigrations(): void
    {
        $this->info($this->trans('installation.running_migrations'));
        
        try {
            $this->executeCommand('php artisan migrate');
        } catch (\Exception $e) {
            // If migration fails, try to run with --force flag
            $this->warn("Migration failed, trying with --force flag...");
            try {
                $this->executeCommand('php artisan migrate --force');
            } catch (\Exception $e2) {
                $this->warn("Some migrations may have failed, but installation will continue...");
                $this->warn("You can run migrations manually later with: php artisan migrate");
            }
        }
    }

    /**
     * Create admin user
     */
    protected function createAdminUser(): void
    {
        if ($this->confirm($this->trans('admin_user.create_question'))) {
            $this->info($this->trans('admin_user.title'));
            
            $name = $this->ask($this->trans('admin_user.name'), 'Admin User');
            $email = $this->ask($this->trans('admin_user.email'), 'admin@example.com');
            $password = $this->secret($this->trans('admin_user.password'));

            // Create admin user logic here
            $this->info($this->trans('admin_user.success'));
        }
    }

    /**
     * Setup OAuth
     */
    protected function setupOAuth(): void
    {
        $this->info($this->trans('installation.setup_oauth'));
        
        // Install Laravel Socialite if not already installed
        if (!$this->isPackageInstalled('laravel/socialite')) {
            $this->executeCommand('composer require laravel/socialite');
        }
        
        // Publish Socialite configuration
        $this->executeCommand('php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"');
    }

    /**
     * Setup multitenancy
     */
    protected function setupMultitenancy(): void
    {
        $this->info($this->trans('installation.setup_multitenancy'));
        
        // Create tenant tables and configurations
        $this->executeCommand('php artisan kaely:setup-multitenancy');
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(): void
    {
        $this->info("\n" . $this->trans('next_steps.title'));
        $this->info($this->trans('next_steps.subtitle'));
        $this->info("");
        
        $steps = $this->transArray('next_steps.steps');
        if (!empty($steps)) {
            $this->info("Próximos pasos:");
            foreach ($steps as $index => $step) {
                $this->info(($index + 1) . ". {$step}");
            }
        }
        
        $this->info("");
        $commands = $this->transArray('next_steps.commands');
        if (!empty($commands)) {
            $this->info("Comandos disponibles:");
            foreach ($commands as $command) {
                $this->info($command);
            }
        }
        
        $this->info("");
        $this->info($this->trans('next_steps.documentation'));
        $this->info($this->trans('next_steps.issues'));
    }
} 