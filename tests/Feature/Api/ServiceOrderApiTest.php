<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Core\Enums\ServicesOrdersPriority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class ServiceOrderApiTest extends TestCase
{
    public function test_list_service_orders_requires_auth(): void
    {
        $response = $this->getJson('/api/service-orders');

        $this->assertEquals(401, $response->status());
    }

    public function test_list_service_orders_returns_paginated(): void
    {
        ServiceOrder::factory(3)->create(['manager_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/service-orders');

        $this->assertEquals(200, $response->status());
        $this->assertCount(3, $response->json('data'));
        $this->assertArrayHasKey('current_page', $response->json('meta'));
    }

    public function test_create_service_order_with_valid_data(): void
    {
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $serviceType = ServiceType::factory()->create();

        // Create minimal valid JPEG (GD extension not available)
        $jpeg = "\xFF\xD8\xFF\xE0\x00\x10\x4A\x46\x49\x46\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xDB\x00\x43\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\x09\x09\x08\x0A\x0C\x14\x0D\x0C\x0B\x0B\x0C\x19\x12\x13\x0F\x14\x1D\x1A\x1F\x1E\x1D\x1A\x1C\x1C\x20\x24\x2E\x27\x20\x22\x2C\x23\x1C\x1C\x28\x37\x29\x2C\x30\x31\x34\x34\x34\x1F\x27\x39\x3D\x38\x32\x3C\x2E\x33\x34\x32\xFF\xC0\x00\x0B\x08\x00\x01\x00\x01\x01\x01\x11\x00\xFF\xC4\x00\x1F\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\xFF\xC4\x00\xB5\x10\x00\x02\x01\x03\x03\x02\x04\x03\x05\x05\x04\x04\x00\x00\x00\x00\x00\x00\x01\x02\x03\x11\x04\x12\x21\x31\x01\x06\x12\x51\x61\x07\x13\x22\x71\x14\x32\x81\x91\xA1\x08\x23\x42\xB1\xC1\x15\x52\xD1\xF0\x24\x33\x62\x72\x82\x09\x0A\x16\x17\x18\x19\x1A\x25\x26\x27\x28\x29\x2A\x34\x35\x36\x37\x38\x39\x3A\x43\x44\x45\x46\x47\x48\x49\x4A\x53\x54\x55\x56\x57\x58\x59\x5A\x63\x64\x65\x66\x67\x68\x69\x6A\x73\x74\x75\x76\x77\x78\x79\x7A\x83\x84\x85\x86\x87\x88\x89\x8A\x92\x93\x94\x95\x96\x97\x98\x99\x9A\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFF\xD9";
        $tempPath = tempnam(sys_get_temp_dir(), 'jpg') . '.jpg';
        file_put_contents($tempPath, $jpeg);
        $photo = new UploadedFile($tempPath, 'photo.jpg', 'image/jpeg', null, true);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->post('/api/service-orders', [
                'process' => 'Fix road damage - Rua da Paz',
                'service_type_id' => $serviceType->id,
                'priority' => ServicesOrdersPriority::HIGH->value,
                'photo' => $photo,
                'parish_id' => $parish->id,
                'street' => 'Rua da Paz, nº 123',
                'postal_code' => '6300-000',
                'latitude' => 40.3399,
                'longitude' => -7.2674,
            ], ['Accept' => 'application/json']);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('pending', $response->json('data.status'));
        $this->assertEquals($this->manager->id, $response->json('data.manager.id'));

        $this->assertDatabaseHas('service_orders', [
            'process' => 'Fix road damage - Rua da Paz',
            'manager_id' => $this->manager->id,
        ]);
    }

    public function test_create_service_order_validation_fails(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/service-orders', [
                'priority' => ServicesOrdersPriority::HIGH->value,
            ]);

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('process', $response->json('errors'));
        $this->assertArrayHasKey('photo', $response->json('errors'));
    }

    public function test_get_service_order(): void
    {
        $order = ServiceOrder::factory()->create(['manager_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/service-orders/{$order->id}");

        $this->assertEquals(200, $response->status());
        $this->assertEquals($order->id, $response->json('data.id'));
        $this->assertEquals($order->process, $response->json('data.process'));
    }

    public function test_get_service_order_not_found(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/service-orders/nonexistent-id');

        $this->assertEquals(404, $response->status());
    }

    public function test_update_service_order(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'process' => 'Updated: Fix potholes',
            ]);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Updated: Fix potholes', $response->json('data.process'));
    }

    public function test_cannot_update_completed_service_order(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'process' => 'Trying to update',
            ]);

        $this->assertEquals(422, $response->status());
    }

    public function test_delete_service_order_soft_deletes(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->deleteJson("/api/service-orders/{$order->id}");

        $this->assertEquals(200, $response->status());

        $this->assertSoftDeleted('service_orders', ['id' => $order->id]);
    }

    public function test_complete_service_order(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/complete");

        $this->assertEquals(200, $response->status());
        $this->assertEquals('completed', $response->json('data.status'));
    }

    public function test_cannot_update_others_service_order(): void
    {
        $otherManager = $this->createUser('manager');
        $order = ServiceOrder::factory()->create([
            'manager_id' => $otherManager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'process' => 'Trying to hijack',
            ]);

        $this->assertEquals(403, $response->status());
    }
}
