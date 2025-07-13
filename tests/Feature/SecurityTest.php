<?php

namespace Kaely\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_injection_protection()
    {
        $response = $this->postJson('/api/login', [
            'email' => "'; DROP TABLE users; --",
            'password' => 'password'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'SQL injection attempt detected']);
    }

    public function test_xss_protection()
    {
        $response = $this->postJson('/api/register', [
            'name' => '<script>alert("xss")</script>',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'XSS attempt detected']);
    }

    public function test_rate_limiting()
    {
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        $response->assertStatus(429);
        $response->assertJson(['error' => 'Rate limit exceeded']);
    }

    public function test_password_strength_validation()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_unauthorized_origin_protection()
    {
        $response = $this->withHeaders([
            'Origin' => 'https://malicious-site.com'
        ])->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(403);
    }
} 