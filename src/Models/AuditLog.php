<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'request_data',
        'response_data',
        'status',
        'error_message',
        'tenant_id',
        'connection_name',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
    ];

    // Status constants
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_WARNING = 'warning';

    // Action constants
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_PASSWORD_RESET = 'password_reset';
    const ACTION_EMAIL_VERIFICATION = 'email_verification';
    const ACTION_SESSION_REVOKED = 'session_revoked';
    const ACTION_PROFILE_UPDATED = 'profile_updated';
    const ACTION_PERMISSION_CHANGED = 'permission_changed';
    const ACTION_ROLE_CHANGED = 'role_changed';
    const ACTION_OAUTH_LOGIN = 'oauth_login';
    const ACTION_TENANT_CHANGED = 'tenant_changed';

    /**
     * Relationship with User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Create a new audit log entry
     */
    public static function log(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = [],
        string $status = self::STATUS_SUCCESS,
        ?string $errorMessage = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_data' => $requestData,
            'response_data' => $responseData,
            'status' => $status,
            'error_message' => $errorMessage,
            'tenant_id' => app('kaely.tenant')->getCurrentTenantId(),
            'connection_name' => app('kaely.connection')->getCurrentConnection(),
        ]);
    }

    /**
     * Log a successful action
     */
    public static function logSuccess(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = []
    ): self {
        return static::log($action, $description, $userId, $requestData, $responseData, self::STATUS_SUCCESS);
    }

    /**
     * Log a failed action
     */
    public static function logFailed(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        string $errorMessage = null
    ): self {
        return static::log($action, $description, $userId, $requestData, [], self::STATUS_FAILED, $errorMessage);
    }

    /**
     * Log a warning
     */
    public static function logWarning(
        string $action,
        string $description,
        ?int $userId = null,
        array $requestData = [],
        array $responseData = []
    ): self {
        return static::log($action, $description, $userId, $requestData, $responseData, self::STATUS_WARNING);
    }

    /**
     * Get logs for a specific user
     */
    public static function getUserLogs(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by action
     */
    public static function getLogsByAction(string $action, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('action', $action)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by status
     */
    public static function getLogsByStatus(string $status, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs within a date range
     */
    public static function getLogsInDateRange(Carbon $startDate, Carbon $endDate, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed login attempts for a user
     */
    public static function getFailedLoginAttempts(int $userId, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        $since = now()->subHours($hours);

        return static::where('user_id', $userId)
            ->where('action', self::ACTION_LOGIN)
            ->where('status', self::STATUS_FAILED)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit statistics
     */
    public static function getAuditStats(int $days = 30): array
    {
        $since = now()->subDays($days);

        $totalLogs = static::where('created_at', '>=', $since)->count();
        $successLogs = static::where('created_at', '>=', $since)
            ->where('status', self::STATUS_SUCCESS)
            ->count();
        $failedLogs = static::where('created_at', '>=', $since)
            ->where('status', self::STATUS_FAILED)
            ->count();

        $actionStats = static::where('created_at', '>=', $since)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'total_logs' => $totalLogs,
            'success_logs' => $successLogs,
            'failed_logs' => $failedLogs,
            'success_rate' => $totalLogs > 0 ? round(($successLogs / $totalLogs) * 100, 2) : 0,
            'action_stats' => $actionStats,
        ];
    }

    /**
     * Clean up old audit logs
     */
    public static function cleanupOldLogs(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);
        return static::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get suspicious activity (multiple failed logins)
     */
    public static function getSuspiciousActivity(int $hours = 1, int $minAttempts = 5): \Illuminate\Database\Eloquent\Collection
    {
        $since = now()->subHours($hours);

        return static::where('created_at', '>=', $since)
            ->where('action', self::ACTION_LOGIN)
            ->where('status', self::STATUS_FAILED)
            ->selectRaw('user_id, ip_address, COUNT(*) as failed_attempts')
            ->groupBy('user_id', 'ip_address')
            ->having('failed_attempts', '>=', $minAttempts)
            ->get();
    }
} 