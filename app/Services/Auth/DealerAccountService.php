<?php

namespace App\Services\Auth;

use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\AccountRoleConflictException;
use App\Models\DealerProfile;
use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Support\Str;

/**
 * SRP: turning a verified phone number plus firm details into a dealer record.
 *
 * A dealer is created in pending_approval and stays there: whether the account
 * may actually sign in is an admin decision, checked separately by the caller.
 */
final class DealerAccountService
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function register(VerifiedPhone $phone, array $details): User
    {
        $existing = $this->findByMobile($phone);

        if ($existing && $existing->role !== User::ROLE_DEALER) {
            throw AccountRoleConflictException::forRole($existing->role);
        }

        $user = $existing ?: User::query()->create([
            'name' => (string) $details['name'],
            'mobile' => $phone->mobile,
            'email' => $this->virtualEmail($phone->mobile),
            'password' => Str::password(32),
            'role' => User::ROLE_DEALER,
            'status' => 'pending_approval',
            'mobile_verified_at' => now(),
        ]);

        $user->forceFill([
            'name' => (string) $details['name'],
            'role' => User::ROLE_DEALER,
            'mobile_verified_at' => now(),
        ])->save();

        DealerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'dealer_code' => 'DLR'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'firm_name' => (string) $details['firm_name'],
                'gst_number' => $details['gst_number'] ?? null,
            ]
        );

        return $user->load('dealerProfile.salesman');
    }

    public function isApproved(User $user): bool
    {
        return $user->status === 'active' && $user->dealerProfile?->approved_at !== null;
    }

    private function findByMobile(VerifiedPhone $phone): ?User
    {
        $variants = MobileNumber::lookupVariants($phone->mobile);

        return $variants === []
            ? null
            : User::query()->whereIn('mobile', $variants)->first();
    }

    private function virtualEmail(string $mobile): string
    {
        return $mobile.'@dealer.bawaskar.local';
    }
}
