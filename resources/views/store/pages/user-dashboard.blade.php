@extends('store.layouts.live')

@section('title', 'My Account')

@section('content')
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>My Account</h2></div></div></div></div>
</section>
<section class="user-dashboard-section section-b-space">
    <div class="container-fluid-lg">
        @if(! $storeUser)
            <div class="alert alert-warning mb-0">Please <a href="{{ route('store.page', ['page' => 'login']) }}">log in</a> to see your account.</div>
        @else
            <div class="row g-4">
                <div class="col-xl-4 col-lg-5">
                    <div class="dashboard-left-sidebar">
                        <div class="close-button d-flex d-lg-none"><button class="close-sidebar">Close</button></div>
                        <div class="profile-box">
                            <div class="cover-image"><img src="{{ asset('fastkart-store/images/inner-page/dashboard-bg.jpg') }}" class="img-fluid blur-up lazyload" alt=""></div>
                            <div class="profile-contain">
                                <div class="profile-name">
                                    <h3>{{ $storeUser->name }}</h3>
                                    <h6 class="text-content">{{ ucfirst($storeUser->role) }} account</h6>
                                    <h6 class="text-content">{{ $storeUser->mobile ?: 'No mobile added' }}</h6>
                                    <h6 class="text-content">{{ $storeUser->email }}</h6>
                                </div>
                                @if($storeUser->role === 'dealer')
                                    <div class="mt-3 pt-3 border-top">
                                        <h6 class="mb-1">Firm: {{ $storeUser->dealerProfile?->firm_name ?: 'Not added' }}</h6>
                                        <h6 class="mb-1">Dealer Code: {{ $storeUser->dealerProfile?->dealer_code ?: 'Pending' }}</h6>
                                        <h6 class="mb-0">Approval: {{ $storeUser->status === 'active' ? 'Approved' : 'Pending' }}</h6>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('store.auth.logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-7">
                    <div class="dashboard-right-sidebar">
                        <div class="row g-4">
                            <div class="col-md-4"><div class="dashboard-home"><div class="dashboard-user-name"><h6 class="text-content">Orders</h6><h2>{{ $storeOrders->count() }}</h2></div></div></div>
                            <div class="col-md-4"><div class="dashboard-home"><div class="dashboard-user-name"><h6 class="text-content">Cart Items</h6><h2>{{ rtrim(rtrim(number_format((float) data_get($storeCart, 'count', 0), 3, '.', ''), '0'), '.') ?: '0' }}</h2></div></div></div>
                            <div class="col-md-4"><div class="dashboard-home"><div class="dashboard-user-name"><h6 class="text-content">Latest Total</h6><h2>Rs. {{ number_format((float) optional($storeOrders->first())->grand_total, 2) }}</h2></div></div></div>
                        </div>

                        <div class="dashboard-order mt-4">
                            <div class="title"><h2>Saved Address</h2></div>
                            @if($storePrimaryAddress)
                                <div class="order-contain">
                                    <h5>{{ $storePrimaryAddress->name }}</h5>
                                    <h6 class="text-content mb-1">{{ $storePrimaryAddress->mobile }}</h6>
                                    <p class="text-content mb-0">{{ $storePrimaryAddress->address_line1 }}{{ $storePrimaryAddress->address_line2 ? ', '.$storePrimaryAddress->address_line2 : '' }}, {{ $storePrimaryAddress->city }}, {{ $storePrimaryAddress->state }} - {{ $storePrimaryAddress->pincode }}</p>
                                </div>
                            @else
                                <div class="alert alert-light">No saved address yet. Add one during checkout.</div>
                            @endif
                        </div>

                        <div class="dashboard-order mt-4">
                            <div class="title"><h2>Recent Orders</h2></div>
                            @if($storeOrders->isEmpty())
                                <div class="alert alert-light">No orders yet. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Start shopping</a>.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead><tr><th>Order No</th><th>Status</th><th>Items</th><th>Total</th><th>Date</th></tr></thead>
                                        <tbody>
                                            @foreach($storeOrders as $order)
                                                <tr>
                                                    <td>{{ $order->order_no }}</td>
                                                    <td>{{ str($order->status)->replace('_', ' ')->title() }}</td>
                                                    <td>{{ $order->items->count() }}</td>
                                                    <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                                    <td>{{ optional($order->created_at)->format('d M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
