@php
    $sectionKey = $sectionKey ?? 'top-selling-items';
    $sectionTitle = $sectionTitle ?? 'Top Selling Items';
    $products = collect($products ?? collect())->filter()->take(8)->values();
    $productColumns = $products->chunk(2)->values();
    $dealProduct = $dealProduct ?? null;
    $audience = $storeAudience ?? 'customer';

    if (! isset($showDealTimer)) {
        $showDealTimer = $dealProduct
            && $dealProduct->is_offer_active
            && $dealProduct->offer_end_at
            && $dealProduct->offer_end_at->isFuture();
    }

    if ($dealProduct) {
        $dealImage = optional($dealProduct->images->first())->url ?: asset('fastkart-store/images/grocery/deal/big.png');
        $dealUrl = route('store.product', ['product' => $dealProduct->id]);
        $dealPrice = (float) ($audience === 'dealer' ? $dealProduct->dealer_price : $dealProduct->customer_price);
        $dealMrp = (float) $dealProduct->mrp;
        $dealStock = max(0, (float) ($dealProduct->available_stock ?? 0));
        $progressWidth = min(100, max(16, (int) round(min($dealStock, 50) * 2)));
    }
@endphp

@if($products->isNotEmpty() || $dealProduct)
    <section class="product-section product-section-3" id="home-section-{{ $sectionKey }}">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>{{ $sectionTitle }}</h2>
            </div>

            <div class="row g-sm-4 g-3">
                @if($dealProduct)
                    <div class="col-xxl-4 col-lg-5 order-lg-2">
                        <div class="product-bg-image wow fadeInUp">
                            <div class="product-title product-warning">
                                <h2>Special Offer</h2>
                            </div>

                            <div class="product-box-4 product-box-3 rounded-0">
                                <div class="deal-box">
                                    <div class="circle-box">
                                        <div class="shape-circle">
                                            <img src="{{ asset('fastkart-store/images/grocery/circle.svg') }}" class="blur-up lazyload" alt="">
                                            <div class="shape-text">
                                                <h6>Hot <br> Deal</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="top-selling-slider product-arrow">
                                    <div>
                                        <div class="product-image">
                                            <a href="{{ $dealUrl }}">
                                                <img src="{{ $dealImage }}" class="img-fluid product-image blur-up lazyload" alt="{{ $dealProduct->name }}">
                                            </a>

                                            <ul class="option">
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Quick View">
                                                    <a href="{{ $dealUrl }}">
                                                        <i class="iconly-Show icli"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="product-detail text-center">
                                            <ul class="rating justify-content-center">
                                                <li><i data-feather="star" class="fill"></i></li>
                                                <li><i data-feather="star" class="fill"></i></li>
                                                <li><i data-feather="star" class="fill"></i></li>
                                                <li><i data-feather="star" class="fill"></i></li>
                                                <li><i data-feather="star"></i></li>
                                            </ul>

                                            <a href="{{ $dealUrl }}">
                                                <h3 class="name w-100 mx-auto text-center text-title">{{ $dealProduct->homepage_title ?: $dealProduct->name }}</h3>
                                            </a>

                                            <h3 class="price theme-color d-flex justify-content-center">
                                                Rs. {{ number_format($dealPrice, 2) }}
                                                @if($dealMrp > $dealPrice)
                                                    <del class="delete-price">Rs. {{ number_format($dealMrp, 2) }}</del>
                                                @endif
                                            </h3>

                                            <div class="progress custom-progressbar">
                                                <div class="progress-bar" style="width: {{ $progressWidth }}%" role="progressbar"></div>
                                            </div>

                                            <h5 class="text-content">Solid : <span class="text-dark">{{ rtrim(rtrim(number_format($dealStock, 3, '.', ''), '0'), '.') ?: '0' }} items</span>
                                                <span class="ms-auto text-content">Hurry up offer end in</span></h5>

                                            @if($showDealTimer)
                                                <div class="timer timer-2 ms-0 my-4 homepage-deal-timer" data-end-at="{{ $dealProduct->offer_end_at->toIso8601String() }}">
                                                    <ul class="d-flex justify-content-center">
                                                        <li><div class="counter"><div class="days"><h6>0</h6></div></div></li>
                                                        <li><div class="counter"><div class="hours"><h6>0</h6></div></div></li>
                                                        <li><div class="counter"><div class="minutes"><h6>0</h6></div></div></li>
                                                        <li><div class="counter"><div class="seconds"><h6>0</h6></div></div></li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="{{ $dealProduct ? 'col-xxl-8 col-lg-7 order-lg-1' : 'col-12' }}">
                    <div class="slider-5_2 img-slider">
                        @foreach($productColumns as $columnProducts)
                            <div>
                                @foreach($columnProducts as $productIndex => $product)
                                    @include('store.partials.top-selling-product-card', [
                                        'product' => $product,
                                        'wowDelay' => $productIndex === 1 ? '0.05s' : '',
                                    ])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
