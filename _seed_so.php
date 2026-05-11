<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\WorkflowType;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Locations\Models\Location;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
use App\Features\Sectors\Models\Sector;
use App\Features\Equipments\Models\Equipment;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Tasks\Models\Task;
use App\Core\Enums\TaskStatus;
use Illuminate\Support\Str;

echo "Seeding test Service Orders...\n";

$admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
$manager = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
$client = Client::first();
$location = Location::first();
$sectors = Sector::all();
$st = ServiceType::first();
$equipment = Equipment::where('is_loanable', true)->first();

if (!$admin || !$manager || !$client || !$location) {
    die("Missing prerequisite data\n");
}

echo "Admin: {$admin->first_name} {$admin->last_name}\n";
echo "Manager: {$manager->first_name} {$manager->last_name}\n";
echo "Client: {$client->id}\n";
echo "Location: {$location->id}\n";

// SO #1 - Standard, Pending
$so1 = ServiceOrder::create([
    'process'         => 'OS/2026/TEST-001',
    'client_id'       => $client->id,
    'manager_id'      => $manager->id,
    'location_id'     => $location->id,
    'service_type_id' => $st?->id,
    'workflow_type'   => WorkflowType::STANDARD->value,
    'priority'        => Priority::NORMAL->value,
    'status'          => ServiceOrderStatus::PENDING->value,
    'description'     => 'Teste de ordem de serviço padrão - Pendente',
]);
$so1->sectors()->sync([$sectors->first()->id]);
echo "SO #1 created: {$so1->process}\n";

// SO #2 - Standard, In Progress
$so2 = ServiceOrder::create([
    'process'         => 'OS/2026/TEST-002',
    'client_id'       => $client->id,
    'manager_id'      => $manager->id,
    'location_id'     => $location->id,
    'service_type_id' => $st?->id,
    'workflow_type'   => WorkflowType::STANDARD->value,
    'priority'        => Priority::HIGH->value,
    'status'          => ServiceOrderStatus::IN_PROGRESS->value,
    'description'     => 'Teste de ordem de serviço padrão - Em Progresso',
]);
$so2->sectors()->sync([$sectors->first()->id]);
// Create a task
Task::create([
    'service_order_id' => $so2->id,
    'manager_id'       => $manager->id,
    'name'             => 'Tarefa 1 - Inspeção',
    'status'           => TaskStatus::IN_PROGRESS->value,
]);
echo "SO #2 created: {$so2->process}\n";

// SO #3 - Loan, Pending
if ($equipment) {
    $so3 = ServiceOrder::create([
        'process'         => 'OS/2026/TEST-003',
        'client_id'       => $client->id,
        'manager_id'      => $manager->id,
        'location_id'     => $location->id,
        'workflow_type'   => WorkflowType::LOAN->value,
        'priority'        => Priority::URGENT->value,
        'status'          => ServiceOrderStatus::PENDING->value,
        'description'     => 'Teste de ordem de serviço de empréstimo - Pendente',
    ]);
    $so3->equipments()->sync([$equipment->id]);
    echo "SO #3 created: {$so3->process} (loan)\n";
}

// SO #4 - Completed
$so4 = ServiceOrder::create([
    'process'         => 'OS/2026/TEST-004',
    'client_id'       => $client->id,
    'manager_id'      => $manager->id,
    'location_id'     => $location->id,
    'service_type_id' => $st?->id,
    'workflow_type'   => WorkflowType::STANDARD->value,
    'priority'        => Priority::LOW->value,
    'status'          => ServiceOrderStatus::COMPLETED->value,
    'description'     => 'Teste de ordem de serviço padrão - Concluída',
]);
$so4->sectors()->sync([$sectors->first()->id]);
Task::create([
    'service_order_id' => $so4->id,
    'manager_id'       => $manager->id,
    'name'             => 'Tarefa concluída',
    'status'           => TaskStatus::COMPLETED->value,
]);
echo "SO #4 created: {$so4->process}\n";

echo "\n✅ Done! 4 Service Orders created.\n";
