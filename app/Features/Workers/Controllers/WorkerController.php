<?php

namespace App\Features\Workers\Controllers;

use App\Features\Workers\Models\Worker;
use App\Features\Workers\Resources\WorkerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class WorkerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Worker::with(['user', 'team']);

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('user', function ($userQuery) use ($searchTerm) {
                $userQuery->where('first_name', 'like', $searchTerm)
                          ->orWhere('last_name', 'like', $searchTerm);
            });
        }

        $workers = $query->latest()->paginate(50);

        return WorkerResource::collection($workers);
    }
}
