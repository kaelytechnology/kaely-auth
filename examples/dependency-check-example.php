<?php

/**
 * Example: Dependency Check System
 * 
 * This example shows how to use the dependency checking system
 * in your Laravel application.
 */

use Kaely\Auth\Services\DependencyChecker;

// Example 1: Basic dependency check
$checker = new DependencyChecker();
$report = $checker->validateAndReport();

if (!$report['can_proceed']) {
    echo "Missing dependencies:\n";
    foreach ($report['missing_dependencies'] as $dep) {
        echo "- {$dep['name']}: {$dep['description']}\n";
    }
    echo "Install command: {$report['install_command']}\n";
} else {
    echo "All dependencies are installed!\n";
}

// Example 2: Check specific dependency
if ($checker->isInstalled('laravel/sanctum')) {
    echo "Sanctum is installed\n";
} else {
    echo "Sanctum is not installed\n";
}

// Example 3: Get setup commands
$setupCommands = $checker->getSetupCommands();
if (!empty($setupCommands)) {
    echo "Setup commands needed:\n";
    foreach ($setupCommands as $command) {
        echo "- {$command}\n";
    }
}

// Example 4: In a controller
class ExampleController extends Controller
{
    public function index(DependencyChecker $checker)
    {
        $report = $checker->validateAndReport();
        
        if (!$report['can_proceed']) {
            return response()->json([
                'error' => 'Dependencies missing',
                'missing' => $report['missing_dependencies'],
                'install_command' => $report['install_command']
            ], 503);
        }
        
        // Continue with normal operation
        return response()->json(['status' => 'ok']);
    }
}

// Example 5: In a service provider
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $checker = new DependencyChecker();
        
        if (!$checker->allRequiredDependenciesInstalled()) {
            // Log warning or show notification
            \Log::warning('KaelyAuth dependencies missing', [
                'missing' => $checker->getMissingDependencies()
            ]);
        }
    }
}

// Example 6: Custom dependency check
class CustomDependencyChecker extends DependencyChecker
{
    protected array $requiredDependencies = [
        'laravel/sanctum' => [
            'name' => 'Laravel Sanctum',
            'description' => 'Sistema de autenticación API',
            'composer_package' => 'laravel/sanctum',
            'config_file' => 'sanctum.php',
            'provider' => 'Laravel\Sanctum\SanctumServiceProvider',
        ],
        'spatie/laravel-permission' => [
            'name' => 'Spatie Laravel Permission',
            'description' => 'Sistema de permisos avanzado',
            'composer_package' => 'spatie/laravel-permission',
            'config_file' => 'permission.php',
            'provider' => 'Spatie\Permission\PermissionServiceProvider',
        ]
    ];
}

// Example 7: Using in Artisan commands
class CustomCommand extends Command
{
    public function handle(DependencyChecker $checker)
    {
        $report = $checker->validateAndReport();
        
        if (!$report['can_proceed']) {
            $this->error('Missing dependencies:');
            foreach ($report['missing_dependencies'] as $dep) {
                $this->error("- {$dep['name']}");
            }
            return 1;
        }
        
        $this->info('All dependencies are installed!');
        return 0;
    }
} 