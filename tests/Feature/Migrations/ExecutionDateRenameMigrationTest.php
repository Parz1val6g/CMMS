<?php

namespace Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ExecutionDateRenameMigrationTest extends TestCase
{
    public function test_service_orders_has_end_date_and_start_date_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('service_orders', 'end_date'));
        $this->assertTrue(Schema::hasColumn('service_orders', 'start_date'));
    }

    public function test_service_orders_no_longer_has_execution_date(): void
    {
        $this->assertFalse(Schema::hasColumn('service_orders', 'execution_date'));
    }

    public function test_service_orders_end_date_is_not_nullable(): void
    {
        $notNull = DB::select("SELECT \"notnull\" FROM pragma_table_info('service_orders') WHERE name = 'end_date'");
        $this->assertCount(1, $notNull);
        $this->assertEquals(1, $notNull[0]->notnull);
    }

    public function test_service_orders_start_date_is_not_nullable(): void
    {
        $notNull = DB::select("SELECT \"notnull\" FROM pragma_table_info('service_orders') WHERE name = 'start_date'");
        $this->assertCount(1, $notNull);
        $this->assertEquals(1, $notNull[0]->notnull);
    }

    public function test_tasks_has_start_date_and_end_date_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('tasks', 'start_date'));
        $this->assertTrue(Schema::hasColumn('tasks', 'end_date'));
    }

    public function test_tasks_date_columns_are_nullable(): void
    {
        $startNotNull = DB::select("SELECT \"notnull\" FROM pragma_table_info('tasks') WHERE name = 'start_date'");
        $this->assertCount(1, $startNotNull);
        $this->assertEquals(0, $startNotNull[0]->notnull);

        $endNotNull = DB::select("SELECT \"notnull\" FROM pragma_table_info('tasks') WHERE name = 'end_date'");
        $this->assertCount(1, $endNotNull);
        $this->assertEquals(0, $endNotNull[0]->notnull);
    }
}
