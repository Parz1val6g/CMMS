<?php

namespace App\Features\Entities\Controllers\Api;

use App\Features\Entities\Models\Entity;
use App\Features\Entities\Requests\StoreEntityRequest;
use App\Features\Entities\Requests\UpdateEntityRequest;
use App\Features\Entities\Resources\EntityResource;
use App\Features\Entities\Services\EntityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EntityController extends Controller
{
    public function __construct(
        private EntityService $entityService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Entity::class);

        $entities = Entity::with(['user', 'location'])
            ->withCount('loanOrders')
            ->latest()
            ->paginate(15);

        return EntityResource::collection($entities);
    }

    public function store(StoreEntityRequest $request): JsonResponse
    {
        Gate::authorize('create', Entity::class);

        $data = array_merge($request->validated(), [
            'user_id' => $request->user()->id,
        ]);
        $entity = $this->entityService->create($data);
        $entity->load(['user', 'location']);

        return (new EntityResource($entity))->response()->setStatusCode(201);
    }

    public function show(string $id): EntityResource
    {
        $entity = Entity::with(['user', 'location'])->findOrFail($id);
        Gate::authorize('view', $entity);

        return new EntityResource($entity);
    }

    public function update(UpdateEntityRequest $request, Entity $entity): EntityResource
    {
        Gate::authorize('update', $entity);

        $updated = $this->entityService->update($entity, $request->validated());
        $updated->load(['user', 'location']);

        return new EntityResource($updated);
    }

    public function destroy(Request $request, Entity $entity): JsonResponse
    {
        Gate::authorize('delete', $entity);
        $this->entityService->delete($entity);

        return response()->json(['message' => 'Entity deleted successfully.']);
    }
}
