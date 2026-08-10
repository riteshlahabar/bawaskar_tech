<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\User;
use App\Services\StorefrontSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontCartController extends Controller
{
    public function add(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request, $storefrontSession)) {
            return $response;
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $product = Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images', 'translations', 'inventoryBatches'])
            ->findOrFail($validated['product_id']);

        $storefrontSession->addToCart($request, $product, (float) $validated['quantity']);

        return $this->cartResponse($request, $storefrontSession, $product->translatedName().' added to cart.');
    }

    public function update(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request, $storefrontSession)) {
            return $response;
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $storefrontSession->updateCart($request, $validated['items']);

        return $this->cartResponse($request, $storefrontSession, 'Cart updated successfully.');
    }

    public function remove(Request $request, int $productId, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request, $storefrontSession)) {
            return $response;
        }

        $storefrontSession->removeFromCart($request, $productId);

        return $this->cartResponse($request, $storefrontSession, 'Item removed from cart.');
    }

    public function clear(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request, $storefrontSession)) {
            return $response;
        }

        $storefrontSession->clearCart($request);

        return $this->cartResponse($request, $storefrontSession, 'Cart cleared successfully.');
    }

    private function applyStoreLocale(Request $request): void
    {
        $locale = (string) $request->session()->get('store_locale', 'en');

        if ($locale !== '') {
            app()->setLocale($locale);
        }
    }
    private function guestRedirectResponse(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse|null
    {
        $user = $storefrontSession->user($request);
        if ($user && in_array($user->role, [User::ROLE_CUSTOMER, User::ROLE_DEALER], true)) {
            return null;
        }

        $redirectTo = (string) ($request->headers->get('referer') ?: route('store.page', ['page' => 'cart']));
        $loginUrl = route('store.page', ['page' => 'login', 'redirect_to' => $redirectTo]);
        $message = 'Please log in before adding products to cart.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => $message,
                'login_url' => $loginUrl,
            ], 401);
        }

        return redirect()->to($loginUrl)->with('error', $message);
    }

    private function cartResponse(Request $request, StorefrontSessionService $storefrontSession, string $message): JsonResponse|RedirectResponse
    {
        $summary = $storefrontSession->cartSummary($request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => (float) ($summary['count'] ?? 0),
                'subtotal' => (float) ($summary['subtotal'] ?? 0),
                'gst_total' => (float) ($summary['gst_total'] ?? 0),
                'grand_total' => (float) ($summary['grand_total'] ?? 0),
                'has_issues' => (bool) ($summary['has_issues'] ?? false),
                'items' => collect($summary['items'] ?? collect())
                    ->map(function (array $item): array {
                        $product = $item['product'];
                        $mrp = (float) $product->mrp;
                        $unitPrice = (float) $item['unit_price'];
                        $quantity = (float) $item['quantity'];
                        $lineBase = (float) $item['line_base'];
                        $lineTotal = (float) $item['line_total'];
                        $savings = max(0, ($mrp * $quantity) - $lineBase);

                        return [
                            'id' => $product->id,
                            'name' => $product->translatedName(),
                            'product_url' => route('store.product', ['product' => $product->id]),
                            'remove_url' => route('store.cart.remove', ['productId' => $product->id]),
                            'image_url' => $product->storefront_image_url,
                            'category_name' => data_get($product, 'category.name') ?: 'Product',
                            'unit_name' => data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs',
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'mrp' => $mrp,
                            'line_base' => $lineBase,
                            'line_total' => $lineTotal,
                            'available_stock' => (float) $item['available_stock'],
                            'has_issue' => (bool) $item['has_issue'],
                            'savings' => $savings,
                        ];
                    })
                    ->values()
                    ->all(),
            ]);
        }

        return back()->with('success', $message);
    }
}


