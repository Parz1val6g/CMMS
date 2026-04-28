<?php

namespace App\Features\ServiceTypes\Controllers;

use App\Features\ServiceTypes\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ServiceTypePageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceType::class);

        $serviceTypes = ServiceType::latest()
            ->paginate(15)
            ->through(fn ($st) => [
                'id' => $st->id,
                'name' => $st->name,
                'description' => $st->description,
                'created_at' => $st->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('ServiceTypes/Pages/Index', [
            'service_types' => $serviceTypes,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'rules' => 'required|max:250'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'rules' => 'required|max:250'],
            ],
            'routes' => [
                'index' => url('/api/service-types'),
                'store' => url('/api/service-types'),
                'update' => url('/api/service-types/__ID__'),
                'destroy' => url('/api/service-types/__ID__'),
                'show' => url('/api/service-types/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search service types...'],
            ],
        ]);
    }
}
