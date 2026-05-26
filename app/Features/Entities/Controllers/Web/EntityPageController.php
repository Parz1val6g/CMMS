<?php

namespace App\Features\Entities\Controllers\Web;

use App\Core\Enums\EntityType;
use App\Core\Traits\GatesRoutes;
use App\Features\Entities\EntityFormSchema;
use App\Features\Entities\Models\Entity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EntityPageController extends Controller
{
    use GatesRoutes;
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Entity::class);

        $entities = Entity::with(['user', 'location'])
            ->withCount('loanOrders')
            ->latest()
            ->paginate(15)
            ->through(fn($e) => [
                'id'          => $e->id,
                'name'        => $e->name,
                'nif'         => $e->nif,
                'entity_type' => $e->entity_type->value,
                'phone'       => $e->phone,
                'user'        => ['id' => $e->user?->id, 'name' => $e->user ? $e->user->first_name . ' ' . $e->user->last_name : null],
                'loan_orders_count' => $e->loan_orders_count,
                'created_at'  => $e->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('Entities/Pages/Index', [
            'entities'    => $entities,
            'columns'     => [
                ['key' => 'name',        'label' => __('forms.entities.name'),        'sortable' => true],
                ['key' => 'entity_type', 'label' => __('forms.entities.entity_type'), 'sortable' => true],
                ['key' => 'nif',         'label' => __('forms.entities.nif'),         'sortable' => false],
                ['key' => 'phone',       'label' => __('forms.entities.phone'),        'sortable' => false],
                ['key' => 'loan_orders_count', 'label' => __('forms.entities.loan_orders_count'), 'sortable' => false],
                ['key' => 'created_at',  'label' => __('forms.entities.created_at'),  'sortable' => true],
            ],
            'formSchema'       => EntityFormSchema::update()->toArray(),
            'createFormSchema' => EntityFormSchema::create()->toArray(),
            'entityTypeOptions' => EntityType::options(),
            'routes'       => $this->gatedRoutes([
                'index'   => '/api/entities',
                'store'   => '/api/entities',
                'update'  => '/api/entities/__ID__',
                'destroy' => '/api/entities/__ID__',
                'show'    => '/api/entities/__ID__',
            ], 'entities'),
            'filterSchema' => [
                ['key' => 'search', 'label' => __('forms.entities.search'), 'type' => 'text', 'placeholder' => '...'],
            ],
        ]);
    }

    public function dashboard(Request $request)
    {
        $user   = $request->user();
        $entity = $user->entityProfile;

        if (!$entity) {
            abort(403, 'No entity profile found for this user.');
        }

        $loanOrders = $entity->loanOrders()
            ->with(['equipments'])
            ->latest()
            ->paginate(10)
            ->through(fn($lo) => [
                'id'         => $lo->id,
                'reference'  => $lo->reference,
                'status'     => $lo->status->value,
                'created_at' => $lo->created_at?->format('Y-m-d'),
                'equipments' => $lo->equipments->map(fn($eq) => ['name' => $eq->name])->toArray(),
            ]);

        return Inertia::render('Entities/Pages/Dashboard', [
            'entity'      => [
                'id'          => $entity->id,
                'name'        => $entity->name,
                'entity_type' => $entity->entity_type->value,
            ],
            'loan_orders' => $loanOrders,
            'stats'       => [
                'pending'   => $entity->loanOrders()->where('status', 'pending')->count(),
                'active'    => $entity->loanOrders()->whereIn('status', ['approved', 'checked_out'])->count(),
                'completed' => $entity->loanOrders()->where('status', 'returned')->count(),
            ],
            'routes' => [
                'store' => '/api/loan-orders',
            ],
        ]);
    }
}
