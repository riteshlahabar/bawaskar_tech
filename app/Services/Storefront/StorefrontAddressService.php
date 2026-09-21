<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\StorefrontAddressContract;
use App\Models\Address;
use App\Models\User;

final class StorefrontAddressService implements StorefrontAddressContract
{
    public function store(User $user, array $data): Address
    {
        $isFirst = $user->addresses()->count() === 0;

        return $user->addresses()->create($this->fields($data) + [
            'is_default' => $isFirst,
        ]);
    }

    public function update(User $user, Address $address, array $data): Address
    {
        $this->authorize($user, $address);

        $address->update($this->fields($data));

        return $address;
    }

    public function destroy(User $user, Address $address): void
    {
        $this->authorize($user, $address);

        $wasDefault = $address->is_default;
        $address->delete();

        // Losing the default address should never leave the account without
        // one while others still exist.
        if ($wasDefault) {
            $user->addresses()->first()?->update(['is_default' => true]);
        }
    }

    public function makeDefault(User $user, Address $address): void
    {
        $this->authorize($user, $address);

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    private function authorize(User $user, Address $address): void
    {
        abort_unless($address->user_id === $user->id, 403);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fields(array $data): array
    {
        return [
            'type' => trim((string) ($data['type'] ?? '')) ?: 'shipping',
            'name' => trim((string) $data['name']),
            'mobile' => trim((string) $data['mobile']),
            'address_line1' => trim((string) $data['address_line1']),
            'address_line2' => trim((string) ($data['address_line2'] ?? '')),
            'city' => trim((string) $data['city']),
            'state' => trim((string) $data['state']),
            'pincode' => trim((string) $data['pincode']),
        ];
    }
}
