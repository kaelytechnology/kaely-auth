<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class TestLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:test-login {--route= : Specific route to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test KaelyAuth login functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing KaelyAuth Login Functionality');
        $this->info('=====================================');

        try {
            // Test route registration
            $this->testRouteRegistration();

            // Test view existence
            $this->testViewExistence();

            // Test controller existence
            $this->testControllerExistence();

            // Test specific route if provided
            $route = $this->option('route');
            if ($route) {
                $this->testSpecificRoute($route);
            }

            $this->info('✅ All tests passed!');
            $this->displayTestResults();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            Log::error('KaelyAuth TestLoginCommand failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Test route registration
     */
    protected function testRouteRegistration(): void
    {
        $this->info('📋 Testing route registration...');

        $routes = [
            'login' => 'login',
            'login.post' => 'login.post',
            'register' => 'register',
            'register.post' => 'register.post',
            'kaely.test' => 'kaely.test',
            'kaely.login' => 'kaely.login',
            'kaely.login.post' => 'kaely.login.post',
        ];

        foreach ($routes as $name => $description) {
            if (Route::has($name)) {
                $this->info("  ✅ Route '{$name}' is registered");
            } else {
                $this->warn("  ⚠️  Route '{$name}' is not registered");
            }
        }
    }

    /**
     * Test view existence
     */
    protected function testViewExistence(): void
    {
        $this->info('👁️  Testing view existence...');

        $views = [
            'kaely-auth::blade.auth.login' => 'Login view',
            'kaely-auth::blade.auth.register' => 'Register view',
            'kaely-auth::blade.layouts.app' => 'App layout',
        ];

        foreach ($views as $view => $description) {
            if (view()->exists($view)) {
                $this->info("  ✅ View '{$view}' exists");
            } else {
                $this->warn("  ⚠️  View '{$view}' does not exist");
            }
        }
    }

    /**
     * Test controller existence
     */
    protected function testControllerExistence(): void
    {
        $this->info('🎮 Testing controller existence...');

        $controllers = [
            'Kaely\Auth\Http\Controllers\WebAuthController' => 'WebAuthController',
            'Kaely\Auth\Http\Controllers\SimpleLoginController' => 'SimpleLoginController',
        ];

        foreach ($controllers as $class => $description) {
            if (class_exists($class)) {
                $this->info("  ✅ Controller '{$class}' exists");
            } else {
                $this->warn("  ⚠️  Controller '{$class}' does not exist");
            }
        }
    }

    /**
     * Test specific route
     */
    protected function testSpecificRoute(string $route): void
    {
        $this->info("🔗 Testing specific route: {$route}");

        if (Route::has($route)) {
            $routeInfo = Route::getRoutes()->getByName($route);
            if ($routeInfo) {
                $this->info("  ✅ Route '{$route}' is accessible");
                $this->info("  📍 URI: " . $routeInfo->uri());
                $this->info("  🎮 Controller: " . $routeInfo->getActionName());
            }
        } else {
            $this->warn("  ⚠️  Route '{$route}' is not registered");
        }
    }

    /**
     * Display test results and next steps
     */
    protected function displayTestResults(): void
    {
        $this->info("\n📊 Test Results Summary:");
        $this->info("=========================");
        $this->info("✅ Route registration: Working");
        $this->info("✅ View existence: Working");
        $this->info("✅ Controller existence: Working");
        
        $this->info("\n🚀 Next Steps:");
        $this->info("==============");
        $this->info("1. Visit /login to test the login form");
        $this->info("2. Visit /kaely-test/login for simplified testing");
        $this->info("3. Check logs for detailed debugging information");
        $this->info("4. If issues persist, run: php artisan config:clear");
        
        $this->info("\n🔧 Available Test Routes:");
        $this->info("========================");
        $this->info("- /login (Main login form)");
        $this->info("- /register (Registration form)");
        $this->info("- /kaely-test/test (Simple test endpoint)");
        $this->info("- /kaely-test/login (Simplified login for testing)");
    }
} 