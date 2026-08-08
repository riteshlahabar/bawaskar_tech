@php
    $imageUrl = $product->storefront_image_url;
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
@endphp

<div>
    <div class="product-box-3 h-100 wow fadeInUp">
        <div class="product-header">
            <div class="product-image">
                <a href="{{ $productUrl }}">
                    <img src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $product->name }}">
                </a>
                <ul class="product-option">
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                        <a href="{{ $productUrl }}"><i data-feather="eye"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="product-footer">
            <div class="product-detail">
                <span class="span-name">{{ data_get($product, 'category.name') ?: 'Product' }}</span>
                <a href="{{ $productUrl }}"><h5 class="name">{{ $product->name }}</h5></a>
                <p class="text-content mt-1 mb-2">{{ str($product->description ?: 'Quality farmer product')->limit(75) }}</p>
                <h6 class="unit">{{ $unitName }}</h6>
                <h5 class="price">
                    <span class="theme-color">Rs. {{ number_format($price, 2) }}</span>
                    @if($mrp > $price)
                        <del>Rs. {{ number_format($mrp, 2) }}</del>
                    @endif
                </h5>
                @if($isOutOfStock)
                    <h6 class="theme-color">Out of Stock</h6>
                @elseif($isLowStock)
                    <h6 class="theme-color">{{ $product->low_stock_text ?: 'Low Stock' }}</h6>
                @elseif($discount > 0)
                    <h6 class="theme-color">{{ $discount }}% off</h6>
                @endif
                <div class="add-to-cart-box bg-white">
                    @if($isOutOfStock)
                        <button class="btn btn-add-cart addcart-button" disabled>Out of Stock</button>
                    @else
                        <form method="POST" action="{{ route('store.cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-add-cart addcart-button">Add To Cart</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



