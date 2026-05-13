<?php

namespace Tests\Feature\Migrations;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

class CostPerHourMigrationTest extends PhpUnitTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        // Bootstrap a minimal Laravel app instance
        $this->app = require __DIR__ . '/../../../bootstrap/app.php';
        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        // Use SQLite :memory: for this test
        $this->app->make('config')->set('database.default', 'sqlite');
        $this->app->make('config')->set('database.connections.sqlite.database', ':memory:');

        // Clear any previous DB state
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // Reboot facades
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        $kernel = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->call('migrate:rollback', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        unset($this->app);
    }

    private function createParentTables(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::create('work_logs_workers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_log_id');
            $table->unsignedBigInteger('worker_id');
            $table->timestamps();
        });
        Schema::create('work_log_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_log_id');
            $table->unsignedBigInteger('equipment_id');
            $table->timestamps();
        });
    }

    public function test_migration_adds_cost_per_hour_to_equipments(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $this->assertTrue(Schema::hasColumn('equipments', 'cost_per_hour'));
    }

    public function test_migration_adds_cost_per_hour_to_workers(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $this->assertTrue(Schema::hasColumn('workers', 'cost_per_hour'));
    }

    public function test_migration_adds_cost_per_hour_to_work_logs_workers(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $this->assertTrue(Schema::hasColumn('work_logs_workers', 'cost_per_hour'));
    }

    public function test_migration_adds_cost_per_hour_to_work_log_equipment(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $this->assertTrue(Schema::hasColumn('work_log_equipment', 'cost_per_hour'));
    }

    public function test_migration_creates_cost_histories_table(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $this->assertTrue(Schema::hasTable('cost_histories'));
    }

    public function test_cost_histories_has_expected_columns(): void
    {
        $this->createParentTables();
        Artisan::call('migrate', [
            '--path' => 'database/migrations/cost_per_hour',
            '--realpath' => true,
            '--force' => true,
        ]);
        $columns = Schema::getColumnListing('cost_histories');
        $this->assertContains('entity_type', $columns);
        $this->assertContains('entity_id', $columns);
        $this->assertContains('cost_per_hour', $columns);
        $this->assertContains('changed_by', $columns);
        $this->assertContains('effective_from', $columns);
        $this->assertContains('effective_until', $columns);
    }
}