@php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $price = (float) $product->customer_price;
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
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
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                        <a href="javascript:void(0)" class="notifi-wishlist"><i data-feather="heart"></i></a>
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
                @if($discount > 0)
                    <h6 class="theme-color">{{ $discount }}% off</h6>
                @endif
                <div class="add-to-cart-box bg-white">
                    <button class="btn btn-add-cart addcart-button" onclick="location.href='{{ $productUrl }}'">View Product</button>
                </div>
            </div>
        </div>
    </div>
</div>