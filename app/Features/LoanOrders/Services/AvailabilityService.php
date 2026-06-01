<?php

namespace App\Features\LoanOrders\Services;

use App\Core\Enums\LoanOrderStatus;
use App\Features\LoanOrders\Models\LoanOrder;

class AvailabilityService
{
    /**
     * Returns true if the equipment has NO overlapping bookings in the given period.
     * Overlap condition: existing.start_date <= $endDate AND existing.end_date >= $startDate
     *
     * @param string      $equipmentId
     * @param string      $startDate           YYYY-MM-DD
     * @param string      $endDate             YYYY-MM-DD
     * @param string|null $excludeLoanOrderId  Skip this loan order when checking (for updates)
     */
    public function isAvailable(
        string $equipmentId,
        string $startDate,
        string $endDate,
        ?string $excludeLoanOrderId = null
    ): bool {
        $activeStatuses = [LoanOrderStatus::APPROVED->value, LoanOrderStatus::CHECKED_OUT->value];

        return LoanOrder::whereIn('status', $activeStatuses)
            ->when($excludeLoanOrderId, fn($q, $id) => $q->where('id', '!=', $id))
            ->whereHas('equipments', fn($q) => $q
                ->where('equipments.id', $equipmentId)
                ->wherePivot('start_date', '<=', $endDate)
                ->wherePivot('end_date', '>=', $startDate)
            )
            ->doesntExist();
    }

    /**
     * Returns array of occupied date ranges for the equipment in the given period.
     *
     * @param string $equipmentId
     * @param string $fromDate    YYYY-MM-DD
     * @param string $toDate      YYYY-MM-DD
     * @return array<array{start_date: string, end_date: string, type: string, reference: string}>
     */
    public function getOccupiedRanges(string $equipmentId, string $fromDate, string $toDate): array
    {
        $activeStatuses = [LoanOrderStatus::APPROVED->value, LoanOrderStatus::CHECKED_OUT->value];

        $loans = LoanOrder::whereIn('status', $activeStatuses)
            ->whereHas('equipments', fn($q) => $q
                ->where('equipments.id', $equipmentId)
                ->wherePivot('start_date', '<=', $toDate)
                ->wherePivot('end_date', '>=', $fromDate)
            )
            ->with(['equipments' => fn($q) => $q
                ->where('equipments.id', $equipmentId)
                ->withPivot(['start_date', 'end_date'])
            ])
            ->get();

        return $loans->flatMap(fn($loan) => $loan->equipments->map(fn($eq) => [
            'start_date' => $eq->pivot->start_date,
            'end_date'   => $eq->pivot->end_date,
            'type'       => 'loan_order',
            'reference'  => $loan->reference,
        ]))->toArray();
    }

    /**
     * Check availability for multiple equipment items at once.
     *
     * @param array<array{equipment_id: string, start_date: string, end_date: string}> $items
     * @param string|null $excludeLoanOrderId
     * @return array<string, bool>  Keyed by equipment_id
     */
    public function checkBulk(array $items, ?string $excludeLoanOrderId = null): array
    {
        if (empty($items)) {
            return [];
        }

        $equipmentIds = array_unique(array_column($items, 'equipment_id'));
        $activeStatuses = [LoanOrderStatus::APPROVED->value, LoanOrderStatus::CHECKED_OUT->value];

        // Collect all date ranges per equipment_id keyed for fast lookup
        $rangesByEquipment = [];
        foreach ($items as $item) {
            $rangesByEquipment[$item['equipment_id']][] = [
                'start' => $item['start_date'],
                'end'   => $item['end_date'],
            ];
        }

        // Single query: load all conflicting loan orders for all equipment IDs at once
        $conflictingEquipmentIds = LoanOrder::whereIn('status', $activeStatuses)
            ->when($excludeLoanOrderId, fn($q, $id) => $q->where('id', '!=', $id))
            ->whereHas('equipments', fn($q) => $q->whereIn('equipments.id', $equipmentIds))
            ->with(['equipments' => fn($q) => $q
                ->whereIn('equipments.id', $equipmentIds)
                ->withPivot(['start_date', 'end_date'])
            ])
            ->get()
            ->flatMap(function ($loan) use ($rangesByEquipment) {
                return $loan->equipments->filter(function ($eq) use ($rangesByEquipment) {
                    foreach ($rangesByEquipment[$eq->id] ?? [] as $range) {
                        if ($eq->pivot->start_date <= $range['end'] && $eq->pivot->end_date >= $range['start']) {
                            return true;
                        }
                    }
                    return false;
                })->pluck('id');
            })
            ->unique()
            ->flip()
            ->toArray();

        $results = [];
        foreach ($items as $item) {
            $results[$item['equipment_id']] = !isset($conflictingEquipmentIds[$item['equipment_id']]);
        }

        return $results;
    }

}
