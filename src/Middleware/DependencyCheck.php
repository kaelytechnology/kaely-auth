<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kaely\Auth\Services\DependencyChecker;
use Symfony\Component\HttpFoundation\Response;

class DependencyCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $checker = new DependencyChecker();
        $report = $checker->validateAndReport();

        if (!$report['can_proceed']) {
            return response()->json([
                'error' => 'Dependencies missing',
                'message' => 'KaelyAuth requires missing dependencies. Please run: php artisan kaely:check-dependencies',
                'missing_dependencies' => $report['missing_dependencies'],
                'install_command' => $report['install_command'],
            ], 503);
        }

        return $next($request);
    }
} 