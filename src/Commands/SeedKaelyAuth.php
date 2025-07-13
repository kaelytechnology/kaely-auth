<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Kaely\Auth\Database\Seeders\KaelyAuthSeeder;

class SeedKaelyAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:seed {--force : Force seeding even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed KaelyAuth package with initial data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌱 Seeding KaelyAuth data...');

        try {
            $seeder = new KaelyAuthSeeder();
            $seeder->run();

            $this->info('✅ KaelyAuth data seeded successfully!');
            $this->info('');
            $this->info('📊 Seeded data:');
            $this->info('- Role Categories: 3');
            $this->info('- Roles: 4 (super-admin, admin, user, support)');
            $this->info('- Modules: 7 (dashboard, users, roles, permissions, modules, branches, departments)');
            $this->info('- Permissions: 35+ (CRUD operations for each module)');
            $this->info('');
            $this->info('🔑 Default roles and permissions have been assigned.');

        } catch (\Exception $e) {
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }
} 