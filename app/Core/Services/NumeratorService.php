<?php

namespace App\Core\Services;

use App\Shared\Models\Numerator;
use Illuminate\Support\Facades\DB;

class NumeratorService
{
    private const PAD_LENGTH = 8;

    public function next(string $entityTable, ?int $year = null): int
    {
        $year ??= (int) now()->format('Y');

        return DB::transaction(function () use ($entityTable, $year) {
            /** @var Numerator|null $row */
            $row = Numerator::where('entity_table', $entityTable)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $row = Numerator::create([
                    'entity_table' => $entityTable,
                    'year'         => $year,
                    'current_value' => 0,
                ]);
            }

            $row->increment('current_value');
            $row->update(['last_generated' => now()]);

            return $row->current_value;
        });
    }

    public function format(string $initials, string $entityTable, ?int $year = null): string
    {
        $counter = $this->next($entityTable, $year);
        $year ??= (int) now()->format('Y');

        return $initials . $year . str_pad((string) $counter, self::PAD_LENGTH, '0', STR_PAD_LEFT);
    }
}
