<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Core\Enums\SystemStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\PermissionManager;
use App\Features\Authentication\Requests\LoginRequest;
use App\Shared\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WebAuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Authentication/Pages/Login');
    }

    /**
     * Handle login POST — session-based auth.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user->status !== SystemStatus::ACTIVE->value) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $roleCount = $user->roles()->count();

        if ($roleCount > 1) {
            return redirect()->route('select-role');
        }

        if ($roleCount === 1) {
            $role = $user->roles()->first()->name;
            $request->session()->put('active_role', $role);

            if ($user->isEntity()) {
                return redirect()->route('portal.index');
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the role selection page for users with multiple roles.
     */
    public function showSelectRole(Request $request): \Inertia\Response
    {
        $user = $request->user();

        $availableRoles = $user->roles()->get()->map(fn($role) => [
            'name'  => $role->name,
            'label' => __('enums.role_name.' . $role->name),
        ]);

        return \Inertia\Inertia::render('Authentication/Pages/SelectRole', [
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Handle in-session role switch from the TopBar dropdown.
     * Stays in context: redirects back to the current page.
     */
    public function switchRole(Request $request, PermissionManager $permissionManager): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'string']]);
        $user = $request->user();

        if (!$user->roles()->where('name', $data['role'])->exists()) {
            abort(403);
        }

        $request->session()->put('active_role', $data['role']);
        $permissionManager->invalidateUserPermissions($user);

        return redirect()->route('dashboard');
    }

    /**
     * Handle role selection POST — sets active_role in session, redirects to dashboard.
     */
    public function selectRole(Request $request, PermissionManager $permissionManager): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'string']]);

        $user = $request->user();

        if (!$user->roles()->where('name', $data['role'])->exists()) {
            abort(403);
        }

        $request->session()->put('active_role', $data['role']);
        $permissionManager->invalidateUserPermissions($user);

        if ($data['role'] === \App\Core\Enums\RoleName::ENTITY) {
            return redirect()->route('portal.index');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Show the registration page (only if feature flag is enabled).
     */
    public function showRegister(): Response
    {
        if (!config('features.registration_enabled', true)) {
            abort(404);
        }

        return Inertia::render('Authentication/Pages/Register');
    }

    /**
     * Handle registration POST.
     */
    public function register(Request $request): RedirectResponse
    {
        if (!config('features.registration_enabled', true)) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:250',
            'last_name'  => 'required|string|max:250',
            'email'      => 'required|string|email|max:250|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => InputSanitizer::sanitize($validated['first_name']),
            'last_name'  => InputSanitizer::sanitize($validated['last_name']),
            'email'      => InputSanitizer::sanitizeEmail($validated['email']),
            'phone'      => $validated['phone'] ? InputSanitizer::sanitizePhone($validated['phone']) : null,
            'password'   => Hash::make($validated['password']),
            'status'     => SystemStatus::ACTIVE->value,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Logout — invalidate session, regenerate CSRF token, redirect.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
