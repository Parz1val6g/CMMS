<?php

namespace App\Features\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->whenLoaded('permissions', function() {
                return $this->permissions->map(function ($perm) {
                    return [
                        'id' => $perm->id,
                        'resource' => $perm->resource,
                        'action' => $perm->action,
                    ];
                });
            }),
        ];
    }
}
