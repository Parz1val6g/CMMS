<?php

namespace App\Features\Teams\Controllers;

use App\Features\Teams\Models\Team;
use App\Features\Teams\Resources\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Team::with(['sector']);

        if ($request->has('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $teams = $query->latest()->paginate(50);

        return TeamResource::collection($teams);
    }
}
