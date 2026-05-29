<?php

namespace Tests\Feature\ServiceOrders;

use Tests\TestCase;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Support\Facades\Artisan;

class NotifyServiceOrderStartTest extends TestCase
{
    public function test_notifies_manager_when_execution_date_is_today_and_pending_and_not_notified(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'end_date' => today(),
            'status' => ServiceOrderStatus::PENDING->value,
            'start_notified_at' => null,
        ]);

        $exitCode = Artisan::call('app:notify-service-order-start');

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->manager->id,
            'type' => 'service_order_start_notification',
        ]);

        $this->assertNotNull($order->fresh()->start_notified_at);
    }

    public function test_does_not_notify_when_already_notified(): void
    {
        ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'end_date' => today(),
            'status' => ServiceOrderStatus::PENDING->value,
            'start_notified_at' => now()->subDay(),
        ]);

        Artisan::call('app:notify-service-order-start');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->manager->id,
            'type' => 'service_order_start_notification',
        ]);
    }

    public function test_does_not_notify_when_status_is_in_progress(): void
    {
        ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'end_date' => today(),
            'status' => ServiceOrderStatus::IN_PROGRESS->value,
            'start_notified_at' => null,
        ]);

        Artisan::call('app:notify-service-order-start');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->manager->id,
            'type' => 'service_order_start_notification',
        ]);
    }

    public function test_does_not_notify_when_execution_date_is_not_today(): void
    {
        ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'end_date' => today()->addDay(),
            'status' => ServiceOrderStatus::PENDING->value,
            'start_notified_at' => null,
        ]);

        Artisan::call('app:notify-service-order-start');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->manager->id,
            'type' => 'service_order_start_notification',
        ]);
    }
}
