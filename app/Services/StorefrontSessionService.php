<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StorefrontSessionService
{
    private const USER_ID_KEY = 'storefront.user_id';
    private const USER_ROLE_KEY = 'storefront.user_role';
    private const CART_KEY = 'storefront.cart';
    private const LAST_ORDER_ID_KEY = 'storefront.last_order_id';

    public function user(Request $request): ?User
    {
        $userId = (int) $request->session()->get(self::USER_ID_KEY, 0);
        $role = (string) $request->session()->get(self::USER_ROLE_KEY, '');

        if ($userId <= 0 || ! in_array($role, [User::ROLE_CUSTOMER, User::ROLE_DEALER], true)) {
            return null;
        }

        $user = User::query()
            ->with(['customerProfile', 'dealerProfile.salesman', 'addresses'])
            ->whereKey($userId)
            ->where('role', $role)
            ->first();

        if (! $user || ! in_array($user->status, ['active', 'pending_approval'], true)) {
            $this->logout($request);

            return null;
        }

        return $user;
    }

    public function audience(Request $request): string
    {
        return $this->user($request)?->role === User::ROLE_DEALER ? 'dealer' : 'customer';
    }

    public function login(Request $request, User $user): void
    {
        $previousRole = (string) $request->session()->get(self::USER_ROLE_KEY, '');

        $request->session()->regenerate();
        $request->session()->put(self::USER_ID_KEY, $user->id);
        $request->session()->put(self::USER_ROLE_KEY, $user->role);

        if ($previousRole !== '' && $previousRole !== $user->role) {
            $this->clearCart($request);
        }
    }

    public function logout(Request $request): void
    {
        $request->session()->forget([
            self::USER_ID_KEY,
            self::USER_ROLE_KEY,
            self::CART_KEY,
            self::LAST_ORDER_ID_KEY,
        ]);

        $request->session()->regenerateToken();
    }

    public function addToCart(Request $request, Product $product, float $quantity): void
    {
        $quantity = round($quantity, 3);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $this->assertVisibleForAudience($product, $this->audience($request));

        $cart = $this->cart($request);
        $nextQuantity = round(((float) ($cart[$product->id] ?? 0)) + $quantity, 3);

        if ($nextQuantity > $product->available_stock + 0.0001) {
            throw ValidationException::withMessages([
                'quantity' => 'Only '.number_format($product->available_stock, 3).' quantity is available for '.$product->name.'.',
            ]);
        }

        $cart[$product->id] = $nextQuantity;
        $this->storeCart($request, $cart);
    }

    public function updateCart(Request $request, array $items): void
    {
        $products = $this->productsForCart($request, $items);
        $cart = [];

        foreach ($items as $productId => $quantity) {
            $quantity = round((float) $quantity, 3);

            if ($quantity <= 0) {
                continue;
            }

            $product = $products->get((int) $productId);
            if (! $product) {
                continue;
            }

            if ($quantity > $product->available_stock + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'Only '.number_format($product->available_stock, 3).' quantity is available for '.$product->name.'.',
                ]);
            }

            $cart[$product->id] = $quantity;
        }

        $this->storeCart($request, $cart);
    }

    public function removeFromCart(Request $request, int $productId): void
    {
        $cart = $this->cart($request);
        unset($cart[$productId]);
        $this->storeCart($request, $cart);
    }

    public function clearCart(Request $request): void
    {
        $request->session()->forget(self::CART_KEY);
    }

    public function cart(Request $request): array
    {
        $stored = $request->session()->get(self::CART_KEY, []);
        if (! is_array($stored)) {
            return [];
        }

        $cart = [];

        foreach ($stored as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = round((float) $quantity, 3);

            if ($productId > 0 && $quantity > 0) {
                $cart[$productId] = $quantity;
            }
        }

        return $cart;
    }

    public function cartSummary(Request $request): array
    {
        $cart = $this->cart($request);
        $products = $this->productsForCart($request, $cart);
        $audience = $this->audience($request);
        $subtotal = 0.0;
        $gstTotal = 0.0;
        $count = 0.0;
        $hasIssues = false;
        $items = collect();

        foreach ($cart as $productId => $quantity) {
            $product = $products->get((int) $productId);
            if (! $product) {
                $hasIssues = true;
                continue;
            }

            $unitPrice = $this->resolveUnitPrice($product, $audience);
            $lineBase = round($unitPrice * $quantity, 2);
            $gstAmount = round($lineBase * ((float) $product->gst_percent / 100), 2);
            $availableStock = (float) $product->available_stock;
            $itemHasIssue = $availableStock + 0.0001 < $quantity;

            $items->push([
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_base' => $lineBase,
                'gst_amount' => $gstAmount,
                'line_total' => $lineBase + $gstAmount,
                'available_stock' => $availableStock,
                'has_issue' => $itemHasIssue,
            ]);

            $subtotal += $lineBase;
            $gstTotal += $gstAmount;
            $count += $quantity;
            $hasIssues = $hasIssues || $itemHasIssue;
        }

        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => round($subtotal, 2),
            'gst_total' => round($gstTotal, 2),
            'grand_total' => round($subtotal + $gstTotal, 2),
            'has_issues' => $hasIssues,
        ];
    }

    public function checkoutItems(Request $request): array
    {
        return collect($this->cartSummary($request)['items'])
            ->map(fn (array $item): array => [
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
            ])
            ->values()
            ->all();
    }

    public function setLastOrderId(Request $request, int $orderId): void
    {
        $request->session()->put(self::LAST_ORDER_ID_KEY, $orderId);
    }

    public function lastOrderId(Request $request): ?int
    {
        $orderId = (int) $request->session()->get(self::LAST_ORDER_ID_KEY, 0);

        return $orderId > 0 ? $orderId : null;
    }

    private function storeCart(Request $request, array $cart): void
    {
        $request->session()->put(self::CART_KEY, $cart);
    }

    private function productsForCart(Request $request, array $cart): Collection
    {
        $productIds = collect($cart)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->with(['category', 'brand', 'unit', 'images', 'inventoryBatches'])
            ->visibleFor($this->audience($request))
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');
    }

    private function resolveUnitPrice(Product $product, string $audience): float
    {
        return (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    }

    private function assertVisibleForAudience(Product $product, string $audience): void
    {
        $column = $audience === 'dealer' ? 'is_visible_to_dealers' : 'is_visible_to_customers';

        if (! $product->is_active || ! (bool) $product->{$column}) {
            throw ValidationException::withMessages([
                'product' => 'This product is not available for the selected account.',
            ]);
        }
    }
}
