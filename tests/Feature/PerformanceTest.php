<?php

namespace Kaely\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_performance()
    {
        $user = User::factory()->create();
        
        // First request (cache miss)
        $startTime = microtime(true);
        $response = $this->actingAs($user)->getJson('/api/user');
        $firstRequestTime = (microtime(true) - $startTime) * 1000;
        
        // Second request (cache hit)
        $startTime = microtime(true);
        $response = $this->actingAs($user)->getJson('/api/user');
        $secondRequestTime = (microtime(true) - $startTime) * 1000;
        
        // Cache hit should be significantly faster
        $this->assertLessThan($firstRequestTime * 0.5, $secondRequestTime);
    }

    public function test_bulk_operations_performance()
    {
        $startTime = microtime(true);
        
        // Create 100 users
        User::factory()->count(100)->create();
        
        $creationTime = (microtime(true) - $startTime) * 1000;
        
        // Should complete within reasonable time
        $this->assertLessThan(5000, $creationTime); // 5 seconds max
    }

    public function test_query_optimization()
    {
        $user = User::factory()->create();
        
        // Test optimized query with relations
        $startTime = microtime(true);
        $userWithRelations = app(\Kaely\Auth\Services\OptimizedQueryService::class)
            ->getUserWithRelations($user->id, ['roles', 'permissions']);
        $queryTime = (microtime(true) - $startTime) * 1000;
        
        // Should be fast
        $this->assertLessThan(100, $queryTime); // 100ms max
        $this->assertNotNull($userWithRelations);
    }

    public function test_memory_usage()
    {
        $initialMemory = memory_get_usage();
        
        // Perform operations
        User::factory()->count(50)->create();
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Memory increase should be reasonable (less than 10MB)
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease);
    }
} 