<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Tasks\Models\Task;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class TaskRejectionTest extends TestCase
{
    private function createTask(TaskStatus $status = TaskStatus::AWAITING_APPROVAL): Task
    {
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $location = Location::factory()->create(['parish_id' => $parish->id, 'landmark' => 'Test landmark']);
        $client = Client::factory()->create(['user_id' => $this->manager->id]);
        $serviceType = ServiceType::factory()->create();

        $serviceOrder = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'client_id' => $client->id,
            'location_id' => $location->id,
            'service_type_id' => $serviceType->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        return Task::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => $status->value,
        ]);
    }

    public function test_manager_can_reject_task_in_awaiting_approval(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'The work does not meet quality standards.',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(TaskStatus::IN_PROGRESS->value, $response->json('data.status'));

        $task->refresh();
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->status);

        $this->assertDatabaseHas('task_rejections', [
            'task_id' => $task->id,
            'rejected_by_id' => $this->manager->id,
            'reason' => 'The work does not meet quality standards.',
        ]);
    }

    public function test_admin_can_reject_task(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Admin review requires corrections.',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(TaskStatus::IN_PROGRESS->value, $response->json('data.status'));

        $task->refresh();
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->status);

        $this->assertDatabaseHas('task_rejections', [
            'task_id' => $task->id,
            'rejected_by_id' => $this->admin->id,
            'reason' => 'Admin review requires corrections.',
        ]);
    }

    public function test_reject_requires_authentication(): void
    {
        $task = $this->createTask();

        $response = $this->postJson("/api/tasks/{$task->id}/reject", [
            'reason' => 'Missing auth.',
        ]);

        $response->assertStatus(401);
    }

    public function test_worker_cannot_reject_task(): void
    {
        $task = $this->createTask();

        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Worker trying to reject.',
            ]);

        $response->assertStatus(403);
    }

    public function test_reject_requires_reason(): void
    {
        $task = $this->createTask();

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_reject_fails_if_task_not_in_awaiting_approval(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Trying to reject a pending task.',
            ]);

        $response->assertStatus(422);

        $task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $task->status);
    }

    public function test_rejection_notification_is_sent(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Needs rework.',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $task->manager_id,
            'type' => 'task_rejected',
        ]);
    }

    public function test_rejection_is_recorded_with_correct_rejected_by(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Incomplete documentation.',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_rejections', [
            'task_id' => $task->id,
            'rejected_by_id' => $this->admin->id,
            'reason' => 'Incomplete documentation.',
        ]);
    }

    public function test_manager_can_list_task_rejections(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'First rejection.',
            ]);

        $task->refresh();
        $task->update(['status' => TaskStatus::AWAITING_APPROVAL->value]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/reject", [
                'reason' => 'Second rejection.',
            ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/tasks/{$task->id}/rejections");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['reason' => 'First rejection.']);
        $response->assertJsonFragment(['reason' => 'Second rejection.']);
        $response->assertJsonFragment(['rejected_by' => [
            'id' => $this->manager->id,
            'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
        ]]);
    }

    public function test_rejections_list_empty_when_no_rejections(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/tasks/{$task->id}/rejections");

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }
}
