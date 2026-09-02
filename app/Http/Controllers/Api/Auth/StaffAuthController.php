<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Auth\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Password logins for the roles that do not use OTP.
 */
final class StaffAuthController extends AuthApiController
{
    public function salesmanLogin(Request $request): JsonResponse
    {
        $user = $this->authenticate($request, User::ROLE_SALESMAN);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'user' => $user->load('salesmanProfile'),
            'token' => $user->createApiToken('salesman-app'),
        ], 'Salesman logged in.');
    }

    public function adminLogin(Request $request): JsonResponse
    {
        $user = $this->authenticate($request, User::ROLE_ADMIN);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('admin-api'),
        ], 'Admin logged in.');
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            ApiToken::query()->where('token_hash', hash('sha256', $token))->delete();
        }

        return $this->success([], 'Logged out.');
    }

    private function authenticate(Request $request, string $role): User|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->where('role', $role)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return $this->fail('Invalid '.$role.' login.', 401);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }
}
