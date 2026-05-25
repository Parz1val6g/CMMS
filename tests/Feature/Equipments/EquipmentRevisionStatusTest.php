<?php

namespace Tests\Feature\Equipments;

use App\Core\Enums\EquipmentRevisionStatus;
use App\Features\Equipments\Models\EquipmentRevision;
use Tests\TestCase;

class EquipmentRevisionStatusTest extends TestCase
{
    public function test_enum_has_expected_cases_with_labels_and_options(): void
    {
        $cases = EquipmentRevisionStatus::cases();

        $this->assertCount(3, $cases);

        $this->assertEquals('approved', EquipmentRevisionStatus::APPROVED->value);
        $this->assertEquals('pending', EquipmentRevisionStatus::PENDING->value);
        $this->assertEquals('rejected', EquipmentRevisionStatus::REJECTED->value);

        $this->assertNotNull(EquipmentRevisionStatus::APPROVED->label());
        $this->assertNotNull(EquipmentRevisionStatus::PENDING->label());
        $this->assertNotNull(EquipmentRevisionStatus::REJECTED->label());

        $options = EquipmentRevisionStatus::options();
        $this->assertCount(3, $options);
        $this->assertArrayHasKey('value', $options[0]);
        $this->assertArrayHasKey('label', $options[0]);
    }

    public function test_model_casts_status_to_enum(): void
    {
        $revision = EquipmentRevision::factory()->create([
            'status' => 'approved',
        ]);

        $revision->refresh();
        $this->assertInstanceOf(EquipmentRevisionStatus::class, $revision->status);
    }

    public function test_is_approved_returns_true_only_for_approved(): void
    {
        $revision = EquipmentRevision::factory()->create(['status' => 'approved']);
        $revision->refresh();

        $this->assertTrue($revision->isApproved());
        $this->assertFalse($revision->isPending());
        $this->assertFalse($revision->isRejected());
    }

    public function test_is_pending_returns_true_only_for_pending(): void
    {
        $revision = EquipmentRevision::factory()->create(['status' => 'pending']);
        $revision->refresh();

        $this->assertFalse($revision->isApproved());
        $this->assertTrue($revision->isPending());
        $this->assertFalse($revision->isRejected());
    }

    public function test_is_rejected_returns_true_only_for_rejected(): void
    {
        $revision = EquipmentRevision::factory()->create(['status' => 'rejected']);
        $revision->refresh();

        $this->assertFalse($revision->isApproved());
        $this->assertFalse($revision->isPending());
        $this->assertTrue($revision->isRejected());
    }
}
