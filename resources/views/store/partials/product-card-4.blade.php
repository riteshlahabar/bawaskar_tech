@php
    $imageUrl = $product->storefront_image_url;
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $categoryName = data_get($product, 'category.name') ?: 'Product';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
    $cardOuterClass = trim((string) ($cardOuterClass ?? ''));
@endphp

<div class="{{ $cardOuterClass }}">
    <div class="product-box-4 wow fadeInUp">
        <div class="product-image product-image-2">
            <a href="{{ $productUrl }}">
                <img src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $product->name }}">
            </a>

            <ul class="option">
                <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                    <a href="{{ $productUrl }}">
                        <i class="iconly-Show icli"></i>
                    </a>
                </li>                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                    <a href="{{ route('store.page', ['page' => 'wishlist']) }}" class="notifi-wishlist">
                        <i class="iconly-Heart icli"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="product-detail">
            <ul class="rating">
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star"></i></li>
            </ul>
            <a href="{{ $productUrl }}">
                <h5 class="name text-title">{{ $product->name }}</h5>
            </a>
            <div class="price-box-4">
                @if($mrp > $price)
                    <h6 class="text-content mb-0 lh-sm"><del>Rs. {{ number_format($mrp, 2) }}</del></h6>
                @endif
                <h5 class="sold text-content mb-0 lh-sm">
                    <span class="theme-color price">Rs. {{ number_format($price, 2) }}</span>
                </h5>
            </div>
            <div class="price-qty mt-1 pt-0 align-items-start">
                <h5 class="text-content mb-0 lh-base">{{ $categoryName }} / {{ $unitName }}</h5>
                @if($isOutOfStock)
                    <button class="add-button addcart-button btn buy-button text-light" disabled>
                        <i class="fa-solid fa-ban"></i>
                    </button>
                @else
                    <form method="POST" action="{{ route('store.cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="add-button addcart-button btn buy-button text-light">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>
                @endif
            </div>
            @if($isOutOfStock)
                <div class="label-tag mt-2"><span>Out of Stock</span></div>
            @elseif($isLowStock)
                <div class="label-tag mt-2"><span>{{ $product->low_stock_text ?: 'Low Stock' }}</span></div>
            @elseif($discount > 0)
                <div class="label-tag mt-2"><span>{{ $discount }}% off</span></div>
            @endif
        </div>
    </div>
</div>






