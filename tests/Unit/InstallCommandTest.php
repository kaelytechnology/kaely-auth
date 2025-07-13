<?php

namespace Kaely\Auth\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Kaely\Auth\Commands\InstallCommand;
use Mockery;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock file operations
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn('{"packages":[]}');
        File::shouldReceive('put')->andReturn(true);
    }

    /** @test */
    public function it_can_detect_laravel_version()
    {
        $command = new InstallCommand();
        
        $version = app()->version();
        
        $this->assertNotEmpty($version);
        $this->assertTrue(version_compare($version, '8.0.0', '>='));
    }

    /** @test */
    public function it_can_detect_installed_packages()
    {
        $command = new InstallCommand();
        
        // Mock composer.lock with Sanctum installed
        $composerLock = [
            'packages' => [
                ['name' => 'laravel/sanctum'],
                ['name' => 'laravel/framework'],
            ]
        ];
        
        File::shouldReceive('get')
            ->with(base_path('composer.lock'))
            ->andReturn(json_encode($composerLock));
        
        $this->assertTrue($command->isPackageInstalled('laravel/sanctum'));
        $this->assertFalse($command->isPackageInstalled('laravel/breeze'));
    }

    /** @test */
    public function it_can_get_package_key()
    {
        $command = new InstallCommand();
        
        $this->assertEquals('sanctum', $command->getPackageKey('laravel/sanctum'));
        $this->assertEquals('breeze', $command->getPackageKey('laravel/breeze'));
        $this->assertEquals('jetstream', $command->getPackageKey('laravel/jetstream'));
        $this->assertEquals('sanctum', $command->getPackageKey('unknown/package'));
    }

    /** @test */
    public function it_can_update_env_file()
    {
        $command = new InstallCommand();
        
        $envContent = "APP_NAME=Laravel\nDB_CONNECTION=mysql\n";
        
        File::shouldReceive('get')
            ->with(base_path('.env'))
            ->andReturn($envContent);
        
        File::shouldReceive('put')
            ->with(base_path('.env'), Mockery::type('string'))
            ->andReturn(true);
        
        $variables = [
            'KAELY_AUTH_DB_MODE' => 'single',
            'KAELY_AUTH_OAUTH_ENABLED' => 'true'
        ];
        
        $command->updateEnvFile($variables);
        
        // Should not throw exception
        $this->assertTrue(true);
    }

    /** @test */
    public function it_throws_exception_when_env_file_not_found()
    {
        $command = new InstallCommand();
        
        File::shouldReceive('exists')
            ->with(base_path('.env'))
            ->andReturn(false);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File .env not found');
        
        $command->updateEnvFile(['TEST' => 'value']);
    }

    /** @test */
    public function it_can_get_translations()
    {
        $command = new InstallCommand();
        
        // Test English translations
        $command->language = 'en';
        $translations = $command->getTranslations();
        
        $this->assertIsArray($translations);
        $this->assertArrayHasKey('welcome', $translations);
        $this->assertArrayHasKey('title', $translations['welcome']);
    }

    /** @test */
    public function it_can_translate_text()
    {
        $command = new InstallCommand();
        $command->language = 'en';
        
        $translated = $command->trans('welcome.title');
        
        $this->assertEquals('🚀 Welcome to KaelyAuth Installation Wizard!', $translated);
    }

    /** @test */
    public function it_can_translate_with_replacements()
    {
        $command = new InstallCommand();
        $command->language = 'en';
        
        $translated = $command->trans('laravel_check.version', ['version' => '10.0']);
        
        $this->assertEquals('Laravel Version: 10.0', $translated);
    }

    /** @test */
    public function it_returns_key_when_translation_not_found()
    {
        $command = new InstallCommand();
        $command->language = 'en';
        
        $translated = $command->trans('nonexistent.key');
        
        $this->assertEquals('nonexistent.key', $translated);
    }

    /** @test */
    public function it_can_switch_language()
    {
        $command = new InstallCommand();
        
        // Test with language option
        $command->setLanguage();
        
        $this->assertContains($command->language, ['en', 'es']);
    }

    /** @test */
    public function it_can_generate_filename()
    {
        $command = new InstallCommand();
        
        $filename = $command->generateFilename('audit', 'excel');
        
        $this->assertStringContainsString('kaely_audit_logs_', $filename);
        $this->assertStringEndsWith('.xlsx', $filename);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 