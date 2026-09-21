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
    @include('store.partials.header')
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
                        <h2>{{ web_t('dashboard.user_dashboard', 'User Dashboard') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">{{ web_t('dashboard.user_dashboard', 'User Dashboard') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->
    <!-- User Dashboard Section Start -->
    @php
        $pendingStatuses = ['draft', 'pending', 'pending_approval', 'confirmed', 'processing'];
        $orderCount = $storeOrders->count();
        $pendingOrderCount = $storeOrders->filter(fn ($order) => in_array((string) $order->status, $pendingStatuses, true))->count();
        $addressCount = $storeUser?->addresses?->count() ?? 0;
        $recentOrders = $storeOrders->take(6);
    @endphp
    <section class="user-dashboard-section section-b-space">
        <div class="container-fluid-lg">
            @if(! $storeUser)
                <div class="row"><div class="col-12"><div class="alert alert-warning mb-0">Please <a href="{{ route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'user-dashboard'])]) }}">log in</a> to access your account dashboard.</div></div></div>
            @else
                @if(session('success'))
                    <div class="row"><div class="col-12"><div class="alert alert-success">{{ session('success') }}</div></div></div>
                @endif
                @if($errors->any())
                    <div class="row"><div class="col-12"><div class="alert alert-danger mb-0">{{ $errors->first() }}</div></div></div>
                @endif
                <div class="row">
                    <div class="col-xxl-3 col-lg-4">
                        <div class="dashboard-left-sidebar">
                            <div class="close-button d-flex d-lg-none"><button class="close-sidebar"><i class="fa-solid fa-xmark"></i></button></div>
                            <div class="profile-box">
                                <div class="cover-image"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/inner-page/cover-img.jpg') }}" class="img-fluid blur-up lazyload" alt=""></div>
                                <div class="profile-contain">
                                    <div class="profile-image"><div class="position-relative"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/inner-page/user/1.jpg') }}" class="blur-up lazyload update_img" alt="{{ $storeUser->name }}"></div></div>
                                    <div class="profile-name"><h3>{{ $storeUser->name }}</h3><h6 class="text-content">{{ $storeUser->email ?: $storeUser->mobile }}</h6></div>
                                </div>
                            </div>
                            <ul class="nav nav-pills user-nav-pills" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" id="pills-dashboard-tab" data-bs-toggle="pill" data-bs-target="#pills-dashboard" type="button"><i data-feather="home"></i>{{ web_t('dashboard.dashboard', 'Dashboard') }}</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="pills-order-tab" data-bs-toggle="pill" data-bs-target="#pills-order" type="button"><i data-feather="shopping-bag"></i>{{ web_t('dashboard.orders', 'Orders') }}</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="pills-address-tab" data-bs-toggle="pill" data-bs-target="#pills-address" type="button"><i data-feather="map-pin"></i>{{ web_t('dashboard.addresses', 'Addresses') }}</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button"><i data-feather="user"></i>{{ web_t('dashboard.profile', 'Profile') }}</button></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xxl-9 col-lg-8">
                        <button class="btn left-dashboard-show btn-animation btn-md fw-bold d-block mb-4 d-lg-none">Show Menu</button>
                        <div class="dashboard-right-sidebar">
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-dashboard" role="tabpanel">
                                    <div class="dashboard-home">
                                        <div class="title"><h2>{{ web_t('dashboard.my_dashboard', 'My Dashboard') }}</h2><span class="title-leaf"><svg class="icon-width bg-gray"><use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use></svg></span></div>
                                        <div class="dashboard-user-name"><h6 class="text-content">{{ web_t('dashboard.hello', 'Hello') }}, <b class="text-title">{{ $storeUser->name }}</b></h6><p class="text-content">{{ web_t('dashboard.review_recent_orders_note', 'You can review recent orders, confirm shipping details, and manage your account information from this dashboard.') }}</p></div>
                                        <div class="total-box"><div class="row g-sm-4 g-3"><div class="col-xxl-4 col-lg-6 col-md-4 col-sm-6"><div class="total-contain"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/order.svg') }}" class="img-1 blur-up lazyload" alt=""><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/order.svg') }}" class="blur-up lazyload" alt=""><div class="total-detail"><h5>{{ web_t('dashboard.total_order', 'Total Order') }}</h5><h3>{{ $orderCount }}</h3></div></div></div><div class="col-xxl-4 col-lg-6 col-md-4 col-sm-6"><div class="total-contain"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/pending.svg') }}" class="img-1 blur-up lazyload" alt=""><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/pending.svg') }}" class="blur-up lazyload" alt=""><div class="total-detail"><h5>{{ web_t('dashboard.total_pending_order', 'Total Pending Order') }}</h5><h3>{{ $pendingOrderCount }}</h3></div></div></div><div class="col-xxl-4 col-lg-6 col-md-4 col-sm-6"><div class="total-contain"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/wishlist.svg') }}" class="img-1 blur-up lazyload" alt=""><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/svg/wishlist.svg') }}" class="blur-up lazyload" alt=""><div class="total-detail"><h5>{{ web_t('dashboard.saved_address', 'Saved Address') }}</h5><h3>{{ $addressCount }}</h3></div></div></div></div></div>
                                        <div class="dashboard-title"><h3>{{ web_t('dashboard.account_information', 'Account Information') }}</h3></div>
                                        <div class="row g-4"><div class="col-xxl-6"><div class="dashboard-content-title"><h4>{{ web_t('dashboard.contact_information', 'Contact Information') }} <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editProfile">{{ web_t('dashboard.edit', 'Edit') }}</a></h4></div><div class="dashboard-detail"><h6 class="text-content">{{ $storeUser->name }}</h6><h6 class="text-content">{{ $storeUser->email ?: web_t('dashboard.email_not_available', 'Email not available') }}</h6><h6 class="text-content">{{ $storeUser->mobile ?: web_t('dashboard.mobile_not_available', 'Mobile not available') }}</h6></div></div><div class="col-xxl-6"><div class="dashboard-content-title"><h4>{{ web_t('dashboard.account_status', 'Account Status') }}</h4></div><div class="dashboard-detail"><h6 class="text-content">{{ web_t('dashboard.account_type', 'Account Type') }}: {{ ucfirst($storeUser->role) }}</h6><h6 class="text-content">{{ web_t('dashboard.status', 'Status') }}: {{ ucwords(str_replace('_', ' ', (string) $storeUser->status)) }}</h6><h6 class="text-content">{{ web_t('dashboard.last_login', 'Last Login') }}: {{ $storeUser->last_login_at?->format('d M Y, h:i A') ?: web_t('dashboard.not_available', 'Not available') }}</h6></div></div><div class="col-12"><div class="dashboard-content-title"><h4>{{ web_t('dashboard.default_address', 'Default Address') }}</h4></div><div class="row g-4"><div class="col-xxl-6"><div class="dashboard-detail"><h6 class="text-content">{{ web_t('dashboard.default_shipping_address', 'Default Shipping Address') }}</h6>@if($storePrimaryAddress)<h6 class="text-content">{{ $storePrimaryAddress->name }}</h6><h6 class="text-content">{{ $storePrimaryAddress->address_line1 }}{{ $storePrimaryAddress->address_line2 ? ', '.$storePrimaryAddress->address_line2 : '' }}</h6><h6 class="text-content">{{ $storePrimaryAddress->city }}, {{ $storePrimaryAddress->state }} - {{ $storePrimaryAddress->pincode }}</h6><h6 class="text-content">{{ $storePrimaryAddress->mobile }}</h6>@else<h6 class="text-content">{{ web_t('dashboard.no_default_address_saved', 'No default address saved yet.') }}</h6>@endif<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editProfile">{{ web_t('dashboard.edit_address', 'Edit Address') }}</a></div></div><div class="col-xxl-6"><div class="dashboard-detail"><h6 class="text-content">{{ web_t('dashboard.checkout_flow', 'Checkout Flow') }}</h6><h6 class="text-content">{{ web_t('dashboard.checkout_flow_note', 'You can update delivery address during checkout and place a new order directly from the storefront.') }}</h6><a href="{{ route('store.page', ['page' => 'checkout']) }}">{{ web_t('dashboard.go_to_checkout', 'Go to Checkout') }}</a></div></div></div></div></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-order" role="tabpanel"><div class="dashboard-order"><div class="title"><h2>{{ web_t('dashboard.my_orders_history', 'My Orders History') }}</h2><span class="title-leaf title-leaf-gray"><svg class="icon-width bg-gray"><use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use></svg></span></div><div class="order-contain">@forelse($recentOrders as $order)@php $firstItem = $order->items->first(); $product = $firstItem?->product; $imageUrl = optional($product?->images?->first())->url ?: asset('fastkart-store/images/vegetable/product/1.png'); $status = ucwords(str_replace('_', ' ', (string) $order->status)); $statusClass = in_array((string) $order->status, ['delivered', 'completed'], true) ? 'success-bg' : ''; $quantityTotal = $order->items->sum('quantity'); @endphp<div class="order-box dashboard-bg-box"><div class="order-container"><div class="order-icon"><i data-feather="box"></i></div><div class="order-detail"><h4>{{ $order->order_no }} <span class="{{ $statusClass }}">{{ $status }}</span></h4><h6 class="text-content">{{ web_t('dashboard.placed_on', 'Placed on') }} {{ $order->created_at?->format('d M Y, h:i A') ?: 'N/A' }} | {{ web_t('dashboard.payment', 'Payment') }}: {{ ucwords(str_replace('_', ' ', (string) ($order->payment_method ?: 'cod'))) }}</h6></div></div><div class="product-order-detail"><a href="{{ $product ? route('store.product', ['product' => $product->id]) : route('store.page', ['page' => 'shop-left-sidebar']) }}" class="order-image"><img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="blur-up lazyload" alt="{{ $product?->translatedName() ?: web_t('dashboard.order_item', 'Order item') }}"></a><div class="order-wrap"><a href="{{ $product ? route('store.product', ['product' => $product->id]) : route('store.page', ['page' => 'shop-left-sidebar']) }}"><h3>{{ $product?->translatedName() ?: web_t('dashboard.order_items', 'Order items') }}</h3></a><p class="text-content">{{ $order->items->count() }} {{ web_t('dashboard.line_items_order_note', 'line item(s) in this order. Delivery and stock movement are handled from the live ecommerce backend.') }}</p><ul class="product-size">@if($firstItem?->variant_name)<li><div class="size-box"><h6 class="text-content">{{ web_t('dashboard.pack_size', 'Pack Size') }} : </h6><h5>{{ $firstItem->variant_name }}</h5></div></li>@endif<li><div class="size-box"><h6 class="text-content">{{ web_t('dashboard.grand_total', 'Grand Total') }} : </h6><h5>Rs. {{ number_format((float) $order->grand_total, 2) }}</h5></div></li><li><div class="size-box"><h6 class="text-content">{{ web_t('dashboard.quantity', 'Quantity') }} : </h6><h5>{{ rtrim(rtrim(number_format((float) $quantityTotal, 3, '.', ''), '0'), '.') }}</h5></div></li><li><div class="size-box"><h6 class="text-content">{{ web_t('dashboard.salesman', 'Salesman') }} : </h6><h5>{{ $order->salesman?->name ?: web_t('dashboard.assigned_later', 'Assigned later') }}</h5></div></li><li><div class="size-box"><h6 class="text-content">{{ web_t('dashboard.order_view', 'Order View') }} : </h6><h5><a href="{{ route('store.page', ['page' => 'order-tracking', 'order' => $order->order_no]) }}">{{ web_t('nav.track_order', 'Track Order') }}</a></h5></div></li></ul></div></div></div>@empty<div class="dashboard-detail"><h6 class="text-content">{{ web_t('dashboard.no_orders_placed_yet', 'No orders placed yet.') }}</h6><a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">{{ web_t('dashboard.start_shopping', 'Start Shopping') }}</a></div>@endforelse</div></div></div>
                                <div class="tab-pane fade" id="pills-address" role="tabpanel">
                                    <div class="dashboard-address">
                                        <div class="title title-flex">
                                            <div>
                                                <h2>{{ web_t('dashboard.saved_addresses', 'Saved Addresses') }}</h2>
                                                <span class="title-leaf title-leaf-gray"><svg class="icon-width bg-gray"><use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use></svg></span>
                                            </div>
                                            <button class="btn theme-bg-color text-white btn-sm fw-bold mt-lg-0 mt-3" data-bs-toggle="modal" data-bs-target="#add-address"><i data-feather="plus" class="me-2"></i>{{ web_t('dashboard.add_new_address', 'Add New Address') }}</button>
                                        </div>

                                        <div class="row g-sm-4 g-3">
                                            @forelse($storeUser->addresses as $address)
                                                <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6">
                                                    <div class="address-box">
                                                        <div>
                                                            <form method="POST" action="{{ route('store.addresses.default', $address->id) }}" class="form-check">
                                                                @csrf
                                                                <input class="form-check-input" type="radio" name="default_address" id="addressDefault{{ $address->id }}" {{ $address->is_default ? 'checked' : '' }} onchange="this.form.submit()">
                                                            </form>

                                                            <div class="label"><label for="addressDefault{{ $address->id }}">{{ ucfirst($address->type ?: 'shipping') }}</label></div>

                                                            <div class="table-responsive address-table">
                                                                <table class="table">
                                                                    <tbody>
                                                                        <tr><td colspan="2">{{ $address->name }}</td></tr>
                                                                        <tr><td>{{ web_t('dashboard.address', 'Address') }} :</td><td><p>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}, {{ $address->city }}, {{ $address->state }}</p></td></tr>
                                                                        <tr><td>{{ web_t('dashboard.pin_code', 'Pin Code') }} :</td><td>{{ $address->pincode }}</td></tr>
                                                                        <tr><td>{{ web_t('dashboard.phone', 'Phone') }} :</td><td>{{ $address->mobile }}</td></tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="button-group">
                                                            <button class="btn btn-sm add-button w-100" data-bs-toggle="modal" data-bs-target="#editAddress{{ $address->id }}"><i data-feather="edit"></i>{{ web_t('dashboard.edit', 'Edit') }}</button>
                                                            <button class="btn btn-sm add-button w-100" data-bs-toggle="modal" data-bs-target="#removeAddress" data-address-id="{{ $address->id }}"><i data-feather="trash-2"></i>{{ web_t('dashboard.remove', 'Remove') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12"><div class="dashboard-detail"><h6 class="text-content">{{ web_t('dashboard.no_saved_addresses_note', 'No saved addresses yet. Add one during checkout and save it as default.') }}</h6></div></div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-profile" role="tabpanel">
                                    <div class="dashboard-profile">
                                        <div class="title">
                                            <h2>{{ web_t('dashboard.profile_details', 'Profile Details') }}</h2>
                                            <span class="title-leaf title-leaf-gray"><svg class="icon-width bg-gray"><use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use></svg></span>
                                        </div>

                                        <div class="profile-detail dashboard-bg-box">
                                            <div class="dashboard-title"><h3>{{ web_t('dashboard.profile_name', 'Profile Name') }}</h3></div>
                                            <div class="profile-name-detail">
                                                <div class="d-sm-flex align-items-center d-block"><h3>{{ $storeUser->name }}</h3></div>
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editProfile">{{ web_t('dashboard.edit', 'Edit') }}</a>
                                            </div>
                                            <div class="location-profile">
                                                <ul>
                                                    <li><div class="location-box"><i data-feather="mail"></i><h6>{{ $storeUser->email ?: web_t('dashboard.not_available', 'Not available') }}</h6></div></li>
                                                    <li><div class="location-box"><i data-feather="phone"></i><h6>{{ $storeUser->mobile ?: web_t('dashboard.not_available', 'Not available') }}</h6></div></li>
                                                    <li><div class="location-box"><i data-feather="check-square"></i><h6>{{ ucfirst($storeUser->role) }} {{ web_t('dashboard.account', 'Account') }}</h6></div></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="profile-about dashboard-bg-box">
                                            <div class="row">
                                                <div class="col-xxl-7">
                                                    <div class="dashboard-title mb-3"><h3>{{ web_t('dashboard.account_details', 'Account Details') }}</h3></div>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <tbody>
                                                                <tr><td>{{ web_t('dashboard.status', 'Status') }} :</td><td>{{ ucwords(str_replace('_', ' ', (string) $storeUser->status)) }}</td></tr>
                                                                <tr><td>{{ web_t('dashboard.orders_available', 'Orders Available') }} :</td><td>{{ $orderCount }}</td></tr>
                                                                <tr><td>{{ web_t('dashboard.saved_addresses', 'Saved Addresses') }} :</td><td>{{ $addressCount }}</td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="dashboard-title mb-3"><h3>{{ web_t('dashboard.login_details', 'Login Details') }}</h3></div>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <tbody>
                                                                <tr><td>{{ web_t('dashboard.email', 'Email') }} :</td><td><a href="javascript:void(0)">{{ $storeUser->email ?: web_t('dashboard.not_available', 'Not available') }} <span data-bs-toggle="modal" data-bs-target="#editProfile">{{ web_t('dashboard.edit', 'Edit') }}</span></a></td></tr>
                                                                <tr><td>{{ web_t('dashboard.password', 'Password') }} :</td><td><a href="javascript:void(0)">●●●●●● <span data-bs-toggle="modal" data-bs-target="#editProfile">{{ web_t('dashboard.edit', 'Edit') }}</span></a></td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="col-xxl-5">
                                                    <div class="profile-image"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/inner-page/dashboard-profile.png') }}" class="img-fluid blur-up lazyload" alt="{{ $storeUser->name }}"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- User Dashboard Section End -->

    <!-- Footer Section Start -->
    @include('store.partials.footer')
    <!-- Footer Section End -->

    <!-- Deal Box Modal Start -->
    @include('store.partials.deal-modal')
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

    <!-- Add address modal box start -->
    <div class="modal fade theme-modal" id="add-address" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add a new address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addAddressForm" method="POST" action="{{ route('store.addresses.store') }}">
                        @csrf
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" name="type" placeholder="Home / Office">
                            <label>{{ web_t('dashboard.address_type', 'Address Type (Home / Office)') }}</label>
                        </div>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" name="name" placeholder="Enter Name" required>
                            <label>{{ web_t('dashboard.name', 'Name') }}</label>
                        </div>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="tel" class="form-control" name="mobile" placeholder="Enter Mobile Number" maxlength="10" required>
                            <label>{{ web_t('dashboard.mobile', 'Mobile') }}</label>
                        </div>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" name="address_line1" placeholder="Enter Address" required>
                            <label>{{ web_t('dashboard.add_address', 'Add Address') }}</label>
                        </div>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" name="address_line2" placeholder="Enter Address 2">
                            <label>{{ web_t('dashboard.add_address_2', 'Add Address 2') }}</label>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="city" placeholder="City" required>
                                    <label>{{ web_t('dashboard.city', 'City') }}</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="state" placeholder="State" required>
                                    <label>{{ web_t('dashboard.state', 'State') }}</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="pincode" placeholder="Pin Code" required>
                                    <label>{{ web_t('dashboard.pin_code', 'Pin Code') }}</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="addAddressForm" class="btn theme-bg-color btn-md text-white">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add address modal box end -->

    @if($storeUser)
        @foreach($storeUser->addresses as $address)
            <!-- Edit address modal for {{ $address->id }} -->
            <div class="modal fade theme-modal" id="editAddress{{ $address->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Address</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="modal-body">
                            <form id="editAddressForm{{ $address->id }}" method="POST" action="{{ route('store.addresses.update', $address->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-floating mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="type" value="{{ $address->type }}" placeholder="Home / Office">
                                    <label>{{ web_t('dashboard.address_type', 'Address Type (Home / Office)') }}</label>
                                </div>
                                <div class="form-floating mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="name" value="{{ $address->name }}" required>
                                    <label>{{ web_t('dashboard.name', 'Name') }}</label>
                                </div>
                                <div class="form-floating mb-4 theme-form-floating">
                                    <input type="tel" class="form-control" name="mobile" value="{{ $address->mobile }}" maxlength="10" required>
                                    <label>{{ web_t('dashboard.mobile', 'Mobile') }}</label>
                                </div>
                                <div class="form-floating mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="address_line1" value="{{ $address->address_line1 }}" required>
                                    <label>{{ web_t('dashboard.add_address', 'Add Address') }}</label>
                                </div>
                                <div class="form-floating mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="address_line2" value="{{ $address->address_line2 }}">
                                    <label>{{ web_t('dashboard.add_address_2', 'Add Address 2') }}</label>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="form-floating theme-form-floating">
                                            <input type="text" class="form-control" name="city" value="{{ $address->city }}" required>
                                            <label>{{ web_t('dashboard.city', 'City') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating theme-form-floating">
                                            <input type="text" class="form-control" name="state" value="{{ $address->state }}" required>
                                            <label>{{ web_t('dashboard.state', 'State') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating theme-form-floating">
                                            <input type="text" class="form-control" name="pincode" value="{{ $address->pincode }}" required>
                                            <label>{{ web_t('dashboard.pin_code', 'Pin Code') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Close</button>
                            <button type="submit" form="editAddressForm{{ $address->id }}" class="btn theme-bg-color btn-md text-white">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Location Modal Start -->
    @include('store.partials.location-modal', ['locationModalLabelId' => 'exampleModalLabel1'])
    <!-- Location Modal End -->

    <!-- Edit Profile Start -->
    <div class="modal fade theme-modal" id="editProfile" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" method="POST" action="{{ route('store.profile.update') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-xxl-12">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="name" id="pname" value="{{ old('name', $storeUser->name) }}">
                                    <label for="pname">Full Name</label>
                                </div>
                            </div>

                            <div class="col-xxl-6">
                                <div class="form-floating theme-form-floating">
                                    <input type="email" class="form-control" name="email" id="email1" value="{{ old('email', $storeUser->email) }}">
                                    <label for="email1">Email address</label>
                                </div>
                            </div>

                            <div class="col-xxl-6">
                                <div class="form-floating theme-form-floating">
                                    <input class="form-control" type="tel" value="{{ $storeUser->mobile }}" id="mobile" readonly>
                                    <label for="mobile">Mobile Number (cannot be changed)</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="address_line1" id="address1"
                                        value="{{ old('address_line1', $storePrimaryAddress->address_line1 ?? '') }}">
                                    <label for="address1">Add Address</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="address_line2" id="address2" value="{{ old('address_line2', $storePrimaryAddress->address_line2 ?? '') }}">
                                    <label for="address2">Add Address 2</label>
                                </div>
                            </div>

                            @include('store.partials.location-fields')
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-animation btn-md fw-bold"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="editProfileForm"
                        class="btn theme-bg-color btn-md fw-bold text-light">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Profile End -->

    <!-- Edit Card Start -->
    <div class="modal fade theme-modal" id="editCard" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel8">Edit Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-xxl-6">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" id="finame" value="Mark">
                                    <label for="finame">First Name</label>
                                </div>
                            </form>
                        </div>

                        <div class="col-xxl-6">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" id="laname" value="Jecno">
                                    <label for="laname">Last Name</label>
                                </div>
                            </form>
                        </div>

                        <div class="col-xxl-4">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <select class="form-select" id="floatingSelect12">
                                        <option selected>Card Type</option>
                                        <option value="kingdom">Visa Card</option>
                                        <option value="states">MasterCard Card</option>
                                        <option value="fra">RuPay Card</option>
                                        <option value="china">Contactless Card</option>
                                        <option value="spain">Maestro Card</option>
                                    </select>
                                    <label for="floatingSelect12">Card Type</label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-animation btn-md fw-bold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn theme-bg-color btn-md fw-bold text-light">Update Card</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Card End -->

    <!-- Remove Profile Modal Start -->
    <div class="modal fade theme-modal remove-profile" id="removeProfile" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header d-block text-center">
                    <h5 class="modal-title w-100" id="exampleModalLabel22">Are You Sure ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="remove-box">
                        <p>The permission for the use/group, preview is inherited from the object, object will create a
                            new permission for this object</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-animation btn-md fw-bold" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn theme-bg-color btn-md fw-bold text-light"
                        data-bs-target="#removeAddress" data-bs-toggle="modal">Yes</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade theme-modal remove-profile" id="removeAddress" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header d-block text-center">
                    <h5 class="modal-title w-100" id="exampleModalLabel12">Remove this address?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="remove-box text-center">
                        <p>This address will be removed from your account. This cannot be undone.</p>
                    </div>
                </div>
                <div class="modal-footer pt-0">
                    <button type="button" class="btn btn-animation btn-md fw-bold" data-bs-dismiss="modal">No</button>
                    <form id="removeAddressForm" method="POST" data-action-template="{{ route('store.addresses.destroy', ['address' => '__ID__']) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn theme-bg-color btn-md fw-bold text-light">Yes, Remove</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Remove Profile Modal End -->

    <!-- latest jquery-->
    <script src="{{ asset('fastkart-store/js/jquery-3.6.0.min.js') }}"></script>

    <!-- jquery ui-->
    <script src="{{ asset('fastkart-store/js/jquery-ui.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- Quantity js -->
    <script src="{{ asset('fastkart-store/js/quantity-2.js') }}"></script>

    <!-- Nav & tab upside js -->
    <script src="{{ asset('fastkart-store/js/nav-tab.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')
    <script src="{{ asset('js/location-picker.js') }}"></script>

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.bootstrap) {
        return;
    }

    const hash = window.location.hash;
    if (!hash) {
        return;
    }

    const tabButton = document.querySelector('[data-bs-target="' + hash + '"]');
    if (tabButton) {
        new bootstrap.Tab(tabButton).show();
        const section = document.querySelector('.user-dashboard-section');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const removeModal = document.getElementById('removeAddress');
    const removeForm = document.getElementById('removeAddressForm');
    if (!removeModal || !removeForm) {
        return;
    }

    removeModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const addressId = button ? button.getAttribute('data-address-id') : null;
        const template = removeForm.getAttribute('data-action-template');
        if (addressId && template) {
            removeForm.setAttribute('action', template.replace('__ID__', addressId));
        }
    });
});
</script>
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.bootstrap) {
        return;
    }

    const modalEl = document.getElementById('editProfile');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});
</script>
@endif
</body>

</html>





