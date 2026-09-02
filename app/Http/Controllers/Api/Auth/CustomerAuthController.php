<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\OtpContract;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CustomerAuthController extends AuthApiController
{
    public function __construct(private readonly OtpContract $otp)
    {
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $existingUser = User::query()->where('mobile', $validated['mobile'])->first();

        if ($existingUser && $existingUser->role !== User::ROLE_CUSTOMER) {
            return $this->fail('This mobile number is registered for another account type.', 422);
        }

        if (! $this->otp->verify($validated['mobile'], 'customer_login', $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 422);
        }

        $user = $existingUser ?: User::query()->create([
            'name' => $validated['name'] ?? 'Customer',
            'mobile' => $validated['mobile'],
            'email' => $this->virtualEmail($validated['mobile'], 'customer'),
            'password' => Str::password(32),
            'role' => User::ROLE_CUSTOMER,
            'status' => 'active',
            'mobile_verified_at' => now(),
        ]);

        $user->forceFill([
            'mobile_verified_at' => now(),
            'last_login_at' => now(),
            'status' => 'active',
        ])->save();

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        return $this->success([
            'user' => $user->load('customerProfile'),
            'token' => $user->createApiToken('customer-app'),
        ], 'Customer logged in.');
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user = User::query()->where('mobile', $validated['mobile'])->first();
        $email = $validated['email'] ?? null;

        if ($user && $user->role !== User::ROLE_CUSTOMER) {
            return $this->fail('This mobile number is already registered for another account type.', 422);
        }

        $user = $user
            ? $this->updateExisting($user, $validated, $email)
            : $this->createNew($validated, $email);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        return $this->success(['user' => $user->load('customerProfile')], 'Customer registered. Verify OTP to continue.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->where('role', User::ROLE_CUSTOMER)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return $this->fail('Invalid customer login.', 401);
        }

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);
        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success([
            'user' => $user->load('customerProfile'),
            'token' => $user->createApiToken('customer-app'),
        ], 'Customer logged in.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createNew(array $validated, ?string $email): User|JsonResponse
    {
        if ($email && User::query()->where('email', $email)->exists()) {
            return $this->fail('This email address is already registered.', 422);
        }

        return User::query()->create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $email ?: $this->virtualEmail($validated['mobile'], 'customer'),
            'password' => $validated['password'] ?? Str::password(32),
            'role' => User::ROLE_CUSTOMER,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updateExisting(User $user, array $validated, ?string $email): User|JsonResponse
    {
        $updates = ['name' => $validated['name'], 'status' => 'active'];

        if ($email && $email !== $user->email) {
            if (User::query()->where('email', $email)->whereKeyNot($user->id)->exists()) {
                return $this->fail('This email address is already registered.', 422);
            }

            $updates['email'] = $email;
        }

        if (! empty($validated['password'])) {
            $updates['password'] = $validated['password'];
        }

        $user->forceFill($updates)->save();

        return $user;
    }
}
