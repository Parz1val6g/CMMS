<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    public function test_admin_dashboard_shows_active_roles_kpi_as_distinct_roles_in_use(): void
    {
        session()->put('active_role', 'admin');

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $page = $response->inertiaProps();

        $this->assertEquals(3, $page['kpis']['active_roles']['value']);
    }

    public function test_manager_dashboard_renders_with_kpis(): void
    {
        session()->put('active_role', 'manager');

        $response = $this->actingAs($this->manager)
            ->get(route('dashboard'));

        $response->assertOk();
        $page = $response->inertiaProps();

        $this->assertEquals('manager', $page['role']);
        $this->assertArrayHasKey('active_orders', $page['kpis']);
        $this->assertArrayHasKey('open_tickets', $page['kpis']);
        $this->assertArrayHasKey('attention', $page);
    }

    public function test_worker_dashboard_renders_with_kpis(): void
    {
        session()->put('active_role', 'worker');

        $response = $this->actingAs($this->worker)
            ->get(route('dashboard'));

        $response->assertOk();
        $page = $response->inertiaProps();

        $this->assertEquals('worker', $page['role']);
        $this->assertArrayHasKey('pending_mini_tasks', $page['kpis']);
        $this->assertArrayHasKey('open_work_logs', $page['kpis']);
    }
}
