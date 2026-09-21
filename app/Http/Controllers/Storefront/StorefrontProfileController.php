<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Location\UserLocationContract;
use App\Contracts\Storefront\StorefrontProfileContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorefrontProfileController extends Controller
{
    public function __construct(
        private readonly StorefrontSessionContextContract $session,
        private readonly StorefrontProfileContract $profile,
        private readonly UserLocationContract $location,
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $storeUser = $this->session->user($request);
        abort_unless($storeUser, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($storeUser->id)],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
        ] + $this->location->rules(), [
            'name.required' => 'Please enter your full name.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'state_code.required' => 'Please select state.',
            'district_code.required' => 'Please select district.',
            'subdistrict_code.required' => 'Please select taluka.',
            'subdistrict_name.required_if' => 'Please type the taluka name.',
            'city_village.required' => 'Please enter city or village.',
            'pincode.required' => 'Please enter pincode.',
            'pincode.regex' => 'Pincode must be 6 digits.',
        ]);

        $this->profile->update($storeUser, $validated);

        return redirect()
            ->route('store.page', ['page' => 'user-dashboard'])
            ->with('success', 'Profile updated successfully.');
    }
}
