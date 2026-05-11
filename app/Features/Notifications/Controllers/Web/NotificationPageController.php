<?php

namespace App\Features\Notifications\Controllers\Web;

use App\Features\Notifications\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NotificationPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Notification::class);

        $notifications = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', get_class($request->user()))
            ->latest()
            ->paginate(20)
            ->through(fn ($n) => [
                'id'          => $n->id,
                'type'        => class_basename($n->type),
                'data'        => $n->data,
                'read_at'     => $n->read_at?->diffForHumans(),
                'created_at'  => $n->created_at->diffForHumans(),
            ]);

        return Inertia::render('Notifications/Pages/Index', [
            'notifications' => $notifications,
        ]);
    }
}
