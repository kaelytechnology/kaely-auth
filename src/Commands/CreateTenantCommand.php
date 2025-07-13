<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Kaely\Auth\Services\MultitenancyService;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kaely:create-tenant 
                            {name : Tenant name}
                            {--subdomain= : Subdomain for the tenant}
                            {--domain= : Domain for the tenant}
                            {--database= : Database name for the tenant}
                            {--admin-email= : Admin email for the tenant}
                            {--admin-password= : Admin password for the tenant}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new tenant for KaelyAuth';

    /**
     * Execute the console command.
     */
    public function handle(MultitenancyService $multitenancyService): int
    {
        $this->info('🏢 Creating KaelyAuth Tenant...');

        $name = $this->argument('name');
        $subdomain = $this->option('subdomain');
        $domain = $this->option('domain');
        $database = $this->option('database');
        $adminEmail = $this->option('admin-email');
        $adminPassword = $this->option('admin-password');

        try {
            // Validate inputs
            $this->validateInputs($name, $subdomain, $domain);

            // Create tenant
            $tenant = $multitenancyService->createTenant([
                'name' => $name,
                'subdomain' => $subdomain,
                'domain' => $domain,
                'database' => $database,
                'admin_email' => $adminEmail,
                'admin_password' => $adminPassword,
            ]);

            $this->info('✅ Tenant created successfully!');
            $this->displayTenantInfo($tenant);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Tenant creation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Validate inputs
     */
    protected function validateInputs(string $name, ?string $subdomain, ?string $domain): void
    {
        if (empty($subdomain) && empty($domain)) {
            throw new \Exception('Either --subdomain or --domain must be provided');
        }

        if (!empty($subdomain) && !empty($domain)) {
            throw new \Exception('Cannot provide both --subdomain and --domain');
        }

        // Validate name format
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new \Exception('Tenant name can only contain letters, numbers, underscores, and hyphens');
        }
    }

    /**
     * Display tenant information
     */
    protected function displayTenantInfo(array $tenant): void
    {
        $this->info("\n📋 Tenant Information:");
        $this->info("Name: {$tenant['name']}");
        
        if (!empty($tenant['subdomain'])) {
            $this->info("Subdomain: {$tenant['subdomain']}");
        }
        
        if (!empty($tenant['domain'])) {
            $this->info("Domain: {$tenant['domain']}");
        }
        
        if (!empty($tenant['database'])) {
            $this->info("Database: {$tenant['database']}");
        }
        
        if (!empty($tenant['admin_email'])) {
            $this->info("Admin Email: {$tenant['admin_email']}");
        }

        $this->info("\n🚀 Next Steps:");
        $this->info("1. Configure DNS records for the tenant");
        $this->info("2. Set up the tenant's database");
        $this->info("3. Configure tenant-specific settings");
        $this->info("4. Test the tenant access");
    }
} 