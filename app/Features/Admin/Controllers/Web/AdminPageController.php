<?php

namespace App\Features\Admin\Controllers\Web;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AdminPageController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.users');
    }

    public function users(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with(['roles'])
            ->latest()
            ->paginate(50)
            ->through(fn ($u) => [
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                'phone' => $u->phone,
                'status' => $u->status,
                'roles' => $u->roles->pluck('name')->join(', '),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        $roles = Role::all()->map(fn ($r) => ['value' => $r->id, 'label' => $r->name]);

        return Inertia::render('Admin/Pages/Users', [
            'users' => $users,
            'columns' => [
                ['key' => 'first_name', 'label' => 'Primeiro Nome', 'sortable' => true],
                ['key' => 'last_name', 'label' => 'Apelido', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                ['key' => 'phone', 'label' => 'Telefone'],
                ['key' => 'status', 'label' => 'Estado'],
                ['key' => 'roles', 'label' => 'Funções'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'first_name', 'label' => 'Primeiro Nome', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Apelido', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Telefone', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => [
                ]],
                ['key' => 'role_ids', 'label' => 'Funções', 'type' => 'multiselect', 'options' => $roles->toArray()],
            ],
            'createFormSchema' => [
                ['key' => 'first_name', 'label' => 'Primeiro Nome', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Apelido', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Telefone', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => [
                ]],
                ['key' => 'role_ids', 'label' => 'Funções', 'type' => 'multiselect', 'options' => $roles->toArray()],
            ],
            'routes' => [
                'index' => url('/api/admin/users'),
                'store' => url('/api/admin/users'),
                'update' => url('/api/admin/users/__ID__'),
                'destroy' => url('/api/admin/users/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'first_name', 'label' => 'Primeiro Nome'],
                ['value' => 'last_name',  'label' => 'Apelido'],
                ['value' => 'email',      'label' => 'Email'],
                ['value' => 'phone',      'label' => 'Telefone'],
                ['value' => 'status',     'label' => 'Estado'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
                ['key' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => [
                ]],
            ],
        ]);
    }

    public function series(Request $request)
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::latest()->paginate(50)->through(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'created_at' => $r->created_at->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/Pages/Series', [
            'series' => $roles,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|max:50'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|max:50'],
            ],
            'routes' => [
                'index' => url('/api/admin/roles'),
                'store' => url('/api/admin/roles'),
                'update' => url('/api/admin/roles/__ID__'),
                'destroy' => url('/api/admin/roles/__ID__'),
            ],
        ]);
    }
}
