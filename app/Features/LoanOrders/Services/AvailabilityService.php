<?php

namespace App\Features\LoanOrders\Services;

use App\Core\Enums\LoanOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // Only check if the pivot table has the date columns
        if (!$this->pivotHasDateColumns()) {
            return true;
        }

        $activeStatuses = [LoanOrderStatus::APPROVED->value, LoanOrderStatus::CHECKED_OUT->value];

        $query = DB::table('equipment_loan_order as elo')
            ->join('loan_orders as lo', 'elo.loan_order_id', '=', 'lo.id')
            ->where('elo.equipment_id', $equipmentId)
            ->whereIn('lo.status', $activeStatuses)
            ->whereNull('lo.deleted_at')
            ->whereNotNull('elo.start_date')
            ->whereNotNull('elo.end_date')
            ->where('elo.start_date', '<=', $endDate)
            ->where('elo.end_date', '>=', $startDate);

        if ($excludeLoanOrderId !== null) {
            $query->where('elo.loan_order_id', '!=', $excludeLoanOrderId);
        }

        return $query->doesntExist();
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
        if (!$this->pivotHasDateColumns()) {
            return [];
        }

        $activeStatuses = [LoanOrderStatus::APPROVED->value, LoanOrderStatus::CHECKED_OUT->value];

        $rows = DB::table('equipment_loan_order as elo')
            ->join('loan_orders as lo', 'elo.loan_order_id', '=', 'lo.id')
            ->where('elo.equipment_id', $equipmentId)
            ->whereIn('lo.status', $activeStatuses)
            ->whereNull('lo.deleted_at')
            ->whereNotNull('elo.start_date')
            ->whereNotNull('elo.end_date')
            ->where('elo.start_date', '<=', $toDate)
            ->where('elo.end_date', '>=', $fromDate)
            ->select(
                'elo.start_date',
                'elo.end_date',
                'lo.reference',
                'lo.id as loan_order_id'
            )
            ->get();

        return $rows->map(fn($row) => [
            'start_date' => $row->start_date,
            'end_date'   => $row->end_date,
            'type'       => 'loan_order',
            'reference'  => $row->reference,
        ])->toArray();
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
        $results = [];
        foreach ($items as $item) {
            $results[$item['equipment_id']] = $this->isAvailable(
                $item['equipment_id'],
                $item['start_date'],
                $item['end_date'],
                $excludeLoanOrderId
            );
        }
        return $results;
    }

    /**
     * Check whether the equipment_loan_order pivot table has date columns.
     * This allows tests running against SQLite migrations to skip the check gracefully.
     */
    private function pivotHasDateColumns(): bool
    {
        return Schema::hasColumn('equipment_loan_order', 'start_date');
    }
}
