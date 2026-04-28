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
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return ParishResource::collection($query->orderBy('name')->get());
    }

    public function show(Parish $parish): ParishResource
    {
        $parish->load(['municipality', 'locations']);
        return new ParishResource($parish);
    }
}
