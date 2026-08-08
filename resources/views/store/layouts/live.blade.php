<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="{{ asset('fastkart-store/images/favicon/5.png') }}" type="image/x-icon">
    <title>@yield('title', 'Bawaskar Farmer Store')</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link id="rtl-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/vendors/ion.rangeSlider.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/font-style.css') }}">
    <link id="color-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/bawaskar-store.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="theme-color-3 dark">
@php
    $footerLinks = collect(data_get($homeContent ?? [], 'footerLinks', collect()));
    $cartCountLabel = rtrim(rtrim(number_format((float) ($storeCartCount ?? 0), 3, '.', ''), '0'), '.');
    $cartCountLabel = $cartCountLabel === '' ? '0' : $cartCountLabel;
@endphp
<div class="fullpage-loader">
    <span></span><span></span><span></span><span></span><span></span><span></span>
</div>
<header class="header-3">
    <div class="top-nav sticky-header sticky-header-2">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="navbar-top gap-3 align-items-center">
                        <a href="{{ route('store.home') }}" class="web-logo nav-logo">
                            <span class="bawaskar-store-logo">
                                <img src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                            </span>
                        </a>
                        <div class="middle-box flex-grow-1 d-none d-xl-block">
                            <form action="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" method="GET" class="searchbar-box-2 input-group d-flex">
                                <button class="btn search-icon" type="submit"><i class="iconly-Search icli"></i></button>
                                <input type="text" class="form-control" name="search" value="{{ $searchQuery ?? '' }}" placeholder="Search for products">
                                <button class="btn search-button" type="submit">Search</button>
                            </form>
                        </div>
                        <div class="rightside-menu d-flex align-items-center gap-2 ms-auto">
                            <a href="{{ route('store.page', ['page' => 'user-dashboard']) }}" class="btn btn-sm btn-outline-light">{{ $storeUser ? \Illuminate\Support\Str::limit($storeUser->name, 18) : 'Login / Register' }}</a>
                            <a href="{{ route('store.page', ['page' => 'cart']) }}" class="btn btn-animation btn-sm position-relative"><i class="fa-solid fa-cart-shopping me-1"></i> Cart <span class="badge bg-white text-dark ms-1">{{ $cartCountLabel }}</span></a>
                        </div>
                    </div>
                    <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky p-0">
                        <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                            <div class="offcanvas-header navbar-shadow">
                                <h5>Menu</h5>
                                <button class="btn-close lead" type="button" data-bs-dismiss="offcanvas"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('store.partials.navbar')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="section-b-space">
    <div class="container-fluid-lg pt-3">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    @yield('content')
</main>
<footer class="section-t-space footer-section-2 footer-color-2">
    <div class="container-fluid-lg">
        <div class="main-footer">
            <div class="row g-md-4 g-3">
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="footer-title"><h4>Store</h4></div>
                    <ul class="footer-list footer-list-light footer-contact">
                        <li><a href="{{ route('store.home') }}" class="light-text">Home</a></li>
                        <li><a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="light-text">Products</a></li>
                        <li><a href="{{ route('store.page', ['page' => 'cart']) }}" class="light-text">Cart</a></li>
                        <li><a href="{{ route('store.page', ['page' => 'checkout']) }}" class="light-text">Checkout</a></li>
                    </ul>
                </div>
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="footer-title"><h4>Useful Links</h4></div>
                    <ul class="footer-list footer-list-light footer-contact">
                        @forelse($footerLinks->get('useful', collect()) as $footerLink)
                            <li><a href="{{ $footerLink->url ?: route('store.home') }}" class="light-text">{{ $footerLink->title }}</a></li>
                        @empty
                            <li><a href="{{ route('store.page', ['page' => 'user-dashboard']) }}" class="light-text">My Account</a></li>
                            <li><a href="{{ route('store.page', ['page' => 'order-success']) }}" class="light-text">Latest Order</a></li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="footer-title"><h4>Categories</h4></div>
                    <ul class="footer-list footer-list-light footer-contact">
                        @forelse($footerLinks->get('categories', collect()) as $footerLink)
                            <li><a href="{{ $footerLink->url ?: route('store.home') }}" class="light-text">{{ $footerLink->title }}</a></li>
                        @empty
                            @foreach(collect($categories ?? collect())->take(4) as $category)
                                <li><a href="{{ route('store.category', ['category' => $category->slug]) }}" class="light-text">{{ $category->name }}</a></li>
                            @endforeach
                        @endforelse
                    </ul>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="footer-title"><h4>Contact</h4></div>
                    <ul class="footer-address footer-contact">
                        <li><i data-feather="map-pin"></i><span>Farmer ecommerce storefront</span></li>
                        <li><i data-feather="phone"></i><span>Support via your admin contact</span></li>
                        <li><i data-feather="mail"></i><span>Live ordering for customer and dealer accounts</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sub-footer sub-footer-lite section-b-space section-t-space"><div class="left-footer"><p class="light-text mb-0">Copyright © 2026 Dr. Bawaskar Technology</p></div></div>
    </div>
</footer>
<div class="bg-overlay"></div>
<script src="{{ asset('fastkart-store/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>
<script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
<script src="{{ asset('fastkart-store/js/slick/slick-animation.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/custom-slick-animated.js') }}"></script>
<script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>
<script src="{{ asset('fastkart-store/js/ion.rangeSlider.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/auto-height.js') }}"></script>
<script src="{{ asset('fastkart-store/js/quantity-2.js') }}"></script>
<script src="{{ asset('fastkart-store/js/fly-cart.js') }}"></script>
<script src="{{ asset('fastkart-store/js/timer1.js') }}"></script>
<script src="{{ asset('fastkart-store/js/timer2.js') }}"></script>
<script src="{{ asset('fastkart-store/js/clipboard.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/copy-clipboard.js') }}"></script>
<script src="{{ asset('fastkart-store/js/wow.min.js') }}"></script>
<script src="{{ asset('fastkart-store/js/custom-wow.js') }}"></script>
<script src="{{ asset('fastkart-store/js/script.js') }}"></script>
<script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>
</html>
