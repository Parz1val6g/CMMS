<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Municipality;
use App\Shared\Resources\MunicipalityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class MunicipalityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Municipality::query();

        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return MunicipalityResource::collection($query->orderBy('name')->get());
    }
}
