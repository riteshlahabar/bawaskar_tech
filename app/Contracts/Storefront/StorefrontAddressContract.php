<?php

namespace App\Contracts\Storefront;

use App\Models\Address;
use App\Models\User;

/**
 * SRP: the website's Address Book tab — add, edit, remove and choose the
 * default delivery address for a signed-in customer or dealer.
 */
interface StorefrontAddressContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(User $user, array $data): Address;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Address $address, array $data): Address;

    public function destroy(User $user, Address $address): void;

    public function makeDefault(User $user, Address $address): void;
}
