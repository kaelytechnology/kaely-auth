<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\Services\AuditService;

class AuditLoggingMiddleware
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if audit logging is enabled
        if (!config('kaely-auth.audit.enabled', true)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        // Log the request/response
        $this->logRequest($request, $response, $endTime - $startTime);

        return $response;
    }

    /**
     * Log the request and response
     */
    protected function logRequest(Request $request, $response, float $duration): void
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        // Determine action based on route
        $action = $this->determineAction($request);
        
        // Determine status based on response
        $status = $this->determineStatus($response);
        
        // Prepare request data (exclude sensitive information)
        $requestData = $this->sanitizeRequestData($request);
        
        // Prepare response data
        $responseData = $this->sanitizeResponseData($response);

        // Create description
        $description = $this->createDescription($request, $response, $duration);

        // Log the activity
        $this->auditService->log(
            $action,
            $description,
            $userId,
            $requestData,
            $responseData,
            $status
        );
    }

    /**
     * Determine the action based on the request
     */
    protected function determineAction(Request $request): string
    {
        $route = $request->route();
        $action = $route ? $route->getActionName() : 'unknown';
        
        // Map common actions
        $actionMap = [
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
            'password/reset' => 'password_reset',
            'email/verify' => 'email_verification',
            'profile/update' => 'profile_updated',
        ];

        foreach ($actionMap as $pattern => $mappedAction) {
            if (str_contains($request->path(), $pattern)) {
                return $mappedAction;
            }
        }

        return 'api_request';
    }

    /**
     * Determine the status based on the response
     */
    protected function determineStatus($response): string
    {
        if ($response instanceof JsonResponse) {
            $statusCode = $response->getStatusCode();
            
            if ($statusCode >= 200 && $statusCode < 300) {
                return 'success';
            } elseif ($statusCode >= 400 && $statusCode < 500) {
                return 'failed';
            } else {
                return 'warning';
            }
        }

        return 'success';
    }

    /**
     * Sanitize request data to remove sensitive information
     */
    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***HIDDEN***';
            }
        }

        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $data,
        ];
    }

    /**
     * Sanitize response data
     */
    protected function sanitizeResponseData($response): array
    {
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            
            // Remove sensitive information from response
            if (is_array($data)) {
                unset($data['token'], $data['access_token'], $data['refresh_token']);
            }

            return [
                'status_code' => $response->getStatusCode(),
                'data' => $data,
            ];
        }

        return [
            'status_code' => $response->getStatusCode(),
            'data' => 'Response data not available',
        ];
    }

    /**
     * Create a description for the audit log
     */
    protected function createDescription(Request $request, $response, float $duration): string
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();
        $durationMs = round($duration * 1000, 2);

        return "{$method} {$path} - Status: {$statusCode} - Duration: {$durationMs}ms";
    }
} 