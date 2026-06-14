<?php

namespace App\Shared\Controllers;

use App\Shared\Models\District;
use App\Shared\Resources\DistrictResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

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

    public function store(Request $request): DistrictResource
    {
        Gate::authorize('create', District::class);

        $data = $request->validate(['name' => 'required|string|max:100|unique:districts,name']);
        $district = District::create($data);

        return new DistrictResource($district);
    }

    public function show(District $district): DistrictResource
    {
        $district->load(['municipalities']);
        return new DistrictResource($district);
    }

    public function update(Request $request, District $district): DistrictResource
    {
        Gate::authorize('update', $district);

        $data = $request->validate(['name' => 'sometimes|string|max:100|unique:districts,name,' . $district->id]);
        $district->update($data);

        return new DistrictResource($district);
    }

    public function destroy(District $district): JsonResponse
    {
        Gate::authorize('delete', $district);

        $district->delete();

        return response()->json(['message' => 'District deleted successfully.']);
    }
}
