<?php

namespace App\Features\Admin\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Admin\Requests\StoreUserRequest;
use App\Features\Authentication\Resources\UserResource;
use App\Shared\Models\User;
use App\Core\Enums\SystemStatus;
use App\Features\Admin\Events\UserCreatedEvent;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            User::with(['roles']),
            $request->only(['search', 'sort']),
            ['first_name', 'last_name', 'email', 'phone', 'status']
        );

        // Search across relationship columns
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('roles', fn($q) => $q
                ->where('name', 'like', "%{$term}%")
            );
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['first_name', 'last_name', 'email', 'phone', 'status', 'created_at']
        );

        $users = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(50);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $data = $request->validated();

        // Internal Creation: Generate a random secure password for new employees/clients
        $data['password'] = Hash::make(Str::random(16));

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => $data['status'],
            'password' => $data['password'],
        ]);

        $user->roles()->attach($data['role_ids']);

        $user->load('roles');
        UserCreatedEvent::dispatch($user);
        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        Gate::authorize('view', $user);
        $user->load('roles');
        return new UserResource($user);
    }

    public function update(Request $request, User $user): UserResource
    {
        Gate::authorize('update', $user);
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:250'],
            'last_name' => ['sometimes', 'string', 'max:250'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'string', 'max:14', 'unique:users,phone,' . $user->id],
            'status' => ['sometimes', Rule::enum(SystemStatus::class)],
            'role_ids' => ['sometimes', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->update($data);

        if (isset($data['role_ids'])) {
            // Role assignment is restricted to admins to prevent privilege escalation
            if (!$request->user()->isAdmin()) {
                abort(403, 'Apenas administradores podem alterar funções de utilizadores.');
            }
            $user->roles()->sync($data['role_ids']);
        }

        $user->load('roles');
        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
