<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Models\Auth\OtpCode;
use App\Models\CustomerProfile;
use App\Models\DealerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends ApiController
{
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'purpose' => ['required', Rule::in(['customer_login', 'dealer_login'])],
        ]);

        $otp = $this->isBypassMobile($validated['mobile'])
            ? (string) config('erp_auth.otp.bypass_code', '123456')
            : (app()->environment('production') ? (string) random_int(100000, 999999) : (string) config('erp_auth.otp.debug_code', '123456'));

        OtpCode::query()->create([
            'mobile' => $validated['mobile'],
            'purpose' => $validated['purpose'],
            'code_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $this->success([
            'mobile' => $validated['mobile'],
            'expires_in_seconds' => 600,
            'debug_otp' => app()->environment('production') && ! $this->isBypassMobile($validated['mobile']) ? null : $otp,
        ], 'OTP generated.');
    }

    public function verifyCustomerOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $this->verifyOtp($validated['mobile'], 'customer_login', $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 422);
        }

        $user = User::query()->firstOrCreate(
            ['mobile' => $validated['mobile']],
            [
                'name' => $validated['name'] ?? 'Customer',
                'email' => $this->virtualEmail($validated['mobile'], 'customer'),
                'password' => Str::password(32),
                'role' => User::ROLE_CUSTOMER,
                'status' => 'active',
                'mobile_verified_at' => now(),
            ]
        );

        $user->forceFill(['mobile_verified_at' => now(), 'last_login_at' => now(), 'status' => 'active'])->save();
        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        return $this->success(['user' => $user->load('customerProfile'), 'token' => $user->createApiToken('customer-app')], 'Customer logged in.');
    }

    public function verifyDealerOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:255'],
            'firm_name' => ['required', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ]);

        if (! $this->verifyOtp($validated['mobile'], 'dealer_login', $validated['otp'])) {
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

        if ($user->status === 'active' && $user->dealerProfile?->approved_at !== null) {
            $user->forceFill(['last_login_at' => now()])->save();

            return $this->success([
                'user' => $user,
                'token' => $user->createApiToken('dealer-app'),
            ], 'Dealer logged in.');
        }

        return $this->success(['user' => $user], 'Dealer registered. Admin approval required.', 201);
    }

    public function salesmanLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->where('role', User::ROLE_SALESMAN)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return $this->fail('Invalid salesman login.', 401);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success(['user' => $user->load('salesmanProfile'), 'token' => $user->createApiToken('salesman-app')], 'Salesman logged in.');
    }

    public function adminLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->where('role', User::ROLE_ADMIN)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return $this->fail('Invalid admin login.', 401);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success(['user' => $user, 'token' => $user->createApiToken('admin-api')], 'Admin logged in.');
    }
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            \App\Models\Auth\ApiToken::query()->where('token_hash', hash('sha256', $token))->delete();
        }

        return $this->success([], 'Logged out.');
    }

    private function verifyOtp(string $mobile, string $purpose, string $otp): bool
    {
        if ($this->isBypassMobile($mobile) && hash_equals((string) config('erp_auth.otp.bypass_code', '123456'), $otp)) {
            return true;
        }

        $otpCode = OtpCode::query()
            ->where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otpCode || ! Hash::check($otp, $otpCode->code_hash)) {
            optional($otpCode)->increment('attempts');

            return false;
        }

        $otpCode->forceFill(['verified_at' => now()])->save();

        return true;
    }
    private function isBypassMobile(string $mobile): bool
    {
        $cleanMobile = preg_replace('/\D+/', '', $mobile);
        $numbers = array_map(
            static fn (string $number): string => preg_replace('/\D+/', '', $number),
            config('erp_auth.otp.bypass_numbers', [])
        );

        return in_array($cleanMobile, $numbers, true);
    }


    private function virtualEmail(string $mobile, string $role): string
    {
        $clean = preg_replace('/\D+/', '', $mobile) ?: Str::lower(Str::random(10));

        return $clean.'@'.$role.'.bawaskar.local';
    }
}





