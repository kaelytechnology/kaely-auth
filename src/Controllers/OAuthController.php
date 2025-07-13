<?php

namespace Kaely\Auth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(Request $request, string $provider)
    {
        $this->validateProvider($provider);
        
        // Log OAuth initiation
        $this->logOAuthActivity($provider, 'oauth.initiated', null, $request);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request, string $provider)
    {
        $this->validateProvider($provider);
        
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Log successful OAuth
            $this->logOAuthActivity($provider, 'oauth.successful', null, $request, [
                'oauth_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'picture' => $socialUser->getAvatar(),
            ]);
            
            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);
            
            // Log in user
            Auth::login($user);
            
            // Generate token
            $token = $user->createToken('oauth-token')->plainTextToken;
            
            return response()->json([
                'message' => 'OAuth login successful',
                'user' => $user,
                'token' => $token,
                'provider' => $provider
            ]);
            
        } catch (\Exception $e) {
            // Log failed OAuth
            $this->logOAuthActivity($provider, 'oauth.failed', null, $request, [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'OAuth login failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Link OAuth account to existing user
     */
    public function link(Request $request, string $provider)
    {
        $this->validateProvider($provider);
        
        $user = Auth::user();
        
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Check if OAuth account is already linked
            $existingLink = DB::table('oauth_accounts')
                ->where('provider', $provider)
                ->where('oauth_id', $socialUser->getId())
                ->first();
            
            if ($existingLink) {
                return response()->json([
                    'error' => 'OAuth account already linked to another user'
                ], 400);
            }
            
            // Link OAuth account
            DB::table('oauth_accounts')->insert([
                'user_id' => $user->id,
                'provider' => $provider,
                'oauth_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'picture' => $socialUser->getAvatar(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Log OAuth linking
            $this->logOAuthActivity($provider, 'oauth.linked', $user->id, $request, [
                'oauth_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'linked_to_existing' => true
            ]);
            
            return response()->json([
                'message' => 'OAuth account linked successfully',
                'provider' => $provider
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to link OAuth account',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Unlink OAuth account
     */
    public function unlink(Request $request, string $provider)
    {
        $this->validateProvider($provider);
        
        $user = Auth::user();
        
        $oauthAccount = DB::table('oauth_accounts')
            ->where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();
        
        if (!$oauthAccount) {
            return response()->json([
                'error' => 'OAuth account not linked'
            ], 404);
        }
        
        // Check if user has other login methods
        $otherAccounts = DB::table('oauth_accounts')
            ->where('user_id', $user->id)
            ->where('provider', '!=', $provider)
            ->count();
        
        if ($otherAccounts === 0 && !$user->password) {
            return response()->json([
                'error' => 'Cannot unlink last login method'
            ], 400);
        }
        
        // Log OAuth unlinking
        $this->logOAuthActivity($provider, 'oauth.unlinked', $user->id, $request, [
            'oauth_id' => $oauthAccount->oauth_id,
            'reason' => 'user_request',
            'linked_accounts_remaining' => $otherAccounts
        ]);
        
        // Unlink OAuth account
        DB::table('oauth_accounts')
            ->where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();
        
        return response()->json([
            'message' => 'OAuth account unlinked successfully',
            'provider' => $provider
        ]);
    }

    /**
     * Get user's linked OAuth accounts
     */
    public function accounts(Request $request)
    {
        $user = Auth::user();
        
        $accounts = DB::table('oauth_accounts')
            ->where('user_id', $user->id)
            ->get();
        
        return response()->json([
            'accounts' => $accounts
        ]);
    }

    /**
     * Validate OAuth provider
     */
    protected function validateProvider(string $provider): void
    {
        $supportedProviders = [
            'google',
            'facebook', 
            'github',
            'linkedin',
            'microsoft',
            'twitter',
            'apple',
            'discord',
            'slack',
            'bitbucket',
            'gitlab',
            'dropbox',
            'box',
            'salesforce',
            'hubspot',
            'zoom',
            'stripe',
            'paypal',
            'twitch',
            'reddit'
        ];
        
        if (!in_array($provider, $supportedProviders)) {
            throw new \InvalidArgumentException("Unsupported OAuth provider: {$provider}");
        }
        
        // Check if provider is enabled in config
        $enabled = config("kaely-auth.oauth.providers.{$provider}.enabled", false);
        if (!$enabled) {
            throw new \InvalidArgumentException("OAuth provider {$provider} is not enabled");
        }
    }

    /**
     * Find or create user from OAuth data
     */
    protected function findOrCreateUser($socialUser, string $provider): User
    {
        // First, try to find user by OAuth ID
        $oauthAccount = DB::table('oauth_accounts')
            ->where('provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();
        
        if ($oauthAccount) {
            $user = User::find($oauthAccount->user_id);
            if ($user) {
                return $user;
            }
        }
        
        // Try to find user by email
        if ($socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
            if ($user) {
                // Link OAuth account to existing user
                DB::table('oauth_accounts')->insert([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'oauth_id' => $socialUser->getId(),
                    'email' => $socialUser->getEmail(),
                    'name' => $socialUser->getName(),
                    'picture' => $socialUser->getAvatar(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                return $user;
            }
        }
        
        // Create new user
        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(), // OAuth emails are typically verified
            'password' => bcrypt(str_random(16)), // Random password for OAuth users
        ]);
        
        // Link OAuth account
        DB::table('oauth_accounts')->insert([
            'user_id' => $user->id,
            'provider' => $provider,
            'oauth_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
            'picture' => $socialUser->getAvatar(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $user;
    }

    /**
     * Log OAuth activity
     */
    protected function logOAuthActivity(string $provider, string $action, ?int $userId, Request $request, array $metadata = []): void
    {
        DB::table('oauth_logs')->insert([
            'user_id' => $userId,
            'provider' => $provider,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tenant_id' => $request->input('current_tenant.id'),
            'metadata' => json_encode($metadata),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 