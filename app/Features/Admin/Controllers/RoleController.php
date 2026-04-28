<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\StoreRoleRequest;
use App\Features\Admin\Requests\UpdateRoleRequest;
use App\Features\Admin\Resources\RoleResource;
use App\Shared\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with(['permissions'])->orderBy('name')->get();
        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): RoleResource
    {
        $role = Role::create($request->validated());
        $role->load(['permissions']);
        return new RoleResource($role);
    }

    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);

        $role->load(['permissions']);
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $role->update($request->validated());
        $role->load(['permissions']);
        return new RoleResource($role->fresh());
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
