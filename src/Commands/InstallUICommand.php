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
                $this->info("3. Use the Blade components in your views:");
                $this->info("   @include('kaely-auth::blade.login')");
                $this->info("   @include('kaely-auth::blade.register')");
                break;
                
            case 'livewire':
                $this->info("\nNext steps for Livewire UI:");
                $this->info("1. Include the CSS file in your layout:");
                $this->info("   <link rel=\"stylesheet\" href=\"/vendor/kaely-auth/css/kaely-auth.css\">");
                $this->info("2. Include the JavaScript file in your layout:");
                $this->info("   <script src=\"/vendor/kaely-auth/js/kaely-auth.js\"></script>");
                $this->info("3. Use the Livewire components in your views:");
                $this->info("   <livewire:kaely-auth.login-form />");
                $this->info("   <livewire:kaely-auth.register-form />");
                $this->info("4. Make sure Livewire is properly configured in your app");
                break;
        }
        
        $this->info("\n📖 Documentation: https://kaely-auth.com/docs/ui");
        $this->info("🐛 Issues: https://github.com/kaelytechnology/kaely-auth/issues");
    }
} 