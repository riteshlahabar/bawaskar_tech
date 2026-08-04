@php
    $detailImages = $product->images->isNotEmpty() ? $product->images : collect([(object) ['url' => asset('fastkart-store/images/product/category/1.jpg')]]);
    $price = (float) $product->customer_price;
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
@endphp

    <!-- Breadcrumb Section Start -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row"><div class="col-12"><div class="breadcrumb-contain">
                <h2>{{ $product->name }}</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('store.home') }}"><i class="fa-solid fa-house"></i></a></li>
                    @if($product->category)<li class="breadcrumb-item"><a href="{{ route('store.category', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>@endif
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ol></nav>
            </div></div></div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Product Left Sidebar Start -->
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
                                                <div><div class="slider-image"><img src="{{ $image->url }}" class="img-fluid image_zoom_cls-{{ $loop->index }} blur-up lazyload" alt="{{ $product->name }}"></div></div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                                        <div class="left-slider-image-2 left-slider no-arrow slick-top">
                                            @foreach($detailImages as $image)
                                                <div><div class="sidebar-image"><img src="{{ $image->url }}" class="img-fluid blur-up lazyload" alt="{{ $product->name }}"></div></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="right-box-contain">
                                @if($discount > 0)<h6 class="offer-top">{{ $discount }}% Off</h6>@endif
                                <h2 class="name">{{ $product->name }}</h2>
                                <div class="price-rating">
                                    <h3 class="theme-color price">Rs. {{ number_format($price, 2) }} @if($mrp > $price)<del class="text-content">Rs. {{ number_format($mrp, 2) }}</del>@endif</h3>
                                </div>
                                <div class="product-contain"><p>{{ $product->description ?: 'Quality product for farmer requirements.' }}</p></div>
                                <div class="pickup-box">
                                    <div class="product-info"><ul class="product-info-list product-info-list-2">
                                        <li>SKU : <a href="javascript:void(0)">{{ $product->sku }}</a></li>
                                        <li>Category : <a href="javascript:void(0)">{{ data_get($product, 'category.name') ?: 'Product' }}</a></li>
                                        <li>Brand : <a href="javascript:void(0)">{{ data_get($product, 'brand.name') ?: 'Bawaskar' }}</a></li>
                                        <li>Unit : <a href="javascript:void(0)">{{ $unitName }}</a></li>
                                        <li>Available Stock : <a href="javascript:void(0)">{{ number_format($product->available_stock, 2) }}</a></li>
                                    </ul></div>
                                </div>
                                <div class="note-box product-package"><button class="btn btn-md bg-dark cart-button text-white w-100">Add To Cart</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Left Sidebar End -->

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