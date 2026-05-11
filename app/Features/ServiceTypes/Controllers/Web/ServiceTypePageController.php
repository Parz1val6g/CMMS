<?php

namespace App\Features\ServiceTypes\Controllers\Web;

use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\ServiceTypes\ServiceTypeFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServiceTypePageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceType::class);

        $serviceTypes = ServiceType::latest()
            ->paginate(15)
            ->through(fn ($st) => [
                'id' => $st->id,
                'name' => $st->name,
                'description' => $st->description,
                'created_at' => $st->created_at->format('Y-m-d'),
            ]);

        $createSchema = ServiceTypeFormSchema::create();
        $updateSchema = ServiceTypeFormSchema::update();

        return Inertia::render('ServiceTypes/Pages/Index', [
            'service_types' => $serviceTypes,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'description', 'label' => 'Descrição'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/service-types'),
                'store' => url('/api/service-types'),
                'update' => url('/api/service-types/__ID__'),
                'destroy' => url('/api/service-types/__ID__'),
                'show' => url('/api/service-types/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',        'label' => 'Nome'],
                ['value' => 'description', 'label' => 'Descrição'],
                ['value' => 'created_at',  'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
