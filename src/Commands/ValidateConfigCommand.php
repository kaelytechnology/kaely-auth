<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidateConfigCommand extends Command
{
    protected $signature = 'kaely:validate-config';
    protected $description = 'Validate KaelyAuth configuration';

    public function handle(): int
    {
        $this->info('🔍 Validating KaelyAuth configuration...');

        $errors = [];
        $warnings = [];

        // Check database configuration
        $this->validateDatabaseConfig($errors, $warnings);

        // Check OAuth configuration
        $this->validateOAuthConfig($errors, $warnings);

        // Check multitenancy configuration
        $this->validateMultitenancyConfig($errors, $warnings);

        // Check required tables
        $this->validateRequiredTables($errors, $warnings);

        // Display results
        $this->displayResults($errors, $warnings);

        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function validateDatabaseConfig(array &$errors, array &$warnings): void
    {
        $mode = config('kaely-auth.database.mode');
        
        if (!in_array($mode, ['single', 'multiple'])) {
            $errors[] = "Invalid database mode: {$mode}. Must be 'single' or 'multiple'.";
        }

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $errors[] = "Database connection failed: " . $e->getMessage();
        }
    }

    protected function validateOAuthConfig(array &$errors, array &$warnings): void
    {
        if (!config('kaely-auth.oauth.enabled')) {
            return;
        }

        $providers = config('kaely-auth.oauth.providers', []);
        
        foreach ($providers as $provider => $config) {
            if ($config['enabled'] ?? false) {
                if (empty($config['client_id']) || empty($config['client_secret'])) {
                    $warnings[] = "OAuth provider '{$provider}' is enabled but missing credentials.";
                }
            }
        }
    }

    protected function validateMultitenancyConfig(array &$errors, array &$warnings): void
    {
        if (!config('kaely-auth.multitenancy.enabled')) {
            return;
        }

        $mode = config('kaely-auth.multitenancy.mode');
        
        if (!in_array($mode, ['subdomain', 'domain'])) {
            $errors[] = "Invalid tenant mode: {$mode}. Must be 'subdomain' or 'domain'.";
        }
    }

    protected function validateRequiredTables(array &$errors, array &$warnings): void
    {
        $requiredTables = [
            'users',
            'password_reset_tokens',
            'personal_access_tokens',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $errors[] = "Required table '{$table}' does not exist.";
            }
        }

        // Check KaelyAuth specific tables
        $kaelyTables = [
            'audit_logs',
            'user_sessions',
            'email_verifications',
        ];

        foreach ($kaelyTables as $table) {
            if (!Schema::hasTable($table)) {
                $warnings[] = "KaelyAuth table '{$table}' does not exist. Run migrations.";
            }
        }
    }

    protected function displayResults(array $errors, array $warnings): void
    {
        if (empty($errors) && empty($warnings)) {
            $this->info('✅ Configuration is valid!');
            return;
        }

        if (!empty($errors)) {
            $this->error('❌ Configuration errors found:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Configuration warnings:');
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }
    }
} 