<?php

namespace App\Features\Admin\Controllers;

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
                'roles' => $u->roles->pluck('id')->toArray(),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        $roles = Role::all()->map(fn ($r) => ['value' => $r->id, 'label' => $r->name]);

        return Inertia::render('Admin/Pages/Users', [
            'users' => $users,
            'columns' => [
                ['key' => 'first_name', 'label' => 'First Name', 'sortable' => true],
                ['key' => 'last_name', 'label' => 'Last Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'roles', 'label' => 'Roles'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'pending', 'label' => 'Pending'],
                ]],
                ['key' => 'role_ids', 'label' => 'Roles', 'type' => 'multiselect', 'options' => $roles->toArray()],
            ],
            'createFormSchema' => [
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ]],
                ['key' => 'role_ids', 'label' => 'Roles', 'type' => 'multiselect', 'options' => $roles->toArray()],
            ],
            'routes' => [
                'index' => url('/api/admin/users'),
                'store' => url('/api/admin/users'),
                'update' => url('/api/admin/users/__ID__'),
                'destroy' => url('/api/admin/users/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search by name or email...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'pending', 'label' => 'Pending'],
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
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:50'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:50'],
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
