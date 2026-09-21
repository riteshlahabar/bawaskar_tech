<?php

namespace App\Contracts\Storefront;

use App\Models\User;

/**
 * SRP: saving what the website's "Edit Profile" popup lets a signed-in
 * customer or dealer change — their own name/email and default address.
 */
interface StorefrontProfileContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): void;
}
