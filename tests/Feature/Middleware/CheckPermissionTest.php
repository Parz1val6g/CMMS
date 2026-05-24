<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class CheckPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth:sanctum', 'permission:service_orders,view'])
            ->get('/_test/so-view', fn() => response()->json(['ok' => true]));
    }

    public function test_returns_403_when_user_lacks_permission(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->getJson('/_test/so-view');

        $this->assertEquals(403, $response->status());
    }

    public function test_returns_403_on_create_when_user_lacks_create_permission(): void
    {
        Route::middleware(['auth:sanctum', 'permission:service_orders,create'])
            ->post('/_test/so-create', fn() => response()->json(['ok' => true]));

        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson('/_test/so-create', []);

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
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/_test/so-view');

        $this->assertEquals(200, $response->status());
    }

    public function test_authorized_manager_can_access_route(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/_test/so-view');

        $this->assertEquals(200, $response->status());
    }
}
