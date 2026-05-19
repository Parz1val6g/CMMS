<?php

namespace App\Features\LoanOrders\Controllers\Web;

use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\LoanOrders\Presenters\LoanOrderPresenter;
use App\Features\LoanOrders\LoanOrderFormSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LoanOrderPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', LoanOrder::class);

        $loanOrders = LoanOrder::with([
            'entity',
            'manager',
            'location.parish',
            'equipments',
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->through(fn($lo) => LoanOrderPresenter::forIndex($lo));

        $columns = [
            ['key' => 'reference', 'label' => __('messages.controllers.loan_orders.col_reference'), 'sortable' => true],
            ['key' => 'manager.name', 'label' => __('messages.controllers.loan_orders.col_manager'), 'sortable' => false],
            ['key' => 'status',    'label' => __('messages.controllers.loan_orders.col_status'),    'sortable' => true],
            ['key' => 'created_at','label' => __('messages.controllers.loan_orders.col_created'),   'sortable' => true],
        ];

        return Inertia::render('LoanOrders/Pages/Index', [
            'loan_orders'          => $loanOrders,
            'columns'              => $columns,
            'formSchema'           => LoanOrderFormSchema::update()->toArray(),
            'createFormSchema'     => LoanOrderFormSchema::create()->toArray(),
            'filterSchema'         => [],
            'advancedFilterFields' => [
                ['value' => 'reference', 'label' => __('messages.controllers.loan_orders.col_reference'), 'type' => 'text'],
                ['value' => 'manager',   'label' => __('messages.controllers.loan_orders.col_manager'),   'type' => 'text'],
                ['value' => 'status',    'label' => __('messages.controllers.loan_orders.col_status'),    'type' => 'select', 'options' => \App\Core\Enums\LoanOrderStatus::options()],
                ['value' => 'created_at','label' => __('messages.controllers.loan_orders.col_created'),   'type' => 'text'],
            ],
            'routes'               => [
                'index'    => '/api/loan-orders',
                'store'    => '/api/loan-orders',
                'show'     => '/api/loan-orders/__ID__',
                'update'   => '/api/loan-orders/__ID__',
                'destroy'  => '/api/loan-orders/__ID__',
                'approve'  => '/api/loan-orders/__ID__/approve',
                'checkout' => '/api/loan-orders/__ID__/checkout',
                'return'   => '/api/loan-orders/__ID__/return',
                'complete' => '/api/loan-orders/__ID__/complete',
                'cancel'   => '/api/loan-orders/__ID__/cancel',
            ],
        ]);
    }
}
