<?php
namespace App\Features\ServiceOrders\Services;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Exceptions\EquipmentUnavailableException;
use App\Exceptions\TaskCreationException;
use App\Features\Equipments\Models\Equipment;
use App\Features\Locations\Models\Location;
use App\Features\ServiceOrders\Events\ServiceOrderCompletedEvent;
use App\Features\ServiceOrders\Events\ServiceOrderCreatedEvent;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\Resources\TaskResource;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
class ServiceOrderService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}
    
    public function create(array $data, string $managerId): ServiceOrder
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            $isLoan = ($data['workflow_type'] ?? WorkflowType::STANDARD->value) === WorkflowType::LOAN->value;

            // 1. Create Location only for STANDARD workflow
            $locationId = null;
            if (!$isLoan && !empty($data['parish_id'])) {
                $location = Location::create([
                    'parish_id' => $data['parish_id'],
                    'postal_code' => $data['postal_code'] ?? '',
                    'street_address' => InputSanitizer::sanitize($data['street'] ?? ''),
                    'landmark' => isset($data['reference_point']) ? InputSanitizer::sanitize($data['reference_point']) : '',
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                ]);
                $locationId = $location->id;
            }

            // 2. Handle photo upload
            $photoPath = null;
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $photoPath = $data['photo']->store('service-orders', 'public');
            }

            // 3. Validate equipment availability for LOAN
            if ($isLoan) {
                $equipment = Equipment::findOrFail($data['equipment_id']);
                if (!$equipment->isAvailableForLoan()) {
                    throw new EquipmentUnavailableException(
                        'Equipment is not available for loan. It must be active and loanable.'
                    );
                }
            }

            // 4. Create ServiceOrder
            $serviceOrder = ServiceOrder::create([
                'process' => InputSanitizer::sanitize($data['process']),
                'client_id' => $data['client_id'] ?? null,
                'manager_id' => $managerId,
                'location_id' => $locationId,
                'service_type_id' => $data['service_type_id'] ?? null,
                'workflow_type' => $data['workflow_type'] ?? WorkflowType::STANDARD->value,
                'equipment_id' => $data['equipment_id'] ?? null,
                'priority' => $data['priority'] ?? null,
                'photo_path' => $photoPath,
                'status' => ServiceOrderStatus::PENDING->value,
            ]);
            ServiceOrderCreatedEvent::dispatch($serviceOrder);

            return $serviceOrder;
        });
    }
    public function update(ServiceOrder $serviceOrder, array $data): ServiceOrder
    {
        if (in_array($serviceOrder->status, [ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::CANCELLED->value])) {
            throw new InvalidArgumentException('Cannot update a completed or cancelled service order.');
        }
        return $this->transactions->execute(function () use ($serviceOrder, $data) {
            $serviceOrder->update($data);
            return $serviceOrder;
        });
    }
    public function cancel(ServiceOrder $serviceOrder): ServiceOrder
    {
        if ($serviceOrder->status === ServiceOrderStatus::COMPLETED->value) {
            throw new InvalidArgumentException('Cannot cancel an already completed service order.');
        }
        return $this->transactions->execute(function () use ($serviceOrder) {
            $serviceOrder->update(['status' => ServiceOrderStatus::CANCELLED->value]);
            return $serviceOrder;
        });
    }
    public function initiateReturn(ServiceOrder $serviceOrder): Task
    {
        return $this->transactions->execute(function () use ($serviceOrder) {
            abort_if($serviceOrder->workflow_type !== WorkflowType::LOAN->value, 400, 'Initiate return is only valid for loan workflows.');

            abort_if($serviceOrder->tasks()->where('name', 'Devolução de Equipamento')->exists(), 409, 'Return task already exists.');

            $checkoutTask = $serviceOrder->tasks()->where('name', 'Empréstimo de Equipamento')->first();
            abort_if(!$checkoutTask || $checkoutTask->status !== TaskStatus::COMPLETED->value, 400, 'Equipment checkout task must be completed before initiating return.');

            $task = Task::create([
                'service_order_id' => $serviceOrder->id,
                'manager_id'       => $serviceOrder->manager_id,
                'name'             => 'Devolução de Equipamento',
                'status'           => TaskStatus::PENDING->value,
            ]);

            $task->load(['sectors', 'manager']);
            return $task;
        });
    }

    public function complete(ServiceOrder $serviceOrder): ServiceOrder
    {
        if ($serviceOrder->status === ServiceOrderStatus::COMPLETED->value) {
            throw new InvalidArgumentException('This service order is already completed.');
        }
        $hasIncompleteTasks = $serviceOrder->tasks()
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->exists();
        if ($hasIncompleteTasks) {
            throw new InvalidArgumentException('Manager Approval Denied: Not all tasks are completed yet.');
        }
        return $this->transactions->execute(function () use ($serviceOrder) {
            $serviceOrder->update(['status' => ServiceOrderStatus::COMPLETED->value]);
            ServiceOrderCompletedEvent::dispatch($serviceOrder);
            return $serviceOrder;
        });
    }
}