<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Parish;
use App\Shared\Resources\ParishResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class ParishController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Parish::query();

        if ($request->has('municipality_id')) {
            $request->validate(['municipality_id' => 'nullable|integer|exists:municipalities,id']);
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($search = $request->validate(['search' => 'nullable|string|max:100'])['search'] ?? null) {
            $safe = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where('name', 'like', '%' . $safe . '%');
        }

        return ParishResource::collection($query->orderBy('name')->get());
    }

    public function store(Request $request): ParishResource
    {
        Gate::authorize('create', Parish::class);

        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'municipality_id' => 'required|uuid|exists:municipalities,id',
        ]);
        $parish = Parish::create($data);

        return new ParishResource($parish);
    }

    public function show(Parish $parish): ParishResource
    {
        $parish->load(['municipality', 'locations']);
        return new ParishResource($parish);
    }

    public function update(Request $request, Parish $parish): ParishResource
    {
        Gate::authorize('update', $parish);

        $data = $request->validate([
            'name'            => 'sometimes|string|max:100',
            'municipality_id' => 'sometimes|uuid|exists:municipalities,id',
        ]);
        $parish->update($data);

        return new ParishResource($parish);
    }

    public function destroy(Parish $parish): JsonResponse
    {
        Gate::authorize('delete', $parish);

        $parish->delete();

        return response()->json(['message' => 'Parish deleted successfully.']);
    }
}
