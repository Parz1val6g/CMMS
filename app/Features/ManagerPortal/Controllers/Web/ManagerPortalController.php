<?php

namespace App\Features\ManagerPortal\Controllers\Web;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Presenters\ServiceOrderPresenter;
use App\Features\ServiceOrders\ServiceOrderFormSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManagerPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isManagerOrAdmin(), 403);

        $base = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType', 'sectors'])
            ->where('manager_id', $user->id);

        $stats = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $orders = (clone $base)
            ->latest()
            ->paginate(20)
            ->through(fn($o) => ServiceOrderPresenter::forIndex($o));

        return Inertia::render('ManagerPortal/Pages/Index', [
            'service_orders'   => $orders,
            'stats'            => $stats,
            'createFormSchema' => ServiceOrderFormSchema::create()->toArray(),
            'filterSchema'     => [
                ['key' => 'status',   'label' => 'Estado',    'type' => 'select', 'options' => ServiceOrderStatus::options()],
                ['key' => 'priority', 'label' => 'Prioridade','type' => 'select', 'options' => Priority::options()],
            ],
            'routes' => [
                'store'    => url('/api/service-orders'),
                'show'     => url('/api/service-orders/__ID__'),
                'update'   => url('/api/service-orders/__ID__'),
                'activate' => url('/api/service-orders/__ID__/activate'),
                'complete' => url('/api/service-orders/__ID__/complete'),
                'cancel'   => url('/api/service-orders/__ID__/cancel'),
            ],
        ]);
    }
}
