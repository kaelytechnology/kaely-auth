<?php

return [
    // Security Configuration
    'security' => [
        'enabled' => env('KAELY_AUTH_SECURITY_ENABLED', true),
        'level' => env('KAELY_AUTH_SECURITY_LEVEL', 'standard'), // standard, medium, high
        'allowed_origins' => env('KAELY_AUTH_ALLOWED_ORIGINS', []),
        'blacklisted_ips' => env('KAELY_AUTH_BLACKLISTED_IPS', []),
        'whitelisted_ips' => env('KAELY_AUTH_WHITELISTED_IPS', []),
        'content_security_policy' => env('KAELY_AUTH_CSP', ''),
        'password_policy' => [
            'min_length' => env('KAELY_AUTH_PASSWORD_MIN_LENGTH', 8),
            'require_uppercase' => env('KAELY_AUTH_PASSWORD_UPPERCASE', true),
            'require_lowercase' => env('KAELY_AUTH_PASSWORD_LOWERCASE', true),
            'require_numbers' => env('KAELY_AUTH_PASSWORD_NUMBERS', true),
            'require_special' => env('KAELY_AUTH_PASSWORD_SPECIAL', true),
            'prevent_common' => env('KAELY_AUTH_PASSWORD_PREVENT_COMMON', true),
        ],
        'session_security' => [
            'regenerate_on_login' => env('KAELY_AUTH_SESSION_REGENERATE', true),
            'regenerate_on_password_change' => env('KAELY_AUTH_SESSION_REGENERATE_PASSWORD', true),
            'invalidate_on_logout' => env('KAELY_AUTH_SESSION_INVALIDATE_LOGOUT', true),
        ],
        'rate_limiting' => [
            'enabled' => env('KAELY_AUTH_RATE_LIMITING_ENABLED', true),
            'login_attempts' => env('KAELY_AUTH_LOGIN_ATTEMPTS', 5),
            'login_decay_minutes' => env('KAELY_AUTH_LOGIN_DECAY', 15),
            'api_requests' => env('KAELY_AUTH_API_REQUESTS', 60),
            'api_decay_minutes' => env('KAELY_AUTH_API_DECAY', 1),
        ],
        'two_factor' => [
            'enabled' => env('KAELY_AUTH_2FA_ENABLED', false),
            'methods' => ['totp', 'sms', 'email'],
            'backup_codes' => env('KAELY_AUTH_2FA_BACKUP_CODES', true),
        ],
    ],
]; 