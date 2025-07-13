<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Check if the token is expired
     */
    public function isExpired(): bool
    {
        $expirationTime = config('kaely-auth.password_reset.expiration_hours', 24);
        return $this->created_at->addHours($expirationTime)->isPast();
    }

    /**
     * Generate a new reset token
     */
    public static function generateToken(string $email): string
    {
        return Str::random(60);
    }

    /**
     * Create a new password reset record
     */
    public static function createReset(string $email): self
    {
        // Delete any existing reset tokens for this email
        static::where('email', $email)->delete();

        return static::create([
            'email' => $email,
            'token' => static::generateToken($email),
            'created_at' => now(),
        ]);
    }

    /**
     * Find a valid reset token
     */
    public static function findValidToken(string $email, string $token): ?self
    {
        $reset = static::where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$reset || $reset->isExpired()) {
            return null;
        }

        return $reset;
    }

    /**
     * Delete expired tokens
     */
    public static function deleteExpired(): int
    {
        $expirationTime = config('kaely-auth.password_reset.expiration_hours', 24);
        $expiredDate = now()->subHours($expirationTime);

        return static::where('created_at', '<', $expiredDate)->delete();
    }
} 