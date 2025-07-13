<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerification extends Model
{
    use HasFactory;

    protected $table = 'email_verifications';

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Check if the verification token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the email is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Mark the email as verified
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Generate a new verification token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new email verification record
     */
    public static function createVerification(int $userId, string $email): self
    {
        // Delete any existing verification tokens for this user
        static::where('user_id', $userId)->delete();

        $expirationHours = config('kaely-auth.email_verification.expiration_hours', 24);

        return static::create([
            'user_id' => $userId,
            'email' => $email,
            'token' => static::generateToken(),
            'expires_at' => now()->addHours($expirationHours),
        ]);
    }

    /**
     * Find a valid verification token
     */
    public static function findValidToken(string $token): ?self
    {
        $verification = static::where('token', $token)->first();

        if (!$verification || $verification->isExpired() || $verification->isVerified()) {
            return null;
        }

        return $verification;
    }

    /**
     * Delete expired verification tokens
     */
    public static function deleteExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    /**
     * Get verification by user ID
     */
    public static function findByUserId(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }
} 