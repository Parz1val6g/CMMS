<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Models\EquipmentType;

class EquipmentApiTest extends TestCase
{
    public function test_create_vehicle_equipment_with_license_plate(): void
    {
        $equipmentType = EquipmentType::factory()->create([
            'name' => 'Viatura Ligeira',
            'category' => 'vehicle',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/equipments', [
                'name' => 'Test Vehicle',
                'equipment_type_id' => $equipmentType->id,
                'license_plate' => 'AA-00-00',
                'cost_per_hour' => 10.50,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Vehicle');
        $response->assertJsonPath('data.license_plate', 'AA-00-00');
        $this->assertDatabaseHas('equipments', [
            'name' => 'Test Vehicle',
            'license_plate' => 'AA-00-00',
        ]);
    }

    public function test_create_general_equipment_with_serial_number(): void
    {
        $equipmentType = EquipmentType::factory()->general()->create([
            'name' => 'Berbequim',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/equipments', [
                'name' => 'Test Drill',
                'equipment_type_id' => $equipmentType->id,
                'serial_number' => 'SN-12345',
                'cost_per_hour' => 5.00,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Drill');
        $response->assertJsonPath('data.serial_number', 'SN-12345');
        $this->assertDatabaseHas('equipments', [
            'name' => 'Test Drill',
            'serial_number' => 'SN-12345',
        ]);
    }

    public function test_create_equipment_requires_auth(): void
    {
        $response = $this->postJson('/api/equipments', [
            'name' => 'Unauthorized',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_equipment_validation_fails(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/equipments', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'equipment_type_id', 'cost_per_hour']);
    }

    public function test_get_equipment(): void
    {
        $equipmentType = EquipmentType::factory()->general()->create();
        $equipment = Equipment::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/equipments/{$equipment->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $equipment->id);
    }

    public function test_get_equipment_not_found(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/equipments/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_update_equipment(): void
    {
        $equipmentType = EquipmentType::factory()->general()->create();
        $equipment = Equipment::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/equipments/{$equipment->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Updated Name');
        $this->assertDatabaseHas('equipments', [
            'id' => $equipment->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_delete_equipment(): void
    {
        $equipmentType = EquipmentType::factory()->general()->create();
        $equipment = Equipment::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/equipments/{$equipment->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($equipment);
    }
}
