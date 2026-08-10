@php
    $displayName = $product->translatedName();
    $displayDescription = $product->translatedDescription();
    $detailImages = $product->images->filter(fn ($image) => filled($image->url))->values();
    if ($detailImages->isEmpty()) {
        $detailImages = collect([(object) ['url' => $product->storefront_image_url]]);
    }
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
    $activeVariants = collect($product->variants ?? collect())->where('is_active', true)->values();
@endphp

<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row"><div class="col-12"><div class="breadcrumb-contain">
            <h2>{{ $displayName }}</h2>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('store.home') }}"><i class="fa-solid fa-house"></i></a></li>
                @if($product->category)<li class="breadcrumb-item"><a href="{{ route('store.category', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>@endif
                <li class="breadcrumb-item active">{{ $displayName }}</li>
            </ol></nav>
        </div></div></div>
    </div>
</section>

<section class="product-section">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-xxl-9 col-xl-8 col-lg-7 wow fadeInUp">
                <div class="row g-4">
                    <div class="col-xl-6 wow fadeInUp">
                        <div class="product-left-box">
                            <div class="row g-2">
                                <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
                                    <div class="product-main-2 no-arrow">
                                        @foreach($detailImages as $image)
                                            <div><div class="slider-image"><img src="{{ $image->url }}" class="img-fluid image_zoom_cls-{{ $loop->index }} blur-up lazyload" alt="{{ $displayName }}"></div></div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                                    <div class="left-slider-image-2 left-slider no-arrow slick-top">
                                        @foreach($detailImages as $image)
                                            <div><div class="sidebar-image"><img src="{{ $image->url }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}"></div></div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="right-box-contain">
                            @if($discount > 0)<h6 class="offer-top">{{ $discount }}% Off</h6>@endif
                            <h2 class="name">{{ $displayName }}</h2>
                            <div class="price-rating">
                                <h3 class="theme-color price">Rs. {{ number_format($price, 2) }} @if($mrp > $price)<del class="text-content">Rs. {{ number_format($mrp, 2) }}</del>@endif</h3>
                            </div>
                            <div class="product-contain"><p>{{ $displayDescription ?: 'Quality product for farmer requirements.' }}</p></div>
                            <div class="pickup-box">
                                <div class="product-info"><ul class="product-info-list product-info-list-2">
                                    <li>SKU : <a href="javascript:void(0)">{{ $product->sku }}</a></li>
                                    <li>Category : <a href="javascript:void(0)">{{ data_get($product, 'category.name') ?: 'Product' }}</a></li>
                                    <li>Brand : <a href="javascript:void(0)">{{ data_get($product, 'brand.name') ?: 'Bawaskar' }}</a></li>
                                    <li>Unit : <a href="javascript:void(0)">{{ $unitName }}</a></li>
                                    <li>Available Stock : <a href="javascript:void(0)">{{ number_format($availableStock, 2) }}</a></li>
                                    <li>Status : <a href="javascript:void(0)">{{ $isOutOfStock ? 'Out of Stock' : ($isLowStock ? ($product->low_stock_text ?: 'Low Stock') : 'In Stock') }}</a></li>
                                </ul></div>
                            </div>

                            @if($activeVariants->isNotEmpty())
                                <div class="product-package mb-3">
                                    <div class="product-title"><h4>Available Options</h4></div>
                                    <ul class="select-package">
                                        @foreach($activeVariants as $variant)
                                            <li>
                                                <a href="javascript:void(0)" class="{{ $variant->is_default ? 'active' : '' }}">
                                                    {{ $variant->group_name }}: {{ $variant->value }}
                                                    @if((float) $variant->price_difference !== 0.0)
                                                        ({{ (float) $variant->price_difference > 0 ? '+' : '' }}Rs. {{ number_format((float) $variant->price_difference, 2) }})
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="note-box product-package">
                                @if($isOutOfStock)
                                    <button class="btn btn-md bg-dark cart-button text-white w-100" disabled>Out of Stock</button>
                                @else
                                    <form method="POST" action="{{ route('store.cart.add') }}" class="row g-2 align-items-center" data-store-cart-add>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <div class="col-sm-4">
                                            <input type="number" class="form-control" name="quantity" value="1" min="0.001" step="0.001">
                                        </div>
                                        <div class="col-sm-8">
                                            <button type="submit" class="btn btn-md bg-dark cart-button text-white w-100">Add To Cart</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@if($product->additional_info || $product->care_instructions || $product->manufacturer_details || $product->seller_name || $product->detail_banner_image)
    <section class="product-list-section section-b-space">
        <div class="container-fluid-lg">
            @if($product->detail_banner_image)
                <a href="{{ $product->detail_banner_url ?: 'javascript:void(0)' }}" class="d-block mb-4">
                    <img src="{{ asset($product->detail_banner_image) }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
                </a>
            @endif
            <div class="row g-4">
                @if($product->additional_info)
                    <div class="col-md-6"><h4>Additional Info</h4><p class="text-content">{{ $product->additional_info }}</p></div>
                @endif
                @if($product->care_instructions)
                    <div class="col-md-6"><h4>Care Instructions</h4><p class="text-content">{{ $product->care_instructions }}</p></div>
                @endif
                @if($product->manufacturer_details)
                    <div class="col-md-6"><h4>{{ $product->manufacturer_title ?: 'Manufacturer Details' }}</h4><p class="text-content">{{ $product->manufacturer_details }}</p></div>
                @endif
                @if($product->seller_name)
                    <div class="col-md-6"><h4>Seller Information</h4><p class="text-content mb-1">{{ $product->seller_name }}</p>@if($product->seller_contact)<p class="text-content mb-1">{{ $product->seller_contact }}</p>@endif @if($product->seller_address)<p class="text-content mb-0">{{ $product->seller_address }}</p>@endif</div>
                @endif
            </div>
        </div>
    </section>
@endif

@if(collect($relatedProducts)->isNotEmpty())
    <section class="product-list-section section-b-space">
        <div class="container-fluid-lg">
            <div class="title"><h2>Related Products</h2></div>
            <div class="slider-6_1 product-wrapper">
                @foreach($relatedProducts as $relatedProduct)
                    @include('store.partials.product-card-4', ['product' => $relatedProduct])
                @endforeach
            </div>
        </div>
    </section>
@endif




