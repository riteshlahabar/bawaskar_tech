<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Services\StorefrontSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontWishlistController extends Controller
{
    public function add(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = $this->wishlistProduct((int) $validated['product_id']);
        $storefrontSession->addToWishlist($request, $product);

        return $this->response(
            $request,
            $storefrontSession,
            true,
            $product->name.' added to wishlist.'
        );
    }

    public function remove(Request $request, int $productId, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $storefrontSession->removeFromWishlist($request, $productId);

        return $this->response(
            $request,
            $storefrontSession,
            false,
            'Item removed from wishlist.'
        );
    }

    public function toggle(Request $request, StorefrontSessionService $storefrontSession): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = $this->wishlistProduct((int) $validated['product_id']);
        $inWishlist = $storefrontSession->toggleWishlist($request, $product);

        return $this->response(
            $request,
            $storefrontSession,
            $inWishlist,
            $inWishlist
                ? $product->name.' added to wishlist.'
                : $product->name.' removed from wishlist.'
        );
    }

    private function wishlistProduct(int $productId): Product
    {
        return Product::query()
            ->with(['category', 'brand', 'unit', 'images', 'inventoryBatches'])
            ->findOrFail($productId);
    }

    private function response(
        Request $request,
        StorefrontSessionService $storefrontSession,
        bool $inWishlist,
        string $message
    ): JsonResponse|RedirectResponse {
        $summary = $storefrontSession->wishlistSummary($request);
        $audience = $storefrontSession->audience($request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'count' => (int) ($summary['count'] ?? 0),
                'ids' => array_values(array_map('intval', $summary['ids'] ?? [])),
                'items' => collect($summary['items'] ?? collect())
                    ->take(3)
                    ->map(function (Product $product) use ($audience): array {
                        $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);

                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'product_url' => route('store.product', ['product' => $product->id]),
                            'image_url' => $product->storefront_image_url,
                            'price' => $price,
                        ];
                    })
                    ->values()
                    ->all(),
            ]);
        }

        return back()->with('success', $message);
    }
}