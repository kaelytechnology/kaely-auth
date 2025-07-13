<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $securityLevel = 'standard')
    {
        // Security checks based on level
        switch ($securityLevel) {
            case 'high':
                $this->performHighSecurityChecks($request);
                break;
            case 'medium':
                $this->performMediumSecurityChecks($request);
                break;
            default:
                $this->performStandardSecurityChecks($request);
        }

        // Add security headers
        $response = $next($request);
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Perform high security checks
     */
    protected function performHighSecurityChecks(Request $request): void
    {
        // Check for suspicious patterns
        $this->detectSuspiciousActivity($request);
        
        // Validate request origin
        $this->validateRequestOrigin($request);
        
        // Check for SQL injection attempts
        $this->detectSQLInjection($request);
        
        // Check for XSS attempts
        $this->detectXSSAttempts($request);
        
        // Rate limiting for high security
        $this->enforceRateLimit($request, 30, 1); // 30 requests per minute
    }

    /**
     * Perform medium security checks
     */
    protected function performMediumSecurityChecks(Request $request): void
    {
        $this->detectSuspiciousActivity($request);
        $this->enforceRateLimit($request, 60, 1); // 60 requests per minute
    }

    /**
     * Perform standard security checks
     */
    protected function performStandardSecurityChecks(Request $request): void
    {
        $this->enforceRateLimit($request, 120, 1); // 120 requests per minute
    }

    /**
     * Detect suspicious activity
     */
    protected function detectSuspiciousActivity(Request $request): void
    {
        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
        ];

        $input = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityThreat($request, 'suspicious_pattern', [
                    'pattern' => $pattern,
                    'input' => $input
                ]);
                
                abort(403, 'Suspicious activity detected');
            }
        }
    }

    /**
     * Validate request origin
     */
    protected function validateRequestOrigin(Request $request): void
    {
        $allowedOrigins = config('kaely-auth.security.allowed_origins', []);
        $origin = $request->header('Origin');
        
        if ($origin && !empty($allowedOrigins)) {
            $isAllowed = false;
            foreach ($allowedOrigins as $allowedOrigin) {
                if (str_contains($origin, $allowedOrigin)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                $this->logSecurityThreat($request, 'unauthorized_origin', [
                    'origin' => $origin,
                    'allowed_origins' => $allowedOrigins
                ]);
                
                abort(403, 'Unauthorized origin');
            }
        }
    }

    /**
     * Detect SQL injection attempts
     */
    protected function detectSQLInjection(Request $request): void
    {
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
            '/alter\s+table/i',
            '/create\s+table/i',
            '/exec\s*\(/i',
            '/xp_cmdshell/i',
        ];

        $input = json_encode($request->all());
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityThreat($request, 'sql_injection_attempt', [
                    'pattern' => $pattern,
                    'input' => $input
                ]);
                
                abort(403, 'SQL injection attempt detected');
            }
        }
    }

    /**
     * Detect XSS attempts
     */
    protected function detectXSSAttempts(Request $request): void
    {
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            '/onmouseover=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];

        $input = json_encode($request->all());
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityThreat($request, 'xss_attempt', [
                    'pattern' => $pattern,
                    'input' => $input
                ]);
                
                abort(403, 'XSS attempt detected');
            }
        }
    }

    /**
     * Enforce rate limiting
     */
    protected function enforceRateLimit(Request $request, int $maxAttempts, int $decayMinutes): void
    {
        $key = $this->getRateLimitKey($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            $this->logSecurityThreat($request, 'rate_limit_exceeded', [
                'max_attempts' => $maxAttempts,
                'decay_minutes' => $decayMinutes,
                'retry_after' => $seconds
            ]);
            
            abort(429, "Rate limit exceeded. Try again in {$seconds} seconds.");
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get rate limit key
     */
    protected function getRateLimitKey(Request $request): string
    {
        $user = $request->user();
        $userId = $user ? $user->id : 'guest';
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        return sha1("security_rate_limit|{$userId}|{$ip}|{$userAgent}");
    }

    /**
     * Add security headers
     */
    protected function addSecurityHeaders($response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Add CSP header if configured
        $csp = config('kaely-auth.security.content_security_policy');
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
        }
    }

    /**
     * Log security threat
     */
    protected function logSecurityThreat(Request $request, string $threatType, array $data = []): void
    {
        $logData = [
            'threat_type' => $threatType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->warning('Security threat detected', $logData);

        // Store in audit logs if enabled
        if (config('kaely-auth.audit.enabled', true)) {
            \DB::table('audit_logs')->insert([
                'user_id' => $request->user()?->id,
                'action' => 'security.threat',
                'description' => "Security threat: {$threatType}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => json_encode($logData),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 