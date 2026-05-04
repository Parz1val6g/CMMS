<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Parish;
use App\Shared\Resources\ParishResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

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

    public function show(Parish $parish): ParishResource
    {
        $parish->load(['municipality', 'locations']);
        return new ParishResource($parish);
    }
}
