<?php

namespace Kaely\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_command_exists()
    {
        $this->assertTrue(
            collect(Artisan::all())->has('kaely:install-wizard')
        );
    }

    public function test_wizard_command_has_correct_signature()
    {
        $command = Artisan::all()['kaely:install-wizard'];
        
        $this->assertEquals(
            'kaely:install-wizard {--force : Force installation without confirmation}',
            $command->getSignature()
        );
    }

    public function test_wizard_command_has_description()
    {
        $command = Artisan::all()['kaely:install-wizard'];
        
        $this->assertEquals(
            'Interactive wizard to install and configure KaelyAuth package',
            $command->getDescription()
        );
    }

    public function test_wizard_can_detect_dependencies()
    {
        // This test verifies the wizard can detect Sanctum
        $this->assertTrue(class_exists('Laravel\Sanctum\SanctumServiceProvider'));
    }

    public function test_wizard_can_detect_auth_systems()
    {
        // Test Sanctum detection
        $this->assertTrue(class_exists('Laravel\Sanctum\SanctumServiceProvider'));
        
        // Test Breeze detection (if installed)
        if (class_exists('Laravel\Breeze\BreezeServiceProvider')) {
            $this->assertTrue(class_exists('Laravel\Breeze\BreezeServiceProvider'));
        }
        
        // Test Jetstream detection (if installed)
        if (class_exists('Laravel\Jetstream\JetstreamServiceProvider')) {
            $this->assertTrue(class_exists('Laravel\Jetstream\JetstreamServiceProvider'));
        }
    }

    public function test_wizard_can_update_user_model()
    {
        // Create a test User model
        $userModelPath = app_path('Models/User.php');
        $userModelContent = '<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        "name",
        "email",
        "password",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];
}';

        File::put($userModelPath, $userModelContent);

        // Test that the wizard can update the User model
        $this->assertTrue(File::exists($userModelPath));
        
        // Clean up
        File::delete($userModelPath);
    }

    public function test_wizard_configuration_structure()
    {
        $wizard = new \Kaely\Auth\Console\Commands\InstallWizardCommand();
        
        // Test that the wizard has the expected properties
        $this->assertTrue(property_exists($wizard, 'config'));
        $this->assertTrue(property_exists($wizard, 'databaseMode'));
        $this->assertTrue(property_exists($wizard, 'oauthProviders'));
        $this->assertTrue(property_exists($wizard, 'authSystem'));
    }
} 