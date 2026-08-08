@php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/deal/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $wowDelay = trim((string) ($wowDelay ?? ''));
@endphp

@if($wowDelay !== '')
    <div class="product-box-4 wow fadeInUp" data-wow-delay="{{ $wowDelay }}">
@else
    <div class="product-box-4 wow fadeInUp">
@endif
    <div class="product-image product-image-2">
        <a href="{{ $productUrl }}">
            <img src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $product->name }}">
        </a>

        <ul class="option">
            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Quick View">
                <a href="{{ $productUrl }}">
                    <i class="iconly-Show icli"></i>
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
            <h5 class="name text-title">{{ $product->homepage_title ?: $product->name }}</h5>
        </a>

        <h5 class="price theme-color">
            Rs. {{ number_format($price, 2) }}
            @if($mrp > $price)
                <del>Rs. {{ number_format($mrp, 2) }}</del>
            @endif
        </h5>

        <div class="addtocart_btn">
            <form method="POST" action="{{ route('store.cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="add-button addcart-button btn buy-button text-light">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </form>
        </div>
    </div>
</div>
