<?php

namespace Tests\Feature\FormSchemas;

use PHPUnit\Framework\TestCase;

class CostPerHourFormSchemaTest extends TestCase
{
    private function getEquipmentSource(): string
    {
        return file_get_contents(__DIR__ . '/../../../app/Features/Equipments/EquipmentFormSchema.php');
    }

    private function getWorkerSource(): string
    {
        return file_get_contents(__DIR__ . '/../../../app/Features/Workers/WorkerFormSchema.php');
    }

    public function test_equipment_create_has_cost_per_hour_field(): void
    {
        $source = $this->getEquipmentSource();
        $this->assertStringContainsString(
            "NumberInput::make('cost_per_hour')",
            $source,
            'Equipment create form must have cost_per_hour NumberInput'
        );
    }

    public function test_equipment_create_has_cost_per_hour_label(): void
    {
        $source = $this->getEquipmentSource();
        $this->assertStringContainsString(
            "forms.equipments.cost_per_hour",
            $source,
            'Equipment form must reference cost_per_hour translation key'
        );
    }

    public function test_equipment_create_has_cost_per_hour_rules(): void
    {
        $source = $this->getEquipmentSource();
        $this->assertStringContainsString(
            "required|numeric|min:0|max:9999.99",
            $source,
            'Equipment cost_per_hour must have required|numeric|min:0|max:9999.99 rules'
        );
    }

    public function test_equipment_update_has_cost_per_hour_field(): void
    {
        $source = $this->getEquipmentSource();
        $this->assertStringContainsString(
            "NumberInput::make('cost_per_hour')",
            $source,
            'Equipment update form must have cost_per_hour NumberInput'
        );
    }

    public function test_worker_create_has_cost_per_hour_field(): void
    {
        $source = $this->getWorkerSource();
        $this->assertStringContainsString(
            "NumberInput::make('cost_per_hour')",
            $source,
            'Worker create form must have cost_per_hour NumberInput'
        );
    }

    public function test_worker_create_has_cost_per_hour_label(): void
    {
        $source = $this->getWorkerSource();
        $this->assertStringContainsString(
            "forms.workers.cost_per_hour",
            $source,
            'Worker form must reference cost_per_hour translation key'
        );
    }

    public function test_worker_create_has_cost_per_hour_rules(): void
    {
        $source = $this->getWorkerSource();
        $this->assertStringContainsString(
            "required|numeric|min:0|max:9999.99",
            $source,
            'Worker cost_per_hour must have required|numeric|min:0|max:9999.99 rules'
        );
    }

    public function test_worker_update_has_cost_per_hour_field(): void
    {
        $source = $this->getWorkerSource();
        $this->assertStringContainsString(
            "NumberInput::make('cost_per_hour')",
            $source,
            'Worker update form must have cost_per_hour NumberInput'
        );
    }
}
