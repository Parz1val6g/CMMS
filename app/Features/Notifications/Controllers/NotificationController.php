<?php
namespace App\Features\Notifications\Controllers;

use App\Features\Notifications\Models\Notification;
use App\Features\Notifications\Resources\NotificationResource;
use App\Features\Notifications\Services\NotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return NotificationResource::collection($notifications);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        Gate::authorize('update', $notification);

        $this->notificationService->markAsRead($notification);

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
