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
}
