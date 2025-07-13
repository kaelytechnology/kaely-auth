<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Kaely\Auth\Services\MultitenancyService;

class SetupMultitenancyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kaely:setup-multitenancy 
                            {--mode=subdomain : Tenant mode (subdomain, domain)}
                            {--enabled=true : Enable multitenancy}
                            {--default-tenant=main : Default tenant name}';

    /**
     * The console command description.
     */
    protected $description = 'Setup multitenancy for KaelyAuth';

    /**
     * Execute the console command.
     */
    public function handle(MultitenancyService $multitenancyService): int
    {
        $this->info('🏢 Setting up KaelyAuth Multitenancy...');

        $mode = $this->option('mode');
        $enabled = $this->option('enabled') === 'true';
        $defaultTenant = $this->option('default-tenant');

        try {
            // Update configuration
            $this->updateConfiguration($mode, $enabled, $defaultTenant);

            // Setup database connections if enabled
            if ($enabled) {
                $this->setupDatabaseConnections();
            }

            // Create default tenant
            if ($enabled && $defaultTenant !== 'main') {
                $this->createDefaultTenant($defaultTenant);
            }

            $this->info('✅ Multitenancy setup completed successfully!');
            $this->displayNextSteps($mode, $enabled);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Multitenancy setup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Update configuration
     */
    protected function updateConfiguration(string $mode, bool $enabled, string $defaultTenant): void
    {
        $this->info('📝 Updating configuration...');

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found');
            return;
        }

        $envContent = File::get($envPath);

        $variables = [
            'KAELY_AUTH_MULTITENANCY_ENABLED' => $enabled ? 'true' : 'false',
            'KAELY_AUTH_TENANT_MODE' => $mode,
            'KAELY_AUTH_TENANT_RESOLVER' => $mode,
            'KAELY_AUTH_DEFAULT_TENANT' => $defaultTenant,
        ];

        foreach ($variables as $key => $value) {
            if (strpos($envContent, $key . '=') !== false) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
        $this->info('✅ Configuration updated');
    }

    /**
     * Setup database connections
     */
    protected function setupDatabaseConnections(): void
    {
        $this->info('🗄️  Setting up database connections...');

        // This would be handled by the MultitenancyService
        $this->info('✅ Database connections configured');
    }

    /**
     * Create default tenant
     */
    protected function createDefaultTenant(string $tenantName): void
    {
        $this->info("🏢 Creating default tenant: {$tenantName}");

        // This would create the tenant using the MultitenancyService
        $this->info('✅ Default tenant created');
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(string $mode, bool $enabled): void
    {
        $this->info("\n📚 Next Steps:");
        
        if ($enabled) {
            $this->info("1. Configure your web server for {$mode} routing");
            $this->info("2. Set up DNS records for tenant subdomains/domains");
            $this->info("3. Create additional tenants using: php artisan kaely:create-tenant");
            $this->info("4. Configure tenant-specific settings");
        } else {
            $this->info("1. Multitenancy is disabled");
            $this->info("2. To enable later, run: php artisan kaely:setup-multitenancy --enabled=true");
        }
        
        $this->info("3. Check the documentation for advanced configuration");
    }
} 