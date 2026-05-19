<?php

namespace App\Features\EntityPortal\Controllers\Web;

use App\Features\LoanOrders\LoanOrderFormSchema;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\LoanOrders\Presenters\LoanOrderPresenter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntityPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isEntity(), 403);

        $loanOrders = LoanOrder::with(['entity', 'manager', 'location', 'equipments'])
            ->whereHas('entity', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn($lo) => LoanOrderPresenter::forIndex($lo));

        return Inertia::render('EntityPortal/Pages/Index', [
            'loan_orders'      => $loanOrders,
            'createFormSchema' => LoanOrderFormSchema::entityCreate()->toArray(),
            'routes'           => [
                'store'  => '/api/loan-orders',
                'show'   => '/api/loan-orders/__ID__',
                'cancel' => '/api/loan-orders/__ID__/cancel',
            ],
        ]);
    }
}
