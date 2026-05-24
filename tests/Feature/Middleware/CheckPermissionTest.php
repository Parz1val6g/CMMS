<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Support\Facades\Route;

class CheckPermissionTest extends TestCase
{
    public function test_returns_403_when_user_lacks_permission(): void
    {
        // Worker has no service_orders permissions
        $response = $this->actingAs($this->worker, 'sanctum')
            ->getJson('/api/service-orders');

        $this->assertEquals(403, $response->status());
    }

    public function test_returns_403_on_create_when_user_lacks_create_permission(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson('/api/service-orders', []);

        $this->assertEquals(403, $response->status());
    }

    public function test_returns_500_when_middleware_has_invalid_resource(): void
    {
        Route::middleware(['auth:sanctum', 'permission:invalid_resource,view'])
            ->get('/_test/bad-resource', fn() => response()->json(['ok' => true]));

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/_test/bad-resource');

        $this->assertEquals(500, $response->status());
    }

    public function test_returns_500_when_middleware_has_invalid_action(): void
    {
        Route::middleware(['auth:sanctum', 'permission:service_orders,fly'])
            ->get('/_test/bad-action', fn() => response()->json(['ok' => true]));

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/_test/bad-action');

        $this->assertEquals(500, $response->status());
    }

    public function test_admin_bypasses_permission_check(): void
    {
        // Admin has no explicit permissions seeded but isAdmin() returns true
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/service-orders');

        $this->assertEquals(200, $response->status());
    }

    public function test_authorized_manager_can_list_service_orders(): void
    {
        // Manager has service_orders:view permission via seeder
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/service-orders');

        $this->assertEquals(200, $response->status());
    }

    public function test_authorized_manager_can_create_service_order(): void
    {
        // Manager has service_orders:create permission — validation will fail (no data),
        // but 422 means the middleware passed, which is what we're testing
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/service-orders', []);

        $this->assertNotEquals(403, $response->status());
        $this->assertEquals(422, $response->status());
    }
}
