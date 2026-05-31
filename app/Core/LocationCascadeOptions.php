<?php

namespace App\Core;

use App\Shared\Models\District;
use App\Shared\Models\Municipality;
use App\Shared\Models\Parish;

/**
 * Provides cached location hierarchy data for the cascading
 * District → Municipality → Parish selector component.
 *
 * Used by FormSchemas to embed cascade metadata on parish_id fields.
 */
class LocationCascadeOptions
{
    private static ?array $cache = null;

    /**
     * Returns the full location hierarchy data for client-side cascading.
     *
     * Keys: districts, municipalities, parishes
     */
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [
                'districts'      => District::orderBy('name')->get(['id', 'name'])
                    ->map(fn($d) => ['value' => $d->id, 'label' => $d->name])
                    ->toArray(),
                'municipalities' => Municipality::orderBy('name')->get(['id', 'name', 'district_id'])
                    ->map(fn($m) => ['value' => $m->id, 'label' => $m->name, 'district_id' => $m->district_id])
                    ->toArray(),
                'parishes'       => Parish::orderBy('name')->get(['id', 'name', 'municipality_id'])
                    ->map(fn($p) => ['value' => $p->id, 'label' => $p->name, 'municipality_id' => $p->municipality_id])
                    ->toArray(),
            ];
        }

        return self::$cache;
    }
}
