<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="{{ asset('fastkart-store/images/favicon/1.png') }}" type="image/x-icon">
    <title>Bawaskar Farmer Store</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">

    <!-- bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/vendors/bootstrap.css') }}">

    <!-- Iconly css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/bulk-style.css') }}">

    <!-- Template css -->
    <link id="color-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/bawaskar-store.css') }}?v={{ filemtime(public_path('fastkart-store/css/bawaskar-store.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <!-- Loader Start -->
    <div class="fullpage-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <!-- Loader End -->

    <!-- Header Start -->
    <header class="pb-md-4 pb-0">
        <div class="header-top">
            <div class="container-fluid-lg">
                <div class="row">
                    <div class="col-xxl-3 d-xxl-block d-none">
                        <div class="top-left-header">
                            <i class="iconly-Location icli text-white"></i>
                            <span class="text-white">1418 Riverwood Drive, CA 96052, US</span>
                        </div>
                    </div>

                    <div class="col-xxl-6 col-lg-9 d-lg-block d-none">
                        <div class="header-offer">
                            <div class="notification-slider">
                                <div>
                                    <div class="timer-notification">
                                        <h6><strong class="me-1">Welcome to Bawaskar Farmer Store!</strong>Wrap new offers/gift
                                            every single day on Weekends.<strong class="ms-1">New Coupon Code: Fast024
                                            </strong>

                                        </h6>
                                    </div>
                                </div>

                                <div>
                                    <div class="timer-notification">
                                        <h6>{{ web_t('topbar.sale_message', 'Something you love is now on sale!') }}
                                            <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-white">{{ web_t('topbar.buy_now', 'Buy Now') }}
                                                !</a>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <ul class="about-list right-nav-about">
                            @include('store.partials.topbar-language-currency')
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="top-nav top-header sticky-header">
            <div class="container-fluid-lg">
                <div class="row">
                    <div class="col-12">
                        <div class="navbar-top">
                            <button class="navbar-toggler d-xl-none d-inline navbar-menu-button" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                                <span class="navbar-toggler-icon">
                                    <i class="fa-solid fa-bars"></i>
                                </span>
                            </button>
                            <a href="{{ route('store.home') }}" class="web-logo nav-logo">
                                <span class="bawaskar-store-logo">
                                    <img src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                            </a>

                            <div class="middle-box">
                                <div class="location-box">
                                    <button class="btn location-button" data-bs-toggle="modal"
                                        data-bs-target="#locationModal">
                                        <span class="location-arrow">
                                            <i data-feather="map-pin"></i>
                                        </span>
                                        <span class="locat-name">Your Location</span>
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </div>

                                <div class="search-box">
                                    <div class="input-group">
                                        <input type="search" class="form-control" placeholder="I'm searching for...">
                                        <button class="btn" type="button" id="button-addon2">
                                            <i data-feather="search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="rightside-box">
                                <div class="search-full">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i data-feather="search" class="font-light"></i>
                                        </span>
                                        <input type="text" class="form-control search-type" placeholder="{{ web_t('header.search_placeholder', 'Search for products') }}">
                                        <span class="input-group-text close-search">
                                            <i data-feather="x" class="font-light"></i>
                                        </span>
                                    </div>
                                </div>
                                @include('store.partials.header-actions')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="header-nav">
                        <div class="header-nav-left">
                            <button class="dropdown-category">
                                <i data-feather="align-left"></i>
                                <span>All Categories</span>
                            </button>

                            <div class="category-dropdown">
                                <div class="category-title">
                                    <h5>{{ web_t('nav.categories', 'Categories') }}</h5>
                                    <button type="button" class="btn p-0 close-button text-content">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <ul class="category-list">
                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/vegetable.svg') }}" alt="">
                                            <h6>Vegetables & Fruit</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Organic Vegetables</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Potato & Tomato</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cucumber & Capsicum</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Leafy Vegetables</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Root Vegetables</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Beans & Okra</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cabbage & Cauliflower</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Gourd & Drumstick</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Specialty</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="list-2">
                                                <div class="category-title-box">
                                                    <h5>Fresh Fruit</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Banana & Papaya</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Kiwi, Citrus Fruit</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Apples & Pomegranate</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Seasonal Fruits</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Mangoes</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Fruit Baskets</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/cup.svg') }}" alt="">
                                            <h6>Beverages</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box w-100">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Energy & Soft Drinks</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Soda & Cocktail Mix</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Soda & Cocktail Mix</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Sports & Energy Drinks</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Non Alcoholic Drinks</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Packaged Water</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Spring Water</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Flavoured Water</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/meats.svg') }}" alt="">
                                            <h6>Meats & Seafood</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Meat</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Fresh Meat</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Frozen Meat</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Marinated Meat</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Fresh & Frozen Meat</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="list-2">
                                                <div class="category-title-box">
                                                    <h5>Seafood</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Fresh Water Fish</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Dry Fish</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Frozen Fish & Seafood</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Marine Water Fish</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Canned Seafood</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Prawans & Shrimps</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Other Seafood</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/breakfast.svg') }}" alt="">
                                            <h6>Breakfast & Dairy</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Breakfast Cereals</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Oats & Porridge</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Kids Cereal</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Muesli</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Flakes</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Granola & Cereal Bars</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Instant Noodles</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Pasta & Macaroni</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Frozen Non-Veg Snacks</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="list-2">
                                                <div class="category-title-box">
                                                    <h5>Dairy</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Milk</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Curd</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Paneer, Tofu & Cream</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Butter & Margarine</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Condensed, Powdered Milk</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Buttermilk & Lassi</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Yogurt & Shrikhand</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Flavoured, Soya Milk</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/frozen.svg') }}" alt="">
                                            <h6>Frozen Foods</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box w-100">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Noodle, Pasta</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Instant Noodles</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Hakka Noodles</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cup Noodles</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Vermicelli</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Instant Pasta</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/biscuit.svg') }}" alt="">
                                            <h6>Biscuits & Snacks</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Biscuits & Cookies</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Salted Biscuits</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Marie, Health, Digestive</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cream Biscuits & Wafers</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Glucose & Milk Biscuits</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cookies</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="list-2">
                                                <div class="category-title-box">
                                                    <h5>Bakery Snacks</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Bread Sticks & Lavash</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Cheese & Garlic Bread</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Puffs, Patties, Sandwiches</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Breadcrumbs & Croutons</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="{{ asset('fastkart-store/svg/1/grocery.svg') }}" alt="">
                                            <h6>Grocery & Staples</h6>
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <div class="onhover-category-box">
                                            <div class="list-1">
                                                <div class="category-title-box">
                                                    <h5>Grocery</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Lemon, Ginger & Garlic</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Indian & Exotic Herbs</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Vegetables</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Fruits</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="list-2">
                                                <div class="category-title-box">
                                                    <h5>Organic Staples</h5>
                                                </div>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Dry Fruits</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Dals & Pulses</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Millet & Flours</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Sugar, Jaggery</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Masalas & Spices</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Rice, Other Rice</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Flours</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)">Organic Edible Oil, Ghee</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="header-nav-middle">
                            <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky">
                                <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                    <div class="offcanvas-header navbar-shadow">
                                        <h5>Menu</h5>
                                        <button class="btn-close lead" type="button"
                                            data-bs-dismiss="offcanvas"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('store.partials.navbar')
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="header-nav-right">
                            <button class="btn deal-button" data-bs-toggle="modal" data-bs-target="#deal-box">
                                <i data-feather="zap"></i>
                                <span>Deal Today</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <!-- mobile fix menu start -->
    <div class="mobile-menu d-md-none d-block mobile-cart">
        <ul>
            <li class="active">
                <a href="{{ route('store.home') }}">
                    <i class="iconly-Home icli"></i>
                    <span>{{ web_t('nav.home', 'Home') }}</span>
                </a>
            </li>

            <li class="mobile-category">
                <a href="javascript:void(0)">
                    <i class="iconly-Category icli js-link"></i>
                    <span>Category</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'search']) }}" class="search-box">
                    <i class="iconly-Search icli"></i>
                    <span>{{ web_t('header.search', 'Search') }}</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                    <i class="iconly-Heart icli"></i>
                    <span>My Wish</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'cart']) }}">
                    <i class="iconly-Bag-2 icli fly-cate"></i>
                    <span>Cart</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- mobile fix menu end -->

    <!-- Breadcrumb Section Start -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Checkout</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Checkout</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

        <!-- Checkout section Start -->
    @php
        $cartItems = collect(data_get($storeCart, 'items', collect()));
        $address = $storePrimaryAddress;
    @endphp
    <section class="checkout-section-2 section-b-space">
        <div class="container-fluid-lg">
            @if(! $storeUser)
                <div class="alert alert-warning mb-0">Please <a href="{{ route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'checkout'])]) }}">log in</a> before checkout.</div>
            @elseif($cartItems->isEmpty())
                <div class="alert alert-light mb-0">Your cart is empty. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Browse products</a>.</div>
            @else
                <form method="POST" action="{{ route('store.checkout.place-order') }}">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger store-checkout-error-alert mb-4" role="alert">
                            <h5 class="mb-2">Please complete the required checkout details.</h5>
                            <ul class="mb-0 store-checkout-error-list">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-sm-4 g-3">
                        <div class="col-lg-8">
                            <div class="left-sidebar-checkout">
                                <div class="checkout-detail-box">
                                    <ul>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/ggihhudh.json" trigger="loop-on-hover" colors="primary:#121331,secondary:#646e78,tertiary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Delivery Address</h4></div>
                                                <div class="checkout-detail">
                                                    @if($address)
                                                        <div class="row g-4 mb-4">
                                                            <div class="col-12">
                                                                <div class="delivery-address-box">
                                                                    <div>
                                                                        <div class="form-check"><input class="form-check-input" type="radio" checked></div>
                                                                        <div class="label"><label>{{ ucfirst($address->type ?: 'shipping') }}</label></div>
                                                                        <ul class="delivery-address-detail">
                                                                            <li><h4 class="fw-500">{{ $address->name }}</h4></li>
                                                                            <li><p class="text-content"><span class="text-title">Address :</span> {{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}, {{ $address->city }}, {{ $address->state }}</p></li>
                                                                            <li><h6 class="text-content"><span class="text-title">Pin Code :</span> {{ $address->pincode }}</h6></li>
                                                                            <li><h6 class="text-content mb-0"><span class="text-title">Phone :</span> {{ $address->mobile }}</h6></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('contact_name') is-invalid @enderror" name="contact_name" id="contact_name" value="{{ old('contact_name', $address?->name ?: $storeUser->name) }}" placeholder="Contact Name">
                                                                <label for="contact_name">Contact Name</label>
                                                            </div>
                                                            @error('contact_name')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('contact_mobile') is-invalid @enderror" name="contact_mobile" id="contact_mobile" value="{{ old('contact_mobile', $address?->mobile ?: $storeUser->mobile) }}" placeholder="Contact Mobile">
                                                                <label for="contact_mobile">Contact Mobile</label>
                                                            </div>
                                                            @error('contact_mobile')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_type') is-invalid @enderror" name="address_type" id="address_type" value="{{ old('address_type', $address?->type ?: 'shipping') }}" placeholder="Address Type">
                                                                <label for="address_type">Address Type</label>
                                                            </div>
                                                            @error('address_type')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_line1') is-invalid @enderror" name="address_line1" id="address_line1" value="{{ old('address_line1', $address?->address_line1) }}" placeholder="Address Line 1">
                                                                <label for="address_line1">Address Line 1</label>
                                                            </div>
                                                            @error('address_line1')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_line2') is-invalid @enderror" name="address_line2" id="address_line2" value="{{ old('address_line2', $address?->address_line2) }}" placeholder="Address Line 2">
                                                                <label for="address_line2">Address Line 2</label>
                                                            </div>
                                                            @error('address_line2')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $address?->city) }}" placeholder="City">
                                                                <label for="city">City</label>
                                                            </div>
                                                            @error('city')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state', $address?->state) }}" placeholder="State">
                                                                <label for="state">State</label>
                                                            </div>
                                                            @error('state')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('pincode') is-invalid @enderror" name="pincode" id="pincode" value="{{ old('pincode', $address?->pincode) }}" placeholder="Pincode">
                                                                <label for="pincode">Pincode</label>
                                                            </div>
                                                            @error('pincode')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12"><div class="form-check custom-form-check"><input class="form-check-input" type="checkbox" name="save_as_default" id="save_as_default" value="1" {{ old('save_as_default', $address?->is_default) ? 'checked' : '' }}><label class="form-check-label" for="save_as_default">Save as default address</label></div></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/oaflahpk.json" trigger="loop-on-hover" colors="primary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Delivery Option</h4></div>
                                                <div class="checkout-detail">
                                                    <div class="delivery-option"><div class="delivery-category"><div class="shipment-detail"><div class="form-check custom-form-check hide-check-box"><input class="form-check-input" type="radio" checked><label class="form-check-label">Standard Delivery Option</label></div></div></div></div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/qmcsqnle.json" trigger="loop-on-hover" colors="primary:#0baf9a,secondary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Payment Option</h4></div>
                                                <div class="checkout-detail">
                                                    <div class="accordion accordion-flush custom-accordion" id="accordionFlushExample">
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="cash"><input class="form-check-input mt-0" type="radio" name="payment_method" id="cash" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}> Cash On Delivery</label></div></div></div>
                                                            <div id="flush-collapseFour" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Cash on delivery is available. Order will stay pending until dispatch confirmation.</p></div></div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="upi"><input class="form-check-input mt-0" type="radio" name="payment_method" id="upi" value="upi" {{ old('payment_method') === 'upi' ? 'checked' : '' }}> UPI</label></div></div></div>
                                                            <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Choose this if payment will be confirmed by UPI collection after order placement.</p></div></div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="bank_transfer"><input class="form-check-input mt-0" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}> Bank Transfer</label></div></div></div>
                                                            <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Use this for NEFT / RTGS / IMPS style payment confirmation after order placement.</p></div></div>
                                                        </div>
                                                        @if($storeUser?->role === \App\Models\User::ROLE_DEALER)
                                                            <div class="accordion-item">
                                                                <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="credit"><input class="form-check-input mt-0" type="radio" name="payment_method" id="credit" value="credit" {{ old('payment_method') === 'credit' ? 'checked' : '' }}> Dealer Credit</label></div></div></div>
                                                                <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Dealer credit can be used for approved dealer accounts and remains subject to account verification.</p></div></div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('payment_method')<div class="invalid-feedback d-block store-checkout-field-error mt-2">{{ $message }}</div>@enderror
                                                    <div class="mt-4"><div class="form-floating theme-form-floating"><textarea class="form-control" name="notes" id="notes" placeholder="Order Notes" style="height: 120px">{{ old('notes') }}</textarea><label for="notes">Order Notes</label></div></div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="right-side-summery-box">
                                <div class="summery-box-2">
                                    <div class="summery-header"><h3>Order Summery</h3></div>
                                    <ul class="summery-contain">
                                        @foreach($cartItems as $item)
                                            @php $imageUrl = $item['product']->storefront_image_url; $displayName = $item['product']->translatedName(); @endphp
                                            <li>
                                                <img src="{{ $imageUrl }}" class="img-fluid blur-up lazyloaded checkout-image" alt="{{ $displayName }}">
                                                <h4>{{ $displayName }} <span>X {{ number_format((float) $item['quantity'], 3) }}</span></h4>
                                                <h4 class="price">Rs. {{ number_format((float) $item['line_total'], 2) }}</h4>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="summery-total">
                                        <li><h4>Subtotal</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'subtotal', 0), 2) }}</h4></li>
                                        <li><h4>Shipping</h4><h4 class="price">Rs. 0.00</h4></li>
                                        <li><h4>Tax</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'gst_total', 0), 2) }}</h4></li>
                                        <li><h4>Coupon/Code</h4><h4 class="price">Rs. 0.00</h4></li>
                                        <li class="list-total"><h4>Total (INR)</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'grand_total', 0), 2) }}</h4></li>
                                    </ul>
                                </div>
                                <div class="checkout-offer">
                                    <div class="offer-title">
                                        <div class="offer-icon"><img src="{{ asset('fastkart-store/images/inner-page/offer.svg') }}" class="img-fluid" alt=""></div>
                                        <div class="offer-name"><h6>Available Offers</h6></div>
                                    </div>
                                    <ul class="offer-detail"><li><p>Pricing, stock validation, and order routing are applied automatically during checkout.</p></li></ul>
                                </div>
                                <button type="submit" class="btn theme-bg-color text-white btn-md w-100 mt-4 fw-bold">Place Order</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>
    <!-- Checkout section End -->

    <!-- Footer Section Start -->
    <footer class="section-t-space">
        <div class="container-fluid-lg">
            <div class="service-section">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="service-contain">
                            <div class="service-box">
                                <div class="service-image">
                                    <img src="{{ asset('fastkart-store/svg/product.svg') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Every Fresh Products</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="{{ asset('fastkart-store/svg/delivery.svg') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Free Delivery For Order Over $50</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="{{ asset('fastkart-store/svg/discount.svg') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Daily Mega Discounts</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="{{ asset('fastkart-store/svg/market.svg') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Best Price On The Market</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-footer section-b-space section-t-space">
                <div class="row g-md-4 g-3">
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="footer-logo">
                            <div class="theme-logo">
                                <a href="{{ route('store.home') }}">
                                    <span class="bawaskar-store-logo">
                                    <img src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                                </a>
                            </div>

                            <div class="footer-logo-contain">
                                <p>We are a friendly bar serving a variety of cocktails, wines and beers. Our bar is a
                                    perfect place for a couple.</p>

                                <ul class="address">
                                    <li>
                                        <i data-feather="home"></i>
                                        <a href="javascript:void(0)">1418 Riverwood Drive, CA 96052, US</a>
                                    </li>
                                    <li>
                                        <i data-feather="mail"></i>
                                        <a href="javascript:void(0)">support@fastkart.com</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="footer-title">
                            <h4>{{ web_t('nav.categories', 'Categories') }}</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Vegetables & Fruit</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Beverages</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Meats & Seafood</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Frozen Foods</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Biscuits & Snacks</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Grocery & Staples</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl col-lg-2 col-sm-3">
                        <div class="footer-title">
                            <h4>Useful Links</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                <li>
                                    <a href="{{ route('store.home') }}" class="text-content">{{ web_t('nav.home', 'Home') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="text-content">Shop</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'about-us']) }}" class="text-content">{{ web_t('nav.about_us', 'About Us') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.home') }}" class="text-content">Blog</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'contact-us']) }}" class="text-content">{{ web_t('nav.contact_us', 'Contact Us') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-2 col-sm-3">
                        <div class="footer-title">
                            <h4>Help Center</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'user-dashboard']) }}#pills-order" class="text-content">Your Order</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'user-dashboard']) }}" class="text-content">Your Account</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'order-tracking']) }}" class="text-content">Track Order</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="text-content">Your Wishlist</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'search']) }}" class="text-content">{{ web_t('header.search', 'Search') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('store.page', ['page'=>'faq']) }}" class="text-content">FAQ</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="footer-title">
                            <h4>{{ web_t('nav.contact_us', 'Contact Us') }}</h4>
                        </div>

                        <div class="footer-contact">
                            <ul>
                                <li>
                                    <div class="footer-number">
                                        <i data-feather="phone"></i>
                                        <div class="contact-number">
                                            <h6 class="text-content">Hotline 24/7 :</h6>
                                            <h5>+91 888 104 2340</h5>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="footer-number">
                                        <i data-feather="mail"></i>
                                        <div class="contact-number">
                                            <h6 class="text-content">Email Address :</h6>
                                            <h5>fastkart@hotmail.com</h5>
                                        </div>
                                    </div>
                                </li>

                                <li class="social-app">
                                    <h5 class="mb-2 text-content">Download App :</h5>
                                    <ul>
                                        <li class="mb-0">
                                            <a href="https://play.google.com/store/apps" target="_blank">
                                                <img src="{{ asset('fastkart-store/images/playstore.svg') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>
                                        </li>
                                        <li class="mb-0">
                                            <a href="https://www.apple.com/in/app-store/" target="_blank">
                                                <img src="{{ asset('fastkart-store/images/appstore.svg') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sub-footer section-small-space">
                <div class="reserve">
                    <h6 class="text-content">ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©2022 Bawaskar Farmer Store All rights reserved</h6>
                </div>

                <div class="payment">
                    <img src="{{ asset('fastkart-store/images/payment/1.png') }}" class="blur-up lazyload" alt="">
                </div>

                <div class="social-link">
                    <h6 class="text-content">Stay connected :</h6>
                    <ul>
                        <li>
                            <a href="https://www.facebook.com/" target="_blank">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://twitter.com/" target="_blank">
                                <i class="fa-brands fa-twitter"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.instagram.com/" target="_blank">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://in.pinterest.com/" target="_blank">
                                <i class="fa-brands fa-pinterest-p"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Location Modal Start -->
    <div class="modal location-modal fade theme-modal" id="locationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Choose your Delivery Location</h5>
                    <p class="mt-1 text-content">Enter your address and we will specify the offer for your area.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="location-list">
                        <div class="search-input">
                            <input type="search" class="form-control" placeholder="Search Your Area">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>

                        <div class="disabled-box">
                            <h6>Select a Location</h6>
                        </div>

                        <ul class="location-select custom-height">
                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Alabama</h6>
                                    <span>Min: $130</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Arizona</h6>
                                    <span>Min: $150</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>California</h6>
                                    <span>Min: $110</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Colorado</h6>
                                    <span>Min: $140</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Florida</h6>
                                    <span>Min: $160</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Georgia</h6>
                                    <span>Min: $120</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Kansas</h6>
                                    <span>Min: $170</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Minnesota</h6>
                                    <span>Min: $120</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>New York</h6>
                                    <span>Min: $110</span>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0)">
                                    <h6>Washington</h6>
                                    <span>Min: $130</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Location Modal End -->

    <!-- Add address modal box start -->
    <div class="modal fade theme-modal" id="add-address" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Add a new address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" id="fname" placeholder="Enter First Name">
                            <label for="fname">First Name</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" id="lname" placeholder="Enter Last Name">
                            <label for="lname">Last Name</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="email" class="form-control" id="email" placeholder="Enter Email Address">
                            <label for="email">Email Address</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <textarea class="form-control" placeholder="Leave a comment here" id="address"
                                style="height: 100px"></textarea>
                            <label for="address">Enter Address</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="email" class="form-control" id="pin" placeholder="Enter Pin Code">
                            <label for="pin">Pin Code</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn theme-bg-color btn-md text-white" data-bs-dismiss="modal">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add address modal box end -->

    <!-- Deal Box Modal Start -->
    <div class="modal fade theme-modal deal-modal" id="deal-box" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title w-100" id="deal_today">Deal Today</h5>
                        <p class="mt-1 text-content">Recommended deals for you.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="deal-offer-box">
                        <ul class="deal-offer-list">
                            <li class="list-1">
                                <div class="deal-offer-contain">
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-image">
                                        <img src="{{ asset('fastkart-store/images/vegetable/product/10.png') }}" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-2">
                                <div class="deal-offer-contain">
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-image">
                                        <img src="{{ asset('fastkart-store/images/vegetable/product/11.png') }}" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-3">
                                <div class="deal-offer-contain">
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-image">
                                        <img src="{{ asset('fastkart-store/images/vegetable/product/12.png') }}" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-1">
                                <div class="deal-offer-contain">
                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-image">
                                        <img src="{{ asset('fastkart-store/images/vegetable/product/13.png') }}" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Deal Box Modal End -->

    <!-- Tap to top and theme setting button start -->
    <div class="theme-option">
        <div class="setting-box">
            <button class="btn setting-button">
                <i class="fa-solid fa-gear"></i>
            </button>

            <div class="theme-setting-2">
                <div class="theme-box">
                    <ul>
                        <li>
                            <div class="setting-name">
                                <h4>Color</h4>
                            </div>
                            <div class="theme-setting-button color-picker">
                                <form class="form-control">
                                    <label for="colorPick" class="form-label mb-0">Theme Color</label>
                                    <input type="color" class="form-control form-control-color" id="colorPick"
                                        value="#0da487" title="Choose your color">
                                </form>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>Dark</h4>
                            </div>
                            <div class="theme-setting-button">
                                <button class="btn btn-2 outline" id="darkButton">Dark</button>
                                <button class="btn btn-2 unline" id="lightButton">Light</button>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>RTL</h4>
                            </div>
                            <div class="theme-setting-button rtl">
                                <button class="btn btn-2 rtl-unline">LTR</button>
                                <button class="btn btn-2 rtl-outline">RTL</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="back-to-top">
            <a id="back-to-top" href="#">
                <i class="fas fa-chevron-up"></i>
            </a>
        </div>
    </div>
    <!-- Tap to top and theme setting button end -->

    <!-- Bg overlay Start -->
    <div class="bg-overlay"></div>
    <!-- Bg overlay End -->

    <!-- latest jquery-->
    <script src="{{ asset('fastkart-store/js/jquery-3.6.0.min.js') }}"></script>

    <!-- jquery ui-->
    <script src="{{ asset('fastkart-store/js/jquery-ui.min.js') }}"></script>

    <!-- Lord-icon Js -->
    <script src="{{ asset('fastkart-store/js/lusqsztk.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Delivery Option js -->
    <script src="{{ asset('fastkart-store/js/delivery-option.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- Quantity js -->
    <script src="{{ asset('fastkart-store/js/quantity.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>






