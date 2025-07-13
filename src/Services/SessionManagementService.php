<?php

namespace Kaely\Auth\Services;

use Kaely\Auth\Models\UserSession;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Models\AuditLog;

class SessionManagementService
{
    /**
     * Create a new session
     */
    public function createSession($user, string $tokenId, array $sessionData = []): UserSession
    {
        $session = UserSession::createSession(
            $user->id,
            $tokenId,
            $sessionData['device_name'] ?? null,
            $sessionData['ip_address'] ?? request()->ip(),
            $sessionData['user_agent'] ?? request()->userAgent()
        );

        AuditLog::logSuccess(
            AuditLog::ACTION_LOGIN,
            "New session created for user: {$user->email}",
            $user->id,
            ['token_id' => $tokenId, 'device_name' => $session->device_name]
        );

        return $session;
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(string $tokenId): bool
    {
        $session = UserSession::findActiveByTokenId($tokenId);
        
        if (!$session) {
            return false;
        }

        $session->revoke();

        AuditLog::logSuccess(
            AuditLog::ACTION_SESSION_REVOKED,
            "Session revoked for user: {$session->user_id}",
            $session->user_id,
            ['token_id' => $tokenId]
        );

        return true;
    }

    /**
     * Revoke all sessions for a user
     */
    public function revokeAllSessions(int $userId): int
    {
        $count = UserSession::revokeAllSessions($userId);

        AuditLog::logSuccess(
            AuditLog::ACTION_SESSION_REVOKED,
            "All sessions revoked for user: {$userId}",
            $userId,
            ['sessions_revoked' => $count]
        );

        return $count;
    }

    /**
     * Revoke all sessions except the current one
     */
    public function revokeOtherSessions(int $userId, string $currentTokenId): int
    {
        $count = UserSession::revokeOtherSessions($userId, $currentTokenId);

        AuditLog::logSuccess(
            AuditLog::ACTION_SESSION_REVOKED,
            "Other sessions revoked for user: {$userId}",
            $userId,
            ['sessions_revoked' => $count, 'current_token' => $currentTokenId]
        );

        return $count;
    }

    /**
     * Get active sessions for a user
     */
    public function getActiveSessions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::getActiveSessions($userId);
    }

    /**
     * Get session statistics for a user
     */
    public function getSessionStats(int $userId): array
    {
        return UserSession::getSessionStats($userId);
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(string $tokenId): bool
    {
        $session = UserSession::findActiveByTokenId($tokenId);
        
        if ($session) {
            $session->updateActivity();
            return true;
        }

        return false;
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        return UserSession::cleanupExpired();
    }

    /**
     * Get suspicious sessions (multiple active sessions from different IPs)
     */
    public function getSuspiciousSessions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $sessions = UserSession::getActiveSessions($userId);
        
        // Group by IP address
        $ipGroups = $sessions->groupBy('ip_address');
        
        // Return sessions from IPs with multiple sessions
        return $sessions->filter(function ($session) use ($ipGroups) {
            return $ipGroups->get($session->ip_address)->count() > 1;
        });
    }

    /**
     * Force logout from all devices
     */
    public function forceLogoutAllDevices(int $userId): int
    {
        $count = $this->revokeAllSessions($userId);

        AuditLog::logSuccess(
            AuditLog::ACTION_LOGOUT,
            "Force logout from all devices for user: {$userId}",
            $userId,
            ['sessions_revoked' => $count]
        );

        return $count;
    }

    /**
     * Get session activity timeline
     */
    public function getSessionTimeline(int $userId, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $since = now()->subDays($days);

        return UserSession::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if user has too many active sessions
     */
    public function hasTooManySessions(int $userId): bool
    {
        $maxSessions = config('kaely-auth.sessions.max_active_sessions', 5);
        $activeSessions = UserSession::getActiveSessions($userId)->count();
        
        return $activeSessions >= $maxSessions;
    }

    /**
     * Get session security report
     */
    public function getSecurityReport(int $userId): array
    {
        $sessions = UserSession::getActiveSessions($userId);
        $stats = UserSession::getSessionStats($userId);
        
        $uniqueIPs = $sessions->pluck('ip_address')->unique()->count();
        $uniqueDevices = $sessions->pluck('device_name')->unique()->count();
        
        $suspiciousSessions = $this->getSuspiciousSessions($userId);
        
        return [
            'active_sessions' => $stats['active_sessions'],
            'unique_ips' => $uniqueIPs,
            'unique_devices' => $uniqueDevices,
            'suspicious_sessions' => $suspiciousSessions->count(),
            'security_score' => $this->calculateSecurityScore($sessions),
            'recommendations' => $this->getSecurityRecommendations($sessions),
        ];
    }

    /**
     * Calculate security score based on sessions
     */
    protected function calculateSecurityScore($sessions): int
    {
        $score = 100;
        
        // Deduct points for multiple sessions
        if ($sessions->count() > 3) {
            $score -= 20;
        }
        
        // Deduct points for multiple IPs
        $uniqueIPs = $sessions->pluck('ip_address')->unique()->count();
        if ($uniqueIPs > 2) {
            $score -= 30;
        }
        
        // Deduct points for suspicious activity
        $suspiciousSessions = $this->getSuspiciousSessions($sessions->first()->user_id);
        if ($suspiciousSessions->count() > 0) {
            $score -= 40;
        }
        
        return max(0, $score);
    }

    /**
     * Get security recommendations
     */
    protected function getSecurityRecommendations($sessions): array
    {
        $recommendations = [];
        
        if ($sessions->count() > 3) {
            $recommendations[] = 'Consider logging out from unused devices';
        }
        
        $uniqueIPs = $sessions->pluck('ip_address')->unique()->count();
        if ($uniqueIPs > 2) {
            $recommendations[] = 'Multiple IP addresses detected - review for suspicious activity';
        }
        
        $suspiciousSessions = $this->getSuspiciousSessions($sessions->first()->user_id);
        if ($suspiciousSessions->count() > 0) {
            $recommendations[] = 'Suspicious activity detected - consider changing password';
        }
        
        return $recommendations;
    }
} 