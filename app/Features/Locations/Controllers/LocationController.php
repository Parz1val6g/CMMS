<?php

namespace App\Features\Locations\Controllers;

use App\Features\Locations\Models\Location;
use App\Features\Locations\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class LocationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Location::with(['parish.municipality']);

        if ($request->has('search')) {
            $query->where('street_address', 'like', '%' . $request->search . '%')
                  ->orWhere('postal_code', 'like', '%' . $request->search . '%')
                  ->orWhere('landmark', 'like', '%' . $request->search . '%');
        }

        $locations = $query->latest()->paginate(50);

        return LocationResource::collection($locations);
    }
}
