<?php

namespace App\Features\Materials\Controllers;

use App\Features\Materials\Models\Material;
use App\Features\Materials\Resources\MaterialResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class MaterialController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Material::with(['unit']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $materials = $query->latest()->paginate(50);

        return MaterialResource::collection($materials);
    }
}
