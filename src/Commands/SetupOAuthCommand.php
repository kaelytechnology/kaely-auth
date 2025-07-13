<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Kaely\Auth\Services\OAuthService;

class SetupOAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kaely:setup-oauth 
                            {--provider= : OAuth provider (google, facebook, github, linkedin, twitter)}
                            {--client-id= : OAuth client ID}
                            {--client-secret= : OAuth client secret}
                            {--redirect-uri= : OAuth redirect URI}
                            {--enabled=true : Enable OAuth}';

    /**
     * The console command description.
     */
    protected $description = 'Setup OAuth providers for KaelyAuth';

    /**
     * Execute the console command.
     */
    public function handle(OAuthService $oauthService): int
    {
        $this->info('🔐 Setting up KaelyAuth OAuth...');

        $provider = $this->option('provider');
        $clientId = $this->option('client-id');
        $clientSecret = $this->option('client-secret');
        $redirectUri = $this->option('redirect-uri');
        $enabled = $this->option('enabled') === 'true';

        try {
            if ($provider) {
                // Configure specific provider
                $this->configureProvider($provider, $clientId, $clientSecret, $redirectUri, $enabled);
            } else {
                // Interactive configuration
                $this->interactiveConfiguration();
            }

            $this->info('✅ OAuth setup completed successfully!');
            $this->displayNextSteps();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ OAuth setup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Configure specific provider
     */
    protected function configureProvider(string $provider, ?string $clientId, ?string $clientSecret, ?string $redirectUri, bool $enabled): void
    {
        $this->info("🔧 Configuring {$provider} OAuth provider...");

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found');
            return;
        }

        $envContent = File::get($envPath);

        $variables = [
            "KAELY_AUTH_OAUTH_ENABLED" => $enabled ? 'true' : 'false',
            "KAELY_AUTH_{$provider}_ENABLED" => $enabled ? 'true' : 'false',
        ];

        if ($clientId) {
            $variables["KAELY_AUTH_{$provider}_CLIENT_ID"] = $clientId;
        }

        if ($clientSecret) {
            $variables["KAELY_AUTH_{$provider}_CLIENT_SECRET"] = $clientSecret;
        }

        if ($redirectUri) {
            $variables["KAELY_AUTH_{$provider}_REDIRECT_URI"] = $redirectUri;
        }

        foreach ($variables as $key => $value) {
            if (strpos($envContent, $key . '=') !== false) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
        $this->info("✅ {$provider} OAuth provider configured");
    }

    /**
     * Interactive configuration
     */
    protected function interactiveConfiguration(): void
    {
        $this->info('🔧 Interactive OAuth Configuration');

        $enableOAuth = $this->confirm('Would you like to enable OAuth?', false);

        if ($enableOAuth) {
            $this->updateEnvFile(['KAELY_AUTH_OAUTH_ENABLED' => 'true']);

            $providers = $this->choice(
                'Select OAuth providers to configure:',
                [
                    'google' => 'Google OAuth',
                    'facebook' => 'Facebook OAuth',
                    'github' => 'GitHub OAuth',
                    'linkedin' => 'LinkedIn OAuth',
                    'twitter' => 'Twitter OAuth',
                    'all' => 'All providers',
                ],
                'google'
            );

            if (in_array($providers, ['google', 'all'])) {
                $this->configureGoogleOAuth();
            }

            if (in_array($providers, ['facebook', 'all'])) {
                $this->configureFacebookOAuth();
            }

            if (in_array($providers, ['github', 'all'])) {
                $this->configureGitHubOAuth();
            }

            if (in_array($providers, ['linkedin', 'all'])) {
                $this->configureLinkedInOAuth();
            }

            if (in_array($providers, ['twitter', 'all'])) {
                $this->configureTwitterOAuth();
            }
        } else {
            $this->updateEnvFile(['KAELY_AUTH_OAUTH_ENABLED' => 'false']);
        }
    }

    /**
     * Configure Google OAuth
     */
    protected function configureGoogleOAuth(): void
    {
        $this->info("\n🔑 Google OAuth Configuration:");
        
        $clientId = $this->ask('Google Client ID:');
        $clientSecret = $this->secret('Google Client Secret:');
        $redirectUri = $this->ask('Redirect URI:', url('/api/v1/oauth/google/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_GOOGLE_ENABLED' => 'true',
            'KAELY_AUTH_GOOGLE_CLIENT_ID' => $clientId,
            'KAELY_AUTH_GOOGLE_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_GOOGLE_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure Facebook OAuth
     */
    protected function configureFacebookOAuth(): void
    {
        $this->info("\n🔑 Facebook OAuth Configuration:");
        
        $clientId = $this->ask('Facebook Client ID:');
        $clientSecret = $this->secret('Facebook Client Secret:');
        $redirectUri = $this->ask('Redirect URI:', url('/api/v1/oauth/facebook/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_FACEBOOK_ENABLED' => 'true',
            'KAELY_AUTH_FACEBOOK_CLIENT_ID' => $clientId,
            'KAELY_AUTH_FACEBOOK_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_FACEBOOK_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure GitHub OAuth
     */
    protected function configureGitHubOAuth(): void
    {
        $this->info("\n🔑 GitHub OAuth Configuration:");
        
        $clientId = $this->ask('GitHub Client ID:');
        $clientSecret = $this->secret('GitHub Client Secret:');
        $redirectUri = $this->ask('Redirect URI:', url('/api/v1/oauth/github/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_GITHUB_ENABLED' => 'true',
            'KAELY_AUTH_GITHUB_CLIENT_ID' => $clientId,
            'KAELY_AUTH_GITHUB_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_GITHUB_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure LinkedIn OAuth
     */
    protected function configureLinkedInOAuth(): void
    {
        $this->info("\n🔑 LinkedIn OAuth Configuration:");
        
        $clientId = $this->ask('LinkedIn Client ID:');
        $clientSecret = $this->secret('LinkedIn Client Secret:');
        $redirectUri = $this->ask('Redirect URI:', url('/api/v1/oauth/linkedin/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_LINKEDIN_ENABLED' => 'true',
            'KAELY_AUTH_LINKEDIN_CLIENT_ID' => $clientId,
            'KAELY_AUTH_LINKEDIN_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_LINKEDIN_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Configure Twitter OAuth
     */
    protected function configureTwitterOAuth(): void
    {
        $this->info("\n🔑 Twitter OAuth Configuration:");
        
        $clientId = $this->ask('Twitter Client ID:');
        $clientSecret = $this->secret('Twitter Client Secret:');
        $redirectUri = $this->ask('Redirect URI:', url('/api/v1/oauth/twitter/callback'));

        $this->updateEnvFile([
            'KAELY_AUTH_TWITTER_ENABLED' => 'true',
            'KAELY_AUTH_TWITTER_CLIENT_ID' => $clientId,
            'KAELY_AUTH_TWITTER_CLIENT_SECRET' => $clientSecret,
            'KAELY_AUTH_TWITTER_REDIRECT_URI' => $redirectUri,
        ]);
    }

    /**
     * Update .env file
     */
    protected function updateEnvFile(array $variables): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found');
            return;
        }

        $envContent = File::get($envPath);

        foreach ($variables as $key => $value) {
            if (strpos($envContent, $key . '=') !== false) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(): void
    {
        $this->info("\n📚 Next Steps:");
        $this->info("1. Install Laravel Socialite: composer require laravel/socialite");
        $this->info("2. Configure your OAuth providers in your OAuth dashboard");
        $this->info("3. Test OAuth login flow");
        $this->info("4. Configure OAuth user mapping if needed");
        $this->info("5. Set up OAuth error handling");
    }
} 