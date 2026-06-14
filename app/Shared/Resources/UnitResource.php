<?php

namespace App\Shared\Resources;

use App\Shared\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Unit $this */
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'abbreviation' => $this->abbreviation,
            'step'         => $this->step,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
