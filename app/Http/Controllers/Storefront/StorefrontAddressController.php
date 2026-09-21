<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontAddressContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontAddressController extends Controller
{
    public function __construct(
        private readonly StorefrontSessionContextContract $session,
        private readonly StorefrontAddressContract $addresses,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $this->addresses->store($this->requireUser($request), $this->validated($request));

        return $this->back('Address added successfully.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->addresses->update($this->requireUser($request), $address, $this->validated($request));

        return $this->back('Address updated successfully.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->addresses->destroy($this->requireUser($request), $address);

        return $this->back('Address removed.');
    }

    public function makeDefault(Request $request, Address $address): RedirectResponse
    {
        $this->addresses->makeDefault($this->requireUser($request), $address);

        return $this->back('Default address updated.');
    }

    private function requireUser(Request $request): User
    {
        $storeUser = $this->session->user($request);
        abort_unless($storeUser, 403);

        return $storeUser;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:12'],
        ]);
    }

    private function back(string $message): RedirectResponse
    {
        return redirect()
            ->route('store.page', ['page' => 'user-dashboard'])
            ->with('success', $message);
    }
}
