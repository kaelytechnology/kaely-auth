<?php

namespace Kaely\Auth\Services;

use Kaely\Auth\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Models\AuditLog;

class EmailVerificationService
{
    /**
     * Send verification email
     */
    public function sendVerificationEmail($user): bool
    {
        try {
            // Check if email is already verified
            if ($user->email_verified_at) {
                return true;
            }

            // Create verification record
            $verification = EmailVerification::createVerification($user->id, $user->email);

            // Send email
            $this->sendVerificationEmailNotification($user, $verification);

            AuditLog::logSuccess(
                AuditLog::ACTION_EMAIL_VERIFICATION,
                "Verification email sent to: {$user->email}",
                $user->id,
                ['email' => $user->email]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            AuditLog::logFailed(
                AuditLog::ACTION_EMAIL_VERIFICATION,
                "Verification email failed for: {$user->email}",
                $user->id,
                ['email' => $user->email],
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(string $token): bool
    {
        try {
            $verification = EmailVerification::findValidToken($token);
            
            if (!$verification) {
                AuditLog::logFailed(
                    AuditLog::ACTION_EMAIL_VERIFICATION,
                    "Invalid verification token: {$token}",
                    null,
                    ['token' => $token]
                );
                return false;
            }

            $userModel = config('auth.providers.users.model');
            $user = $userModel::find($verification->user_id);
            
            if (!$user) {
                AuditLog::logFailed(
                    AuditLog::ACTION_EMAIL_VERIFICATION,
                    "User not found for verification: {$verification->user_id}",
                    null,
                    ['user_id' => $verification->user_id]
                );
                return false;
            }

            // Mark email as verified
            $user->update(['email_verified_at' => now()]);
            $verification->markAsVerified();

            AuditLog::logSuccess(
                AuditLog::ACTION_EMAIL_VERIFICATION,
                "Email verified successfully: {$user->email}",
                $user->id,
                ['email' => $user->email]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            AuditLog::logFailed(
                AuditLog::ACTION_EMAIL_VERIFICATION,
                "Email verification failed for token: {$token}",
                null,
                ['token' => $token],
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail($user): bool
    {
        // Delete existing verification
        EmailVerification::where('user_id', $user->id)->delete();
        
        return $this->sendVerificationEmail($user);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified($user): bool
    {
        return !is_null($user->email_verified_at);
    }

    /**
     * Clean up expired verification tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return EmailVerification::deleteExpired();
    }

    /**
     * Send verification email notification
     */
    protected function sendVerificationEmailNotification($user, EmailVerification $verification): void
    {
        $verificationUrl = $this->buildVerificationUrl($verification->token);
        
        Mail::send('kaely-auth::emails.email-verification', [
            'user' => $user,
            'verificationUrl' => $verificationUrl,
            'expiresAt' => $verification->expires_at,
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('Verify Your Email Address');
        });
    }

    /**
     * Build verification URL
     */
    protected function buildVerificationUrl(string $token): string
    {
        $frontendUrl = config('kaely-auth.email_verification.frontend_url');
        
        if ($frontendUrl) {
            return rtrim($frontendUrl, '/') . '/verify-email?token=' . $token;
        }

        return url('/verify-email?token=' . $token);
    }
} 