<?php

namespace App\Features\Clients\Controllers\Api;

use App\Features\Clients\Models\Client;
use App\Features\Clients\Models\ClientLocation;
use App\Features\Clients\Requests\StoreClientLocationRequest;
use App\Features\Clients\Requests\UpdateClientLocationRequest;
use App\Features\Clients\Resources\ClientLocationResource;
use App\Features\Clients\Services\ClientLocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientLocationController extends Controller
{
    public function __construct(private ClientLocationService $clientLocationService) {}

    public function index(Client $client): AnonymousResourceCollection
    {
        Gate::authorize('view', $client);

        $locations = $client->clientLocations()->with('location.parish')->get();

        return ClientLocationResource::collection($locations);
    }

    public function store(StoreClientLocationRequest $request, Client $client): ClientLocationResource
    {
        Gate::authorize('update', $client);

        $clientLocation = $this->clientLocationService->create($client, $request->validated());
        $clientLocation->load('location.parish');

        return new ClientLocationResource($clientLocation);
    }

    public function update(UpdateClientLocationRequest $request, Client $client, ClientLocation $clientLocation): ClientLocationResource
    {
        Gate::authorize('update', $client);

        $updated = $this->clientLocationService->update($clientLocation, $request->validated());

        return new ClientLocationResource($updated);
    }

    public function destroy(Client $client, ClientLocation $clientLocation): JsonResponse
    {
        Gate::authorize('update', $client);

        $this->clientLocationService->delete($clientLocation);

        return response()->json(['message' => 'Location removed.']);
    }
}
