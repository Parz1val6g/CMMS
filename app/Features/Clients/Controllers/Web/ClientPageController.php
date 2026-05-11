<?php

namespace App\Features\Clients\Controllers\Web;

use App\Features\Clients\ClientFormSchema;
use App\Features\Clients\Models\Client;
use App\Shared\Models\District;
use App\Shared\Models\Municipality;
use App\Shared\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ClientPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Client::class);

        $clients = Client::with(['user'])
            ->latest()
            ->paginate(15)
            ->through(fn ($c) => [
                'id' => $c->id,
                'nif' => $c->nif,
                'first_name' => $c->user?->first_name,
                'last_name' => $c->user?->last_name,
                'name' => $c->user?->first_name . ' ' . $c->user?->last_name,
                'email' => $c->user?->email,
                'phone' => $c->user?->phone,
                'created_at' => $c->created_at->format('Y-m-d'),
            ]);

        $createSchema = ClientFormSchema::create();
        $updateSchema = ClientFormSchema::update();

        return Inertia::render('Clients/Pages/Index', [
            'clients' => $clients,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'phone', 'label' => 'Telefone'],
                ['key' => 'nif', 'label' => 'NIF'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/clients'),
                'store' => url('/api/clients'),
                'update' => url('/api/clients/__ID__'),
                'destroy' => url('/api/clients/__ID__'),
                'show' => url('/api/clients/__ID__'),
            ],
            'districts' => District::orderBy('name')->get(['id', 'name'])
                ->map(fn($d) => ['value' => $d->id, 'label' => $d->name])
                ->toArray(),
            'municipalities' => Municipality::orderBy('name')->get(['id', 'name', 'district_id'])
                ->map(fn($m) => ['value' => $m->id, 'label' => $m->name, 'district_id' => $m->district_id])
                ->toArray(),
            'parishes' => Parish::orderBy('name')->get(['id', 'name', 'municipality_id'])
                ->map(fn($p) => ['value' => $p->id, 'label' => $p->name, 'municipality_id' => $p->municipality_id])
                ->toArray(),
            'advancedFilterFields' => [
                ['value' => 'name',       'label' => 'Nome'],
                ['value' => 'email',      'label' => 'Email'],
                ['value' => 'nif',        'label' => 'NIF'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
