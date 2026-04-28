<?php

namespace App\Features\ServiceOrders\Controllers;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\ServicesOrdersPriority;
use App\Shared\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServiceOrderPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $orders = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType'])
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'process' => $o->process,
                'client_id' => $o->client_id,
                'location_id' => $o->location_id,
                'service_type_id' => $o->service_type_id,
                'manager_id' => $o->manager_id,
                'priority' => $o->priority,
                'status' => $o->status,
                'execution_date' => $o->execution_date?->format('Y-m-d'),
                'created_at' => $o->created_at->format('Y-m-d'),
                'photo_url' => $o->photo_url,
                'client' => $o->client ? [
                    'id' => $o->client->id,
                    'name' => trim(($o->client->user?->first_name ?? '') . ' ' . ($o->client->user?->last_name ?? '')) ?: 'N/A',
                ] : null,
                'manager' => $o->manager ? [
                    'id' => $o->manager->id,
                    'name' => trim(($o->manager->first_name ?? '') . ' ' . ($o->manager->last_name ?? '')) ?: 'N/A',
                ] : null,
                'location' => $o->location ? [
                    'id' => $o->location->id,
                    'parish' => $o->location->parish ? ['name' => $o->location->parish->name] : null,
                    'street' => $o->location->street_address,
                    'landmark' => $o->location->landmark,
                    'latitude' => $o->location->latitude,
                    'longitude' => $o->location->longitude,
                ] : null,
                'service_type' => $o->serviceType ? ['name' => $o->serviceType->name] : null,
            ])
            ->toArray();

        $clientOptions = Client::with('user')->get()->map(fn ($c) => [
            'value' => $c->id,
            'label' => $c->user?->first_name . ' ' . $c->user?->last_name,
        ])->toArray();

        $serviceTypeOptions = ServiceType::all()->map(fn ($st) => [
            'value' => $st->id,
            'label' => $st->name,
        ])->toArray();

        $parishOptions = Parish::orderBy('name')->get()->map(fn ($p) => [
            'value' => $p->id,
            'label' => $p->name,
        ])->toArray();

        return Inertia::render('ServiceOrders/Pages/Index', [
            'service_orders' => $orders,
            'columns' => [
                ['key' => 'process', 'label' => 'Process', 'sortable' => true],
                ['key' => 'client.name', 'label' => 'Client'],
                ['key' => 'priority', 'label' => 'Priority', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'process', 'label' => 'Process', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'client_id', 'label' => 'Client', 'type' => 'select', 'options' => $clientOptions],
                ['key' => 'service_type_id', 'label' => 'Service Type', 'type' => 'select', 'options' => $serviceTypeOptions],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => [
                    ['value' => 'low', 'label' => 'Low'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'high', 'label' => 'High'],
                    ['value' => 'urgent', 'label' => 'Urgent'],
                ]],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ]],
            ],
            'createFormSchema' => [
                // ── Core Details ──
                ['key' => 'section-core', 'label' => 'Core Details', 'type' => 'section-header'],
                ['key' => 'process', 'label' => 'Process', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'client_id', 'label' => 'Client', 'type' => 'select', 'options' => $clientOptions],
                ['key' => 'service_type_id', 'label' => 'Service Type', 'type' => 'select', 'options' => $serviceTypeOptions],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => [
                    ['value' => 'low', 'label' => 'Low'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'high', 'label' => 'High'],
                    ['value' => 'urgent', 'label' => 'Urgent'],
                ]],

                // ── Photo ──
                ['key' => 'section-photo', 'label' => 'Photo', 'type' => 'section-header'],
                ['key' => 'photo', 'label' => 'Upload Photo', 'type' => 'file', 'required' => true, 'accept' => 'image/jpeg,image/png'],

                // ── Smart Location Group ──
                ['key' => 'section-location', 'label' => 'Location', 'type' => 'section-header'],
                ['key' => 'parish_id', 'label' => 'Parish / Freguesia', 'type' => 'select', 'options' => $parishOptions],
                ['key' => 'street', 'label' => 'Street', 'type' => 'text'],
                ['key' => 'reference_point', 'label' => 'Reference Point', 'type' => 'text'],

                // ── Map Picker ──
                ['key' => 'section-map', 'label' => 'Map Coordinates', 'type' => 'section-header'],
                ['key' => 'map_picker', 'label' => 'Select on Map', 'type' => 'map-picker', 'apiKey' => env('GOOGLE_MAPS_API_KEY')],
                ['key' => 'latitude', 'label' => 'Latitude', 'type' => 'map_input'],
                ['key' => 'longitude', 'label' => 'Longitude', 'type' => 'map_input'],
            ],
            'routes' => [
                'index' => url('/api/service-orders'),
                'store' => url('/api/service-orders'),
                'update' => url('/api/service-orders/:id'),
                'destroy' => url('/api/service-orders/:id'),
                'show' => url('/api/service-orders/:id'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search process...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ]],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => [
                    ['value' => 'low', 'label' => 'Low'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'high', 'label' => 'High'],
                    ['value' => 'urgent', 'label' => 'Urgent'],
                ]],
            ],
            'parishOptions' => $parishOptions,
            'googleMapsApiKey' => env('GOOGLE_MAPS_API_KEY'),
        ]);
    }
}
