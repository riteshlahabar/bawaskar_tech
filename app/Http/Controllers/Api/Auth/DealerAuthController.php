<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\OtpContract;
use App\Models\DealerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DealerAuthController extends AuthApiController
{
    public function __construct(private readonly OtpContract $otp) {}

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:255'],
            'firm_name' => ['required', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ]);

        if (! $this->otp->verify($validated['mobile'], 'dealer_login', $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 422);
        }

        $user = User::query()->firstOrCreate(
            ['mobile' => $validated['mobile']],
            [
                'name' => $validated['name'],
                'email' => $this->virtualEmail($validated['mobile'], 'dealer'),
                'password' => Str::password(32),
                'role' => User::ROLE_DEALER,
                'status' => 'pending_approval',
                'mobile_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'name' => $validated['name'],
            'role' => User::ROLE_DEALER,
            'mobile_verified_at' => now(),
        ])->save();

        DealerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'dealer_code' => 'DLR'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'firm_name' => $validated['firm_name'],
                'gst_number' => $validated['gst_number'] ?? null,
            ]
        );

        $user->load('dealerProfile.salesman');

        // A dealer only gets a token once an admin has approved the account.
        if (! $this->isApproved($user)) {
            return $this->success(['user' => $user], 'Dealer registered. Admin approval required.', 201);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('dealer-app'),
        ], 'Dealer logged in.');
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with('dealerProfile.salesman')
            ->where('email', $validated['email'])
            ->where('role', User::ROLE_DEALER)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->fail('Invalid dealer login.', 401);
        }

        if (! $this->isApproved($user)) {
            return $this->fail('Dealer approval is pending.', 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('dealer-app'),
        ], 'Dealer logged in.');
    }

    private function isApproved(User $user): bool
    {
        return $user->status === 'active' && $user->dealerProfile?->approved_at !== null;
    }
}
