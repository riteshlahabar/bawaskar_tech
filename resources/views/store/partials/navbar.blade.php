@php
    $navigation = $storefrontNavigation ?? [];
    $navCategories = collect(data_get($navigation, 'categories', collect()));
    $navProductTypes = collect(data_get($navigation, 'productTypes', collect()));
    $navFeaturedProducts = collect(data_get($navigation, 'featuredProducts', collect()));
    $navHomeSections = collect(data_get($homeContent ?? [], 'productSections', collect()))
        ->map(fn ($entry) => data_get($entry, 'section'))
        ->filter(fn ($section) => $section && $section->title)
        ->values();

    if ($navHomeSections->isEmpty()) {
        $navHomeSections = collect(data_get($homeContent ?? [], 'sections', collect()))
            ->values()
            ->filter(fn ($section) => $section && $section->title)
            ->values();
    }

    $shopUrl = route('store.page', ['page' => 'shop-left-sidebar']);
    $storeRole = data_get($storeUser, 'role');
    $customerLoginUrl = route('store.page', ['page' => 'login', 'role' => 'customer']);
    $dealerLoginUrl = route('store.page', ['page' => 'login', 'role' => 'dealer']);
    $customerRegisterUrl = route('store.page', ['page' => 'sign-up', 'role' => 'customer']);
    $dealerRegisterUrl = route('store.page', ['page' => 'sign-up', 'role' => 'dealer']);
@endphp

<ul class="navbar-nav">
    <li class="nav-item dropdown">
        <a class="nav-link ps-0 dropdown-toggle" href="{{ route('store.home') }}" data-bs-toggle="dropdown">Home</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('store.home') }}">Homepage</a></li>
            @forelse($navHomeSections->take(10) as $homeSection)
                <li><a class="dropdown-item" href="{{ route('store.home') }}#home-section-{{ $homeSection->section_key }}">{{ $homeSection->title }}</a></li>
            @empty
                <li><a class="dropdown-item" href="{{ $shopUrl }}">Browse Products</a></li>
            @endforelse
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ $shopUrl }}" data-bs-toggle="dropdown">Categories</a>
        <ul class="dropdown-menu">
            @forelse($navCategories as $category)
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="{{ $category->slug ? route('store.category', ['category' => $category->slug]) : $shopUrl }}">
                        <span>{{ $category->name }}</span>
                        <small>{{ (int) ($category->products_count ?? 0) }}</small>
                    </a>
                </li>
            @empty
                <li><a class="dropdown-item" href="{{ $shopUrl }}">All Categories</a></li>
            @endforelse
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ $shopUrl }}" data-bs-toggle="dropdown">Products</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ $shopUrl }}">All Products</a></li>
            @foreach($navProductTypes as $productType)
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="{{ route('store.page', ['page' => 'shop-left-sidebar', 'product_type' => $productType['slug']]) }}">
                        <span>{{ $productType['name'] }}</span>
                        <small>{{ (int) ($productType['products_count'] ?? 0) }}</small>
                    </a>
                </li>
            @endforeach
        </ul>
    </li>

    <li class="nav-item dropdown dropdown-mega">
        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown">Quick Access</a>
        <div class="dropdown-menu dropdown-menu-2 dropdown-menu-left">
            <div class="row">
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Featured Products</h5>
                    @forelse($navFeaturedProducts->take(6) as $product)
                        <a class="dropdown-item" href="{{ route('store.product', ['product' => $product->getKey()]) }}">{{ $product->name }}</a>
                    @empty
                        <a class="dropdown-item" href="{{ $shopUrl }}">Browse Store</a>
                    @endforelse
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Customer</h5>
                    <a class="dropdown-item" href="{{ $customerLoginUrl }}">Customer Login</a>
                    <a class="dropdown-item" href="{{ $customerRegisterUrl }}">Customer Register</a>
                    <a class="dropdown-item" href="{{ route('store.page', ['page' => 'cart']) }}">Customer Cart</a>
                    <a class="dropdown-item" href="{{ route('store.page', ['page' => 'checkout']) }}">Customer Checkout</a>
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Dealer</h5>
                    <a class="dropdown-item" href="{{ $dealerLoginUrl }}">Dealer Login</a>
                    <a class="dropdown-item" href="{{ $dealerRegisterUrl }}">Dealer Register</a>
                    <a class="dropdown-item" href="{{ route('store.page', ['page' => 'checkout']) }}">Dealer Checkout</a>
                    <a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}">Dealer Dashboard</a>
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Account</h5>
                    @if($storeUser)
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}">{{ $storeUser->name }}</a>
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'order-success']) }}">Latest Order</a>
                        <form method="POST" action="{{ route('store.auth.logout') }}" class="px-3 py-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Logout</button>
                        </form>
                    @else
                        <a class="dropdown-item" href="{{ $customerLoginUrl }}">Login</a>
                        <a class="dropdown-item" href="{{ $customerRegisterUrl }}">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('store.page', ['page' => 'user-dashboard']) }}" data-bs-toggle="dropdown">
            {{ $storeRole === 'dealer' ? 'Dealer Account' : 'My Account' }}
        </a>
        <ul class="dropdown-menu">
            @if($storeUser)
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}">Dashboard</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'cart']) }}">Cart</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'checkout']) }}">Checkout</a></li>
            @else
                <li><a class="dropdown-item" href="{{ $customerLoginUrl }}">Customer Login</a></li>
                <li><a class="dropdown-item" href="{{ $dealerLoginUrl }}">Dealer Login</a></li>
            @endif
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('store.page', ['page' => 'about-us']) }}" data-bs-toggle="dropdown">About Us</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'about-us']) }}">Company Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'faq']) }}">FAQ</a></li>
            <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'contact-us']) }}">Contact Us</a></li>
        </ul>
    </li>
</ul>
