<?php

namespace App\Features\ServiceOrders\Controllers;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Presenters\ServiceOrderPresenter;
use App\Features\ServiceOrders\Schemas\ServiceOrderFormSchema;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServiceOrderPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $orders = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType', 'equipment'])
            ->latest()
            ->paginate(15)
            ->through(fn ($o) => ServiceOrderPresenter::forIndex($o));

        $createSchema = ServiceOrderFormSchema::create();
        $updateSchema = ServiceOrderFormSchema::update();

        return Inertia::render('ServiceOrders/Pages/Index', [
            'service_orders'   => $orders,
            'columns'          => [
                ['key' => 'process',    'label' => 'Process',    'sortable' => true],
                ['key' => 'description','label' => 'Description'],
                ['key' => 'client.name','label' => 'Client'],
                ['key' => 'priority',   'label' => 'Priority',   'sortable' => true],
                ['key' => 'status',     'label' => 'Status',     'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created',    'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes'           => [
                'index'   => url('/api/service-orders'),
                'store'   => url('/api/service-orders'),
                'update'  => url('/api/service-orders/:id'),
                'destroy' => url('/api/service-orders/:id'),
                'show'    => url('/api/service-orders/:id'),
            ],
            'filterSchema'     => [
                ['key' => 'search',   'label' => 'Search',   'type' => 'text',   'placeholder' => 'Search process...'],
                ['key' => 'status',   'label' => 'Status',   'type' => 'select', 'options' => ServiceOrderStatus::options()],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => Priority::options()],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $only  = array_values(array_filter(explode(',', $request->query('only', ''))));
        $query = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType', 'equipment.manager']);

        if (empty($only) || in_array('tasks', $only)) {
            $query->with([
                'tasks' => fn ($q) => $q->with([
                    'manager',
                    'sectors',
                    'miniTasks' => fn ($mt) => $mt->with([
                        'sectors',
                        'workLogs' => fn ($wl) => $wl->with(['workers', 'materials', 'equipment', 'reviewer']),
                    ]),
                ]),
            ]);
        }

        $serviceOrder = $query->findOrFail($id);
        Gate::authorize('view', $serviceOrder);

        return Inertia::render('ServiceOrders/Pages/Show', [
            'service_order' => ServiceOrderPresenter::forDetail($serviceOrder, $only),
            'formSchema'    => ServiceOrderFormSchema::update()->toArray(),
            'routes'        => [
                'index'  => url('/service-orders'),
                'update' => url('/api/service-orders/:id'),
            ],
        ]);
    }
}
