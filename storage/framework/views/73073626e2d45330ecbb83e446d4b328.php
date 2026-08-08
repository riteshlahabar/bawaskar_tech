<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="<?php echo e(asset('fastkart-store/images/favicon/1.png')); ?>" type="image/x-icon">
    <title>Bawaskar Farmer Store</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">

    <!-- bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="<?php echo e(asset('fastkart-store/css/vendors/bootstrap.css')); ?>">

    <!-- Iconly css -->
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('fastkart-store/css/bulk-style.css')); ?>">

    <!-- Template css -->
    <link id="color-link" rel="stylesheet" type="text/css" href="<?php echo e(asset('fastkart-store/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-store/css/bawaskar-store.css')); ?>">
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
                                        <h6>Something you love is now on sale!
                                            <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-white">Buy Now
                                                !</a>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <ul class="about-list right-nav-about">
                            <li class="right-nav-list">
                                <div class="dropdown theme-form-select">
                                    <button class="btn dropdown-toggle" type="button" id="select-language"
                                        data-bs-toggle="dropdown">
                                        <img src="<?php echo e(asset('fastkart-store/images/country/united-states.png')); ?>"
                                            class="img-fluid blur-up lazyload" alt="">
                                        <span>English</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" id="english">
                                                <img src="<?php echo e(asset('fastkart-store/images/country/united-kingdom.png')); ?>"
                                                    class="img-fluid blur-up lazyload" alt="">
                                                <span>English</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" id="france">
                                                <img src="<?php echo e(asset('fastkart-store/images/country/germany.png')); ?>"
                                                    class="img-fluid blur-up lazyload" alt="">
                                                <span>Germany</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" id="chinese">
                                                <img src="<?php echo e(asset('fastkart-store/images/country/turkish.png')); ?>"
                                                    class="img-fluid blur-up lazyload" alt="">
                                                <span>Turki</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="right-nav-list">
                                <div class="dropdown theme-form-select">
                                    <button class="btn dropdown-toggle" type="button" id="select-dollar"
                                        data-bs-toggle="dropdown">
                                        <span>USD</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end sm-dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" id="aud" href="javascript:void(0)">AUD</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" id="eur" href="javascript:void(0)">EUR</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" id="cny" href="javascript:void(0)">CNY</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
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
                            <a href="<?php echo e(route('store.home')); ?>" class="web-logo nav-logo">
                                <span class="bawaskar-store-logo">
                                    <img src="<?php echo e(asset('logo/logo.png')); ?>" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
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
                                        <input type="text" class="form-control search-type" placeholder="Search for products">
                                        <span class="input-group-text close-search">
                                            <i data-feather="x" class="font-light"></i>
                                        </span>
                                    </div>
                                </div>
                                <ul class="right-side-menu">
                                    <li class="right-side">
                                        <div class="delivery-login-box">
                                            <div class="delivery-icon">
                                                <div class="search-box">
                                                    <i data-feather="search"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="right-side">
                                        <a href="<?php echo e(route('store.page', ['page'=>'contact-us'])); ?>" class="delivery-login-box">
                                            <div class="delivery-icon">
                                                <i data-feather="phone-call"></i>
                                            </div>
                                            <div class="delivery-detail">
                                                <h6>24/7 Delivery</h6>
                                                <h5>+91 888 104 2340</h5>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="right-side">
                                        <a href="<?php echo e(route('store.page', ['page'=>'wishlist'])); ?>" class="btn p-0 position-relative header-wishlist">
                                            <i data-feather="heart"></i>
                                        </a>
                                    </li>
                                    <li class="right-side">
                                        <div class="onhover-dropdown header-badge">
                                            <button type="button" class="btn p-0 position-relative header-wishlist">
                                                <i data-feather="shopping-cart"></i>
                                                <span class="position-absolute top-0 start-100 translate-middle badge">2
                                                    <span class="visually-hidden">unread messages</span>
                                                </span>
                                            </button>

                                            <div class="onhover-div">
                                                <ul class="cart-list">
                                                    <li class="product-box-contain">
                                                        <div class="drop-cart">
                                                            <a href="<?php echo e(route('store.page', ['page'=>'product-left-thumbnail'])); ?>" class="drop-image">
                                                                <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/1.png')); ?>"
                                                                    class="blur-up lazyload" alt="">
                                                            </a>

                                                            <div class="drop-contain">
                                                                <a href="<?php echo e(route('store.page', ['page'=>'product-left-thumbnail'])); ?>">
                                                                    <h5>Fantasy Crunchy Choco Chip Cookies</h5>
                                                                </a>
                                                                <h6><span>1 x</span> $80.58</h6>
                                                                <button class="close-button close_button">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>

                                                    <li class="product-box-contain">
                                                        <div class="drop-cart">
                                                            <a href="<?php echo e(route('store.page', ['page'=>'product-left-thumbnail'])); ?>" class="drop-image">
                                                                <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/2.png')); ?>"
                                                                    class="blur-up lazyload" alt="">
                                                            </a>

                                                            <div class="drop-contain">
                                                                <a href="<?php echo e(route('store.page', ['page'=>'product-left-thumbnail'])); ?>">
                                                                    <h5>Peanut Butter Bite Premium Butter Cookies 600 g
                                                                    </h5>
                                                                </a>
                                                                <h6><span>1 x</span> $25.68</h6>
                                                                <button class="close-button close_button">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>

                                                <div class="price-box">
                                                    <h5>Total :</h5>
                                                    <h4 class="theme-color fw-bold">$106.58</h4>
                                                </div>

                                                <div class="button-group">
                                                    <a href="<?php echo e(route('store.page', ['page'=>'cart'])); ?>" class="btn btn-sm cart-button">View Cart</a>
                                                    <a href="<?php echo e(route('store.page', ['page'=>'checkout'])); ?>" class="btn btn-sm cart-button theme-bg-color
                                                    text-white">Checkout</a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="right-side onhover-dropdown">
                                        <div class="delivery-login-box">
                                            <div class="delivery-icon">
                                                <i data-feather="user"></i>
                                            </div>
                                            <div class="delivery-detail">
                                                <h6>Hello,</h6>
                                                <h5>My Account</h5>
                                            </div>
                                        </div>

                                        <div class="onhover-div onhover-div-login">
                                            <ul class="user-box-name">
                                                <li class="product-box-contain">
                                                    <i></i>
                                                    <a href="<?php echo e(route('store.page', ['page'=>'login'])); ?>">Log In</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="<?php echo e(route('store.page', ['page'=>'sign-up'])); ?>">Register</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="<?php echo e(route('store.page', ['page'=>'forgot'])); ?>">Forgot Password</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
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
                                    <h5>Categories</h5>
                                    <button type="button" class="btn p-0 close-button text-content">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <ul class="category-list">
                                    <li class="onhover-category-list">
                                        <a href="javascript:void(0)" class="category-name">
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/vegetable.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/cup.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/meats.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/breakfast.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/frozen.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/biscuit.svg')); ?>" alt="">
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
                                            <img src="<?php echo e(asset('fastkart-store/svg/1/grocery.svg')); ?>" alt="">
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
                                        <?php echo $__env->make('store.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                <a href="<?php echo e(route('store.home')); ?>">
                    <i class="iconly-Home icli"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="mobile-category">
                <a href="javascript:void(0)">
                    <i class="iconly-Category icli js-link"></i>
                    <span>Category</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('store.page', ['page'=>'search'])); ?>" class="search-box">
                    <i class="iconly-Search icli"></i>
                    <span>Search</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('store.page', ['page'=>'wishlist'])); ?>" class="notifi-wishlist">
                    <i class="iconly-Heart icli"></i>
                    <span>My Wish</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('store.page', ['page'=>'cart'])); ?>">
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
                        <h2>About Us</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo e(route('store.home')); ?>">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">About Us</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Fresh Vegetable Section Start -->
    <section class="fresh-vegetable-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row gx-xl-5 gy-xl-0 g-3 ratio_148_1">
                <div class="col-xl-6 col-12">
                    <div class="row g-sm-4 g-2">
                        <div class="col-6">
                            <div class="fresh-image-2">
                                <div>
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/about-us/1.jpg')); ?>"
                                        class="bg-img blur-up lazyload" alt="">
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="fresh-image">
                                <div>
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/about-us/2.jpg')); ?>"
                                        class="bg-img blur-up lazyload" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-12">
                    <div class="fresh-contain p-center-left">
                        <div>
                            <div class="review-title">
                                <h4>About Us</h4>
                                <h2>We make Organic Food In Market</h2>
                            </div>

                            <div class="delivery-list">
                                <p class="text-content">Just a few seconds to measure your body temperature. Up to 5
                                    users! The battery lasts up to 2 years. There are many variations of passages of
                                    Lorem Ipsum available.We started in 2019 and haven't stopped smashing it since. A
                                    global brand that doesn't sleep, we are 24/7 and always bringing something new with
                                    over 100 new products dropping on the monthly, bringing you the latest looks for
                                    less.</p>

                                <ul class="delivery-box">
                                    <li>
                                        <div class="delivery-box">
                                            <div class="delivery-icon">
                                                <img src="<?php echo e(asset('fastkart-store/svg/3/delivery.svg')); ?>" class="blur-up lazyload" alt="">
                                            </div>

                                            <div class="delivery-detail">
                                                <h5 class="text">Free delivery for all orders</h5>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="delivery-box">
                                            <div class="delivery-icon">
                                                <img src="<?php echo e(asset('fastkart-store/svg/3/leaf.svg')); ?>" class="blur-up lazyload" alt="">
                                            </div>

                                            <div class="delivery-detail">
                                                <h5 class="text">Only fresh foods</h5>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="delivery-box">
                                            <div class="delivery-icon">
                                                <img src="<?php echo e(asset('fastkart-store/svg/3/delivery.svg')); ?>" class="blur-up lazyload" alt="">
                                            </div>

                                            <div class="delivery-detail">
                                                <h5 class="text">Free delivery for all orders</h5>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="delivery-box">
                                            <div class="delivery-icon">
                                                <img src="<?php echo e(asset('fastkart-store/svg/3/leaf.svg')); ?>" class="blur-up lazyload" alt="">
                                            </div>

                                            <div class="delivery-detail">
                                                <h5 class="text">Only fresh foods</h5>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Fresh Vegetable Section End -->

    <!-- Client Section Start -->
    <section class="client-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="about-us-title text-center">
                        <h4>What We Do</h4>
                        <h2 class="center">We are Trusted by Clients</h2>
                    </div>

                    <div class="slider-3_1 product-wrapper">
                        <div>
                            <div class="clint-contain">
                                <div class="client-icon">
                                    <img src="<?php echo e(asset('fastkart-store/svg/3/work.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>
                                <h2>10</h2>
                                <h4>Business Years</h4>
                                <p>A coffee shop is a small business that sells coffee, pastries, and other morning
                                    goods. There are many different types of coffee shops around the world.</p>
                            </div>
                        </div>

                        <div>
                            <div class="clint-contain">
                                <div class="client-icon">
                                    <img src="<?php echo e(asset('fastkart-store/svg/3/buy.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>
                                <h2>80 K+</h2>
                                <h4>Products Sales</h4>
                                <p>Some coffee shops have a seating area, while some just have a spot to order and then
                                    go somewhere else to sit down. The coffee shop that I am going to.</p>
                            </div>
                        </div>

                        <div>
                            <div class="clint-contain">
                                <div class="client-icon">
                                    <img src="<?php echo e(asset('fastkart-store/svg/3/user.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>
                                <h2>90%</h2>
                                <h4>Happy Customers</h4>
                                <p>My goal for this coffee shop is to be able to get a coffee and get on with my day.
                                    It's a Thursday morning and I am rushing between meetings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Client Section End -->

    <!-- Team Section Start -->
    <section class="team-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="about-us-title text-center">
                <h4 class="text-content">Our Creative Team</h4>
                <h2 class="center">fastkart team member</h2>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-user product-wrapper">
                        <div>
                            <div class="team-box">
                                <div class="team-image">
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/1.jpg')); ?>" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="team-name">
                                    <h3>Anna Baranov</h3>
                                    <h5>Marketing</h5>
                                    <p>cheeseburger airedale mozzarella the big cheese fondue.</p>
                                    <ul class="team-media">
                                        <li>
                                            <a href="https://www.facebook.com/" class="fb-bg">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://in.pinterest.com/" class="pint-bg">
                                                <i class="fa-brands fa-pinterest-p"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://twitter.com/" class="twitter-bg">
                                                <i class="fa-brands fa-twitter"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://www.instagram.com/" class="insta-bg">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="team-box">
                                <div class="team-image">
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/2.jpg')); ?>" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="team-name">
                                    <h3>Anna Baranov</h3>
                                    <h5>Marketing</h5>
                                    <p>cheese on toast mozzarella bavarian bergkase smelly cheese cheesy feet.</p>
                                    <ul class="team-media">
                                        <li>
                                            <a href="https://www.facebook.com/" class="fb-bg">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://in.pinterest.com/" class="pint-bg">
                                                <i class="fa-brands fa-pinterest-p"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://twitter.com/" class="twitter-bg">
                                                <i class="fa-brands fa-twitter"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://www.instagram.com/" class="insta-bg">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="team-box">
                                <div class="team-image">
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/3.jpg')); ?>" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="team-name">
                                    <h3>Anna Baranov</h3>
                                    <h5>Marketing</h5>
                                    <p>camembert de normandie. Bocconcini rubber cheese fromage frais port-salut.</p>
                                    <ul class="team-media">
                                        <li>
                                            <a href="https://www.facebook.com/" class="fb-bg">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://in.pinterest.com/" class="pint-bg">
                                                <i class="fa-brands fa-pinterest-p"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://twitter.com/" class="twitter-bg">
                                                <i class="fa-brands fa-twitter"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://www.instagram.com/" class="insta-bg">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="team-box">
                                <div class="team-image">
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/4.jpg')); ?>" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="team-name">
                                    <h3>Anna Baranov</h3>
                                    <h5>Marketing</h5>
                                    <p>Fondue stinking bishop goat. Macaroni cheese croque monsieur cottage cheese.</p>
                                    <ul class="team-media">
                                        <li>
                                            <a href="https://www.facebook.com/" class="fb-bg">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://in.pinterest.com/" class="pint-bg">
                                                <i class="fa-brands fa-pinterest-p"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://twitter.com/" class="twitter-bg">
                                                <i class="fa-brands fa-twitter"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://www.instagram.com/" class="insta-bg">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="team-box">
                                <div class="team-image">
                                    <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/1.jpg')); ?>" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="team-name">
                                    <h3>Anna Baranov</h3>
                                    <h5>Marketing</h5>
                                    <p>squirty cheese cheddar macaroni cheese airedale cheese triangles.</p>
                                    <ul class="team-media">
                                        <li>
                                            <a href="https://www.facebook.com/" class="fb-bg">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://in.pinterest.com/" class="pint-bg">
                                                <i class="fa-brands fa-pinterest-p"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://twitter.com/" class="twitter-bg">
                                                <i class="fa-brands fa-twitter"></i>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="https://www.instagram.com/" class="insta-bg">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Team Section End -->

    <!-- Review Section Start -->
    <section class="review-section section-lg-space">
        <div class="container-fluid">
            <div class="about-us-title text-center">
                <h4 class="text-content">Latest Testimonials</h4>
                <h2 class="center">What people say</h2>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-4-half product-wrapper">
                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"I usually try to keep my sadness pent up inside where it can fester quietly as a
                                    mental illness. There, now he's trapped in a book I wrote: a crummy world of plot
                                    holes and spelling errors! As an interesting side note."</p>

                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/1.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Betty J. Turner</h4>
                                        <h6>CTO, Company</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"My busy schedule leaves little, if any, time for blogging and social media. The
                                    Lorem Ipsum Company has been a huge part of helping me grow my business through
                                    organic search and content marketing."</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/2.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Alfredo S. Rocha</h4>
                                        <h6>Project Manager</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"Professional, responsive, and able to keep up with ever-changing demand and tight
                                    deadlines: That's how I would describe Jeramy and his team at The Lorem Ipsum
                                    Company. When it comes to content marketing."</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/3.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Donald C. Spurr</h4>
                                        <h6>Sale Agents</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"After being forced to move twice within five years, our customers had a hard time
                                    finding us and our sales plummeted. The Lorem Ipsum Co. not only revitalized our
                                    brand."</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/4.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Terry G. Fain</h4>
                                        <h6>Photographer</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"I was skeptical of SEO and content marketing at first, but the Lorem Ipsum Company
                                    not only proved itself financially speaking, but the response I have received from
                                    customers is incredible."</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/1.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Gwen J. Geiger</h4>
                                        <h6>Designer</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"Jeramy and his team at the Lorem Ipsum Company whipped my website into shape just in
                                    time for tax season. I was excited by the results and am proud to direct clients to
                                    my website once again."</p>

                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/2.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Constance K. Whang</h4>
                                        <h6>CEO, Company</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"Yeah, and if you were the pope they'd be all, "Straighten your pope hat." And "Put
                                    on your good vestments." What are their names? Yep, I remember. They came in last at
                                    the Olympics!"</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/3.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Christopher R. Lee</h4>
                                        <h6>Managing Director</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="reviewer-box">
                                <i class="fa-solid fa-quote-right"></i>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>

                                <h3>Top Quality, Beautiful Location</h3>

                                <p>"Good man. Nixon's pro-war and pro-family. Hey, tell me something. You've got all
                                    this money. How come you always dress like you're doing your laundry? So, how 'bout
                                    them Knicks? What kind of a father would I be if I said no?."</p>
                                <div class="reviewer-profile">
                                    <div class="reviewer-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/inner-page/user/4.jpg')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </div>

                                    <div class="reviewer-name">
                                        <h4>Eileen R. Chu</h4>
                                        <h6>Marketing Director</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Review Section End -->

    <!-- Blog Section Start -->
    <section class="section-lg-space">
        <div class="container-fluid-lg">
            <div class="about-us-title text-center">
                <h4 class="text-content">Our Blog</h4>
                <h2 class="center">Our Latest Blog</h2>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-5 ratio_87">
                        <div>
                            <div class="blog-box">
                                <div class="blog-box-image">
                                    <div class="blog-image">
                                        <a href="<?php echo e(route('store.home')); ?>" class="rounded-3">
                                            <img src="<?php echo e(asset('fastkart-store/images/veg-2/blog/1.jpg')); ?>" class="bg-img blur-up lazyload"
                                                alt="">
                                        </a>
                                    </div>
                                </div>

                                <a href="<?php echo e(route('store.home')); ?>" class="blog-detail d-block">
                                    <h6>Farmart</h6>
                                    <h5>Fresh Meat Saugage</h5>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="blog-box">
                                <div class="blog-box-image">
                                    <div class="blog-image">
                                        <a href="<?php echo e(route('store.home')); ?>" class="rounded-3">
                                            <img src="<?php echo e(asset('fastkart-store/images/veg-2/blog/2.jpg')); ?>" class="bg-img blur-up lazyload"
                                                alt="">
                                        </a>
                                    </div>
                                </div>

                                <a href="<?php echo e(route('store.home')); ?>" class="blog-detail d-block">
                                    <h6>Soda Brand</h6>
                                    <h5>Soda 500ml - 20% OFF</h5>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="blog-box">
                                <div class="blog-box-image">
                                    <div class="blog-image">
                                        <a href="<?php echo e(route('store.home')); ?>" class="rounded-3">
                                            <img src="<?php echo e(asset('fastkart-store/images/veg-2/blog/3.jpg')); ?>" class="bg-img blur-up lazyload"
                                                alt="">
                                        </a>
                                    </div>
                                </div>

                                <a href="<?php echo e(route('store.home')); ?>" class="blog-detail d-block">
                                    <h6>Beer Brand</h6>
                                    <h5>Soda 500ml - 20% OFF</h5>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="blog-box">
                                <div class="blog-box-image">
                                    <div class="blog-image">
                                        <a href="<?php echo e(route('store.home')); ?>" class="rounded-3">
                                            <img src="<?php echo e(asset('fastkart-store/images/veg-2/blog/4.jpg')); ?>" class="bg-img blur-up lazyload"
                                                alt="">
                                        </a>
                                    </div>
                                </div>

                                <a href="<?php echo e(route('store.home')); ?>" class="blog-detail d-block">
                                    <h6>Beer Brand</h6>
                                    <h5>Fresh Beer -30% OFF</h5>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="blog-box">
                                <div class="blog-image">
                                    <a href="<?php echo e(route('store.home')); ?>" class="rounded-3">
                                        <img src="<?php echo e(asset('fastkart-store/images/veg-2/blog/5.jpg')); ?>" class="bg-img blur-up lazyload"
                                            alt="">
                                    </a>
                                </div>

                                <a href="<?php echo e(route('store.home')); ?>" class="blog-detail d-block">
                                    <h6>Milk Brand</h6>
                                    <h5>Fresh Milk</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Section End -->

    <!-- Footer Section Start -->
    <footer class="section-t-space">
        <div class="container-fluid-lg">
            <div class="service-section">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="service-contain">
                            <div class="service-box">
                                <div class="service-image">
                                    <img src="<?php echo e(asset('fastkart-store/svg/product.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Every Fresh Products</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="<?php echo e(asset('fastkart-store/svg/delivery.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Free Delivery For Order Over $50</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="<?php echo e(asset('fastkart-store/svg/discount.svg')); ?>" class="blur-up lazyload" alt="">
                                </div>

                                <div class="service-detail">
                                    <h5>Daily Mega Discounts</h5>
                                </div>
                            </div>

                            <div class="service-box">
                                <div class="service-image">
                                    <img src="<?php echo e(asset('fastkart-store/svg/market.svg')); ?>" class="blur-up lazyload" alt="">
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
                                <a href="<?php echo e(route('store.home')); ?>">
                                    <span class="bawaskar-store-logo">
                                    <img src="<?php echo e(asset('logo/logo.png')); ?>" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
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
                            <h4>Categories</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Vegetables & Fruit</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Beverages</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Meats & Seafood</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Frozen Foods</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Biscuits & Snacks</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Grocery & Staples</a>
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
                                    <a href="<?php echo e(route('store.home')); ?>" class="text-content">Home</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="text-content">Shop</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'about-us'])); ?>" class="text-content">About Us</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.home')); ?>" class="text-content">Blog</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'contact-us'])); ?>" class="text-content">Contact Us</a>
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
                                    <a href="<?php echo e(route('store.page', ['page'=>'order-success'])); ?>" class="text-content">Your Order</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'user-dashboard'])); ?>" class="text-content">Your Account</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'order-tracking'])); ?>" class="text-content">Track Order</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'wishlist'])); ?>" class="text-content">Your Wishlist</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'search'])); ?>" class="text-content">Search</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('store.page', ['page'=>'faq'])); ?>" class="text-content">FAQ</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="footer-title">
                            <h4>Contact Us</h4>
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
                                                <img src="<?php echo e(asset('fastkart-store/images/playstore.svg')); ?>" class="blur-up lazyload"
                                                    alt="">
                                            </a>
                                        </li>
                                        <li class="mb-0">
                                            <a href="https://www.apple.com/in/app-store/" target="_blank">
                                                <img src="<?php echo e(asset('fastkart-store/images/appstore.svg')); ?>" class="blur-up lazyload"
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
                    <h6 class="text-content">©2022 Bawaskar Farmer Store All rights reserved</h6>
                </div>

                <div class="payment">
                    <img src="<?php echo e(asset('fastkart-store/images/payment/1.png')); ?>" class="blur-up lazyload" alt="">
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
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/10.png')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-2">
                                <div class="deal-offer-contain">
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/11.png')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-3">
                                <div class="deal-offer-contain">
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/12.png')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-contain">
                                        <h5>Blended Instant Coffee 50 g Buy 1 Get 1 Free</h5>
                                        <h6>$52.57 <del>57.62</del> <span>500 G</span></h6>
                                    </a>
                                </div>
                            </li>

                            <li class="list-1">
                                <div class="deal-offer-contain">
                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-image">
                                        <img src="<?php echo e(asset('fastkart-store/images/vegetable/product/13.png')); ?>" class="blur-up lazyload"
                                            alt="">
                                    </a>

                                    <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="deal-contain">
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
    <script src="<?php echo e(asset('fastkart-store/js/jquery-3.6.0.min.js')); ?>"></script>

    <!-- jquery ui-->
    <script src="<?php echo e(asset('fastkart-store/js/jquery-ui.min.js')); ?>"></script>

    <!-- Bootstrap js-->
    <script src="<?php echo e(asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('fastkart-store/js/bootstrap/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js')); ?>"></script>

    <!-- feather icon js-->
    <script src="<?php echo e(asset('fastkart-store/js/feather/feather.min.js')); ?>"></script>
    <script src="<?php echo e(asset('fastkart-store/js/feather/feather-icon.js')); ?>"></script>

    <!-- Lazyload Js -->
    <script src="<?php echo e(asset('fastkart-store/js/lazysizes.min.js')); ?>"></script>

    <!-- Slick js-->
    <script src="<?php echo e(asset('fastkart-store/js/slick/slick.js')); ?>"></script>
    <script src="<?php echo e(asset('fastkart-store/js/slick/slick-animation.min.js')); ?>"></script>
    <script src="<?php echo e(asset('fastkart-store/js/slick/custom_slick.js')); ?>"></script>

    <!-- script js -->
    <script src="<?php echo e(asset('fastkart-store/js/script.js')); ?>"></script>

    <!-- theme setting js -->
    <script src="<?php echo e(asset('fastkart-store/js/theme-setting.js')); ?>"></script>
</body>

</html><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\pages\about-us.blade.php ENDPATH**/ ?>