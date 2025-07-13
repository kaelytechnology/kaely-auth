<?php

namespace Kaely\Auth\Services;

use Kaely\Auth\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AuditService
{
    /**
     * Log user activity
     */
    public function logActivity(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = [],
        string $status = AuditLog::STATUS_SUCCESS,
        ?string $errorMessage = null
    ): AuditLog {
        return AuditLog::log($action, $description, $userId, $requestData, $responseData, $status, $errorMessage);
    }

    /**
     * Log successful activity
     */
    public function logSuccess(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = []
    ): AuditLog {
        return AuditLog::logSuccess($action, $description, $userId, $requestData, $responseData);
    }

    /**
     * Log failed activity
     */
    public function logFailed(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        string $errorMessage = null
    ): AuditLog {
        return AuditLog::logFailed($action, $description, $userId, $requestData, $errorMessage);
    }

    /**
     * Log warning
     */
    public function logWarning(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = []
    ): AuditLog {
        return AuditLog::logWarning($action, $description, $userId, $requestData, $responseData);
    }

    /**
     * Get user activity timeline
     */
    public function getUserTimeline(int $userId, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $since = now()->subDays($days);

        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get security alerts
     */
    public function getSecurityAlerts(int $hours = 24): array
    {
        $since = now()->subHours($hours);
        
        $failedLogins = AuditLog::where('created_at', '>=', $since)
            ->where('action', AuditLog::ACTION_LOGIN)
            ->where('status', AuditLog::STATUS_FAILED)
            ->count();

        $suspiciousActivity = AuditLog::getSuspiciousActivity($hours);

        $alerts = [];

        if ($failedLogins > 10) {
            $alerts[] = [
                'type' => 'high_failed_logins',
                'message' => "High number of failed login attempts: {$failedLogins}",
                'severity' => 'high'
            ];
        }

        foreach ($suspiciousActivity as $activity) {
            $alerts[] = [
                'type' => 'suspicious_activity',
                'message' => "Suspicious activity detected for user {$activity->user_id} from IP {$activity->ip_address}",
                'severity' => 'medium',
                'data' => $activity
            ];
        }

        return $alerts;
    }

    /**
     * Get audit statistics
     */
    public function getAuditStats(int $days = 30): array
    {
        return AuditLog::getAuditStats($days);
    }

    /**
     * Get activity heatmap
     */
    public function getActivityHeatmap(int $days = 30): array
    {
        $since = now()->subDays($days);
        
        $activities = AuditLog::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $heatmap = [];
        foreach ($activities as $activity) {
            $heatmap[$activity->date] = $activity->count;
        }

        return $heatmap;
    }

    /**
     * Get top actions
     */
    public function getTopActions(int $days = 30, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $since = now()->subDays($days);

        return AuditLog::where('created_at', '>=', $since)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get error trends
     */
    public function getErrorTrends(int $days = 30): array
    {
        $since = now()->subDays($days);

        $errors = AuditLog::where('created_at', '>=', $since)
            ->where('status', AuditLog::STATUS_FAILED)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trends = [];
        foreach ($errors as $error) {
            $trends[$error->date] = $error->count;
        }

        return $trends;
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId, int $days = 30): array
    {
        $since = now()->subDays($days);

        $activities = AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->get();

        $successCount = $activities->where('status', AuditLog::STATUS_SUCCESS)->count();
        $failedCount = $activities->where('status', AuditLog::STATUS_FAILED)->count();
        $warningCount = $activities->where('status', AuditLog::STATUS_WARNING)->count();

        $actionBreakdown = $activities->groupBy('action')->map(function ($group) {
            return $group->count();
        });

        return [
            'total_activities' => $activities->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'warning_count' => $warningCount,
            'success_rate' => $activities->count() > 0 ? round(($successCount / $activities->count()) * 100, 2) : 0,
            'action_breakdown' => $actionBreakdown,
            'last_activity' => $activities->sortByDesc('created_at')->first()?->created_at,
        ];
    }

    /**
     * Monitor for security threats
     */
    public function monitorSecurityThreats(): array
    {
        $threats = [];

        // Check for brute force attacks
        $recentFailedLogins = AuditLog::getFailedLoginAttempts(0, 1); // Last hour
        if ($recentFailedLogins->count() > 20) {
            $threats[] = [
                'type' => 'brute_force',
                'severity' => 'high',
                'message' => 'Potential brute force attack detected',
                'data' => $recentFailedLogins
            ];
        }

        // Check for unusual activity patterns
        $suspiciousActivity = AuditLog::getSuspiciousActivity(1, 3);
        if ($suspiciousActivity->count() > 0) {
            $threats[] = [
                'type' => 'suspicious_activity',
                'severity' => 'medium',
                'message' => 'Suspicious activity patterns detected',
                'data' => $suspiciousActivity
            ];
        }

        // Check for multiple failed logins from same IP
        $failedLoginsByIP = AuditLog::where('created_at', '>=', now()->subHour())
            ->where('action', AuditLog::ACTION_LOGIN)
            ->where('status', AuditLog::STATUS_FAILED)
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->having('count', '>', 5)
            ->get();

        if ($failedLoginsByIP->count() > 0) {
            $threats[] = [
                'type' => 'ip_attack',
                'severity' => 'high',
                'message' => 'Multiple failed logins from same IP addresses',
                'data' => $failedLoginsByIP
            ];
        }

        return $threats;
    }

    /**
     * Generate audit report
     */
    public function generateAuditReport(int $days = 30): array
    {
        $stats = $this->getAuditStats($days);
        $heatmap = $this->getActivityHeatmap($days);
        $topActions = $this->getTopActions($days);
        $errorTrends = $this->getErrorTrends($days);
        $securityAlerts = $this->getSecurityAlerts(24);
        $threats = $this->monitorSecurityThreats();

        return [
            'period' => $days . ' days',
            'summary' => $stats,
            'activity_heatmap' => $heatmap,
            'top_actions' => $topActions,
            'error_trends' => $errorTrends,
            'security_alerts' => $securityAlerts,
            'security_threats' => $threats,
            'generated_at' => now(),
        ];
    }

    /**
     * Clean up old audit logs
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return AuditLog::cleanupOldLogs($days);
    }

    /**
     * Export audit logs
     */
    public function exportAuditLogs(int $days = 30, string $format = 'json'): string
    {
        $since = now()->subDays($days);

        $logs = AuditLog::where('created_at', '>=', $since)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'json') {
            return $logs->toJson();
        }

        if ($format === 'csv') {
            return $this->convertToCsv($logs);
        }

        return $logs->toJson();
    }

    /**
     * Convert logs to CSV
     */
    protected function convertToCsv($logs): string
    {
        $csv = "ID,User ID,Action,Description,IP Address,Status,Error Message,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%d,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user_id ?? 0,
                $log->action,
                str_replace(',', ';', $log->description),
                $log->ip_address,
                $log->status,
                str_replace(',', ';', $log->error_message ?? ''),
                $log->created_at
            );
        }

        return $csv;
    }
} 