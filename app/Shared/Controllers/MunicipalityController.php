<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Municipality;
use App\Shared\Resources\MunicipalityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

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

    public function store(Request $request): MunicipalityResource
    {
        Gate::authorize('create', Municipality::class);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'district_id' => 'required|uuid|exists:districts,id',
        ]);
        $municipality = Municipality::create($data);

        return new MunicipalityResource($municipality);
    }

    public function show(Municipality $municipality): MunicipalityResource
    {
        $municipality->load(['district', 'parishes']);
        return new MunicipalityResource($municipality);
    }

    public function update(Request $request, Municipality $municipality): MunicipalityResource
    {
        Gate::authorize('update', $municipality);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'district_id' => 'sometimes|uuid|exists:districts,id',
        ]);
        $municipality->update($data);

        return new MunicipalityResource($municipality);
    }

    public function destroy(Municipality $municipality): JsonResponse
    {
        Gate::authorize('delete', $municipality);

        $municipality->delete();

        return response()->json(['message' => 'Municipality deleted successfully.']);
    }
}
