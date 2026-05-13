<?php

namespace Tests\Feature\Models;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

class CostPerHourModelsTest extends PhpUnitTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = require __DIR__ . '/../../../bootstrap/app.php';
        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        $this->app->make('config')->set('database.default', 'sqlite');
        $this->app->make('config')->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        unset($this->app);
    }

    public function test_equipment_fillable_contains_cost_per_hour(): void
    {
        $equipment = new \App\Features\Equipments\Models\Equipment();
        $this->assertContains('cost_per_hour', $equipment->getFillable());
    }

    public function test_equipment_has_decimal_cast_for_cost_per_hour(): void
    {
        $equipment = new \App\Features\Equipments\Models\Equipment();
        $casts = $equipment->getCasts();
        $this->assertArrayHasKey('cost_per_hour', $casts);
        $this->assertEquals('decimal:2', $casts['cost_per_hour']);
    }

    public function test_worker_fillable_contains_cost_per_hour(): void
    {
        $worker = new \App\Features\Workers\Models\Worker();
        $this->assertContains('cost_per_hour', $worker->getFillable());
    }

    public function test_worker_has_decimal_cast_for_cost_per_hour(): void
    {
        $worker = new \App\Features\Workers\Models\Worker();
        $casts = $worker->getCasts();
        $this->assertArrayHasKey('cost_per_hour', $casts);
        $this->assertEquals('decimal:2', $casts['cost_per_hour']);
    }

    public function test_work_log_workers_relation_has_cost_per_hour_pivot(): void
    {
        $workLog = new \App\Features\WorkLogs\Models\WorkLog();
        $relation = $workLog->workers();
        $pivotColumns = $relation->getPivotColumns();
        $this->assertContains('cost_per_hour', $pivotColumns);
    }

    public function test_work_log_equipment_relation_has_cost_per_hour_pivot(): void
    {
        $workLog = new \App\Features\WorkLogs\Models\WorkLog();
        $relation = $workLog->equipment();
        $pivotColumns = $relation->getPivotColumns();
        $this->assertContains('cost_per_hour', $pivotColumns);
    }

    public function test_cost_history_class_exists(): void
    {
        $this->assertTrue(class_exists('App\Shared\Models\CostHistory'));
    }

    public function test_cost_history_has_expected_fillable(): void
    {
        $model = new \App\Shared\Models\CostHistory();
        $fillable = $model->getFillable();
        $this->assertContains('entity_type', $fillable);
        $this->assertContains('entity_id', $fillable);
        $this->assertContains('cost_per_hour', $fillable);
        $this->assertContains('changed_by', $fillable);
        $this->assertContains('effective_from', $fillable);
        $this->assertContains('effective_until', $fillable);
    }

    public function test_cost_history_has_expected_casts(): void
    {
        $model = new \App\Shared\Models\CostHistory();
        $casts = $model->getCasts();
        $this->assertEquals('decimal:2', $casts['cost_per_hour']);
        $this->assertEquals('datetime', $casts['effective_from']);
        $this->assertEquals('datetime', $casts['effective_until']);
    }

    public function test_cost_history_has_entity_morph_relation(): void
    {
        $model = new \App\Shared\Models\CostHistory();
        $this->assertTrue(method_exists($model, 'entity'));
    }

    public function test_cost_history_has_active_scope(): void
    {
        $model = new \App\Shared\Models\CostHistory();
        $this->assertTrue(method_exists($model, 'scopeActive'));
    }

    public function test_cost_history_has_effective_at_scope(): void
    {
        $model = new \App\Shared\Models\CostHistory();
        $this->assertTrue(method_exists($model, 'scopeEffectiveAt'));
    }
}
