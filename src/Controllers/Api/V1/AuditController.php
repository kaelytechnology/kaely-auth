<?php

namespace Kaely\Auth\Controllers\Api\V1;

use Kaely\Auth\Controllers\Controller;
use Kaely\Auth\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Get user activity timeline
     */
    public function getUserTimeline(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $days = $request->input('days', 30);
        $timeline = $this->auditService->getUserTimeline($user->id, $days);

        return response()->json([
            'success' => true,
            'data' => $timeline
        ]);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $days = $request->input('days', 30);
        $summary = $this->auditService->getUserActivitySummary($user->id, $days);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get audit statistics
     */
    public function getAuditStats(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $stats = $this->auditService->getAuditStats($days);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get activity heatmap
     */
    public function getActivityHeatmap(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $heatmap = $this->auditService->getActivityHeatmap($days);

        return response()->json([
            'success' => true,
            'data' => $heatmap
        ]);
    }

    /**
     * Get top actions
     */
    public function getTopActions(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $limit = $request->input('limit', 10);
        $actions = $this->auditService->getTopActions($days, $limit);

        return response()->json([
            'success' => true,
            'data' => $actions
        ]);
    }

    /**
     * Get error trends
     */
    public function getErrorTrends(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $trends = $this->auditService->getErrorTrends($days);

        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }

    /**
     * Get security alerts
     */
    public function getSecurityAlerts(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $alerts = $this->auditService->getSecurityAlerts($hours);

        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Get security threats
     */
    public function getSecurityThreats(Request $request): JsonResponse
    {
        $threats = $this->auditService->monitorSecurityThreats();

        return response()->json([
            'success' => true,
            'data' => $threats
        ]);
    }

    /**
     * Generate audit report
     */
    public function generateAuditReport(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $report = $this->auditService->generateAuditReport($days);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Export audit logs
     */
    public function exportAuditLogs(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $format = $request->input('format', 'json');

        $export = $this->auditService->exportAuditLogs($days, $format);

        return response()->json([
            'success' => true,
            'data' => [
                'format' => $format,
                'export' => $export,
                'days' => $days
            ]
        ]);
    }

    /**
     * Get user logs (admin only)
     */
    public function getUserLogs(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $limit = $request->input('limit', 50);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 422);
        }

        // Check if current user has permission to view other users' logs
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->id != $userId) {
            // Check if user has admin permissions
            if (!$currentUser->hasPermission('view_audit_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ], 403);
            }
        }

        $logs = $this->auditService->getUserLogs($userId, $limit);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get logs by action (admin only)
     */
    public function getLogsByAction(Request $request): JsonResponse
    {
        $action = $request->input('action');
        $limit = $request->input('limit', 50);

        if (!$action) {
            return response()->json([
                'success' => false,
                'message' => 'Action is required'
            ], 422);
        }

        // Check if current user has permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('view_audit_logs')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        $logs = $this->auditService->getLogsByAction($action, $limit);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
} 