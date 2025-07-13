<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KaelyAuth Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the KaelyAuth package.
    | You can customize these settings according to your needs.
    |
    */

    // Database Configuration
    'database' => [
        'mode' => env('KAELY_AUTH_DB_MODE', 'single'), // single, multiple
        'prefix' => env('KAELY_AUTH_DB_PREFIX', ''), // No prefix by default
        'connections' => [
            'default' => env('KAELY_AUTH_DEFAULT_CONNECTION', 'mysql'),
            'auth' => env('KAELY_AUTH_AUTH_CONNECTION', 'mysql'),
        ],
    ],

    // OAuth Configuration
    'oauth' => [
        'enabled' => env('KAELY_AUTH_OAUTH_ENABLED', false),
        'providers' => [
            'google' => [
                'enabled' => env('KAELY_AUTH_GOOGLE_ENABLED', false),
                'client_id' => env('KAELY_AUTH_GOOGLE_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_GOOGLE_REDIRECT_URI'),
            ],
            'facebook' => [
                'enabled' => env('KAELY_AUTH_FACEBOOK_ENABLED', false),
                'client_id' => env('KAELY_AUTH_FACEBOOK_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_FACEBOOK_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_FACEBOOK_REDIRECT_URI'),
            ],
            'github' => [
                'enabled' => env('KAELY_AUTH_GITHUB_ENABLED', false),
                'client_id' => env('KAELY_AUTH_GITHUB_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_GITHUB_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_GITHUB_REDIRECT_URI'),
            ],
            'linkedin' => [
                'enabled' => env('KAELY_AUTH_LINKEDIN_ENABLED', false),
                'client_id' => env('KAELY_AUTH_LINKEDIN_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_LINKEDIN_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_LINKEDIN_REDIRECT_URI'),
            ],
            'microsoft' => [
                'enabled' => env('KAELY_AUTH_MICROSOFT_ENABLED', false),
                'client_id' => env('KAELY_AUTH_MICROSOFT_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_MICROSOFT_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_MICROSOFT_REDIRECT_URI'),
            ],
            'twitter' => [
                'enabled' => env('KAELY_AUTH_TWITTER_ENABLED', false),
                'client_id' => env('KAELY_AUTH_TWITTER_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_TWITTER_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_TWITTER_REDIRECT_URI'),
            ],
            'apple' => [
                'enabled' => env('KAELY_AUTH_APPLE_ENABLED', false),
                'client_id' => env('KAELY_AUTH_APPLE_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_APPLE_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_APPLE_REDIRECT_URI'),
            ],
            'discord' => [
                'enabled' => env('KAELY_AUTH_DISCORD_ENABLED', false),
                'client_id' => env('KAELY_AUTH_DISCORD_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_DISCORD_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_DISCORD_REDIRECT_URI'),
            ],
            'slack' => [
                'enabled' => env('KAELY_AUTH_SLACK_ENABLED', false),
                'client_id' => env('KAELY_AUTH_SLACK_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_SLACK_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_SLACK_REDIRECT_URI'),
            ],
            'bitbucket' => [
                'enabled' => env('KAELY_AUTH_BITBUCKET_ENABLED', false),
                'client_id' => env('KAELY_AUTH_BITBUCKET_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_BITBUCKET_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_BITBUCKET_REDIRECT_URI'),
            ],
            'gitlab' => [
                'enabled' => env('KAELY_AUTH_GITLAB_ENABLED', false),
                'client_id' => env('KAELY_AUTH_GITLAB_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_GITLAB_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_GITLAB_REDIRECT_URI'),
            ],
            'dropbox' => [
                'enabled' => env('KAELY_AUTH_DROPBOX_ENABLED', false),
                'client_id' => env('KAELY_AUTH_DROPBOX_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_DROPBOX_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_DROPBOX_REDIRECT_URI'),
            ],
            'box' => [
                'enabled' => env('KAELY_AUTH_BOX_ENABLED', false),
                'client_id' => env('KAELY_AUTH_BOX_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_BOX_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_BOX_REDIRECT_URI'),
            ],
            'salesforce' => [
                'enabled' => env('KAELY_AUTH_SALESFORCE_ENABLED', false),
                'client_id' => env('KAELY_AUTH_SALESFORCE_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_SALESFORCE_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_SALESFORCE_REDIRECT_URI'),
            ],
            'hubspot' => [
                'enabled' => env('KAELY_AUTH_HUBSPOT_ENABLED', false),
                'client_id' => env('KAELY_AUTH_HUBSPOT_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_HUBSPOT_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_HUBSPOT_REDIRECT_URI'),
            ],
            'zoom' => [
                'enabled' => env('KAELY_AUTH_ZOOM_ENABLED', false),
                'client_id' => env('KAELY_AUTH_ZOOM_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_ZOOM_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_ZOOM_REDIRECT_URI'),
            ],
            'stripe' => [
                'enabled' => env('KAELY_AUTH_STRIPE_ENABLED', false),
                'client_id' => env('KAELY_AUTH_STRIPE_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_STRIPE_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_STRIPE_REDIRECT_URI'),
            ],
            'paypal' => [
                'enabled' => env('KAELY_AUTH_PAYPAL_ENABLED', false),
                'client_id' => env('KAELY_AUTH_PAYPAL_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_PAYPAL_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_PAYPAL_REDIRECT_URI'),
            ],
            'twitch' => [
                'enabled' => env('KAELY_AUTH_TWITCH_ENABLED', false),
                'client_id' => env('KAELY_AUTH_TWITCH_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_TWITCH_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_TWITCH_REDIRECT_URI'),
            ],
            'reddit' => [
                'enabled' => env('KAELY_AUTH_REDDIT_ENABLED', false),
                'client_id' => env('KAELY_AUTH_REDDIT_CLIENT_ID'),
                'client_secret' => env('KAELY_AUTH_REDDIT_CLIENT_SECRET'),
                'redirect_uri' => env('KAELY_AUTH_REDDIT_REDIRECT_URI'),
            ],
        ],
    ],

    // Multitenancy Configuration
    'multitenancy' => [
        'enabled' => env('KAELY_AUTH_MULTITENANCY_ENABLED', false), // Disabled by default
        'mode' => env('KAELY_AUTH_TENANT_MODE', 'subdomain'), // subdomain, domain
        'resolver' => env('KAELY_AUTH_TENANT_RESOLVER', 'subdomain'),
        'default_tenant' => env('KAELY_AUTH_DEFAULT_TENANT', 'main'),
    ],

    // Password Reset Configuration
    'password_reset' => [
        'enabled' => env('KAELY_AUTH_PASSWORD_RESET_ENABLED', true),
        'expiration_hours' => env('KAELY_AUTH_PASSWORD_RESET_EXPIRATION', 24),
        'frontend_url' => env('KAELY_AUTH_PASSWORD_RESET_FRONTEND_URL'),
        'email_template' => env('KAELY_AUTH_PASSWORD_RESET_EMAIL_TEMPLATE', 'kaely-auth::emails.password-reset'),
    ],

    // Email Verification Configuration
    'email_verification' => [
        'enabled' => env('KAELY_AUTH_EMAIL_VERIFICATION_ENABLED', true),
        'expiration_hours' => env('KAELY_AUTH_EMAIL_VERIFICATION_EXPIRATION', 24),
        'frontend_url' => env('KAELY_AUTH_EMAIL_VERIFICATION_FRONTEND_URL'),
        'email_template' => env('KAELY_AUTH_EMAIL_VERIFICATION_EMAIL_TEMPLATE', 'kaely-auth::emails.email-verification'),
        'required' => env('KAELY_AUTH_EMAIL_VERIFICATION_REQUIRED', false),
    ],

    // Session Management Configuration
    'sessions' => [
        'enabled' => env('KAELY_AUTH_SESSION_MANAGEMENT_ENABLED', true),
        'lifetime_hours' => env('KAELY_AUTH_SESSION_LIFETIME', 24 * 30), // 30 days
        'max_active_sessions' => env('KAELY_AUTH_MAX_ACTIVE_SESSIONS', 5),
        'track_activity' => env('KAELY_AUTH_TRACK_SESSION_ACTIVITY', true),
        'auto_cleanup' => env('KAELY_AUTH_AUTO_CLEANUP_SESSIONS', true),
    ],

    // Audit Logging Configuration
    'audit' => [
        'enabled' => env('KAELY_AUTH_AUDIT_ENABLED', true),
        'retention_days' => env('KAELY_AUTH_AUDIT_RETENTION_DAYS', 90),
        'log_failed_attempts' => env('KAELY_AUTH_LOG_FAILED_ATTEMPTS', true),
        'log_successful_actions' => env('KAELY_AUTH_LOG_SUCCESSFUL_ACTIONS', true),
        'log_suspicious_activity' => env('KAELY_AUTH_LOG_SUSPICIOUS_ACTIVITY', true),
        'security_alerts' => [
            'enabled' => env('KAELY_AUTH_SECURITY_ALERTS_ENABLED', true),
            'failed_login_threshold' => env('KAELY_AUTH_FAILED_LOGIN_THRESHOLD', 5),
            'suspicious_ip_threshold' => env('KAELY_AUTH_SUSPICIOUS_IP_THRESHOLD', 3),
        ],
    ],

    // Security Configuration
    'security' => [
        'password_policy' => [
            'min_length' => env('KAELY_AUTH_PASSWORD_MIN_LENGTH', 8),
            'require_uppercase' => env('KAELY_AUTH_PASSWORD_REQUIRE_UPPERCASE', true),
            'require_lowercase' => env('KAELY_AUTH_PASSWORD_REQUIRE_LOWERCASE', true),
            'require_numbers' => env('KAELY_AUTH_PASSWORD_REQUIRE_NUMBERS', true),
            'require_symbols' => env('KAELY_AUTH_PASSWORD_REQUIRE_SYMBOLS', false),
        ],
        'account_lockout' => [
            'enabled' => env('KAELY_AUTH_ACCOUNT_LOCKOUT_ENABLED', true),
            'max_attempts' => env('KAELY_AUTH_MAX_LOGIN_ATTEMPTS', 5),
            'lockout_duration' => env('KAELY_AUTH_LOCKOUT_DURATION', 15), // minutes
        ],
        'two_factor' => [
            'enabled' => env('KAELY_AUTH_2FA_ENABLED', false),
            'required' => env('KAELY_AUTH_2FA_REQUIRED', false),
        ],
    ],

    // API Configuration
    'api' => [
        'prefix' => env('KAELY_AUTH_API_PREFIX', 'api/v1'),
        'rate_limiting' => [
            'enabled' => env('KAELY_AUTH_RATE_LIMITING_ENABLED', true),
            'max_attempts' => env('KAELY_AUTH_RATE_LIMIT_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('KAELY_AUTH_RATE_LIMIT_DECAY_MINUTES', 1),
        ],
    ],

    // Middleware Configuration
    'middleware' => [
        'auth' => env('KAELY_AUTH_MIDDLEWARE_AUTH', 'auth:sanctum'),
        'verified' => env('KAELY_AUTH_MIDDLEWARE_VERIFIED', 'verified'),
        'tenant' => env('KAELY_AUTH_MIDDLEWARE_TENANT', 'kaely.tenant'),
        'permission' => env('KAELY_AUTH_MIDDLEWARE_PERMISSION', 'kaely.permission'),
    ],

    // Notification Configuration
    'notifications' => [
        'channels' => [
            'mail' => env('KAELY_AUTH_NOTIFY_MAIL', true),
            'database' => env('KAELY_AUTH_NOTIFY_DATABASE', false),
            'slack' => env('KAELY_AUTH_NOTIFY_SLACK', false),
        ],
        'events' => [
            'login' => env('KAELY_AUTH_NOTIFY_LOGIN', false),
            'logout' => env('KAELY_AUTH_NOTIFY_LOGOUT', false),
            'password_reset' => env('KAELY_AUTH_NOTIFY_PASSWORD_RESET', true),
            'email_verification' => env('KAELY_AUTH_NOTIFY_EMAIL_VERIFICATION', true),
            'suspicious_activity' => env('KAELY_AUTH_NOTIFY_SUSPICIOUS_ACTIVITY', true),
        ],
    ],

    // Cache Configuration
    'cache' => [
        'enabled' => env('KAELY_AUTH_CACHE_ENABLED', true),
        'prefix' => env('KAELY_AUTH_CACHE_PREFIX', 'kaely_auth'),
        'ttl' => [
            'user_permissions' => env('KAELY_AUTH_CACHE_USER_PERMISSIONS_TTL', 3600), // 1 hour
            'tenant_data' => env('KAELY_AUTH_CACHE_TENANT_DATA_TTL', 1800), // 30 minutes
            'session_data' => env('KAELY_AUTH_CACHE_SESSION_DATA_TTL', 300), // 5 minutes
        ],
    ],

    // Performance Configuration
    'performance' => [
        'enabled' => env('KAELY_AUTH_PERFORMANCE_ENABLED', true),
        'caching' => [
            'enabled' => env('KAELY_AUTH_CACHING_ENABLED', true),
            'user_permissions_ttl' => env('KAELY_AUTH_USER_PERMISSIONS_TTL', 3600),
            'user_roles_ttl' => env('KAELY_AUTH_USER_ROLES_TTL', 3600),
            'oauth_providers_ttl' => env('KAELY_AUTH_OAUTH_PROVIDERS_TTL', 1800),
            'audit_stats_ttl' => env('KAELY_AUTH_AUDIT_STATS_TTL', 3600),
        ],
        'query_optimization' => [
            'enabled' => env('KAELY_AUTH_QUERY_OPTIMIZATION_ENABLED', true),
            'eager_loading' => env('KAELY_AUTH_EAGER_LOADING', true),
            'query_caching' => env('KAELY_AUTH_QUERY_CACHING', true),
        ],
        'slow_request_threshold' => env('KAELY_AUTH_SLOW_REQUEST_THRESHOLD', 1000), // milliseconds
        'max_queries_threshold' => env('KAELY_AUTH_MAX_QUERIES_THRESHOLD', 10),
        'bulk_insert_chunk_size' => env('KAELY_AUTH_BULK_INSERT_CHUNK_SIZE', 1000),
    ],

    // Debug Configuration
    'debug' => [
        'enabled' => env('KAELY_AUTH_DEBUG_ENABLED', false),
        'query_logging' => env('KAELY_AUTH_QUERY_LOGGING', false),
        'performance_logging' => env('KAELY_AUTH_PERFORMANCE_LOGGING', false),
        'security_logging' => env('KAELY_AUTH_SECURITY_LOGGING', true),
    ],

    // UI Configuration
    'ui' => [
        'enabled' => env('KAELY_AUTH_UI_ENABLED', false),
        'type' => env('KAELY_AUTH_UI_TYPE', 'none'), // blade, livewire, none
        'theme' => env('KAELY_AUTH_UI_THEME', 'default'), // default, dark, custom
        'custom_css' => env('KAELY_AUTH_UI_CUSTOM_CSS'),
        'custom_js' => env('KAELY_AUTH_UI_CUSTOM_JS'),
    ],
]; 