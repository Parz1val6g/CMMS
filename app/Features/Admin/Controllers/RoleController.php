<?php

namespace App\Features\Admin\Controllers;

use App\Shared\Models\Role;
use App\Features\Admin\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $roles = Role::with(['permissions'])->orderBy('name')->get();
        return RoleResource::collection($roles);
    }
}
