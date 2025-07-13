<?php

namespace Kaely\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Kaely\Auth\Services\OAuthService;
use Kaely\Auth\Models\User;
use Kaely\Auth\Models\Person;

class OAuthController extends Controller
{
    protected $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider(Request $request, string $provider)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
        ]);

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
     * Handle OAuth callback
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
        ]);

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
     * Get available OAuth providers
     */
    public function getProviders()
    {
        $providers = $this->oauthService->getEnabledProviders();
        
        return response()->json([
            'providers' => $providers,
            'count' => count($providers)
        ]);
    }

    /**
     * Get OAuth statistics
     */
    public function getStats()
    {
        $stats = $this->oauthService->getOAuthStats();
        
        return response()->json($stats);
    }

    /**
     * Validate OAuth configuration
     */
    public function validateConfig()
    {
        $validation = $this->oauthService->validateConfiguration();
        
        return response()->json($validation);
    }

    /**
     * Sync OAuth user across databases
     */
    public function syncUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
        ]);

        $result = $this->oauthService->syncOAuthUser(
            User::find($request->user_id),
            $request->provider
        );

        return response()->json($result);
    }

    /**
     * Disconnect OAuth account
     */
    public function disconnect(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
        ]);

        $user = Auth::user();
        $result = $this->oauthService->disconnectProvider($user, $request->provider);

        return response()->json($result);
    }

    /**
     * Link OAuth account
     */
    public function linkAccount(Request $request, string $provider)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
        ]);

        $user = Auth::user();
        $result = $this->oauthService->linkProvider($user, $provider);

        return response()->json($result);
    }

    /**
     * Get user's connected OAuth accounts
     */
    public function getConnectedAccounts(Request $request)
    {
        $user = Auth::user();
        $accounts = $this->oauthService->getUserConnectedAccounts($user);

        return response()->json([
            'accounts' => $accounts,
            'count' => count($accounts)
        ]);
    }

    /**
     * Update OAuth user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github,linkedin,twitter',
            'sync_avatar' => 'boolean',
            'sync_profile' => 'boolean',
        ]);

        $user = Auth::user();
        $result = $this->oauthService->updateOAuthProfile(
            $user,
            $request->provider,
            $request->only(['sync_avatar', 'sync_profile'])
        );

        return response()->json($result);
    }
} 