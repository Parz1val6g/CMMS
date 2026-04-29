<?php

namespace App\Features\Workers\Controllers;

use App\Features\Workers\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkerPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Worker::class);

        $workers = Worker::with(['user', 'team.sector'])
            ->latest()
            ->paginate(15)
            ->through(fn ($w) => [
                'id' => $w->id,
                'name' => $w->user?->first_name . ' ' . $w->user?->last_name,
                'email' => $w->user?->email,
                'phone' => $w->user?->phone,
                'team' => $w->team ? ['id' => $w->team->id, 'name' => $w->team->name] : null,
                'created_at' => $w->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Workers/Pages/Index', [
            'workers' => $workers,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'team', 'label' => 'Team'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'team_id', 'label' => 'Team', 'type' => 'select', 'options' => []],
            ],
            'createFormSchema' => [
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'team_id', 'label' => 'Team', 'type' => 'select', 'options' => []],
            ],
            'routes' => [
                'index' => url('/api/workers'),
                'store' => url('/api/workers'),
                'update' => url('/api/workers/__ID__'),
                'destroy' => url('/api/workers/__ID__'),
                'show' => url('/api/workers/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search workers...'],
            ],
        ]);
    }
}
