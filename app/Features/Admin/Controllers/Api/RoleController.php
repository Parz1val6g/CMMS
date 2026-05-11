<?php

namespace App\Features\Admin\Controllers\Api;

use App\Features\Admin\Requests\StoreRoleRequest;
use App\Features\Admin\Requests\UpdateRoleRequest;
use App\Features\Admin\Resources\RoleResource;
use App\Shared\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(private FilterService $filterService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        $query = $this->filterService->apply(
            Role::with(['permissions']),
            $request->only(['search', 'sort']),
            ['name']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'created_at']
        );

        $roles = $query
            ->when($request->filled('sort'), fn($q) => $this->filterService->sort($q, $request->sort))
            ->when(!$request->filled('sort'), fn($q) => $q->orderBy('name'))
            ->paginate(50);
        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): RoleResource
    {
        Gate::authorize('create', Role::class);

        $role = Role::create($request->validated());
        $role->load(['permissions']);
        return new RoleResource($role);
    }

    public function show(Role $role): RoleResource
    {
        Gate::authorize('view', $role);

        $role->load(['permissions']);
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        Gate::authorize('update', $role);

        $role->update($request->validated());
        $role->load(['permissions']);
        return new RoleResource($role->fresh());
    }

    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('delete', $role);

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
