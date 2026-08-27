<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StorefrontSessionService
{
    private const USER_ID_KEY = 'storefront.user_id';
    private const USER_ROLE_KEY = 'storefront.user_role';
    private const CART_KEY = 'storefront.cart';
    private const WISHLIST_KEY = 'storefront.wishlist';
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
            $this->clearWishlist($request);
        }
    }

    public function logout(Request $request): void
    {
        $request->session()->forget([
            self::USER_ID_KEY,
            self::USER_ROLE_KEY,
            self::CART_KEY,
            self::WISHLIST_KEY,
            self::LAST_ORDER_ID_KEY,
        ]);

        $request->session()->regenerateToken();
    }

    public function addToCart(Request $request, Product $product, float $quantity, ?ProductVariant $variant = null): void
    {
        $quantity = round($quantity, 3);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $this->assertVisibleForAudience($product, $this->audience($request));

        $variant = $variant ?: $product->mainVariant();
        if ($variant && ((int) $variant->product_id !== (int) $product->id || ! $variant->is_active)) {
            throw ValidationException::withMessages(['variant_id' => 'The selected size/pack is not available.']);
        }

        $cart = $this->cart($request);
        $lineKey = $this->cartLineKey($product->id, $variant?->id);
        $nextQuantity = round(((float) data_get($cart, $lineKey.'.quantity', 0)) + $quantity, 3);
        $unitQuantity = $this->unitQuantity($request, $nextQuantity, $variant);
        $availableStock = $this->availableStock($product, $variant);

        if ($unitQuantity > $availableStock + 0.0001) {
            throw ValidationException::withMessages([
                'quantity' => 'Only '.number_format($availableStock, 3).' retail packs are available for '.$product->translatedName().'.',
            ]);
        }

        $cart[$lineKey] = [
            'product_id' => (int) $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $nextQuantity,
        ];
        $this->storeCart($request, $cart);
    }

    public function updateCart(Request $request, array $items): void
    {
        $currentCart = $this->cart($request);
        $products = $this->productsForCart($request, $currentCart);
        $cart = [];

        foreach ($items as $lineKey => $quantity) {
            $quantity = round((float) $quantity, 3);

            if ($quantity <= 0) {
                continue;
            }

            $entry = $currentCart[(string) $lineKey] ?? null;
            if (! is_array($entry)) {
                continue;
            }

            $product = $products->get((int) $entry['product_id']);
            $variant = $product ? $this->variantForEntry($product, $entry) : null;
            if (! $product || (! empty($entry['variant_id']) && ! $variant)) continue;

            $unitQuantity = $this->unitQuantity($request, $quantity, $variant);
            $availableStock = $this->availableStock($product, $variant);

            if ($unitQuantity > $availableStock + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'Only '.number_format($availableStock, 3).' retail packs are available for '.$product->translatedName().'.',
                ]);
            }

            $cart[(string) $lineKey] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
            ];
        }

        $this->storeCart($request, $cart);
    }

    public function removeFromCart(Request $request, string $lineKey): void
    {
        $cart = $this->cart($request);
        unset($cart[$lineKey]);

        if (ctype_digit($lineKey)) {
            unset($cart[$this->cartLineKey((int) $lineKey, null)]);
        }
        $this->storeCart($request, $cart);
    }

    public function clearCart(Request $request): void
    {
        $request->session()->forget(self::CART_KEY);
    }

    public function addToWishlist(Request $request, Product $product): void
    {
        $this->assertVisibleForAudience($product, $this->audience($request));

        $wishlist = $this->wishlist($request);
        if (! in_array($product->id, $wishlist, true)) {
            $wishlist[] = $product->id;
        }

        $this->storeWishlist($request, $wishlist);
    }

    public function removeFromWishlist(Request $request, int $productId): void
    {
        $wishlist = collect($this->wishlist($request))
            ->reject(fn (int $storedProductId): bool => $storedProductId === $productId)
            ->values()
            ->all();

        $this->storeWishlist($request, $wishlist);
    }

    public function toggleWishlist(Request $request, Product $product): bool
    {
        if ($this->hasInWishlist($request, $product->id)) {
            $this->removeFromWishlist($request, $product->id);

            return false;
        }

        $this->addToWishlist($request, $product);

        return true;
    }

    public function clearWishlist(Request $request): void
    {
        $request->session()->forget(self::WISHLIST_KEY);
    }

    public function cart(Request $request): array
    {
        $stored = $request->session()->get(self::CART_KEY, []);
        if (! is_array($stored)) {
            return [];
        }

        $cart = [];

        foreach ($stored as $storedKey => $storedValue) {
            if (is_array($storedValue)) {
                $productId = (int) ($storedValue['product_id'] ?? 0);
                $variantId = (int) ($storedValue['variant_id'] ?? 0) ?: null;
                $quantity = round((float) ($storedValue['quantity'] ?? 0), 3);
            } else {
                $productId = (int) $storedKey;
                $variantId = null;
                $quantity = round((float) $storedValue, 3);
            }

            if ($productId > 0 && $quantity > 0) {
                $lineKey = $this->cartLineKey($productId, $variantId);
                $cart[$lineKey] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cart;
    }

    public function wishlist(Request $request): array
    {
        $stored = $request->session()->get(self::WISHLIST_KEY, []);
        if (! is_array($stored)) {
            return [];
        }

        return collect($stored)
            ->map(fn ($productId): int => (int) $productId)
            ->filter(fn (int $productId): bool => $productId > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function hasInWishlist(Request $request, int $productId): bool
    {
        return in_array($productId, $this->wishlist($request), true);
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

        foreach ($cart as $lineKey => $entry) {
            $product = $products->get((int) $entry['product_id']);
            if (! $product) {
                $hasIssues = true;
                continue;
            }

            $variant = $this->variantForEntry($product, $entry);
            if (! empty($entry['variant_id']) && ! $variant) {
                $hasIssues = true;
                continue;
            }

            $quantity = (float) $entry['quantity'];
            $unitsPerCase = $variant ? max(1, (float) $variant->units_per_case) : 1.0;
            $unitQuantity = $audience === 'dealer' ? round($quantity * $unitsPerCase, 3) : $quantity;
            $unitPrice = $this->resolveUnitPrice($product, $audience, $variant);
            $priceTotal = round($unitPrice * $unitQuantity, 2);
            $gstPercent = (float) $product->gst_percent;
            if ($variant) {
                $lineTotal = $priceTotal;
                $lineBase = $gstPercent > 0 ? round($lineTotal / (1 + ($gstPercent / 100)), 2) : $lineTotal;
                $gstAmount = round($lineTotal - $lineBase, 2);
            } else {
                $lineBase = $priceTotal;
                $gstAmount = round($lineBase * ($gstPercent / 100), 2);
                $lineTotal = round($lineBase + $gstAmount, 2);
            }
            $availableStock = $this->availableStock($product, $variant);
            $itemHasIssue = $availableStock + 0.0001 < $unitQuantity;

            $items->push([
                'line_key' => (string) $lineKey,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'unit_quantity' => $unitQuantity,
                'units_per_case' => $unitsPerCase,
                'unit_price' => $unitPrice,
                'line_base' => $lineBase,
                'gst_amount' => $gstAmount,
                'line_total' => $lineTotal,
                'available_stock' => $availableStock,
                'has_issue' => $itemHasIssue,
                'quantity_label' => $audience === 'dealer' && $variant ? 'case(s)' : 'retail pack(s)',
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

    public function wishlistSummary(Request $request): array
    {
        $wishlist = collect($this->wishlist($request));
        $products = $this->productsForWishlist($request, $wishlist->all());
        $items = $wishlist
            ->map(fn (int $productId): ?Product => $products->get($productId))
            ->filter()
            ->values();

        return [
            'items' => $items,
            'count' => $items->count(),
            'ids' => $items->pluck('id')->all(),
        ];
    }

    public function checkoutItems(Request $request): array
    {
        return collect($this->cartSummary($request)['items'])
            ->map(fn (array $item): array => [
                'product_id' => $item['product']->id,
                'variant_id' => $item['variant']?->id,
                'quantity' => $item['unit_quantity'],
                'pack_quantity' => $item['quantity'],
                'units_per_case' => $item['units_per_case'],
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

    private function storeWishlist(Request $request, array $wishlist): void
    {
        $request->session()->put(self::WISHLIST_KEY, array_values($wishlist));
    }

    private function productsForCart(Request $request, array $cart): Collection
    {
        $productIds = collect($cart)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images', 'translations', 'inventoryBatches', 'variants.inventoryBatches'])
            ->visibleFor($this->audience($request))
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');
    }

    private function productsForWishlist(Request $request, array $wishlist): Collection
    {
        $productIds = collect($wishlist)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images', 'translations', 'inventoryBatches', 'variants.inventoryBatches'])
            ->visibleFor($this->audience($request))
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');
    }

    private function resolveUnitPrice(Product $product, string $audience, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $variant->priceFor($audience);
        }

        return (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    }

    private function cartLineKey(int $productId, ?int $variantId): string
    {
        return $productId.':'.($variantId ?: 0);
    }

    private function variantForEntry(Product $product, array $entry): ?ProductVariant
    {
        $variantId = (int) ($entry['variant_id'] ?? 0);
        if ($variantId <= 0) return null;

        return $product->variants->firstWhere('id', $variantId);
    }

    private function unitQuantity(Request $request, float $quantity, ?ProductVariant $variant): float
    {
        if ($this->audience($request) !== 'dealer' || ! $variant) {
            return round($quantity, 3);
        }

        return round($quantity * max(1, (float) $variant->units_per_case), 3);
    }

    private function availableStock(Product $product, ?ProductVariant $variant): float
    {
        return $variant ? (float) $variant->available_stock : (float) $product->available_stock;
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


