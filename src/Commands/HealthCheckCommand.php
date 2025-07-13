<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class HealthCheckCommand extends Command
{
    protected $signature = 'kaely:health-check {--detailed : Show detailed information}';
    protected $description = 'Check KaelyAuth system health';

    public function handle(): int
    {
        $this->info('🏥 KaelyAuth Health Check');
        $this->info('========================');

        $issues = [];
        $warnings = [];

        // Check database connection
        $this->checkDatabaseConnection($issues, $warnings);

        // Check required tables
        $this->checkRequiredTables($issues, $warnings);

        // Check configuration
        $this->checkConfiguration($issues, $warnings);

        // Check cache
        $this->checkCache($issues, $warnings);

        // Check performance
        $this->checkPerformance($issues, $warnings);

        // Display results
        $this->displayResults($issues, $warnings);

        return empty($issues) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function checkDatabaseConnection(array &$issues, array &$warnings): void
    {
        $this->info('🔍 Checking database connection...');

        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $issues[] = "Database connection failed: " . $e->getMessage();
            $this->error('❌ Database connection: FAILED');
        }
    }

    protected function checkRequiredTables(array &$issues, array &$warnings): void
    {
        $this->info('📋 Checking required tables...');

        $requiredTables = [
            'users' => 'Core users table',
            'password_reset_tokens' => 'Password reset functionality',
            'personal_access_tokens' => 'API authentication',
        ];

        $kaelyTables = [
            'audit_logs' => 'Audit logging',
            'user_sessions' => 'Session management',
            'email_verifications' => 'Email verification',
        ];

        foreach ($requiredTables as $table => $description) {
            if (Schema::hasTable($table)) {
                $this->info("✅ {$description}: OK");
            } else {
                $issues[] = "Required table '{$table}' missing";
                $this->error("❌ {$description}: MISSING");
            }
        }

        foreach ($kaelyTables as $table => $description) {
            if (Schema::hasTable($table)) {
                $this->info("✅ {$description}: OK");
            } else {
                $warnings[] = "KaelyAuth table '{$table}' missing";
                $this->warn("⚠️  {$description}: MISSING");
            }
        }
    }

    protected function checkConfiguration(array &$issues, array &$warnings): void
    {
        $this->info('⚙️  Checking configuration...');

        // Check security configuration
        if (!Config::get('kaely-auth.security.enabled')) {
            $warnings[] = 'Security features are disabled';
            $this->warn('⚠️  Security: DISABLED');
        } else {
            $this->info('✅ Security: ENABLED');
        }

        // Check performance configuration
        if (!Config::get('kaely-auth.performance.enabled')) {
            $warnings[] = 'Performance optimizations are disabled';
            $this->warn('⚠️  Performance: DISABLED');
        } else {
            $this->info('✅ Performance: ENABLED');
        }

        // Check OAuth configuration
        if (Config::get('kaely-auth.oauth.enabled')) {
            $providers = Config::get('kaely-auth.oauth.providers', []);
            $enabledProviders = array_filter($providers, fn($p) => $p['enabled'] ?? false);
            
            if (empty($enabledProviders)) {
                $warnings[] = 'OAuth enabled but no providers configured';
                $this->warn('⚠️  OAuth: NO PROVIDERS');
            } else {
                $this->info('✅ OAuth: CONFIGURED');
            }
        } else {
            $this->info('ℹ️  OAuth: DISABLED');
        }
    }

    protected function checkCache(array &$issues, array &$warnings): void
    {
        $this->info('💾 Checking cache...');

        try {
            Cache::put('kaely_health_check', 'test', 60);
            $value = Cache::get('kaely_health_check');
            
            if ($value === 'test') {
                $this->info('✅ Cache: WORKING');
            } else {
                $issues[] = 'Cache is not working properly';
                $this->error('❌ Cache: FAILED');
            }
        } catch (\Exception $e) {
            $issues[] = 'Cache connection failed: ' . $e->getMessage();
            $this->error('❌ Cache: FAILED');
        }
    }

    protected function checkPerformance(array &$issues, array &$warnings): void
    {
        $this->info('⚡ Checking performance...');

        // Test query performance
        $startTime = microtime(true);
        $userCount = DB::table('users')->count();
        $queryTime = (microtime(true) - $startTime) * 1000;

        if ($queryTime > 1000) {
            $warnings[] = "Slow query performance: {$queryTime}ms";
            $this->warn("⚠️  Query Performance: SLOW ({$queryTime}ms)");
        } else {
            $this->info("✅ Query Performance: GOOD ({$queryTime}ms)");
        }

        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryMB = round($memoryUsage / 1024 / 1024, 2);

        if ($memoryMB > 128) {
            $warnings[] = "High memory usage: {$memoryMB}MB";
            $this->warn("⚠️  Memory Usage: HIGH ({$memoryMB}MB)");
        } else {
            $this->info("✅ Memory Usage: GOOD ({$memoryMB}MB)");
        }
    }

    protected function displayResults(array $issues, array $warnings): void
    {
        $this->newLine();
        $this->info('📊 Health Check Summary');
        $this->info('======================');

        if (empty($issues) && empty($warnings)) {
            $this->info('🎉 All systems are healthy!');
            return;
        }

        if (!empty($issues)) {
            $this->error('❌ Critical Issues:');
            foreach ($issues as $issue) {
                $this->error("  - {$issue}");
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Warnings:');
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }

        if (!empty($issues)) {
            $this->error('🔧 Please fix the critical issues above.');
        }

        if (!empty($warnings)) {
            $this->warn('💡 Consider addressing the warnings for optimal performance.');
        }
    }
} 