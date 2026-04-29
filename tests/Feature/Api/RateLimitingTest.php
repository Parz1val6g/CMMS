<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_global_rate_limit_blocks_after_60_requests(): void
    {
        $key = "global:{$this->manager->id}";

        for ($i = 0; $i < 60; $i++) {
            $allowed = RateLimiter::attempt($key, 60, fn () => true, 60);
            $this->assertTrue($allowed, "Request {$i} should be allowed");
        }

        $allowed = RateLimiter::attempt($key, 60, fn () => true, 60);
        $this->assertFalse($allowed, '61st request should be blocked by rate limit');
    }

    public function test_login_throttle_blocks_after_5_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'nonexistent' . $i . '@example.com',
                'password' => 'wrong_password',
            ]);

            $this->assertEquals(422, $response->status(), "Attempt {$i} should be 422");
        }

        $response = $this->postJson('/api/auth/login', [
            'email' => 'any@example.com',
            'password' => 'any',
        ]);

        $this->assertEquals(429, $response->status());
    }

    public function test_rate_limit_per_user_isolation(): void
    {
        $manager1Key = "global:{$this->manager->id}";
        $manager2 = $this->createUser('manager');
        $manager2Key = "global:{$manager2->id}";

        for ($i = 0; $i < 60; $i++) {
            RateLimiter::attempt($manager1Key, 60, fn () => true, 60);
        }

        $this->assertFalse(RateLimiter::attempt($manager1Key, 60, fn () => true, 60));

        $this->assertTrue(RateLimiter::attempt($manager2Key, 60, fn () => true, 60));
    }

    public function test_rate_limit_response_includes_retry_after_header(): void
    {
        $key = "global:{$this->manager->id}";

        for ($i = 0; $i < 60; $i++) {
            RateLimiter::attempt($key, 60, fn () => true, 60);
        }

        $allowed = RateLimiter::attempt($key, 60, fn () => true, 60);
        $this->assertFalse($allowed);

        $availableIn = RateLimiter::availableIn($key);
        $this->assertGreaterThan(0, $availableIn);
    }
}
