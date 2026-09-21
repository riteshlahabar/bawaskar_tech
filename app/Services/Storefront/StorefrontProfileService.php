<?php

namespace App\Services\Storefront;

use App\Contracts\Location\UserLocationContract;
use App\Contracts\Storefront\StorefrontProfileContract;
use App\Models\Address;
use App\Models\User;

final class StorefrontProfileService implements StorefrontProfileContract
{
    public function __construct(private readonly UserLocationContract $location) {}

    public function update(User $user, array $data): void
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        // A blank email keeps the stored one, so an OTP-only account is
        // never left without one — same rule the apps' own profile edit uses.
        $location = $this->location->attributes($data);

        $user->forceFill(array_filter([
            'name' => trim((string) $data['name']),
            'email' => $email,
        ], fn (string $value) => $value !== '') + $location)->save();

        $this->updateDefaultAddress($user, $data, $location);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $location  State/district/taluka resolved
     *                                          by UserLocationContract, used
     *                                          to keep the shipping address's
     *                                          city/state/pincode in sync.
     */
    private function updateDefaultAddress(User $user, array $data, array $location): void
    {
        $fields = [
            'address_line1' => trim((string) ($data['address_line1'] ?? '')),
            'address_line2' => trim((string) ($data['address_line2'] ?? '')),
            'city' => (string) ($location['city_village'] ?? ''),
            'state' => (string) ($location['state_name'] ?? ''),
            'pincode' => (string) ($location['pincode'] ?? ''),
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
