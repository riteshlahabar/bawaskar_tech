<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Services\StorefrontSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontCartController extends Controller
{
    public function add(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $product = Product::query()
            ->with(['category', 'brand', 'unit', 'images', 'inventoryBatches'])
            ->findOrFail($validated['product_id']);

        $storefrontSession->addToCart($request, $product, (float) $validated['quantity']);

        return back()->with('success', $product->name.' added to cart.');
    }

    public function update(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $storefrontSession->updateCart($request, $validated['items']);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove(Request $request, int $productId, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $storefrontSession->removeFromCart($request, $productId);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request, StorefrontSessionService $storefrontSession): RedirectResponse
    {
        $storefrontSession->clearCart($request);

        return back()->with('success', 'Cart cleared successfully.');
    }
}
