<?php

namespace Tests\Feature\Observers;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

class CostPerHourObserversTest extends PhpUnitTestCase
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

    public function test_equipment_observer_class_exists(): void
    {
        $this->assertTrue(class_exists('App\Features\Equipments\Observers\EquipmentObserver'));
    }

    public function test_worker_observer_class_exists(): void
    {
        $this->assertTrue(class_exists('App\Features\Workers\Observers\WorkerObserver'));
    }

    public function test_equipment_observer_has_updated_method(): void
    {
        $this->assertTrue(method_exists('App\Features\Equipments\Observers\EquipmentObserver', 'updated'));
    }

    public function test_worker_observer_has_updated_method(): void
    {
        $this->assertTrue(method_exists('App\Features\Workers\Observers\WorkerObserver', 'updated'));
    }
}
