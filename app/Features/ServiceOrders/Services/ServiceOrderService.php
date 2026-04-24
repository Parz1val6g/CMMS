<?php
namespace App\Features\ServiceOrders\Services;
use App\Features\Tasks\Models\Task;
use App\Core\Enums\ServiceOrderStatus; 
use App\Core\Enums\TaskStatus;
use App\Core\Services\TransactionHandler;
use App\Features\ServiceOrders\Events\ServiceOrderCompletedEvent;
use App\Features\ServiceOrders\Models\ServiceOrder;
use InvalidArgumentException;
class ServiceOrderService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}
    
    public function create(array $data, string $managerId): ServiceOrder
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            $serviceOrder = ServiceOrder::create([
                'process' => $data['process'],
                'client_id' => $data['client_id'] ?? null,
                'manager_id' => $managerId,
                'location_id' => $data['location_id'],
                'service_type_id' => $data['service_type_id'] ?? null,
                'priority' => $data['priority'],
                'execution_date' => $data['execution_date'] ?? null,
                'status' => ServiceOrderStatus::PENDING->value,
            ]);
            foreach ($data['tasks'] as $taskData) {
                $task = Task::create([
                    'service_order_id' => $serviceOrder->id,
                    'manager_id' => $managerId, 
                    'name' => $taskData['name'],
                    'status' => TaskStatus::PENDING->value,
                ]);
                $task->sectors()->attach($taskData['sector_id']);
            }
            return $serviceOrder;
        });
    }
    public function update(ServiceOrder $serviceOrder, array $data): ServiceOrder
    {
        if (in_array($serviceOrder->status, [ServiceOrderStatus::COMPLETED->value, 'cancelled'])) {
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
            $serviceOrder->update(['status' => 'cancelled']);
            return $serviceOrder;
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