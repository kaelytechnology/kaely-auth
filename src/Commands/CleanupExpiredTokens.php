<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Kaely\Auth\Models\PasswordReset;
use Kaely\Auth\Models\EmailVerification;
use Kaely\Auth\Models\UserSession;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:cleanup-tokens 
                            {--type=all : Type of tokens to cleanup (password-reset, email-verification, sessions, all)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired password reset tokens, email verification tokens, and user sessions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info('Starting cleanup of expired tokens...');

        $totalDeleted = 0;

        if (in_array($type, ['password-reset', 'all'])) {
            $deleted = $this->cleanupPasswordResetTokens($dryRun);
            $totalDeleted += $deleted;
            $this->info("Deleted {$deleted} expired password reset tokens");
        }

        if (in_array($type, ['email-verification', 'all'])) {
            $deleted = $this->cleanupEmailVerificationTokens($dryRun);
            $totalDeleted += $deleted;
            $this->info("Deleted {$deleted} expired email verification tokens");
        }

        if (in_array($type, ['sessions', 'all'])) {
            $deleted = $this->cleanupExpiredSessions($dryRun);
            $totalDeleted += $deleted;
            $this->info("Deleted {$deleted} expired user sessions");
        }

        $this->info("Cleanup completed. Total deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }

    /**
     * Clean up expired password reset tokens
     */
    protected function cleanupPasswordResetTokens(bool $dryRun): int
    {
        if ($dryRun) {
            $count = PasswordReset::where('created_at', '<', now()->subHours(
                config('kaely-auth.password_reset.expiration_hours', 24)
            ))->count();
            
            $this->line("Would delete {$count} expired password reset tokens");
            return $count;
        }

        return PasswordReset::deleteExpired();
    }

    /**
     * Clean up expired email verification tokens
     */
    protected function cleanupEmailVerificationTokens(bool $dryRun): int
    {
        if ($dryRun) {
            $count = EmailVerification::where('expires_at', '<', now())->count();
            
            $this->line("Would delete {$count} expired email verification tokens");
            return $count;
        }

        return EmailVerification::deleteExpired();
    }

    /**
     * Clean up expired user sessions
     */
    protected function cleanupExpiredSessions(bool $dryRun): int
    {
        if ($dryRun) {
            $count = UserSession::where('expires_at', '<', now())->count();
            
            $this->line("Would delete {$count} expired user sessions");
            return $count;
        }

        return UserSession::cleanupExpired();
    }
} 