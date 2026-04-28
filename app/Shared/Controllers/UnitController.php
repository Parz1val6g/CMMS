<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Unit;
use App\Shared\Requests\StoreUnitRequest;
use App\Shared\Requests\UpdateUnitRequest;
use App\Shared\Resources\UnitResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class UnitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Unit::class);

        $query = Unit::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return UnitResource::collection($query->orderBy('name')->paginate(15));
    }

    public function store(StoreUnitRequest $request): UnitResource
    {
        $unit = Unit::create($request->validated());
        return new UnitResource($unit);
    }

    public function show(Unit $unit): UnitResource
    {
        $this->authorize('view', $unit);
        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): UnitResource
    {
        $unit->update($request->validated());
        return new UnitResource($unit->fresh());
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $this->authorize('delete', $unit);
        $unit->delete();
        return response()->json(['message' => 'Unit deleted successfully']);
    }
}
