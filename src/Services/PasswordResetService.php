<?php

namespace Kaely\Auth\Services;

use Kaely\Auth\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Models\AuditLog;

class PasswordResetService
{
    /**
     * Send password reset email
     */
    public function sendResetEmail(string $email): bool
    {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                AuditLog::logFailed(
                    AuditLog::ACTION_PASSWORD_RESET,
                    "Password reset requested for non-existent email: {$email}",
                    null,
                    ['email' => $email]
                );
                return false;
            }

            // Create password reset record
            $reset = PasswordReset::createReset($email);

            // Send email
            $this->sendResetEmailNotification($user, $reset);

            AuditLog::logSuccess(
                AuditLog::ACTION_PASSWORD_RESET,
                "Password reset email sent to: {$email}",
                $user->id,
                ['email' => $email]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            AuditLog::logFailed(
                AuditLog::ACTION_PASSWORD_RESET,
                "Password reset email failed for: {$email}",
                null,
                ['email' => $email],
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        try {
            $reset = PasswordReset::findValidToken($email, $token);
            
            if (!$reset) {
                AuditLog::logFailed(
                    AuditLog::ACTION_PASSWORD_RESET,
                    "Invalid password reset token for: {$email}",
                    null,
                    ['email' => $email, 'token' => $token]
                );
                return false;
            }

            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                AuditLog::logFailed(
                    AuditLog::ACTION_PASSWORD_RESET,
                    "User not found for password reset: {$email}",
                    null,
                    ['email' => $email]
                );
                return false;
            }

            // Update password
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Delete the reset token
            $reset->delete();

            AuditLog::logSuccess(
                AuditLog::ACTION_PASSWORD_RESET,
                "Password successfully reset for: {$email}",
                $user->id,
                ['email' => $email]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            AuditLog::logFailed(
                AuditLog::ACTION_PASSWORD_RESET,
                "Password reset failed for: {$email}",
                null,
                ['email' => $email],
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Validate reset token
     */
    public function validateToken(string $email, string $token): bool
    {
        $reset = PasswordReset::findValidToken($email, $token);
        return $reset !== null;
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return PasswordReset::deleteExpired();
    }

    /**
     * Get user by email
     */
    protected function getUserByEmail(string $email)
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::where('email', $email)->first();
    }

    /**
     * Send reset email notification
     */
    protected function sendResetEmailNotification($user, PasswordReset $reset): void
    {
        $resetUrl = $this->buildResetUrl($reset->token);
        
        Mail::send('kaely-auth::emails.password-reset', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'expiresAt' => $reset->created_at->addHours(
                config('kaely-auth.password_reset.expiration_hours', 24)
            ),
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('Password Reset Request');
        });
    }

    /**
     * Build reset URL
     */
    protected function buildResetUrl(string $token): string
    {
        $frontendUrl = config('kaely-auth.password_reset.frontend_url');
        
        if ($frontendUrl) {
            return rtrim($frontendUrl, '/') . '/reset-password?token=' . $token;
        }

        return url('/reset-password?token=' . $token);
    }
} 