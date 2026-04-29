<?php

namespace App\Features\Settings\Controllers;

use App\Core\Helpers\InputSanitizer;
use App\Shared\Models\AppSetting;
use App\Shared\Models\User;
use App\Shared\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SettingsPageController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->roles()->where('name', 'admin')->exists();

        $preferences = UserPreference::where('user_id', $user->id)
            ->pluck('value', 'key')
            ->toArray();

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        return Inertia::render('Settings/Pages/Settings', [
            'user' => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
            ],
            'preferences' => $preferences,
            'appSettings'  => $appSettings,
            'isAdmin'      => $isAdmin,
            'routes'       => [
                'updateUser'     => route('settings.update-user'),
                'updatePassword' => route('settings.update-password'),
                'updateAdmin'    => route('settings.update-admin'),
                'deleteAccount'  => route('settings.delete-account'),
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile fields.
     */
    public function updateUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:250',
            'last_name'  => 'required|string|max:250',
            'email'      => 'required|email|max:250|unique:users,email,' . $request->user()->id,
            'phone'      => 'nullable|string|max:20',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'first_name' => InputSanitizer::sanitize($validated['first_name']),
                'last_name'  => InputSanitizer::sanitize($validated['last_name']),
                'email'      => InputSanitizer::sanitizeEmail($validated['email']),
                'phone'      => $validated['phone'] ? InputSanitizer::sanitizePhone($validated['phone']) : null,
            ]);
        });

        return redirect()->back()->with('success', __('Profile updated successfully.'));
    }

    /**
     * Update system-wide settings (admin only).
     */
    public function updateAdmin(Request $request): RedirectResponse
    {
        Gate::authorize('update', AppSetting::class);

        $validated = $request->validate([
            'app_name' => 'nullable|string|max:250',
            'locale'   => 'nullable|string|max:10',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                if ($value !== null) {
                    AppSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => InputSanitizer::sanitize($value)]
                    );
                }
            }
        });

        return redirect()->back()->with('success', __('System settings updated.'));
    }

    /**
     * Update the authenticated user's password with security validation.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password'      => 'required|string|current_password',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', __('Password updated successfully.'));
    }

    /**
     * Delete the authenticated user's account (soft-delete).
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Revoke all tokens if using Sanctum
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete(); // soft-delete via Base trait
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
