<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\DealerProfile;
use App\Models\User;
use App\Services\StorefrontSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StorefrontAuthController extends Controller
{
    public function login(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_DEALER])],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with(['dealerProfile.salesman', 'customerProfile'])
            ->where('role', $validated['role'])
            ->where(function ($query) use ($validated): void {
                $query->where('email', $validated['login'])
                    ->orWhere('mobile', $validated['login']);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Invalid login credentials.']);
        }

        if ($user->role === User::ROLE_DEALER && $user->status !== 'active') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Dealer account is pending admin approval.']);
        }

        if ($user->status !== 'active') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'This account is not active.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $storefrontSession->login($request, $user);

        return redirect()->to($request->input('redirect_to', route('store.page', ['page' => 'user-dashboard'])))
            ->with('success', 'Welcome back, '.$user->name.'.');
    }

    public function register(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_DEALER])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'firm_name' => ['nullable', 'string', 'max:255', 'required_if:role,dealer'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ]);

        $isDealer = $validated['role'] === User::ROLE_DEALER;

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => $isDealer ? 'pending_approval' : 'active',
            'mobile_verified_at' => now(),
        ]);

        if ($isDealer) {
            DealerProfile::query()->create([
                'user_id' => $user->id,
                'dealer_code' => 'DLR'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'firm_name' => $validated['firm_name'],
                'gst_number' => $validated['gst_number'] ?? null,
            ]);

            return redirect()->route('store.page', ['page' => 'login', 'role' => User::ROLE_DEALER])
                ->with('success', 'Dealer registration submitted. You can log in after admin approval.');
        }

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);
        $user->forceFill(['last_login_at' => now()])->save();
        $storefrontSession->login($request, $user);

        return redirect()->route('store.page', ['page' => 'user-dashboard'])
            ->with('success', 'Customer account created successfully.');
    }

    public function logout(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $storefrontSession->logout($request);

        return redirect()->route('store.home')->with('success', 'You have been logged out.');
    }
}
