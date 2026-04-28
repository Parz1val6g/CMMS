<?php

namespace App\Features\Clients\Controllers;

use App\Features\Clients\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ClientPageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::with(['user'])
            ->latest()
            ->paginate(15)
            ->through(fn ($c) => [
                'id' => $c->id,
                'nif' => $c->nif,
                'name' => $c->user?->first_name . ' ' . $c->user?->last_name,
                'email' => $c->user?->email,
                'phone' => $c->user?->phone,
                'created_at' => $c->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Clients/Pages/Index', [
            'clients' => $clients,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'nif', 'label' => 'NIF'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'nif', 'label' => 'NIF', 'type' => 'text', 'rules' => 'required|max:20'],
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ],
            'createFormSchema' => [
                ['key' => 'nif', 'label' => 'NIF', 'type' => 'text', 'rules' => 'required|max:20'],
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ],
            'routes' => [
                'index' => url('/api/clients'),
                'store' => url('/api/clients'),
                'update' => url('/api/clients/__ID__'),
                'destroy' => url('/api/clients/__ID__'),
                'show' => url('/api/clients/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search by name, email or NIF...'],
            ],
        ]);
    }
}
