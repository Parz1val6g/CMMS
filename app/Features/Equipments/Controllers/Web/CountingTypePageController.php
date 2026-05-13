<?php

namespace App\Features\Equipments\Controllers\Web;

use App\Features\Equipments\Models\CountingType;
use App\Features\Equipments\CountingTypeFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CountingTypePageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', CountingType::class);

        $countingTypes = CountingType::latest()
            ->paginate(15)
            ->through(fn ($ct) => [
                'id' => $ct->id,
                'name' => $ct->name,
                'value' => $ct->value,
                'active' => $ct->active,
                'created_at' => $ct->created_at->format('Y-m-d'),
            ]);

        $createSchema = CountingTypeFormSchema::create();
        $updateSchema = CountingTypeFormSchema::update();

        return Inertia::render('Equipments/Pages/CountingTypesIndex', [
            'counting_types' => $countingTypes,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'value', 'label' => 'Valor'],
                ['key' => 'active', 'label' => 'Ativo'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/counting-types'),
                'store' => url('/api/counting-types'),
                'update' => url('/api/counting-types/__ID__'),
                'destroy' => url('/api/counting-types/__ID__'),
                'show' => url('/api/counting-types/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',       'label' => 'Nome'],
                ['value' => 'value',      'label' => 'Valor'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
