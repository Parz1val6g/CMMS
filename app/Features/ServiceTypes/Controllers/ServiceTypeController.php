<?php

namespace App\Features\ServiceTypes\Controllers;

use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\ServiceTypes\Resources\ServiceTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ServiceTypeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ServiceType::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $serviceTypes = $query->latest()->paginate(50);

        return ServiceTypeResource::collection($serviceTypes);
    }
}
