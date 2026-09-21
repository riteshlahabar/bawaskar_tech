<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\StorefrontProfileContract;
use App\Models\Address;
use App\Models\User;

final class StorefrontProfileService implements StorefrontProfileContract
{
    public function update(User $user, array $data): void
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        // A blank email keeps the stored one, so an OTP-only account is
        // never left without one — same rule the apps' own profile edit uses.
        $user->forceFill(array_filter([
            'name' => trim((string) $data['name']),
            'email' => $email,
        ], fn (string $value) => $value !== ''))->save();

        $this->updateDefaultAddress($user, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateDefaultAddress(User $user, array $data): void
    {
        $fields = [
            'address_line1' => trim((string) ($data['address_line1'] ?? '')),
            'address_line2' => trim((string) ($data['address_line2'] ?? '')),
            'city' => trim((string) ($data['city'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'pincode' => trim((string) ($data['pincode'] ?? '')),
        ];

        // Nothing address-related was submitted, so the existing address
        // (or lack of one) is left exactly as it was.
        if (array_filter($fields) === []) {
            return;
        }

        $address = $user->addresses->firstWhere('is_default', true) ?? $user->addresses->first();

        if ($address) {
            $address->update($fields);

            return;
        }

        Address::query()->create($fields + [
            'user_id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'type' => 'shipping',
            'is_default' => true,
        ]);
    }
}
