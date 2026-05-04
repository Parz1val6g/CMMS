<?php

namespace App\Features\Locations\Controllers;

use App\Features\Locations\Models\Location;
use App\Features\Locations\Schemas\LocationFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LocationPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Location::class);

        $locations = Location::with(['parish.municipality.district'])
            ->latest()
            ->paginate(15)
            ->through(fn ($l) => [
                'id' => $l->id,
                'street_address' => $l->street_address,
                'postal_code' => $l->postal_code,
                'landmark' => $l->landmark,
                'parish' => $l->parish ? [
                    'id' => $l->parish->id,
                    'name' => $l->parish->name,
                    'municipality' => $l->parish->municipality ? [
                        'id' => $l->parish->municipality->id,
                        'name' => $l->parish->municipality->name,
                        'district' => $l->parish->municipality->district ? [
                            'id' => $l->parish->municipality->district->id,
                            'name' => $l->parish->municipality->district->name,
                        ] : null,
                    ] : null,
                ] : null,
                'latitude' => $l->latitude,
                'longitude' => $l->longitude,
                'created_at' => $l->created_at->format('Y-m-d'),
            ]);

        $createSchema = LocationFormSchema::create();
        $updateSchema = LocationFormSchema::update();

        return Inertia::render('Locations/Pages/Index', [
            'locations' => $locations,
            'columns' => [
                ['key' => 'street_address', 'label' => 'Address', 'sortable' => true],
                ['key' => 'postal_code', 'label' => 'Postal Code'],
                ['key' => 'parish', 'label' => 'Parish'],
                ['key' => 'landmark', 'label' => 'Landmark'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/locations'),
                'store' => url('/api/locations'),
                'update' => url('/api/locations/__ID__'),
                'destroy' => url('/api/locations/__ID__'),
                'show' => url('/api/locations/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search by address or landmark...'],
            ],
        ]);
    }
}
