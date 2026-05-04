<?php

namespace App\Shared\Controllers;

use App\Shared\Models\District;
use App\Shared\Resources\DistrictResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class DistrictController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = District::query();

        if ($search = $request->validate(['search' => 'nullable|string|max:100'])['search'] ?? null) {
            $safe = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where('name', 'like', '%' . $safe . '%');
        }

        return DistrictResource::collection($query->orderBy('name')->get());
    }

    public function show(District $district): DistrictResource
    {
        $district->load(['municipalities']);
        return new DistrictResource($district);
    }
}
