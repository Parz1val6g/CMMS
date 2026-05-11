<?php

namespace App\Features\ServiceOrders\Controllers\Web;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Presenters\ServiceOrderPresenter;
use App\Features\ServiceOrders\ServiceOrderFormSchema;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServiceOrderPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $user = $request->user();

        $orders = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType', 'equipments', 'sectors'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('manager_id', $user->id))
            ->latest()
            ->paginate(15)
            ->through(fn ($o) => ServiceOrderPresenter::forIndex($o));

        $createSchema = ServiceOrderFormSchema::create();
        $updateSchema = ServiceOrderFormSchema::update();

        return Inertia::render('ServiceOrders/Pages/Index', [
            'service_orders'   => $orders,
            'columns'          => [
                ['key' => 'process',    'label' => __('messages.controllers.service_orders.col_process'),    'sortable' => true],
                ['key' => 'description','label' => __('messages.controllers.service_orders.col_description')],
                ['key' => 'client.name','label' => __('messages.controllers.service_orders.col_client')],
                ['key' => 'status',     'label' => __('messages.controllers.service_orders.col_status'),     'sortable' => true],
                ['key' => 'priority',   'label' => __('messages.controllers.service_orders.col_priority'),   'sortable' => true],
                ['key' => 'created_at', 'label' => __('messages.controllers.service_orders.col_created'),    'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes'               => [
                'index'   => url('/api/service-orders'),
                'store'   => url('/api/service-orders'),
                'update'  => url('/api/service-orders/:id'),
                'destroy' => url('/api/service-orders/:id'),
                'show'    => url('/api/service-orders/:id'),
            ],
            'advancedFilterFields' => [
                ['value' => 'process',     'label' => __('messages.controllers.service_orders.col_process')],
                ['value' => 'description', 'label' => __('messages.controllers.service_orders.col_description')],
                ['value' => 'priority',    'label' => __('messages.controllers.service_orders.col_priority'), 'type' => 'select', 'options' => Priority::options()],
                ['value' => 'status',      'label' => __('messages.controllers.service_orders.col_status'),   'type' => 'select', 'options' => ServiceOrderStatus::options()],
                ['value' => 'created_at',  'label' => __('messages.controllers.service_orders.col_created')],
            ],
            'filterSchema'         => [
                ['key' => 'search',   'label' => __('messages.controllers.service_orders.filter_search'),   'type' => 'text',   'placeholder' => __('messages.controllers.service_orders.search_placeholder')],
                ['key' => 'status',   'label' => __('messages.controllers.service_orders.filter_status'),   'type' => 'select', 'options' => ServiceOrderStatus::options()],
                ['key' => 'priority', 'label' => __('messages.controllers.service_orders.filter_priority'), 'type' => 'select', 'options' => Priority::options()],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $only  = array_values(array_filter(explode(',', $request->query('only', ''))));
        $query = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType', 'equipments.manager', 'sectors']);

        if (empty($only) || in_array('tasks', $only)) {
            $query->with([
                'tasks' => fn ($q) => $q->with([
                    'manager',
                    'sectors',
                    'miniTasks' => fn ($mt) => $mt->with([
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
