<?php

namespace App\Features\Sectors\Controllers;

use App\Features\Sectors\Models\Sector;
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

        return Inertia::render('Sectors/Pages/Index', [
            'sectors' => $sectors,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'head', 'label' => 'Head'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'head_id', 'label' => 'Head', 'type' => 'select', 'options' => [], 'rules' => 'required'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'head_id', 'label' => 'Head', 'type' => 'select', 'options' => [], 'rules' => 'required'],
            ],
            'routes' => [
                'index' => url('/api/sectors'),
                'store' => url('/api/sectors'),
                'update' => url('/api/sectors/__ID__'),
                'destroy' => url('/api/sectors/__ID__'),
                'show' => url('/api/sectors/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search sectors...'],
            ],
        ]);
    }
}
