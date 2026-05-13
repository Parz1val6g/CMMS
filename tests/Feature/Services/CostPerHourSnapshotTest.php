<?php

namespace Tests\Feature\Services;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

class CostPerHourSnapshotTest extends PhpUnitTestCase
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

    private function getSource(): string
    {
        return file_get_contents(__DIR__ . '/../../../app/Features/WorkLogs/Services/WorkLogService.php');
    }

    public function test_approve_method_loads_workers_and_equipment(): void
    {
        $source = $this->getSource();
        $this->assertStringContainsString(
            "loadMissing('workers', 'equipment')",
            $source,
            'approve() must loadMissing workers and equipment before snapshot'
        );
    }

    public function test_approve_method_snapshots_worker_cost_per_hour(): void
    {
        $source = $this->getSource();
        $this->assertStringContainsString(
            "updateExistingPivot(\$worker->id",
            $source,
            'approve() must snapshot worker cost_per_hour to pivot'
        );
        $this->assertStringContainsString(
            "'cost_per_hour' => \$worker->cost_per_hour",
            $source,
            'approve() must snapshot worker cost_per_hour value'
        );
    }

    public function test_approve_method_snapshots_equipment_cost_per_hour(): void
    {
        $source = $this->getSource();
        $this->assertStringContainsString(
            "updateExistingPivot(\$equipment->id",
            $source,
            'approve() must snapshot equipment cost_per_hour to pivot'
        );
        $this->assertStringContainsString(
            "'cost_per_hour' => \$equipment->cost_per_hour",
            $source,
            'approve() must snapshot equipment cost_per_hour value'
        );
    }

    public function test_complete_method_does_not_snapshot_cost_per_hour(): void
    {
        $source = $this->getSource();
        $this->assertStringNotContainsString(
            'updateExistingPivot',
            $this->extractMethod($source, 'complete'),
            'complete() must NOT snapshot cost_per_hour to pivots'
        );
    }

    private function extractMethod(string $source, string $methodName): string
    {
        $lines = explode("\n", $source);
        $inMethod = false;
        $braceCount = 0;
        $result = [];

        foreach ($lines as $line) {
            if (!$inMethod && preg_match('/function\s+' . $methodName . '\s*\(/', $line)) {
                $inMethod = true;
                $braceCount = 0;
                $result = [];
            }
            if ($inMethod) {
                $result[] = $line;
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');
                if ($braceCount === 0) {
                    break;
                }
            }
        }

        return implode("\n", $result);
    }
}
