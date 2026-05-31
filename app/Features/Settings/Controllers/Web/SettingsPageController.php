<?php

namespace App\Features\Settings\Controllers\Web;

use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Shared\Models\AppSetting;
use App\Shared\Models\User;
use App\Shared\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SettingsPageController extends Controller
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    /**
     * Show the settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = Gate::allows('update', new AppSetting());

        $preferences = UserPreference::where('user_id', $user->id)
            ->pluck('value', 'key')
            ->toArray();

        if (!isset($preferences['language']) && $user->locale) {
            $preferences['language'] = $user->locale === 'pt_PT' ? 'pt' : $user->locale;
        }

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        $user->loadMissing('roles');

        return Inertia::render('Settings/Pages/Settings', [
            'user' => [
                'id'               => $user->id,
                'first_name'       => $user->first_name,
                'last_name'        => $user->last_name,
                'full_name'        => $user->first_name . ' ' . $user->last_name,
                'email'            => $user->email,
                'roles'            => $user->roles->pluck('name')->toArray(),
                'permissions_count'=> $user->rolePermissions()->count(),
                'created_at'       => $user->created_at?->toIso8601String(),
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
    public function updateUser(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:250',
            'last_name'  => 'required|string|max:250',
            'email'      => 'required|email|max:250|unique:users,email,' . $request->user()->id,
            'phone'      => 'nullable|string|max:20',
            'language'   => 'nullable|string|in:pt,en',
        ]);

        $user = $request->user();

        $this->transactions->execute(function () use ($user, $validated) {
            $data = [
                'first_name' => InputSanitizer::sanitize($validated['first_name']),
                'last_name'  => InputSanitizer::sanitize($validated['last_name']),
                'email'      => InputSanitizer::sanitizeEmail($validated['email']),
            ];

            if (isset($validated['phone'])) {
                $data['phone'] = InputSanitizer::sanitizePhone($validated['phone']);
            }

            if (isset($validated['language'])) {
                $data['locale'] = $validated['language'];
            }

            $user->update($data);
        });

        if (isset($validated['language'])) {
            App::setLocale($validated['language'] === 'pt' ? 'pt_PT' : $validated['language']);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Profile updated successfully.')]);
        }

        return redirect()->back()->with('success', __('Profile updated successfully.'));
    }

    /**
     * Update system-wide settings (admin only).
     */
    public function updateAdmin(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', new AppSetting());

        $validated = $request->validate([
            'company_name'               => 'nullable|string|max:250',
            'default_language'           => 'nullable|string|in:PT,EN',
            'default_timezone'           => 'nullable|string|max:50',
            'currency'                   => 'nullable|string|max:3',
            'csv_enabled'                => 'nullable|boolean',
            'user_registration_enabled'  => 'nullable|boolean',
            'support_email'              => 'nullable|email|max:250',
            'company_website'            => 'nullable|url|max:250',
        ]);

        $this->transactions->execute(function () use ($validated, $request) {
            foreach ($validated as $key => $value) {
                if ($value !== null) {
                    AppSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => InputSanitizer::sanitize($value)]
                    );
                }
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('logos', 'public');
                AppSetting::updateOrCreate(
                    ['key' => 'logo_path'],
                    ['value' => $path]
                );
            }

            if ($request->boolean('delete_logo')) {
                AppSetting::where('key', 'logo_path')->delete();
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => __('System settings updated.')]);
        }

        return redirect()->back()->with('success', __('System settings updated.'));
    }

    /**
     * Update the authenticated user's password with security validation.
     */
    public function updatePassword(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'current_password'      => 'required|string|current_password',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Password updated successfully.')]);
        }

        return redirect()->back()->with('success', __('Password updated successfully.'));
    }

    /**
     * Delete the authenticated user's account (soft-delete).
     */
    public function deleteAccount(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();

        $this->transactions->execute(function () use ($user) {
            // Revoke all tokens if using Sanctum
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete(); // soft-delete via Base trait
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Account deleted successfully.')]);
        }

        return redirect()->route('login');
    }
}
