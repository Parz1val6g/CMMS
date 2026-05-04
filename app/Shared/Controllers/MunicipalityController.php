<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Municipality;
use App\Shared\Resources\MunicipalityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class MunicipalityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Municipality::query();

        if ($request->has('district_id')) {
            $request->validate(['district_id' => 'nullable|integer|exists:districts,id']);
            $query->where('district_id', $request->district_id);
        }

        if ($search = $request->validate(['search' => 'nullable|string|max:100'])['search'] ?? null) {
            $safe = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where('name', 'like', '%' . $safe . '%');
        }

        return MunicipalityResource::collection($query->orderBy('name')->get());
    }

    public function show(Municipality $municipality): MunicipalityResource
    {
        $municipality->load(['district', 'parishes']);
        return new MunicipalityResource($municipality);
    }
}
