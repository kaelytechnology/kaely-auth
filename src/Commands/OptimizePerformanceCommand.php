<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Kaely\Auth\Services\CacheService;
use Kaely\Auth\Services\OptimizedQueryService;

class OptimizePerformanceCommand extends Command
{
    protected $signature = 'kaely:optimize-performance 
                            {--cache-only : Only warm up cache}
                            {--indexes-only : Only create indexes}
                            {--tables-only : Only optimize tables}';
    
    protected $description = 'Optimize KaelyAuth performance';

    public function handle(): int
    {
        $this->info('🚀 Starting KaelyAuth performance optimization...');

        try {
            if (!$this->option('indexes-only') && !$this->option('tables-only')) {
                $this->warmUpCache();
            }

            if (!$this->option('cache-only') && !$this->option('tables-only')) {
                $this->createIndexes();
            }

            if (!$this->option('cache-only') && !$this->option('indexes-only')) {
                $this->optimizeTables();
            }

            $this->info('✅ Performance optimization completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Performance optimization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Warm up cache
     */
    protected function warmUpCache(): void
    {
        $this->info('📦 Warming up cache...');

        $cacheService = new CacheService();
        $cacheService->warmUpCache();

        // Cache user data
        $users = DB::table('users')->pluck('id');
        $bar = $this->output->createProgressBar(count($users));
        
        foreach ($users as $userId) {
            // Cache user permissions
            $permissions = DB::table('permissions')
                ->join('user_permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->where('user_permissions.user_id', $userId)
                ->pluck('permissions.name')
                ->toArray();
            
            $cacheService->cacheUserPermissions($userId, $permissions);

            // Cache user roles
            $roles = DB::table('roles')
                ->join('user_role', 'roles.id', '=', 'user_role.role_id')
                ->where('user_role.user_id', $userId)
                ->pluck('roles.name')
                ->toArray();
            
            $cacheService->cacheUserRoles($userId, $roles);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Cache warmed up successfully!');
    }

    /**
     * Create database indexes
     */
    protected function createIndexes(): void
    {
        $this->info('🔍 Creating database indexes...');

        $queryService = new OptimizedQueryService();
        $results = $queryService->createIndexes();

        $this->table(['Index', 'Status'], array_map(function ($index, $status) {
            return [$index, $status];
        }, array_keys($results), $results));

        $this->info('✅ Indexes created successfully!');
    }

    /**
     * Optimize database tables
     */
    protected function optimizeTables(): void
    {
        $this->info('🗄️  Optimizing database tables...');

        $queryService = new OptimizedQueryService();
        $results = $queryService->optimizeTables();

        $this->table(['Table', 'Status'], array_map(function ($table, $status) {
            return [$table, $status];
        }, array_keys($results), $results));

        $this->info('✅ Tables optimized successfully!');
    }
} 