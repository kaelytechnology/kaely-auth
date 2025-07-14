<?php

namespace Kaely\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Kaely\Auth\KaelyAuthManager;
use Kaely\Auth\Services\OAuthService;
use Kaely\Auth\Models\User;
use Kaely\Auth\Models\Person;

class AuthController extends Controller
{
    protected $authManager;
    protected $oauthService;

    public function __construct(KaelyAuthManager $authManager, OAuthService $oauthService)
    {
        $this->authManager = $authManager;
        $this->oauthService = $oauthService;
    }

    /**
     * Login user.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'permissions' => $this->authManager->getUserPermissions($user),
            'roles' => $this->authManager->getUserRoles($user),
            'menu' => $this->authManager->getUserMenu($user)
        ]);
    }

    /**
     * Register new user.
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

        // Create associated person record
        Person::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
            'permissions' => $this->authManager->getUserPermissions($user),
            'roles' => $this->authManager->getUserRoles($user),
            'menu' => $this->authManager->getUserMenu($user)
        ], 201);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        $user = $this->authManager->getUser();

        return response()->json([
            'user' => $user,
            'permissions' => $this->authManager->getUserPermissions($user),
            'roles' => $this->authManager->getUserRoles($user),
            'menu' => $this->authManager->getUserMenu($user),
            'branches' => $this->authManager->getUserBranches($user),
            'departments' => $this->authManager->getUserDepartments($user)
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update($request->only(['name', 'email']));

        // Update associated person record
        if ($user->person) {
            $user->person->update($request->only(['name', 'email']));
        }

        return response()->json([
            'user' => $user->fresh(),
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Redirect to OAuth provider.
     */
    public function redirectToProvider(Request $request, string $provider)
    {
        if (!$this->oauthService->isProviderEnabled($provider)) {
            return response()->json([
                'error' => 'OAuth provider not enabled',
                'message' => "The {$provider} OAuth provider is not configured."
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->redirectUrl(
                config("kaely-auth.oauth.providers.{$provider}.redirect")
            );

            return response()->json([
                'redirect_url' => $redirectUrl,
                'provider' => $provider
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'OAuth configuration error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle OAuth callback.
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        if (!$this->oauthService->isProviderEnabled($provider)) {
            return response()->json([
                'error' => 'OAuth provider not enabled',
                'message' => "The {$provider} OAuth provider is not configured."
            ], 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            $result = $this->oauthService->handleCallback($provider, $socialUser);
            
            if ($result['success']) {
                $user = $result['user'];
                $token = $user->createToken('oauth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'token' => $token,
                    'provider' => $provider,
                    'message' => 'Successfully authenticated via OAuth'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'OAuth callback error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics.
     */
    public function getStats()
    {
        return response()->json($this->authManager->getAuthStats());
    }

    /**
     * Get database status.
     */
    public function getDatabaseStatus()
    {
        $singleDatabaseService = app(\Kaely\Auth\Services\SingleDatabaseService::class);
        return response()->json($singleDatabaseService->getDatabaseStatus());
    }

    /**
     * Get table statistics.
     */
    public function getTableStats()
    {
        $singleDatabaseService = app(\Kaely\Auth\Services\SingleDatabaseService::class);
        return response()->json($singleDatabaseService->getTableStats());
    }

    /**
     * Optimize database tables.
     */
    public function optimizeTables()
    {
        $singleDatabaseService = app(\Kaely\Auth\Services\SingleDatabaseService::class);
        return response()->json($singleDatabaseService->optimizeTables());
    }

    /**
     * Create database indexes.
     */
    public function createIndexes()
    {
        $singleDatabaseService = app(\Kaely\Auth\Services\SingleDatabaseService::class);
        return response()->json($singleDatabaseService->createIndexes());
    }

    /**
     * Validate database relations.
     */
    public function validateRelations()
    {
        $result = $this->authManager->validateDatabaseRelations();
        return response()->json($result);
    }
} 