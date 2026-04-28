<?php

namespace App\Features\Profile\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ProfilePageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        return Inertia::render('Profile/Pages/Profile', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'role' => $user->roles->first()?->name ?? 'pending',
                'status' => $user->status,
            ],
        ]);
    }
}
