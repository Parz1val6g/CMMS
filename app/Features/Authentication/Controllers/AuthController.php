<?php
namespace App\Features\Authentication\Controllers;
use App\Features\Authentication\Requests\LoginRequest;
use App\Features\Authentication\Resources\UserResource;
use App\Shared\Models\User;
use App\Core\Enums\SystemStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    /**
     * Authenticate user and return a Bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if ($user->status !== SystemStatus::ACTIVE->value) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }
        // Generate the Bearer Token for Vue / Mobile App
        $token = $user->createToken('auth_token')->plainTextToken;
        // Return the token AND the eager-loaded user profile
        $user->load('roles');
        return response()->json([
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }
    /**
     * Fetch the currently authenticated user's profile.
     */
    public function me(Request $request): UserResource
    {
        $user = $request->user();
        $user->load('roles'); // Eager load roles so the frontend knows what they can do
        return new UserResource($user);
    }
    /**
     * Reset the user's password using a previously issued reset token.
     * Token must not be used and must not be expired.
     */
    public function passwordReset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'                 => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('token', $data['token'])
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'token' => ['Este token de redefinição é inválido ou expirou.'],
            ]);
        }

        $user = User::findOrFail($record->user_id);
        $user->update(['password' => Hash::make($data['password'])]);

        DB::table('password_reset_tokens')
            ->where('token', $data['token'])
            ->update(['used' => 1]);

        // Revoke all active tokens so the new password takes effect immediately
        $user->tokens()->delete();

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }

    /**
     * Revoke the token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete the current access token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}