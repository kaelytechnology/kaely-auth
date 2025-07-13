<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query logging for debugging
        if (config('kaely-auth.debug.query_logging', false)) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Performance monitoring
        $this->monitorPerformance($request, $startTime, $startMemory);

        // Add performance headers
        $this->addPerformanceHeaders($response, $startTime, $startMemory);

        return $response;
    }

    /**
     * Monitor performance metrics
     */
    protected function monitorPerformance(Request $request, float $startTime, int $startMemory): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = memory_get_usage() - $startMemory;
        $peakMemory = memory_get_peak_usage();

        // Log slow requests
        if ($executionTime > config('kaely-auth.performance.slow_request_threshold', 1000)) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage,
                'peak_memory' => $peakMemory,
            ]);
        }

        // Cache performance metrics
        $this->cachePerformanceMetrics($request, $executionTime, $memoryUsage);

        // Log queries if enabled
        if (config('kaely-auth.debug.query_logging', false)) {
            $queries = DB::getQueryLog();
            if (count($queries) > config('kaely-auth.performance.max_queries_threshold', 10)) {
                Log::warning('High query count detected', [
                    'url' => $request->fullUrl(),
                    'query_count' => count($queries),
                    'queries' => $queries,
                ]);
            }
        }
    }

    /**
     * Cache performance metrics
     */
    protected function cachePerformanceMetrics(Request $request, float $executionTime, int $memoryUsage): void
    {
        $key = 'performance_metrics_' . date('Y-m-d');
        $metrics = Cache::get($key, []);

        $metrics[] = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 1000 metrics
        if (count($metrics) > 1000) {
            $metrics = array_slice($metrics, -1000);
        }

        Cache::put($key, $metrics, 86400); // Cache for 24 hours
    }

    /**
     * Add performance headers
     */
    protected function addPerformanceHeaders($response, float $startTime, int $startMemory): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_usage() - $startMemory;

        $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
        $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsage));
        $response->headers->set('X-Peak-Memory', $this->formatBytes(memory_get_peak_usage()));
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 