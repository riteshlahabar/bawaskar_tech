@php
    $homepageRows = collect(data_get($homeContent ?? [], 'homepageRows', collect()));

    $isProduct = fn ($entry) => $entry instanceof \App\Models\Catalog\Product;

    $entryTitle = function ($entry, $fallback = '') use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_title ?: $entry->name ?: $fallback;
        }

        return $entry->title ?: $fallback;
    };

    $entrySubtitle = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_subtitle ?: $entry->sale_badge_text;
        }

        return $entry->subtitle;
    };

    $entryDescription = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_description ?: $entry->short_description;
        }

        return $entry->description;
    };

    $entryImage = function ($entry, string $type = 'main', string $fallback = '') use ($isProduct) {
        if ($isProduct($entry)) {
            $field = match ($type) {
                'mobile' => 'homepage_mobile_image_path',
                'logo' => 'homepage_logo_image_path',
                'offer' => 'homepage_offer_image_path',
                default => 'homepage_image_path',
            };

            $path = $entry->{$field}
                ?: $entry->homepage_image_path
                ?: data_get($entry, 'images.0.path');
        } else {
            $field = match ($type) {
                'mobile' => 'mobile_image_path',
                'logo' => 'logo_image_path',
                'offer' => 'offer_image_path',
                default => 'image_path',
            };

            $path = $entry->{$field} ?? null;

            if ($type === 'offer' && ! $path) {
                $path = $entry->image_path ?? null;
            }
        }

        return $path ? asset($path) : asset($fallback);
    };

    $entryUrl = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_button_url ?: route('store.product', $entry);
        }

        return $entry->button_url ?: 'javascript:void(0)';
    };

    $entryButton = function ($entry, $default = 'Shop Now') use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_button_text ?: $default;
        }

        return $entry->button_text ?: $default;
    };

    $entryDiscount = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_discount_text : $entry->discount_text;
    };

    $entryValidity = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_validity_text : $entry->validity_text;
    };

    $entryCoupon = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_coupon_code : $entry->coupon_code;
    };

    $entryIcon = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_icon_key : $entry->icon_key;
    };

    $entryBg = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_background_color : $entry->background_color;
    };

    $entryColor = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_text_color : $entry->text_color;
    };
@endphp

@foreach($homepageRows as $homepageRow)
    @php
        $section = $homepageRow['section'];
        $items = collect($homepageRow['items'] ?? collect());
        $products = collect($homepageRow['products'] ?? collect());
        $entries = $products->isNotEmpty() ? $products : $items;
    @endphp

    @switch($section->section_type)

        @case('hero_slider')
            @if($entries->isNotEmpty())
                <section class="home-section-2 home-section-bg pt-0 overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid p-0">
                        <div class="slider-animate">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="home-contain rounded-0 p-0">
                                        <img src="{{ $entryImage($entry, 'main', 'fastkart-store/images/grocery/banner/1.jpg') }}" class="img-fluid bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                        <div class="home-detail home-big-space p-center-left home-overlay position-relative">
                                            <div class="container-fluid-lg">
                                                @if($entrySubtitle($entry))<h6 class="ls-expanded theme-color text-uppercase">{{ $entrySubtitle($entry) }}</h6>@endif
                                                <h1 class="heding-2">{{ $entryTitle($entry, $section->title) }}</h1>
                                                @if($entryDescription($entry))<h5 class="text-content">{{ $entryDescription($entry) }}</h5>@endif
                                                <button class="btn theme-bg-color btn-md text-white fw-bold mt-md-4 mt-2 mend-auto" onclick="location.href='{{ $entryUrl($entry) }}';">{{ $entryButton($entry) }} <i class="fa-solid fa-arrow-right icon"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('top_small_banners')
            @if($entries->isNotEmpty())
                <section class="banner-section banner-small ratio_65" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="slider-4-banner no-arrow slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="banner-contain-3 hover-effect">
                                        <a href="{{ $entryUrl($entry) }}"><img src="{{ $entryImage($entry, 'main', 'fastkart-store/images/grocery/banner/2.jpg') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}"></a>
                                        <div class="banner-detail p-center-left w-75 banner-p-sm mend-auto">
                                            @if($entrySubtitle($entry))<h5 class="fw-light mb-2">{{ $entrySubtitle($entry) }}</h5>@endif
                                            <h4 class="fw-bold mb-0">{{ $entryTitle($entry, $section->title) }}</h4>
                                            <button onclick="location.href='{{ $entryUrl($entry) }}';" class="btn shop-now-button mt-3 ps-0 mend-auto theme-color fw-bold">{{ $entryButton($entry) }} <i class="fa-solid fa-chevron-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('category_section')
            <section class="category-section-3" id="home-section-{{ $section->section_key }}">
                <div class="container-fluid-lg">
                    <div class="title"><h2>{{ $section->title ?: 'Shop By Categories' }}</h2></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="category-slider-1 arrow-slider wow fadeInUp">
                                @include('store.partials.category-slider')
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('product_section')
            @if($products->isNotEmpty())
                <section class="product-section-3" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title">
                            <h2>{{ $section->title }}</h2>
                            @if($section->subtitle)<span class="title-leaf"><span>{{ $section->subtitle }}</span></span>@endif
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="slider-7_1 arrow-slider img-slider">
                                    @foreach($products as $product)
                                        @include('store.partials.product-card-4', ['product' => $product])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('coupon_section')
            @if($entries->isNotEmpty())
                <section class="bank-section overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ $section->title }}</h2></div>
                        <div class="slider-bank-3 arrow-slider slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="bank-offer">
                                        <div class="bank-header">
                                            <div class="bank-left w-100">
                                                <div class="bank-image">
                                                    <img src="{{ $entryImage($entry, 'logo', 'fastkart-store/images/grocery/bank/name/1.png') }}" class="img-fluid" alt="{{ $entryTitle($entry, $section->title) }}">
                                                </div>
                                                <div class="bank-name">
                                                    <h2>{{ $entryTitle($entry, $section->title) }}</h2>
                                                    @if($entryDiscount($entry))<h5 class="discount text-content">{{ $entryDiscount($entry) }}</h5>@endif
                                                    @if($entryValidity($entry))<h5 class="valid text-content">{{ $entryValidity($entry) }}</h5>@endif
                                                </div>
                                            </div>
                                            <div class="bank-right w-100">
                                                <img src="{{ $entryImage($entry, 'offer', 'fastkart-store/images/grocery/bank/price/1.svg') }}" class="img-fluid" alt="{{ $entryTitle($entry, $section->title) }}">
                                            </div>
                                        </div>
                                        @if($entryCoupon($entry))
                                            <div class="bank-footer bank-footer-1">
                                                <h4>Code : <input value="{{ $entryCoupon($entry) }}" readonly></h4>
                                                <button class="bank-coupon btn">Copy Code</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('top_selling_section')
            @php($assignedDealProduct = $products->first(fn($product) => (int) ($product->homepage_section_id ?? 0) === (int) $section->id))
            @php($dealProduct = $assignedDealProduct ?: $products->firstWhere('is_deal_timer_product', true) ?: data_get($homeContent ?? [], 'dealTimerProduct'))
            @php($normalProducts = $products->filter(fn($p) => ! $dealProduct || $p->id !== $dealProduct->id)->values())
            @php($showDealTimer = $dealProduct && $dealProduct->is_offer_active && $dealProduct->offer_end_at && $dealProduct->offer_end_at->isFuture())
            @if($normalProducts->isNotEmpty() || $dealProduct)
                <section class="product-section product-section-3" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ $section->title ?: 'Top Selling Items' }}</h2></div>
                        <div class="row g-sm-4 g-3">
                            @if($dealProduct)
                                <div class="col-xxl-4 col-lg-5 order-lg-2">
                                    <div class="product-bg-image wow fadeInUp">
                                        <div class="product-title product-warning"><h2>Special Offer</h2></div>
                                        @include('store.partials.product-card-4', ['product' => $dealProduct])
                                        @if($showDealTimer)
                                            <div class="time deal-timer homepage-deal-timer mx-md-0 mx-auto mt-3" data-end-at="{{ $dealProduct->offer_end_at->toIso8601String() }}">
                                                <div class="product-title">
                                                    <h4>Hurry up! Sales Ends In</h4>
                                                </div>
                                                <ul>
                                                    <li><div class="counter d-block"><div class="days d-block"><h5>0</h5></div><h6>Days</h6></div></li>
                                                    <li><div class="counter d-block"><div class="hours d-block"><h5>0</h5></div><h6>Hours</h6></div></li>
                                                    <li><div class="counter d-block"><div class="minutes d-block"><h5>0</h5></div><h6>Min</h6></div></li>
                                                    <li><div class="counter d-block"><div class="seconds d-block"><h5>0</h5></div><h6>Sec</h6></div></li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="{{ $dealProduct ? 'col-xxl-8 col-lg-7' : 'col-12' }}">
                                <div class="slider-7_1 arrow-slider img-slider">
                                    @foreach($normalProducts as $product)
                                        @include('store.partials.product-card-4', ['product' => $product])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('offer_section')
            @if($entries->isNotEmpty())
                <section class="banner-section ratio_60" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        @if($section->title)<div class="title"><h2>{{ $section->title }}</h2></div>@endif
                        <div class="row g-sm-4 g-3">
                            @foreach($entries as $entry)
                                <div class="{{ $section->layout_type === 'full_width_banner' ? 'col-12' : ($section->layout_type === 'two_column_banner' ? 'col-md-6' : ($loop->first && $section->layout_type === 'big_small_banner' ? 'col-lg-8' : 'col-lg-4 col-md-6')) }}">
                                    <div class="banner-contain hover-effect">
                                        <a href="{{ $entryUrl($entry) }}">
                                            <img src="{{ $entryImage($entry, 'main', 'fastkart-store/images/grocery/banner/6.jpg') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('strip_offer_banner')
            @php($entry = $entries->first())
            @if($entry)
                <section class="offer-section" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="offer-box hover-effect" style="{{ $entryBg($entry) ? 'background-color: '.$entryBg($entry).';' : '' }} {{ $entryColor($entry) ? 'color: '.$entryColor($entry).';' : '' }}">
                            <h2>{{ $entryTitle($entry, $section->title) }} <span>{{ $entryDiscount($entry) }}</span></h2>
                            <button class="btn theme-bg-color text-white" onclick="location.href='{{ $entryUrl($entry) }}';">{{ $entryButton($entry) }}</button>
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('blog_section')
            @if($entries->isNotEmpty())
                <section class="blog-section" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ $section->title ?: 'Featured Blog' }}</h2></div>
                        <div class="slider-3-blog arrow-slider slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="blog-box ratio_50">
                                        <div class="blog-box-image">
                                            <a href="{{ $entryUrl($entry) }}">
                                                <img src="{{ $entryImage($entry, 'main', 'fastkart-store/images/grocery/blog/1.jpg') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                            </a>
                                        </div>
                                        <div class="blog-detail">
                                            @if($entrySubtitle($entry))<label>{{ $entrySubtitle($entry) }}</label>@endif
                                            <a href="{{ $entryUrl($entry) }}"><h2>{{ $entryTitle($entry, $section->title) }}</h2></a>
                                            @if($entryDescription($entry))<p class="text-content">{{ str($entryDescription($entry))->limit(120) }}</p>@endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break
        @case('service_section')
            @if($entries->isNotEmpty())
                <section class="service-section section-b-space" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="row g-3">
                            @foreach($entries as $entry)
                                <div class="col-xxl-2 col-lg-3 col-sm-6">
                                    <div class="service-contain-2">
                                        @if($entryIcon($entry))
                                            <svg class="icon-width"><use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon.svg#'.$entryIcon($entry)) }}"></use></svg>
                                        @endif
                                        <div class="service-detail">
                                            <h3>{{ $entryTitle($entry, $section->title) }}</h3>
                                            <h6 class="text-content">{{ $entrySubtitle($entry) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

    @endswitch
@endforeach
<script>
(function () {
    function updateHomepageDealTimer(timer) {
        var endAt = timer.dataset.endAt;
        if (!endAt) {
            return;
        }

        var endTime = new Date(endAt).getTime();
        if (Number.isNaN(endTime)) {
            return;
        }

        function render() {
            var diff = Math.max(0, endTime - Date.now());
            var totalSeconds = Math.floor(diff / 1000);
            var days = Math.floor(totalSeconds / 86400);
            var hours = Math.floor((totalSeconds % 86400) / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            timer.querySelector('.days h5').textContent = days;
            timer.querySelector('.hours h5').textContent = hours;
            timer.querySelector('.minutes h5').textContent = minutes;
            timer.querySelector('.seconds h5').textContent = seconds;
        }

        render();
        window.setInterval(render, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.homepage-deal-timer[data-end-at]').forEach(updateHomepageDealTimer);
        });
    } else {
        document.querySelectorAll('.homepage-deal-timer[data-end-at]').forEach(updateHomepageDealTimer);
    }
})();
</script>