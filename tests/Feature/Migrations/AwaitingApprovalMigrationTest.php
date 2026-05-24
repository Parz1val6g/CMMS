<?php

namespace Tests\Feature\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AwaitingApprovalMigrationTest extends TestCase
{
    public function test_service_orders_has_start_notified_at_column(): void
    {
        $this->assertTrue(Schema::hasColumn('service_orders', 'start_notified_at'));
    }

    public function test_start_notified_at_is_nullable(): void
    {
        $column = collect(Schema::getColumns('service_orders'))
            ->firstWhere('name', 'start_notified_at');

        $this->assertNotNull($column);
        $this->assertTrue($column['nullable']);
    }

    public function test_task_rejections_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('task_rejections'));
    }

    public function test_task_rejections_has_expected_columns(): void
    {
        $columns = Schema::getColumnListing('task_rejections');

        $this->assertContains('id', $columns);
        $this->assertContains('task_id', $columns);
        $this->assertContains('rejected_by_id', $columns);
        $this->assertContains('reason', $columns);
        $this->assertContains('created_at', $columns);
    }

    public function test_task_rejections_has_no_updated_at(): void
    {
        $this->assertFalse(Schema::hasColumn('task_rejections', 'updated_at'));
    }
}
