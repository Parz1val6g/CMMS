<?php
namespace App\Features\Authentication\Controllers;
use App\Features\Authentication\Requests\LoginRequest;
use App\Features\Authentication\Resources\UserResource;
use App\Shared\Models\User;
use App\Core\Enums\SystemStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
     * Revoke the token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete the current access token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}