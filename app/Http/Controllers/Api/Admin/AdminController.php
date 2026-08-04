<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Catalog\Product;
use App\Models\Communication\AppTranslation;
use App\Models\DealerProfile;
use App\Models\Field\SalesmanAsset;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Models\SalesmanProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends ApiController
{
    public function dashboard(Request $request): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        return $this->success([
            'salesmen' => User::query()->where('role', User::ROLE_SALESMAN)->count(),
            'dealers' => User::query()->where('role', User::ROLE_DEALER)->count(),
            'pending_dealers' => User::query()->where('role', User::ROLE_DEALER)->where('status', 'pending_approval')->count(),
            'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            'admin_review_orders' => Order::query()->where('status', 'admin_review')->count(),
        ]);
    }

    public function createSalesman(Request $request): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8'],
            'employee_code' => ['required', 'string', 'max:50', 'unique:salesman_profiles,employee_code'],
            'territory' => ['nullable', 'string', 'max:255'],
            'basic_salary' => ['nullable', 'numeric'],
            'target_amount' => ['nullable', 'numeric'],
        ]);

        $salesman = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_SALESMAN,
            'status' => 'active',
        ]);

        SalesmanProfile::query()->create([
            'user_id' => $salesman->id,
            'employee_code' => $validated['employee_code'],
            'territory' => $validated['territory'] ?? null,
            'basic_salary' => $validated['basic_salary'] ?? 0,
            'target_amount' => $validated['target_amount'] ?? 0,
        ]);

        return $this->success(['salesman' => $salesman->load('salesmanProfile')], 'Salesman created.', 201);
    }

    public function dealers(Request $request): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        return $this->success(['dealers' => User::query()->with('dealerProfile.salesman')->where('role', User::ROLE_DEALER)->latest()->paginate(20)]);
    }

    public function approveDealer(Request $request, User $dealer): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'salesman_id' => ['required', 'integer', 'exists:users,id'],
            'credit_limit' => ['nullable', 'numeric'],
        ]);

        $salesman = User::query()->where('role', User::ROLE_SALESMAN)->findOrFail($validated['salesman_id']);

        if ($dealer->role !== User::ROLE_DEALER) {
            return $this->fail('Selected user is not a dealer.');
        }

        $dealer->forceFill(['status' => 'active'])->save();
        $dealer->dealerProfile()->update([
            'salesman_id' => $salesman->id,
            'credit_limit' => $validated['credit_limit'] ?? $dealer->dealerProfile?->credit_limit ?? 0,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        return $this->success(['dealer' => $dealer->fresh('dealerProfile.salesman')], 'Dealer approved and assigned.');
    }

    public function assignDealer(Request $request, User $dealer): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate(['salesman_id' => ['required', 'integer', 'exists:users,id']]);
        $salesman = User::query()->where('role', User::ROLE_SALESMAN)->findOrFail($validated['salesman_id']);

        if ($dealer->role !== User::ROLE_DEALER) {
            return $this->fail('Selected user is not a dealer.');
        }

        $dealer->dealerProfile()->update(['salesman_id' => $salesman->id]);

        return $this->success(['dealer' => $dealer->fresh('dealerProfile.salesman')], 'Dealer assigned.');
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', 'max:40'],
            'hsn_code' => ['nullable', 'string', 'max:40'],
            'gst_percent' => ['nullable', 'numeric'],
            'mrp' => ['required', 'numeric'],
            'customer_price' => ['required', 'numeric'],
            'dealer_price' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_visible_to_customers' => ['boolean'],
            'is_visible_to_dealers' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $product = Product::query()->create($validated);
        $this->bumpCatalogCacheVersion();

        return $this->success(['product' => $product], 'Product created.', 201);
    }

    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'packing', 'dispatched', 'delivered', 'cancelled'])],
        ]);

        $order->forceFill([
            'status' => $validated['status'],
            'approved_by' => $validated['status'] === 'approved' ? $admin->id : $order->approved_by,
            'approved_at' => $validated['status'] === 'approved' ? now() : $order->approved_at,
        ])->save();

        return $this->success(['order' => $order->fresh('items.product')], 'Order status updated.');
    }

    public function upsertDispatch(Request $request, Order $order): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'dispatch_no' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', 'max:40'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'tracking_no' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'string', 'max:255'],
            'current_latitude' => ['nullable', 'numeric'],
            'current_longitude' => ['nullable', 'numeric'],
        ]);

        $dispatch = Dispatch::query()->updateOrCreate(['order_id' => $order->id, 'dispatch_no' => $validated['dispatch_no']], $validated);

        return $this->success(['dispatch' => $dispatch], 'Dispatch updated.');
    }

    public function assignAsset(Request $request, User $salesman): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        if ($salesman->role !== User::ROLE_SALESMAN) {
            return $this->fail('Selected user is not a salesman.');
        }

        $validated = $request->validate([
            'asset_type' => ['required', 'string', 'max:40'],
            'asset_name' => ['required', 'string', 'max:255'],
            'serial_no' => ['nullable', 'string', 'max:255'],
            'issued_on' => ['nullable', 'date'],
            'condition' => ['nullable', 'string', 'max:255'],
        ]);

        $asset = SalesmanAsset::query()->create($validated + ['salesman_id' => $salesman->id]);

        return $this->success(['asset' => $asset], 'Salesman asset assigned.', 201);
    }

    public function upsertTranslation(Request $request): JsonResponse
    {
        $admin = $this->requireUser($request, User::ROLE_ADMIN);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'group' => ['nullable', 'string', 'max:80'],
            'translation_key' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'value' => ['required', 'string'],
        ]);

        $translation = AppTranslation::query()->updateOrCreate(
            ['translation_key' => $validated['translation_key'], 'locale' => $validated['locale']],
            ['group' => $validated['group'] ?? 'app', 'value' => $validated['value'], 'is_active' => true]
        );
        $this->bumpCatalogCacheVersion();

        return $this->success(['translation' => $translation], 'Translation saved.');
    }
    private function bumpCatalogCacheVersion(): void
    {
        Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
    }
}
