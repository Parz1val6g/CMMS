<?php

namespace App\Features\Clients\Controllers\Api;

use App\Features\Clients\Models\Client;
use App\Features\Clients\Requests\StoreClientRequest;
use App\Features\Clients\Requests\UpdateClientRequest;
use App\Features\Clients\Resources\ClientResource;
use App\Features\Clients\Services\ClientService;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private ClientService $clientService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Client::class);

        $query = Client::with(['user']);

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('nif', 'like', $searchTerm)
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])
                                ->orWhere('email', 'like', $searchTerm)
                                ->orWhere('phone', 'like', $searchTerm);
                  });
        }

        // Handle advanced filter rules for user-relationship fields (name, email)
        $this->applyUserRelationshipFilters($request, $query, ['name', 'email']);

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['nif', 'created_at']
        );

        $clients = $query
            ->when($request->filled('sort'), fn($q) => $this->filterService->sort($q, $request->sort))
            ->when(!$request->filled('sort'), fn($q) => $q->latest())
            ->paginate(15);

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): ClientResource
    {
        Gate::authorize('create', Client::class);

        $client = $this->clientService->create($request->validated());
        $client->load(['user', 'clientLocations.location.parish']);

        return new ClientResource($client);
    }

    public function show(Client $client): ClientResource
    {
        Gate::authorize('view', $client);

        $client->load(['user']);

        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        Gate::authorize('update', $client);

        $updated = $this->clientService->update($client, $request->validated());
        $updated->load(['user']);

        return new ClientResource($updated);
    }

    public function destroy(Client $client): JsonResponse
    {
        Gate::authorize('delete', $client);

        $this->clientService->delete($client);

        return response()->json(['message' => 'Client deleted successfully.']);
    }
}
