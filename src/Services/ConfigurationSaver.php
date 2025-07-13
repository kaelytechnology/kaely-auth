<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class ConfigurationSaver
{
    /**
     * Save wizard configuration to config file and environment
     */
    public function saveConfiguration(array $config): bool
    {
        try {
            $this->saveToConfigFile($config);
            $this->saveToEnvironment($config);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save configuration to config file
     */
    private function saveToConfigFile(array $config): void
    {
        $configPath = config_path('kaely-auth.php');
        
        if (!File::exists($configPath)) {
            return; // Config file doesn't exist yet
        }

        $configContent = File::get($configPath);
        
        // Update database configuration
        if (isset($config['database'])) {
            $configContent = $this->updateDatabaseConfig($configContent, $config['database']);
        }
        
        // Update OAuth configuration
        if (isset($config['oauth'])) {
            $configContent = $this->updateOAuthConfig($configContent, $config['oauth']);
        }
        
        // Update permissions configuration
        if (isset($config['permissions'])) {
            $configContent = $this->updatePermissionsConfig($configContent, $config['permissions']);
        }
        
        File::put($configPath, $configContent);
    }

    /**
     * Update database configuration in config file
     */
    private function updateDatabaseConfig(string $content, array $databaseConfig): string
    {
        if ($databaseConfig['mode'] === 'single') {
            $newConfig = [
                "'mode' => 'single',",
                "'prefix' => '{$databaseConfig['prefix']}',",
                "'connection' => '{$databaseConfig['connection']}',"
            ];
        } else {
            $connections = [];
            foreach ($databaseConfig['connections'] as $name => $connection) {
                $connections[] = "'{$name}' => [";
                $connections[] = "    'prefix' => '{$connection['prefix']}',";
                $connections[] = "    'connection' => '{$connection['connection']}',";
                $connections[] = "],";
            }
            
            $newConfig = [
                "'mode' => 'multi',",
                "'connections' => [",
                ...$connections,
                "],"
            ];
        }
        
        return $this->replaceConfigSection($content, 'database', $newConfig);
    }

    /**
     * Update OAuth configuration in config file
     */
    private function updateOAuthConfig(string $content, array $oauthConfig): string
    {
        if (!$oauthConfig['enabled']) {
            $newConfig = [
                "'enabled' => false,",
                "'providers' => [],"
            ];
        } else {
            $providers = [];
            foreach ($oauthConfig['providers'] as $provider => $config) {
                $providers[] = "'{$provider}' => [";
                $providers[] = "    'client_id' => env('OAUTH_{$provider}_CLIENT_ID'),";
                $providers[] = "    'client_secret' => env('OAUTH_{$provider}_CLIENT_SECRET'),";
                $providers[] = "    'redirect' => env('OAUTH_{$provider}_REDIRECT_URI'),";
                $providers[] = "],";
            }
            
            $newConfig = [
                "'enabled' => true,",
                "'providers' => [",
                ...$providers,
                "],"
            ];
        }
        
        return $this->replaceConfigSection($content, 'oauth', $newConfig);
    }

    /**
     * Update permissions configuration in config file
     */
    private function updatePermissionsConfig(string $content, array $permissionsConfig): string
    {
        $newConfig = [
            "'cache_enabled' => " . ($permissionsConfig['cache_enabled'] ? 'true' : 'false') . ",",
            "'cache_ttl' => {$permissionsConfig['cache_ttl']},",
            "'auto_sync' => " . ($permissionsConfig['auto_sync'] ? 'true' : 'false') . ","
        ];
        
        return $this->replaceConfigSection($content, 'permissions', $newConfig);
    }

    /**
     * Replace a configuration section in the config file
     */
    private function replaceConfigSection(string $content, string $section, array $newConfig): string
    {
        $pattern = "/('{$section}' => \[)(.*?)(\],)/s";
        $replacement = "$1\n        " . implode("\n        ", $newConfig) . "\n    $3";
        
        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * Save configuration to environment file
     */
    private function saveToEnvironment(array $config): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return; // .env file doesn't exist
        }

        $envContent = File::get($envPath);
        $newEnvVars = [];

        // Add OAuth environment variables
        if (isset($config['oauth']['enabled']) && $config['oauth']['enabled']) {
            foreach ($config['oauth']['providers'] as $provider => $providerConfig) {
                $providerUpper = strtoupper($provider);
                $newEnvVars[] = "OAUTH_{$providerUpper}_CLIENT_ID={$providerConfig['client_id']}";
                $newEnvVars[] = "OAUTH_{$providerUpper}_CLIENT_SECRET={$providerConfig['client_secret']}";
                $newEnvVars[] = "OAUTH_{$providerUpper}_REDIRECT_URI={$providerConfig['redirect']}";
            }
        }

        // Add database environment variables
        if (isset($config['database']['mode']) && $config['database']['mode'] === 'multi') {
            $newEnvVars[] = "KAELY_DATABASE_MODE=multi";
            foreach ($config['database']['connections'] as $name => $connection) {
                $newEnvVars[] = "KAELY_DB_{$name}_PREFIX={$connection['prefix']}";
            }
        } else {
            $newEnvVars[] = "KAELY_DATABASE_MODE=single";
            $newEnvVars[] = "KAELY_DB_PREFIX={$config['database']['prefix']}";
        }

        // Add permissions environment variables
        if (isset($config['permissions'])) {
            $newEnvVars[] = "KAELY_CACHE_ENABLED=" . ($config['permissions']['cache_enabled'] ? 'true' : 'false');
            $newEnvVars[] = "KAELY_CACHE_TTL={$config['permissions']['cache_ttl']}";
            $newEnvVars[] = "KAELY_AUTO_SYNC=" . ($config['permissions']['auto_sync'] ? 'true' : 'false');
        }

        // Append new environment variables
        $envContent .= "\n# KaelyAuth Configuration\n";
        $envContent .= implode("\n", $newEnvVars) . "\n";

        File::put($envPath, $envContent);
    }
} 