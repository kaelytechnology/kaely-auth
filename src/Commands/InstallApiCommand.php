<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'install:api {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Install API authentication with Laravel Sanctum';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Installing API Authentication with Laravel Sanctum...');

        // Install Sanctum if not already installed
        $this->installSanctum();

        // Publish Sanctum configuration
        $this->publishSanctumConfig();

        // Run migrations
        $this->runMigrations();

        // Create API routes
        $this->createApiRoutes();

        // Create API controllers
        $this->createApiControllers();

        // Create API middleware
        $this->createApiMiddleware();

        $this->info('✅ API authentication installed successfully!');
        $this->info('');
        $this->info('📋 Next steps:');
        $this->info('1. Configure your API routes in routes/api.php');
        $this->info('2. Set up your frontend to use the API endpoints');
        $this->info('3. Test the API with: php artisan route:list --path=api');
        $this->info('4. Available endpoints:');
        $this->info('   - POST /api/auth/register');
        $this->info('   - POST /api/auth/login');
        $this->info('   - POST /api/auth/logout (requires auth)');
        $this->info('   - POST /api/auth/refresh (requires auth)');
        $this->info('   - GET /api/user (requires auth)');
        $this->info('   - PUT /api/user (requires auth)');

        return 0;
    }

    /**
     * Check if Sanctum is installed
     */
    protected function isSanctumInstalled(): bool
    {
        // Check if Sanctum config exists (this means it was published)
        if (File::exists(config_path('sanctum.php'))) {
            return true;
        }
        
        // Check if Sanctum service provider is registered in config/app.php
        $configPath = config_path('app.php');
        if (File::exists($configPath)) {
            $configContent = File::get($configPath);
            if (strpos($configContent, 'Laravel\\Sanctum\\SanctumServiceProvider') !== false) {
                return true;
            }
        }
        
        // Check if Sanctum migrations exist
        $migrationsPath = database_path('migrations');
        if (File::exists($migrationsPath)) {
            $migrationFiles = File::glob($migrationsPath . '/*_create_personal_access_tokens_table.php');
            if (!empty($migrationFiles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Install Laravel Sanctum
     */
    protected function installSanctum(): void
    {
        if (!$this->isSanctumInstalled()) {
            $this->info('📦 Installing Laravel Sanctum...');
            $this->executeCommand('composer require laravel/sanctum');
        } else {
            $this->info('✅ Laravel Sanctum is already installed');
        }
    }

    /**
     * Publish Sanctum configuration
     */
    protected function publishSanctumConfig(): void
    {
        $this->info('📋 Publishing Sanctum configuration...');
        $this->executeCommand('php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"');
    }

    /**
     * Run migrations
     */
    protected function runMigrations(): void
    {
        $this->info('🗄️ Running migrations...');
        
        try {
            $this->executeCommand('php artisan migrate');
        } catch (\Exception $e) {
            // If migration fails, try to run with --force flag
            $this->warn("Migration failed, trying with --force flag...");
            try {
                $this->executeCommand('php artisan migrate --force');
            } catch (\Exception $e2) {
                $this->warn("Some migrations may have failed, but installation will continue...");
                $this->warn("You can run migrations manually later with: php artisan migrate");
            }
        }
    }

    /**
     * Create API routes
     */
    protected function createApiRoutes(): void
    {
        $this->info('🛣️ Creating API routes...');
        
        $routesPath = base_path('routes/api.php');
        $apiRoutes = $this->getApiRoutes();
        
        if (File::exists($routesPath)) {
            $currentContent = File::get($routesPath);
            
            // Check if routes already exist
            if (strpos($currentContent, '// KaelyAuth API Routes') === false) {
                $newContent = $currentContent . "\n" . $apiRoutes;
                File::put($routesPath, $newContent);
                $this->info('✅ API routes added to routes/api.php');
            } else {
                $this->info('✅ API routes already exist');
            }
        }
    }

    /**
     * Create API controllers
     */
    protected function createApiControllers(): void
    {
        $this->info('🎮 Creating API controllers...');
        
        $controllersDir = app_path('Http/Controllers/Api');
        if (!File::exists($controllersDir)) {
            File::makeDirectory($controllersDir, 0755, true);
        }

        // Create AuthController
        $authControllerPath = $controllersDir . '/AuthController.php';
        if (!File::exists($authControllerPath)) {
            $authController = $this->getAuthController();
            File::put($authControllerPath, $authController);
            $this->info('✅ AuthController created');
        }

        // Create UserController
        $userControllerPath = $controllersDir . '/UserController.php';
        if (!File::exists($userControllerPath)) {
            $userController = $this->getUserController();
            File::put($userControllerPath, $userController);
            $this->info('✅ UserController created');
        }
    }

    /**
     * Create API middleware
     */
    protected function createApiMiddleware(): void
    {
        $this->info('🛡️ Creating API middleware...');
        
        $middlewareDir = app_path('Http/Middleware');
        if (!File::exists($middlewareDir)) {
            File::makeDirectory($middlewareDir, 0755, true);
        }

        // Create ApiAuthMiddleware
        $apiAuthMiddlewarePath = $middlewareDir . '/ApiAuthMiddleware.php';
        if (!File::exists($apiAuthMiddlewarePath)) {
            $apiAuthMiddleware = $this->getApiAuthMiddleware();
            File::put($apiAuthMiddlewarePath, $apiAuthMiddleware);
            $this->info('✅ ApiAuthMiddleware created');
        }
    }

    /**
     * Get API routes content
     */
    protected function getApiRoutes(): string
    {
        return <<<'PHP'

// KaelyAuth API Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::put('/user', [App\Http\Controllers\Api\UserController::class, 'update']);
});

PHP;
    }

    /**
     * Get AuthController content
     */
    protected function getAuthController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token,
        ]);
    }
}
PHP;
    }

    /**
     * Get UserController content
     */
    protected function getUserController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}
PHP;
    }

    /**
     * Get ApiAuthMiddleware content
     */
    protected function getApiAuthMiddleware(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}
PHP;
    }

    /**
     * Execute command
     */
    protected function executeCommand(string $command, bool $throwOnError = true): void
    {
        $this->info("Executing: {$command}");
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->error("Command failed: " . implode("\n", $output));
            if ($throwOnError) {
                throw new \Exception("Command failed: {$command}");
            }
        } else {
            $this->info("Command executed successfully");
        }
    }
} 