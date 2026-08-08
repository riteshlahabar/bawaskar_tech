@extends('store.layouts.live')

@section('title', 'Checkout')

@section('content')
@php
    $cartItems = collect(data_get($storeCart, 'items', collect()));
    $address = $storePrimaryAddress;
@endphp
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>Checkout</h2></div></div></div></div>
</section>
<section class="checkout-section-2 section-b-space">
    <div class="container-fluid-lg">
        @if(! $storeUser)
            <div class="alert alert-warning mb-0">Please <a href="{{ route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'checkout'])]) }}">log in</a> before checkout.</div>
        @elseif($cartItems->isEmpty())
            <div class="alert alert-light mb-0">Your cart is empty. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Browse products</a>.</div>
        @else
            <form method="POST" action="{{ route('store.checkout.place-order') }}">
                @csrf
                <div class="row g-sm-4 g-3">
                    <div class="col-lg-8">
                        <div class="left-sidebar-checkout">
                            <div class="checkout-detail-box">
                                <ul>
                                    <li>
                                        <div class="checkout-box">
                                            <div class="checkout-title"><h4>Delivery Address</h4></div>
                                            <div class="checkout-detail">
                                                <div class="row g-3">
                                                    <div class="col-md-6"><label class="form-label">Contact Name</label><input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address?->name ?: $storeUser->name) }}"></div>
                                                    <div class="col-md-6"><label class="form-label">Contact Mobile</label><input type="text" class="form-control" name="contact_mobile" value="{{ old('contact_mobile', $address?->mobile ?: $storeUser->mobile) }}"></div>
                                                    <div class="col-md-4"><label class="form-label">Address Type</label><input type="text" class="form-control" name="address_type" value="{{ old('address_type', $address?->type ?: 'shipping') }}"></div>
                                                    <div class="col-md-8"><label class="form-label">Address Line 1</label><input type="text" class="form-control" name="address_line1" value="{{ old('address_line1', $address?->address_line1) }}"></div>
                                                    <div class="col-12"><label class="form-label">Address Line 2</label><input type="text" class="form-control" name="address_line2" value="{{ old('address_line2', $address?->address_line2) }}"></div>
                                                    <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="{{ old('city', $address?->city) }}"></div>
                                                    <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="{{ old('state', $address?->state) }}"></div>
                                                    <div class="col-md-4"><label class="form-label">Pincode</label><input type="text" class="form-control" name="pincode" value="{{ old('pincode', $address?->pincode) }}"></div>
                                                    <div class="col-12 form-check mt-2 ms-2"><input class="form-check-input" type="checkbox" name="save_as_default" value="1" id="saveAsDefault" {{ old('save_as_default', $address?->is_default) ? 'checked' : '' }}><label class="form-check-label" for="saveAsDefault">Save as default address</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="checkout-box">
                                            <div class="checkout-title"><h4>Payment Method</h4></div>
                                            <div class="checkout-detail">
                                                <div class="row g-3">
                                                    @foreach(['cod' => 'Cash on Delivery', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'credit' => 'Dealer Credit'] as $value => $label)
                                                        <div class="col-md-6">
                                                            <div class="form-check custom-form-check">
                                                                <input class="form-check-input" type="radio" name="payment_method" id="payment_{{ $value }}" value="{{ $value }}" {{ old('payment_method', 'cod') === $value ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="payment_{{ $value }}">{{ $label }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="col-12"><label class="form-label">Order Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes') }}</textarea></div>
                                                </div>
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
                                <div class="summery-header"><h3>Order Summary</h3></div>
                                <ul class="summery-contain">
                                    @foreach($cartItems as $item)
                                        <li>
                                            <h4>{{ $item['product']->name }} x {{ number_format($item['quantity'], 3) }}</h4>
                                            <h4 class="price">Rs. {{ number_format($item['line_total'], 2) }}</h4>
                                        </li>
                                    @endforeach
                                </ul>
                                <ul class="summery-total">
                                    <li><h4>Subtotal <span>Rs. {{ number_format((float) data_get($storeCart, 'subtotal', 0), 2) }}</span></h4></li>
                                    <li><h4>GST <span>Rs. {{ number_format((float) data_get($storeCart, 'gst_total', 0), 2) }}</span></h4></li>
                                    <li><h4>Grand Total <span>Rs. {{ number_format((float) data_get($storeCart, 'grand_total', 0), 2) }}</span></h4></li>
                                </ul>
                                <button type="submit" class="btn btn-animation w-100">Place Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection
