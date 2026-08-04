<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function success(array $data = [], string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function fail(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    protected function user(Request $request, ?string $role = null): ?User
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return null;
        }

        $apiToken = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $apiToken || ! $apiToken->user || $apiToken->user->status !== 'active') {
            return null;
        }

        if ($role && $apiToken->user->role !== $role) {
            return null;
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        return $apiToken->user;
    }

    protected function requireUser(Request $request, ?string $role = null): User|JsonResponse
    {
        $user = $this->user($request, $role);

        return $user ?: $this->fail('Unauthenticated or unauthorized.', 401);
    }
}
