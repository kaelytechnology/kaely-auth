<?php

namespace Kaely\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecurityValidationService
{
    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $score = 0;

        // Length check
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $score += 1;
        }

        // Uppercase check
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $score += 1;
        }

        // Lowercase check
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $score += 1;
        }

        // Number check
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $score += 1;
        }

        // Special character check
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        } else {
            $score += 1;
        }

        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common';
        } else {
            $score += 1;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => $score,
            'strength' => $this->getPasswordStrength($score)
        ];
    }

    /**
     * Check if password is common
     */
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Get password strength level
     */
    protected function getPasswordStrength(int $score): string
    {
        if ($score >= 5) return 'strong';
        if ($score >= 3) return 'medium';
        return 'weak';
    }

    /**
     * Validate session security
     */
    public function validateSessionSecurity(string $sessionId): array
    {
        $issues = [];

        // Check session age
        $sessionAge = Cache::get("session_age_{$sessionId}");
        if ($sessionAge && $sessionAge > config('kaely-auth.sessions.lifetime_hours', 720)) {
            $issues[] = 'Session expired';
        }

        // Check for concurrent sessions
        $activeSessions = \DB::table('user_sessions')
            ->where('session_id', $sessionId)
            ->where('active', true)
            ->count();

        if ($activeSessions > config('kaely-auth.sessions.max_active_sessions', 5)) {
            $issues[] = 'Too many active sessions';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Generate secure token
     */
    public function generateSecureToken(): string
    {
        return Str::random(64);
    }

    /**
     * Validate IP address
     */
    public function validateIPAddress(string $ip): bool
    {
        $blacklistedIPs = config('kaely-auth.security.blacklisted_ips', []);
        $whitelistedIPs = config('kaely-auth.security.whitelisted_ips', []);

        // Check blacklist
        if (in_array($ip, $blacklistedIPs)) {
            return false;
        }

        // Check whitelist (if not empty, only allow whitelisted IPs)
        if (!empty($whitelistedIPs) && !in_array($ip, $whitelistedIPs)) {
            return false;
        }

        return true;
    }
} 