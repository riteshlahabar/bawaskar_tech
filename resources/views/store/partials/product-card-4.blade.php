@php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $price = (float) $product->customer_price;
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $categoryName = data_get($product, 'category.name') ?: 'Product';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
@endphp

<div>
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
                </li>
                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                    <a href="javascript:void(0)" class="notifi-wishlist">
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
            <h5 class="sold text-content">
                <span class="theme-color price">Rs. {{ number_format($price, 2) }}</span>
                @if($mrp > $price)
                    <del>Rs. {{ number_format($mrp, 2) }}</del>
                @endif
            </h5>
            <div class="price-qty">
                <h5 class="text-content">{{ $categoryName }} / {{ $unitName }}</h5>
                <button class="add-button addcart-button btn buy-button text-light" onclick="location.href='{{ $productUrl }}'" @disabled($isOutOfStock)>
                    <i class="fa-solid {{ $isOutOfStock ? 'fa-ban' : 'fa-plus' }}"></i>
                </button>
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