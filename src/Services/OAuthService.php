<?php

namespace Kaely\Auth\Services;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class OAuthService
{
    protected array $config;
    protected MultiDatabaseService $dbService;

    public function __construct(MultiDatabaseService $dbService)
    {
        $this->config = config('kaely-auth.oauth', []);
        $this->dbService = $dbService;
    }

    /**
     * Check if OAuth is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Get enabled providers
     */
    public function getEnabledProviders(): array
    {
        $enabled = [];
        
        foreach ($this->config['providers'] as $provider => $config) {
            if ($config['enabled'] ?? false) {
                $enabled[$provider] = $config;
            }
        }
        
        return $enabled;
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider(string $provider): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->isProviderEnabled($provider)) {
            throw new \Exception("OAuth provider '{$provider}' is not enabled");
        }

        return Socialite::driver($provider)
            ->scopes($this->config['providers'][$provider]['scopes'] ?? [])
            ->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(string $provider): array
    {
        if (!$this->isProviderEnabled($provider)) {
            throw new \Exception("OAuth provider '{$provider}' is not enabled");
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            return $this->processSocialUser($socialUser, $provider);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider,
            ];
        }
    }

    /**
     * Process social user data
     */
    protected function processSocialUser($socialUser, string $provider): array
    {
        $userData = $this->mapSocialUserData($socialUser, $provider);
        
        // Find existing user
        $existingUser = $this->findExistingUser($userData['email']);
        
        if ($existingUser) {
            // Update existing user with OAuth data
            $this->updateUserWithOAuthData($existingUser, $userData, $provider);
            
            return [
                'success' => true,
                'action' => 'login',
                'user' => $existingUser,
                'provider' => $provider,
            ];
        } else {
            // Create new user
            if ($this->config['auto_create_users'] ?? true) {
                $newUser = $this->createUserFromOAuth($userData, $provider);
                
                return [
                    'success' => true,
                    'action' => 'register',
                    'user' => $newUser,
                    'provider' => $provider,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'User not found and auto-creation is disabled',
                    'provider' => $provider,
                ];
            }
        }
    }

    /**
     * Map social user data to our format
     */
    protected function mapSocialUserData($socialUser, string $provider): array
    {
        $mapping = $this->config['user_mapping'] ?? [];
        
        $userData = [
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider_id' => $socialUser->getId(),
            'provider_name' => $provider,
        ];

        // Map avatar if enabled
        if ($this->config['sync_avatar'] ?? true) {
            $userData['avatar'] = $socialUser->getAvatar();
        }

        // Map additional fields if they exist
        if (isset($mapping['name'])) {
            $userData['name'] = $socialUser->getRaw()[mapping['name']] ?? $userData['name'];
        }

        return $userData;
    }

    /**
     * Find existing user by email
     */
    protected function findExistingUser(string $email): ?User
    {
        if ($this->dbService->isMultipleMode()) {
            // Search across multiple databases
            $userData = $this->dbService->getUserFromMultiple($email);
            if ($userData) {
                $userModel = config('kaely-auth.models.user');
                return new $userModel((array) $userData['user']);
            }
        } else {
            // Search in single database
            return User::where('email', $email)->first();
        }
        
        return null;
    }

    /**
     * Update existing user with OAuth data
     */
    protected function updateUserWithOAuthData(User $user, array $userData, string $provider): void
    {
        $updateData = [
            'provider_id' => $userData['provider_id'],
            'provider_name' => $provider,
        ];

        if ($this->config['sync_avatar'] ?? true) {
            $updateData['avatar'] = $userData['avatar'];
        }

        $user->update($updateData);
    }

    /**
     * Create new user from OAuth data
     */
    protected function createUserFromOAuth(array $userData, string $provider): User
    {
        $userModel = config('kaely-auth.models.user');
        
        $userData['password'] = Hash::make(Str::random(32));
        $userData['email_verified_at'] = now(); // OAuth users are pre-verified
        
        $user = new $userModel($userData);
        $user->save();

        // Auto-assign default role if enabled
        if ($this->config['auto_assign_roles'] ?? true) {
            $defaultRole = $this->config['default_role'] ?? 'user';
            $user->assignRole($defaultRole);
        }

        return $user;
    }

    /**
     * Check if provider is enabled
     */
    public function isProviderEnabled(string $provider): bool
    {
        return $this->config['providers'][$provider]['enabled'] ?? false;
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(string $provider): array
    {
        return $this->config['providers'][$provider] ?? [];
    }

    /**
     * Get OAuth login URL
     */
    public function getLoginUrl(string $provider): string
    {
        return route('kaely.oauth.redirect', ['provider' => $provider]);
    }

    /**
     * Get OAuth callback URL
     */
    public function getCallbackUrl(string $provider): string
    {
        return route('kaely.oauth.callback', ['provider' => $provider]);
    }

    /**
     * Validate OAuth configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        $warnings = [];

        if (!$this->isEnabled()) {
            $warnings[] = 'OAuth is disabled in configuration';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        foreach ($this->config['providers'] as $provider => $config) {
            if ($config['enabled'] ?? false) {
                // Check required fields
                $requiredFields = ['client_id', 'client_secret', 'redirect'];
                
                foreach ($requiredFields as $field) {
                    if (empty($config[$field])) {
                        $errors[] = "Missing {$field} for {$provider} provider";
                    }
                }

                // Check if Socialite driver exists
                try {
                    Socialite::driver($provider);
                } catch (\Exception $e) {
                    $errors[] = "Socialite driver for {$provider} is not configured";
                }
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Get OAuth statistics
     */
    public function getOAuthStats(): array
    {
        $stats = [
            'enabled' => $this->isEnabled(),
            'providers' => [],
            'users' => [],
        ];

        if ($this->isEnabled()) {
            foreach ($this->config['providers'] as $provider => $config) {
                $stats['providers'][$provider] = [
                    'enabled' => $config['enabled'] ?? false,
                    'scopes' => $config['scopes'] ?? [],
                ];
            }

            // Get user statistics by provider
            if ($this->dbService->isMultipleMode()) {
                foreach ($this->dbService->getActiveConnections() as $connection) {
                    $db = $this->dbService->getConnection($connection);
                    $table = $this->dbService->getTableName('users', $connection);
                    
                    $providerStats = $db->table($table)
                        ->whereNotNull('provider_name')
                        ->selectRaw('provider_name, COUNT(*) as count')
                        ->groupBy('provider_name')
                        ->get();
                    
                    $stats['users'][$connection] = $providerStats;
                }
            } else {
                $userModel = config('kaely-auth.models.user');
                $providerStats = $userModel::whereNotNull('provider_name')
                    ->selectRaw('provider_name, COUNT(*) as count')
                    ->groupBy('provider_name')
                    ->get();
                
                $stats['users']['default'] = $providerStats;
            }
        }

        return $stats;
    }

    /**
     * Sync user across databases (for multi-database mode)
     */
    public function syncOAuthUser(User $user): array
    {
        if (!$this->dbService->isMultipleMode()) {
            return ['message' => 'Single database mode - no sync needed'];
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'provider_id' => $user->provider_id,
            'provider_name' => $user->provider_name,
            'avatar' => $user->avatar,
        ];

        return $this->dbService->syncUserAcrossDatabases($userData);
    }
} 