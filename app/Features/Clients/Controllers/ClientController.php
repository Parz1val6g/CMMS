<?php

namespace App\Features\Clients\Controllers;

use App\Features\Clients\Models\Client;
use App\Features\Clients\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::with(['user']);

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('nif', 'like', $searchTerm)
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('first_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                  });
        }

        $clients = $query->latest()->paginate(50);

        return ClientResource::collection($clients);
    }
}
