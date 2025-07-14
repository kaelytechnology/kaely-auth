<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallUICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:install-ui 
                            {type : Type of UI to install (blade/livewire)}
                            {--force : Force installation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install KaelyAuth UI components';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $force = $this->option('force');

        if (!in_array($type, ['blade', 'livewire'])) {
            $this->error('Invalid UI type. Please choose "blade" or "livewire".');
            return Command::FAILURE;
        }

        if (!$force && !$this->confirm("Are you sure you want to install {$type} UI components?")) {
            $this->info('Installation cancelled.');
            return Command::SUCCESS;
        }

        try {
            switch ($type) {
                case 'blade':
                    $this->installBladeUI();
                    break;
                case 'livewire':
                    $this->installLivewireUI();
                    break;
            }

            $this->info("✅ {$type} UI components installed successfully!");
            $this->displayNextSteps($type);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to install {$type} UI: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Install Blade UI components
     */
    protected function installBladeUI(): void
    {
        $this->info('📦 Installing Blade UI components...');
        
        // Publish Blade views
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-views --force');
        
        // Publish assets
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-assets --force');
        
        // Create web routes file
        $this->createWebRoutesFile();
        
        $this->info('✅ Blade UI components installed successfully!');
    }

    /**
     * Install Livewire UI components
     */
    protected function installLivewireUI(): void
    {
        $this->info('📦 Installing Livewire UI components...');
        
        // Install Livewire if not already installed
        if (!$this->isPackageInstalled('livewire/livewire')) {
            $this->info('📦 Installing Livewire package...');
            $this->executeCommand('composer require livewire/livewire');
        }
        
        // Publish Livewire views
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-livewire --force');
        
        // Publish assets
        $this->executeCommand('php artisan vendor:publish --tag=kaely-auth-assets --force');
        
        // Create web routes file
        $this->createWebRoutesFile();
        
        $this->info('✅ Livewire UI components installed successfully!');
    }

    /**
     * Check if package is installed
     */
    protected function isPackageInstalled(string $package): bool
    {
        $composerLockPath = base_path('composer.lock');
        
        if (File::exists($composerLockPath)) {
            $composerLock = json_decode(File::get($composerLockPath), true);
            
            if ($composerLock && isset($composerLock['packages'])) {
                foreach ($composerLock['packages'] as $installedPackage) {
                    if ($installedPackage['name'] === $package) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Execute command
     */
    protected function executeCommand(string $command): void
    {
        $this->info("Executing: {$command}");
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Command failed: " . implode("\n", $output));
            throw new \Exception("Command failed: {$command}");
        } else {
            $this->info("Command executed successfully");
        }
    }

    /**
     * Create web routes file
     */
    protected function createWebRoutesFile(): void
    {
        $this->info('🛣️ Creating web routes file...');
        
        $routesPath = base_path('routes/web.php');
        
        if (!File::exists($routesPath)) {
            $webRoutesContent = $this->getWebRoutesFileContent();
            File::put($routesPath, $webRoutesContent);
            $this->info('✅ Web routes file created at routes/web.php');
        } else {
            $this->info('✅ Web routes file already exists');
        }
    }

    /**
     * Get web routes file content
     */
    protected function getWebRoutesFileContent(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// KaelyAuth routes are automatically loaded by the package
// Available routes:
// - /login (GET, POST)
// - /register (GET, POST)
// - /forgot-password (GET, POST)
// - /reset-password/{token} (GET, POST)
// - /verify-email (GET)
// - /dashboard (GET) - requires auth
// - /profile (GET, PUT) - requires auth
// - /change-password (GET, PUT) - requires auth
// - /logout (POST) - requires auth
// - /admin/* (GET) - requires auth and admin permissions
PHP;
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(string $type): void
    {
        $this->info("\n🎉 UI installation complete!");
        $this->info("=====================");
        
        switch ($type) {
            case 'blade':
                $this->info("\nNext steps for Blade UI:");
                $this->info("1. Include the CSS file in your layout:");
                $this->info("   <link rel=\"stylesheet\" href=\"/vendor/kaely-auth/css/kaely-auth.css\">");
                $this->info("2. Include the JavaScript file in your layout:");
                $this->info("   <script src=\"/vendor/kaely-auth/js/kaely-auth.js\"></script>");
                $this->info("3. Web routes are automatically loaded by the package");
                $this->info("4. Available routes:");
                $this->info("   - /login (GET, POST)");
                $this->info("   - /register (GET, POST)");
                $this->info("   - /dashboard (GET) - requires auth");
                $this->info("   - /profile (GET, PUT) - requires auth");
                $this->info("   - /logout (POST) - requires auth");
                break;
                
            case 'livewire':
                $this->info("\nNext steps for Livewire UI:");
                $this->info("1. Include the CSS file in your layout:");
                $this->info("   <link rel=\"stylesheet\" href=\"/vendor/kaely-auth/css/kaely-auth.css\">");
                $this->info("2. Include the JavaScript file in your layout:");
                $this->info("   <script src=\"/vendor/kaely-auth/js/kaely-auth.js\"></script>");
                $this->info("3. Web routes are automatically loaded by the package");
                $this->info("4. Available routes:");
                $this->info("   - /login (GET, POST)");
                $this->info("   - /register (GET, POST)");
                $this->info("   - /dashboard (GET) - requires auth");
                $this->info("   - /profile (GET, PUT) - requires auth");
                $this->info("   - /logout (POST) - requires auth");
                $this->info("5. Make sure Livewire is properly configured in your app");
                break;
        }
        
        $this->info("\n📖 Documentation: https://kaely-auth.com/docs/ui");
        $this->info("🐛 Issues: https://github.com/kaelytechnology/kaely-auth/issues");
    }
} 