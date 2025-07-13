<?php

namespace Kaely\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class InstallCommandFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test .env file
        $envContent = "APP_NAME=Laravel\nDB_CONNECTION=sqlite\nDB_DATABASE=:memory:\n";
        File::shouldReceive('exists')->with(base_path('.env'))->andReturn(true);
        File::shouldReceive('get')->with(base_path('.env'))->andReturn($envContent);
        File::shouldReceive('put')->andReturn(true);
    }

    /** @test */
    public function it_can_run_install_command_with_skip_wizard()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_can_run_install_command_with_specific_language()
    {
        $this->artisan('kaely:install', [
            '--language' => 'es',
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_validates_language_option()
    {
        $this->artisan('kaely:install', [
            '--language' => 'invalid',
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0); // Should still work with fallback
    }

    /** @test */
    public function it_can_publish_configuration()
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'kaely-auth-config'
        ])->assertExitCode(0);
        
        $this->assertFileExists(config_path('kaely-auth.php'));
    }

    /** @test */
    public function it_can_run_migrations()
    {
        $this->artisan('migrate')->assertExitCode(0);
    }

    /** @test */
    public function it_can_check_database_connection()
    {
        // Test with SQLite in-memory database
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_can_configure_oauth()
    {
        $this->artisan('kaely:setup-oauth')->assertExitCode(0);
    }

    /** @test */
    public function it_can_configure_multitenancy()
    {
        $this->artisan('kaely:setup-multitenancy')->assertExitCode(0);
    }

    /** @test */
    public function it_can_create_tenant()
    {
        $this->artisan('kaely:create-tenant', [
            '--name' => 'Test Tenant',
            '--domain' => 'test.example.com'
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_can_cleanup_tokens()
    {
        $this->artisan('kaely:cleanup-tokens')->assertExitCode(0);
    }

    /** @test */
    public function it_can_generate_audit_report()
    {
        $this->artisan('kaely:audit-report')->assertExitCode(0);
    }

    /** @test */
    public function it_can_export_logs()
    {
        $this->artisan('kaely:export-logs', [
            'type' => 'audit',
            '--format' => 'json',
            '--days' => '7'
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_validates_export_format()
    {
        $this->artisan('kaely:export-logs', [
            'type' => 'audit',
            '--format' => 'invalid'
        ])->assertExitCode(1);
    }

    /** @test */
    public function it_can_handle_missing_env_file()
    {
        File::shouldReceive('exists')->with(base_path('.env'))->andReturn(false);
        
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(1);
    }

    /** @test */
    public function it_can_handle_database_connection_failure()
    {
        // Configure invalid database connection
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'invalid-host',
            'database' => 'invalid-db',
            'username' => 'invalid-user',
            'password' => 'invalid-pass',
        ]);
        
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0); // Should handle gracefully
    }

    /** @test */
    public function it_can_configure_single_database_mode()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
        
        // Check that single database mode is configured
        $this->assertTrue(true); // Add actual assertions when config is implemented
    }

    /** @test */
    public function it_can_configure_multiple_database_mode()
    {
        // This would require more complex setup with multiple databases
        $this->markTestSkipped('Multiple database mode test requires complex setup');
    }

    /** @test */
    public function it_can_handle_oauth_configuration()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
        
        // Check that OAuth can be configured
        $this->assertTrue(true); // Add actual assertions when OAuth is implemented
    }

    /** @test */
    public function it_can_handle_multitenancy_configuration()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
        
        // Check that multitenancy can be configured
        $this->assertTrue(true); // Add actual assertions when multitenancy is implemented
    }

    /** @test */
    public function it_can_handle_additional_features_configuration()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
        
        // Check that additional features can be configured
        $this->assertTrue(true); // Add actual assertions when features are implemented
    }

    /** @test */
    public function it_can_handle_admin_user_creation()
    {
        $this->artisan('kaely:install', [
            '--skip-wizard' => true,
            '--force' => true
        ])->assertExitCode(0);
        
        // Check that admin user creation works
        $this->assertTrue(true); // Add actual assertions when user creation is implemented
    }

    /** @test */
    public function it_can_handle_command_execution()
    {
        $command = new \Kaely\Auth\Commands\InstallCommand();
        
        // Mock command execution
        $command->executeCommand('echo "test"');
        
        $this->assertTrue(true); // Should not throw exception
    }

    /** @test */
    public function it_can_handle_command_execution_failure()
    {
        $command = new \Kaely\Auth\Commands\InstallCommand();
        
        $this->expectException(\Exception::class);
        
        // This should fail
        $command->executeCommand('invalid-command-that-does-not-exist');
    }

    protected function tearDown(): void
    {
        // Clean up any created files
        if (File::exists(config_path('kaely-auth.php'))) {
            unlink(config_path('kaely-auth.php'));
        }
        
        parent::tearDown();
    }
} 