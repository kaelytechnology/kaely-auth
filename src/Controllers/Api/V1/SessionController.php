<?php

namespace Kaely\Auth\Controllers\Api\V1;

use Kaely\Auth\Controllers\Controller;
use Kaely\Auth\Services\SessionManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    protected SessionManagementService $sessionManagementService;

    public function __construct(SessionManagementService $sessionManagementService)
    {
        $this->sessionManagementService = $sessionManagementService;
    }

    /**
     * Get user's active sessions
     */
    public function getActiveSessions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $sessions = $this->sessionManagementService->getActiveSessions($user->id);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $stats = $this->sessionManagementService->getSessionStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $tokenId = $request->input('token_id');

        if (!$tokenId) {
            return response()->json([
                'success' => false,
                'message' => 'Token ID is required'
            ], 422);
        }

        $success = $this->sessionManagementService->revokeSession($tokenId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Session revoked successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Session not found or already revoked'
        ], 400);
    }

    /**
     * Revoke all sessions except current
     */
    public function revokeOtherSessions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $currentToken = $request->bearerToken();
        
        if (!$currentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Current token not found'
            ], 400);
        }

        $count = $this->sessionManagementService->revokeOtherSessions($user->id, $currentToken);

        return response()->json([
            'success' => true,
            'message' => "Revoked {$count} other sessions",
            'data' => [
                'sessions_revoked' => $count
            ]
        ]);
    }

    /**
     * Revoke all sessions
     */
    public function revokeAllSessions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $count = $this->sessionManagementService->revokeAllSessions($user->id);

        return response()->json([
            'success' => true,
            'message' => "Revoked {$count} sessions",
            'data' => [
                'sessions_revoked' => $count
            ]
        ]);
    }

    /**
     * Get session security report
     */
    public function getSecurityReport(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $report = $this->sessionManagementService->getSecurityReport($user->id);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get session timeline
     */
    public function getSessionTimeline(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $days = $request->input('days', 30);
        $timeline = $this->sessionManagementService->getSessionTimeline($user->id, $days);

        return response()->json([
            'success' => true,
            'data' => $timeline
        ]);
    }

    /**
     * Force logout from all devices
     */
    public function forceLogoutAllDevices(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $count = $this->sessionManagementService->forceLogoutAllDevices($user->id);

        return response()->json([
            'success' => true,
            'message' => "Logged out from {$count} devices",
            'data' => [
                'sessions_revoked' => $count
            ]
        ]);
    }
} 