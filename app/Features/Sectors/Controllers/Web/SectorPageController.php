<?php

namespace App\Features\Sectors\Controllers\Web;

use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\SectorFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SectorPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sector::class);

        $sectors = Sector::with(['head'])
            ->latest()
            ->paginate(15)
            ->through(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'head' => $s->head ? [
                    'id' => $s->head->id,
                    'name' => $s->head->first_name . ' ' . $s->head->last_name,
                ] : null,
                'created_at' => $s->created_at->format('Y-m-d'),
            ]);

        $createSchema = SectorFormSchema::create();
        $updateSchema = SectorFormSchema::update();

        return Inertia::render('Sectors/Pages/Index', [
            'sectors' => $sectors,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'head', 'label' => 'Responsável'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/sectors'),
                'store' => url('/api/sectors'),
                'update' => url('/api/sectors/__ID__'),
                'destroy' => url('/api/sectors/__ID__'),
                'show' => url('/api/sectors/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',       'label' => 'Nome'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
