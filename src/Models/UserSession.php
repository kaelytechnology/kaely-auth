<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token_id',
        'device_name',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Check if the session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the session is active
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Revoke the session
     */
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Create a new session record
     */
    public static function createSession(
        int $userId,
        string $tokenId,
        string $deviceName = null,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        $sessionLifetime = config('kaely-auth.sessions.lifetime_hours', 24 * 30); // 30 days default

        return static::create([
            'user_id' => $userId,
            'token_id' => $tokenId,
            'device_name' => $deviceName,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity_at' => now(),
            'expires_at' => now()->addHours($sessionLifetime),
            'is_active' => true,
        ]);
    }

    /**
     * Find active session by token ID
     */
    public static function findActiveByTokenId(string $tokenId): ?self
    {
        return static::where('token_id', $tokenId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Get all active sessions for a user
     */
    public static function getActiveSessions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    /**
     * Revoke all sessions for a user
     */
    public static function revokeAllSessions(int $userId): int
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Revoke session by token ID
     */
    public static function revokeByTokenId(string $tokenId): bool
    {
        $session = static::findActiveByTokenId($tokenId);
        
        if ($session) {
            $session->revoke();
            return true;
        }

        return false;
    }

    /**
     * Revoke all sessions except the current one
     */
    public static function revokeOtherSessions(int $userId, string $currentTokenId): int
    {
        return static::where('user_id', $userId)
            ->where('token_id', '!=', $currentTokenId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Clean up expired sessions
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    /**
     * Get session statistics for a user
     */
    public static function getSessionStats(int $userId): array
    {
        $activeSessions = static::where('user_id', $userId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->count();

        $totalSessions = static::where('user_id', $userId)->count();

        return [
            'active_sessions' => $activeSessions,
            'total_sessions' => $totalSessions,
            'expired_sessions' => $totalSessions - $activeSessions,
        ];
    }
} 