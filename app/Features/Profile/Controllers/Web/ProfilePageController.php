<?php

namespace App\Features\Profile\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ProfilePageController extends Controller
{
    public function index(Request $request)
    {
        return redirect('/settings');
    }
}
