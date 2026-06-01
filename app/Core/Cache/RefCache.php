<?php

namespace App\Core\Cache;

use App\Features\Entities\Models\Entity;
use App\Shared\Models\Unit;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Teams\Models\Team;
use App\Shared\Models\Parish;
use Illuminate\Support\Facades\Cache;

class RefCache
{
    public static function sectors(): array
    {
        return Cache::tags(['ref', 'sectors'])->remember('ref:sectors', now()->addHour(), fn() =>
            Sector::orderBy('name')->get(['id', 'name'])
                ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
                ->toArray()
        );
    }

    public static function serviceTypes(): array
    {
        return Cache::tags(['ref', 'service_types'])->remember('ref:service_types', now()->addHour(), fn() =>
            ServiceType::orderBy('name')->get(['id', 'name'])
                ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
                ->toArray()
        );
    }

    public static function parishes(): array
    {
        return Cache::tags(['ref', 'parishes'])->remember('ref:parishes', now()->addHour(), fn() =>
            Parish::orderBy('name')->get(['id', 'name'])
                ->map(fn($p) => ['value' => $p->id, 'label' => $p->name])
                ->toArray()
        );
    }

    public static function teams(): array
    {
        return Cache::tags(['ref', 'teams'])->remember('ref:teams', now()->addHour(), fn() =>
            Team::orderBy('name')->get(['id', 'name'])
                ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
                ->toArray()
        );
    }

    public static function units(): array
    {
        return Cache::tags(['ref', 'units'])->remember('ref:units', now()->addHour(), fn() =>
            Unit::orderBy('name')->get(['id', 'name', 'abbreviation'])
                ->map(fn($u) => ['value' => $u->id, 'label' => $u->name . ' (' . $u->abbreviation . ')'])
                ->toArray()
        );
    }

    public static function entities(): array
    {
        return Cache::tags(['ref', 'entities'])->remember('ref:entities', now()->addHour(), fn() =>
            Entity::orderBy('name')->get(['id', 'name'])
                ->map(fn($e) => ['value' => $e->id, 'label' => $e->name])
                ->toArray()
        );
    }

    public static function flushSectors(): void
    {
        Cache::tags(['ref', 'sectors'])->flush();
    }

    public static function flushServiceTypes(): void
    {
        Cache::tags(['ref', 'service_types'])->flush();
    }

    public static function flushTeams(): void
    {
        Cache::tags(['ref', 'teams'])->flush();
    }

    public static function flushUnits(): void
    {
        Cache::tags(['ref', 'units'])->flush();
    }

    public static function flushEntities(): void
    {
        Cache::tags(['ref', 'entities'])->flush();
    }
}
