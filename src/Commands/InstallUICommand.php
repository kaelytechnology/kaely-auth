<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallUICommand extends Command
{
    protected $signature = 'kaely:install-ui {type : The UI type (blade, livewire)}';
    protected $description = 'Install KaelyAuth UI components';

    public function handle()
    {
        $type = $this->argument('type');

        switch ($type) {
            case 'blade':
                $this->installBladeUI();
                break;
            case 'livewire':
                $this->installLivewireUI();
                break;
            default:
                $this->error('Invalid UI type. Use "blade" or "livewire".');
                return 1;
        }

        return 0;
    }

    protected function installBladeUI()
    {
        $this->info('Installing Blade UI...');

        // Publish Blade views
        $this->call('vendor:publish', ['--tag' => 'kaely-auth-blade-views']);

        // Create routes
        $this->createBladeRoutes();

        $this->info('Blade UI installed successfully!');
        $this->info('Routes added to web.php');
        $this->info('Views published to resources/views/vendor/kaely-auth/blade');
    }

    protected function installLivewireUI()
    {
        $this->info('Installing Livewire UI...');

        // Check if Livewire is installed
        if (!$this->isPackageInstalled('livewire/livewire')) {
            $this->info('Installing Livewire package...');
            $this->executeCommand('composer require livewire/livewire');
        }

        // Publish Livewire views
        $this->call('vendor:publish', ['--tag' => 'kaely-auth-livewire-views']);

        // Create routes
        $this->createLivewireRoutes();

        $this->info('Livewire UI installed successfully!');
        $this->info('Routes added to web.php');
        $this->info('Views published to resources/views/vendor/kaely-auth/livewire');
    }

    protected function createBladeRoutes()
    {
        $routesContent = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

// KaelyAuth Blade UI Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('kaely-auth::blade.auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('kaely-auth::blade.auth.register');
    })->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('kaely-auth::blade.dashboard');
    })->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');
});
PHP;

        $this->addRoutesToFile($routesContent, 'KaelyAuth Blade UI Routes');
    }

    protected function createLivewireRoutes()
    {
        $routesContent = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

// KaelyAuth Livewire UI Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', \Kaely\Auth\Livewire\Auth\LoginForm::class)->name('login');
    Route::get('/register', \Kaely\Auth\Livewire\Auth\RegisterForm::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('kaely-auth::blade.dashboard');
    })->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');
});
PHP;

        $this->addRoutesToFile($routesContent, 'KaelyAuth Livewire UI Routes');
    }

    protected function addRoutesToFile($routesContent, $comment)
    {
        $routesPath = base_path('routes/web.php');
        $currentContent = File::get($routesPath);
        
        // Add routes if not already present
        if (!str_contains($currentContent, $comment)) {
            $currentContent .= "\n\n" . $routesContent;
            File::put($routesPath, $currentContent);
        }
    }

    protected function isPackageInstalled($package)
    {
        $composerJson = json_decode(File::get(base_path('composer.json')), true);
        return isset($composerJson['require'][$package]);
    }

    protected function executeCommand($command)
    {
        $this->info("Executing: $command");
        $output = shell_exec($command . ' 2>&1');
        $this->info($output);
    }
} 