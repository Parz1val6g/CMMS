<?php

namespace Tests\Feature\LoanOrders;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\WorkflowType;
use App\Features\Clients\Models\Client;
use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanOrderMigrationTest extends TestCase
{
    private Client $client;
    private Entity $entity;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->manager is provided by TestCase::setUp().
        // Override so factories pick up a clean reference.
        $this->manager = User::factory()->create();
        $managerRole = \App\Shared\Models\Role::where('name', 'manager')->first();
        if ($managerRole) {
            $this->manager->roles()->attach($managerRole);
        }

        // The drop-column + drop-pivot migration runs before this test.
        // Recreate legacy infrastructure so we can set up test data.
        if (!Schema::hasColumn('service_orders', 'workflow_type')) {
            Schema::table('service_orders', function ($table) {
                $table->string('workflow_type', 50)->nullable()->after('service_type_id');
            });
        }
        if (!Schema::hasTable('equipment_service_order')) {
            Schema::create('equipment_service_order', function ($table) {
                $table->foreignUuid('equipment_id')->constrained('equipments')->cascadeOnDelete();
                $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
                $table->primary(['equipment_id', 'service_order_id']);
                $table->timestamps();
            });
        }

        $clientUser = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $clientUser->id]);
        $this->entity = Entity::factory()->create();
    }

    public function test_import_creates_loan_orders(): void
    {
        $equipment1 = Equipment::factory()->loanable()->active()->create();
        $equipment2 = Equipment::factory()->loanable()->active()->create();

        $so1 = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
            'description'   => 'Loan SO 1',
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment1->id, 'service_order_id' => $so1->id],
        ]);

        $task1 = Task::factory()->pending()->create([
            'service_order_id' => $so1->id,
            'manager_id'       => $this->manager->id,
        ]);

        $so2 = ServiceOrder::factory()->completed()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
            'description'   => 'Loan SO 2',
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment2->id, 'service_order_id' => $so2->id],
        ]);

        $task2 = Task::factory()->completed()->create([
            'service_order_id' => $so2->id,
            'manager_id'       => $this->manager->id,
        ]);

        // Act
        $exitCode = Artisan::call('loan-orders:import-existing');

        // Assert
        $this->assertEquals(0, $exitCode);

        $loanOrders = LoanOrder::whereIn('migrated_from_so_id', [$so1->id, $so2->id])->get();
        $this->assertCount(2, $loanOrders);

        $loan1 = $loanOrders->firstWhere('migrated_from_so_id', $so1->id);
        $this->assertEquals(LoanOrderStatus::PENDING->value, $loan1->status->value);
        $this->assertEquals($so1->description, $loan1->description);
        $this->assertEquals($this->manager->id, $loan1->manager_id);
        $this->assertCount(1, $loan1->equipments);
        $this->assertEquals($equipment1->id, $loan1->equipments->first()->id);
        $this->assertCount(1, $loan1->tasks);
        $this->assertEquals($task1->id, $loan1->tasks->first()->id);

        $loan2 = $loanOrders->firstWhere('migrated_from_so_id', $so2->id);
        $this->assertEquals(LoanOrderStatus::CHECKED_OUT->value, $loan2->status->value);

        // Assert migrated_to_loan_id set on originals
        $so1->refresh();
        $so2->refresh();
        $this->assertEquals($loan1->id, $so1->migrated_to_loan_id);
        $this->assertEquals($loan2->id, $so2->migrated_to_loan_id);

        // Assert tasks reassigned
        $task1->refresh();
        $this->assertNull($task1->service_order_id);
        $this->assertEquals($loan1->id, $task1->taskable_id);
        $this->assertEquals(LoanOrder::class, $task1->taskable_type);
    }

    public function test_import_is_idempotent(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $so = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment->id, 'service_order_id' => $so->id],
        ]);

        // First run
        Artisan::call('loan-orders:import-existing');
        $this->assertCount(1, LoanOrder::where('migrated_from_so_id', $so->id)->get());

        // Second run — idempotent
        $exitCode = Artisan::call('loan-orders:import-existing');
        $this->assertEquals(0, $exitCode);
        $this->assertCount(1, LoanOrder::where('migrated_from_so_id', $so->id)->get());
    }

    public function test_import_skips_non_loan_service_orders(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $regularSO = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::STANDARD->value,
        ]);
        $loanSO = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment->id, 'service_order_id' => $loanSO->id],
        ]);

        Artisan::call('loan-orders:import-existing');

        $this->assertCount(0, LoanOrder::where('migrated_from_so_id', $regularSO->id)->get());
        $this->assertCount(1, LoanOrder::where('migrated_from_so_id', $loanSO->id)->get());
    }

    public function test_import_handles_edge_case_no_equipment(): void
    {
        $so = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
        ]);

        $exitCode = Artisan::call('loan-orders:import-existing');

        $this->assertEquals(0, $exitCode);
        $loanOrder = LoanOrder::where('migrated_from_so_id', $so->id)->first();
        $this->assertNotNull($loanOrder);
        $this->assertCount(0, $loanOrder->equipments);
    }

    public function test_dry_run_does_not_write(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $so = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment->id, 'service_order_id' => $so->id],
        ]);

        Artisan::call('loan-orders:import-existing', ['--dry-run' => true]);

        $this->assertCount(0, LoanOrder::where('migrated_from_so_id', $so->id)->get());
        $so->refresh();
        $this->assertNull($so->migrated_to_loan_id);
    }

    public function test_import_reports_summary(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $so = ServiceOrder::factory()->pending()->create([
            'manager_id'    => $this->manager->id,
            'client_id'     => $this->client->id,
            'workflow_type' => WorkflowType::LOAN->value,
        ]);
        DB::table('equipment_service_order')->insert([
            ['equipment_id' => $equipment->id, 'service_order_id' => $so->id],
        ]);

        $exitCode = Artisan::call('loan-orders:import-existing');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('imported', $output);
    }
}
