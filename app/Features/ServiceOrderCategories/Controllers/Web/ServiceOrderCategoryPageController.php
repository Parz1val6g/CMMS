<?php

namespace App\Features\ServiceOrderCategories\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Features\ServiceOrderCategories\ServiceOrderCategoryFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServiceOrderCategoryPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceOrderCategory::class);

        $categories = ServiceOrderCategory::latest()
            ->paginate(15)
            ->through(fn($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'description' => $c->description,
                'created_at'  => $c->created_at->format('Y-m-d'),
            ]);

        $createSchema = ServiceOrderCategoryFormSchema::create();
        $updateSchema = ServiceOrderCategoryFormSchema::update();

        return Inertia::render('ServiceOrderCategories/Pages/Index', [
            'service_order_categories' => $categories,
            'columns' => [
                ['key' => 'name',        'label' => 'Nome',        'sortable' => true],
                ['key' => 'description', 'label' => 'Descricao'],
                ['key' => 'created_at',  'label' => 'Criado',      'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/service-order-categories'),
                'store'   => url('/api/service-order-categories'),
                'update'  => url('/api/service-order-categories/__ID__'),
                'destroy' => url('/api/service-order-categories/__ID__'),
                'show'    => url('/api/service-order-categories/__ID__'),
            ], 'service_order_categories'),
            'advancedFilterFields' => [
                ['value' => 'name',        'label' => 'Nome'],
                ['value' => 'description', 'label' => 'Descricao'],
                ['value' => 'created_at',  'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
