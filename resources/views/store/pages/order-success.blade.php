@extends('store.layouts.live')

@section('title', 'Order Success')

@section('content')
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>Order Status</h2></div></div></div></div>
</section>
<section class="order-success-section section-b-space">
    <div class="container-fluid-lg">
        @if(! $storeLastOrder)
            <div class="alert alert-light mb-0">No recent order found. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Browse products</a>.</div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="order-box-contain">
                        <div class="order-box">
                            <div class="order-image"><img src="{{ asset('fastkart-store/images/gif/order-success.gif') }}" class="img-fluid blur-up lazyload" alt=""></div>
                            <div class="order-contain">
                                <h3>Thank you, {{ $storeUser?->name ?: 'Customer' }}</h3>
                                <h5 class="text-content">Your order has been placed successfully.</h5>
                                <h6 class="text-content">Order No: {{ $storeLastOrder->order_no }}</h6>
                                <h6 class="text-content">Status: {{ str($storeLastOrder->status)->replace('_', ' ')->title() }}</h6>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h4>Items</h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                                    <tbody>
                                        @foreach($storeLastOrder->items as $item)
                                            <tr>
                                                <td>{{ $item->product?->name ?: 'Product' }}</td>
                                                <td>{{ number_format((float) $item->quantity, 3) }}</td>
                                                <td>Rs. {{ number_format((float) $item->unit_price, 2) }}</td>
                                                <td>Rs. {{ number_format((float) $item->line_total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="summery-box p-sticky">
                        <div class="summery-header"><h3>Order Summary</h3></div>
                        <ul class="summery-contain">
                            <li><h4>Subtotal <span>Rs. {{ number_format((float) $storeLastOrder->subtotal, 2) }}</span></h4></li>
                            <li><h4>GST <span>Rs. {{ number_format((float) $storeLastOrder->gst_total, 2) }}</span></h4></li>
                            <li><h4>Grand Total <span>Rs. {{ number_format((float) $storeLastOrder->grand_total, 2) }}</span></h4></li>
                        </ul>
                        <div class="mt-3">
                            <h5 class="mb-2">Delivery Address</h5>
                            <p class="text-content mb-0">{{ $storeLastOrder->contact_name }}<br>{{ $storeLastOrder->contact_mobile }}<br>{{ $storeLastOrder->address_line1 }}{{ $storeLastOrder->address_line2 ? ', '.$storeLastOrder->address_line2 : '' }}<br>{{ $storeLastOrder->city }}, {{ $storeLastOrder->state }} - {{ $storeLastOrder->pincode }}</p>
                        </div>
                        <div class="button-group cart-button mt-4">
                            <a href="{{ route('store.page', ['page' => 'user-dashboard']) }}" class="btn btn-animation fw-bold">Go To Dashboard</a>
                            <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="btn btn-light shopping-button text-dark">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
