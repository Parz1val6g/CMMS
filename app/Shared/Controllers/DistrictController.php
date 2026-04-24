<?php

namespace App\Shared\Controllers;

use App\Shared\Models\District;
use App\Shared\Resources\DistrictResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class DistrictController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = District::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return DistrictResource::collection($query->orderBy('name')->get());
    }
}
