@php
    $homepageRows = collect(data_get($homeContent ?? [], 'homepageRows', collect()));
    $categoriesForHome = collect($categories ?? collect());
    $assetUrl = fn ($path, $fallback = '') => $path ? asset($path) : asset($fallback);
@endphp

@foreach($homepageRows as $homepageRow)
    @php
        $section = $homepageRow['section'];
        $items = collect($homepageRow['items'] ?? collect());
        $sectionProducts = collect($homepageRow['products'] ?? collect());
    @endphp

    @switch($section->section_type)
        @case('hero_slider')
            @if($items->isNotEmpty())
                <section class="home-section-2 home-section-bg pt-0 overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid p-0">
                        <div class="slider-animate">
                            @foreach($items as $item)
                                <div>
                                    <div class="home-contain rounded-0 p-0">
                                        <img src="{{ $assetUrl($item->image_path, 'fastkart-store/images/grocery/banner/1.jpg') }}" class="img-fluid bg-img blur-up lazyload" alt="{{ $item->title }}">
                                        <div class="home-detail home-big-space p-center-left home-overlay position-relative">
                                            <div class="container-fluid-lg">
                                                @if($item->subtitle)<h6 class="ls-expanded theme-color text-uppercase">{{ $item->subtitle }}</h6>@endif
                                                @if($item->title)<h1 class="heding-2">{{ $item->title }}</h1>@endif
                                                @if($item->description)<h5 class="text-content">{{ $item->description }}</h5>@endif
                                                @if($item->button_text || $item->button_url)
                                                    <button class="btn theme-bg-color btn-md text-white fw-bold mt-md-4 mt-2 mend-auto" onclick="location.href='{{ $item->button_url ?: route('store.home') }}';">{{ $item->button_text ?: 'Shop Now' }} <i class="fa-solid fa-arrow-right icon"></i></button>
                                                @endif
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
            @if($items->isNotEmpty())
                <section class="banner-section banner-small ratio_65" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="slider-4-banner no-arrow slick-height">
                            @foreach($items as $item)
                                <div>
                                    <div class="banner-contain-3 hover-effect">
                                        <a href="{{ $item->button_url ?: 'javascript:void(0)' }}"><img src="{{ $assetUrl($item->image_path, 'fastkart-store/images/grocery/banner/2.jpg') }}" class="bg-img blur-up lazyload" alt="{{ $item->title }}"></a>
                                        <div class="banner-detail p-center-left w-75 banner-p-sm mend-auto">
                                            @if($item->subtitle)<h5 class="fw-light mb-2">{{ $item->subtitle }}</h5>@endif
                                            @if($item->title)<h4 class="fw-bold mb-0">{{ $item->title }}</h4>@endif
                                            @if($item->button_text)<button onclick="location.href='{{ $item->button_url ?: route('store.home') }}';" class="btn shop-now-button mt-3 ps-0 mend-auto theme-color fw-bold">{{ $item->button_text }} <i class="fa-solid fa-chevron-right"></i></button>@endif
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
                    <div class="row"><div class="col-12"><div class="category-slider-1 arrow-slider wow fadeInUp">@include('store.partials.category-slider')</div></div></div>
                </div>
            </section>
            @break

        @case('product_section')
            @if($sectionProducts->isNotEmpty())
                <section class="product-section-3" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title">
                            <h2>{{ $section->title }}</h2>
                            @if($section->subtitle)<span class="title-leaf"><span>{{ $section->subtitle }}</span></span>@endif
                        </div>
                        <div class="row"><div class="col-12"><div class="slider-7_1 arrow-slider img-slider">
                            @foreach($sectionProducts as $product)
                                @include('store.partials.product-card-4', ['product' => $product])
                            @endforeach
                        </div></div></div>
                    </div>
                </section>
            @endif
            @break

        @case('coupon_section')
            @if($items->isNotEmpty())
                <section class="bank-section overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ $section->title }}</h2></div>
                        <div class="slider-bank-3 arrow-slider slick-height">
                            @foreach($items as $item)
                                <div>
                                    <div class="bank-offer">
                                        <div class="bank-header">
                                            <div class="bank-left w-100">
                                                @if($item->logo_image_path)<div class="bank-image"><img src="{{ asset($item->logo_image_path) }}" class="img-fluid" alt="{{ $item->title }}"></div>@endif
                                                <div class="bank-name">
                                                    @if($item->title)<h2>{{ $item->title }}</h2>@endif
                                                    @if($item->discount_text)<h5 class="discount text-content">{{ $item->discount_text }}</h5>@endif
                                                    @if($item->validity_text)<h5 class="valid text-content">{{ $item->validity_text }}</h5>@endif
                                                </div>
                                            </div>
                                            @if($item->image_path)<div class="bank-right w-100"><img src="{{ asset($item->image_path) }}" class="img-fluid" alt="{{ $item->title }}"></div>@endif
                                        </div>
                                        @if($item->coupon_code)
                                            <div class="bank-footer bank-footer-1"><h4>Code : <input value="{{ $item->coupon_code }}" readonly></h4><button class="bank-coupon btn">Copy Code</button></div>
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
            @php($dealProduct = data_get($homeContent ?? [], 'dealTimerProduct'))
            @if($sectionProducts->isNotEmpty() || $dealProduct)
                <section class="product-section product-section-3" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ $section->title ?: 'Top Selling Items' }}</h2></div>
                        <div class="row g-sm-4 g-3">
                            @if($dealProduct)
                                <div class="col-xxl-4 col-lg-5 order-lg-2">
                                    <div class="product-bg-image wow fadeInUp">
                                        <div class="product-title product-warning"><h2>Special Offer</h2></div>
                                        @include('store.partials.product-card-4', ['product' => $dealProduct])
                                    </div>
                                </div>
                            @endif
                            <div class="{{ $dealProduct ? 'col-xxl-8 col-lg-7' : 'col-12' }}">
                                <div class="slider-7_1 arrow-slider img-slider">
                                    @foreach($sectionProducts as $product)
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
            @if($items->isNotEmpty())
                <section class="banner-section ratio_60" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        @if($section->title)<div class="title"><h2>{{ $section->title }}</h2></div>@endif
                        <div class="row g-sm-4 g-3">
                            @foreach($items as $item)
                                <div class="{{ $section->layout_type === 'full_width_banner' ? 'col-12' : ($loop->first && $section->layout_type === 'big_small_banner' ? 'col-lg-8' : 'col-lg-4 col-md-6') }}">
                                    <div class="banner-contain hover-effect">
                                        <a href="{{ $item->button_url ?: 'javascript:void(0)' }}"><img src="{{ $assetUrl($item->image_path, 'fastkart-store/images/grocery/banner/6.jpg') }}" class="bg-img blur-up lazyload" alt="{{ $item->title }}"></a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('strip_offer_banner')
            @php($item = $items->first())
            @if($item)
                <section class="offer-section" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="offer-box hover-effect" style="{{ $item->background_color ? 'background-color: '.$item->background_color.';' : '' }} {{ $item->text_color ? 'color: '.$item->text_color.';' : '' }}">
                            <h2>{{ $item->title ?: $section->title }} <span>{{ $item->discount_text }}</span></h2>
                            @if($item->button_text)<button class="btn theme-bg-color text-white" onclick="location.href='{{ $item->button_url ?: route('store.home') }}';">{{ $item->button_text }}</button>@endif
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('service_section')
            @if($items->isNotEmpty())
                <section class="service-section section-b-space" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="row g-3">
                            @foreach($items as $item)
                                <div class="col-xxl-2 col-lg-3 col-sm-6">
                                    <div class="service-contain-2">
                                        <svg class="icon-width"><use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon.svg#'.$item->icon_key) }}"></use></svg>
                                        <div class="service-detail">
                                            <h3>{{ $item->title }}</h3>
                                            <h6 class="text-content">{{ $item->subtitle }}</h6>
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