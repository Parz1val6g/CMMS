<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;

class ContextSwitchingTest extends TestCase
{
    public function test_select_role_page_loads_for_authenticated_user(): void
    {
        $user = $this->createUser(['manager', 'worker']);

        $response = $this->actingAs($user)
            ->get(route('select-role'));

        $response->assertOk();
    }

    public function test_select_role_page_redirects_unauthenticated(): void
    {
        $response = $this->get(route('select-role'));

        $response->assertRedirect(route('login'));
    }

    public function test_switch_role_with_unassigned_role_returns_403(): void
    {
        $user = $this->createUser('manager');

        $response = $this->actingAs($user)
            ->postJson('/api/auth/switch-role', ['role' => 'admin']);

        $response->assertStatus(403);
    }

    public function test_can_prop_includes_activate_service_order_for_manager(): void
    {
        $user = $this->createUser('manager');
        session()->put('active_role', 'manager');

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $page = $response->inertiaProps();
        $this->assertTrue($page['can']['activateServiceOrder'] ?? false);
    }

    public function test_can_prop_excludes_activate_service_order_for_worker(): void
    {
        $user = $this->createUser(['manager', 'worker']);
        session()->put('active_role', 'worker');

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $page = $response->inertiaProps();
        $this->assertFalse($page['can']['activateServiceOrder'] ?? true);
    }

    public function test_can_prop_includes_new_permissions(): void
    {
        $user = $this->createUser('manager');
        session()->put('active_role', 'manager');

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $page = $response->inertiaProps();
        $this->assertArrayHasKey('can', $page);
        $this->assertArrayHasKey('viewEntities', $page['can']);
        $this->assertArrayHasKey('viewLoanOrders', $page['can']);
        $this->assertArrayHasKey('viewEquipmentTypes', $page['can']);
        $this->assertArrayHasKey('viewCountingTypes', $page['can']);
        $this->assertArrayHasKey('assignWorkers', $page['can']);
        $this->assertArrayHasKey('assignMaterials', $page['can']);
        $this->assertArrayHasKey('assignEquipment', $page['can']);
        $this->assertArrayHasKey('completeTask', $page['can']);
    }
}
