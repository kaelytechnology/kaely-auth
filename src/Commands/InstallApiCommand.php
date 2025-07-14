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

        // Check if we should run in non-interactive mode
        $noInteraction = $this->option('force') || $this->option('no-interaction');

        // Install Sanctum if not already installed
        $this->installSanctum($noInteraction);

        // Publish Sanctum configuration
        $this->publishSanctumConfig($noInteraction);

        // Run migrations
        $this->runMigrations($noInteraction);

        // Create API routes file in project root
        $this->createApiRoutesFile();

        // Create API controllers (only if they don't exist)
        $this->createApiControllers();

        // Create API middleware (only if it doesn't exist)
        $this->createApiMiddleware();

        $this->info('✅ API authentication installed successfully!');
        $this->info('');
        $this->info('📋 Next steps:');
        $this->info('1. API routes file created at routes/api.php');
        $this->info('2. Add your custom API routes in routes/api.php');
        $this->info('3. Set up your frontend to use the API endpoints');
        $this->info('4. Test the API with: php artisan route:list --path=api');
        $this->info('');
        $this->info('🔐 Available KaelyAuth endpoints:');
        $this->info('   - POST /api/auth/register');
        $this->info('   - POST /api/auth/login');
        $this->info('   - POST /api/auth/logout (requires auth)');
        $this->info('   - POST /api/auth/refresh (requires auth)');
        $this->info('   - GET /api/user (requires auth)');
        $this->info('   - PUT /api/user (requires auth)');
        $this->info('');
        $this->info('📝 Example custom route:');
        $this->info('   Route::get("/api/v1/test", function() {');
        $this->info('       return response()->json(["message" => "Hello API!"]);');
        $this->info('   });');

        return Command::SUCCESS;
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
    protected function installSanctum(bool $noInteraction = false): void
    {
        if (!$this->isSanctumInstalled()) {
            $this->info('📦 Installing Laravel Sanctum...');
            $command = $noInteraction ? 'composer require laravel/sanctum --no-interaction' : 'composer require laravel/sanctum';
            $this->executeCommand($command);
        } else {
            $this->info('✅ Laravel Sanctum is already installed');
        }
    }

    /**
     * Publish Sanctum configuration
     */
    protected function publishSanctumConfig(bool $noInteraction = false): void
    {
        $this->info('📋 Publishing Sanctum configuration...');
        $command = 'php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"';
        if ($noInteraction) {
            $command .= ' --force';
        }
        $this->executeCommand($command);
    }

    /**
     * Run migrations
     */
    protected function runMigrations(bool $noInteraction = false): void
    {
        $this->info('🗄️ Running migrations...');
        
        try {
            // Try with --force first to avoid interactive prompts
            $command = $noInteraction ? 'php artisan migrate --force' : 'php artisan migrate';
            $this->executeCommand($command);
        } catch (\Exception $e) {
            // If that fails, try without --force
            $this->warn("Migration failed, trying with --force...");
            try {
                $this->executeCommand('php artisan migrate --force');
            } catch (\Exception $e2) {
                $this->warn("Some migrations may have failed, but installation will continue...");
                $this->warn("You can run migrations manually later with: php artisan migrate");
            }
        }
    }



    /**
     * Create API routes file in project root
     */
    protected function createApiRoutesFile(): void
    {
        $this->info('🛣️ Creating API routes file...');
        
        $routesPath = base_path('routes/api.php');
        
        if (!File::exists($routesPath)) {
            $apiRoutesContent = $this->getApiRoutesFileContent();
            File::put($routesPath, $apiRoutesContent);
            $this->info('✅ API routes file created at routes/api.php');
        } else {
            $this->info('✅ API routes file already exists');
        }
    }

    /**
     * Get API routes file content
     */
    protected function getApiRoutesFileContent(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tus rutas API personalizadas aquí
Route::prefix('v1')->group(function () {
    // Ejemplo de rutas para tu aplicación
    Route::get('/test', function () {
        return response()->json(['message' => 'API funcionando correctamente']);
    });
    
    // Aquí puedes agregar más rutas de tu aplicación
    // Route::apiResource('products', ProductController::class);
    // Route::apiResource('categories', CategoryController::class);
    // etc.
});

// KaelyAuth API Routes (automatically loaded by the package)
// Las rutas de autenticación están disponibles en:
// - POST /api/auth/register
// - POST /api/auth/login
// - POST /api/auth/logout (requires auth)
// - POST /api/auth/refresh (requires auth)
// - GET /api/user (requires auth)
// - PUT /api/user (requires auth)
PHP;
    }

    /**
     * Create API controllers
     */
    protected function createApiControllers(): void
    {
        $this->info('🎮 Checking API controllers...');
        
        $controllersDir = app_path('Http/Controllers/Api');
        if (!File::exists($controllersDir)) {
            File::makeDirectory($controllersDir, 0755, true);
        }

        // Create AuthController only if it doesn't exist
        $authControllerPath = $controllersDir . '/AuthController.php';
        if (!File::exists($authControllerPath)) {
            $authController = $this->getAuthController();
            File::put($authControllerPath, $authController);
            $this->info('✅ AuthController created');
        } else {
            $this->info('✅ AuthController already exists');
        }

        // Create UserController only if it doesn't exist
        $userControllerPath = $controllersDir . '/UserController.php';
        if (!File::exists($userControllerPath)) {
            $userController = $this->getUserController();
            File::put($userControllerPath, $userController);
            $this->info('✅ UserController created');
        } else {
            $this->info('✅ UserController already exists');
        }
    }

    /**
     * Create API middleware
     */
    protected function createApiMiddleware(): void
    {
        $this->info('🛡️ Checking API middleware...');
        
        $middlewareDir = app_path('Http/Middleware');
        if (!File::exists($middlewareDir)) {
            File::makeDirectory($middlewareDir, 0755, true);
        }

        // Create ApiAuthMiddleware only if it doesn't exist
        $apiAuthMiddlewarePath = $middlewareDir . '/ApiAuthMiddleware.php';
        if (!File::exists($apiAuthMiddlewarePath)) {
            $apiAuthMiddleware = $this->getApiAuthMiddleware();
            File::put($apiAuthMiddlewarePath, $apiAuthMiddleware);
            $this->info('✅ ApiAuthMiddleware created');
        } else {
            $this->info('✅ ApiAuthMiddleware already exists');
        }
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
        
        // For interactive commands, we need to handle them differently
        if (strpos($command, 'vendor:publish') !== false && strpos($command, '--force') === false) {
            // Use --force flag to avoid interactive prompts
            $command = str_replace('vendor:publish', 'vendor:publish --force', $command);
        }
        
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