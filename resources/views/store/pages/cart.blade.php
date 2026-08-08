@extends('store.layouts.live')

@section('title', 'Cart')

@section('content')
@php
    $cartItems = collect(data_get($storeCart, 'items', collect()));
@endphp
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>Cart</h2></div></div></div></div>
</section>
<section class="cart-section section-b-space">
    <div class="container-fluid-lg">
        @if($cartItems->isEmpty())
            <div class="alert alert-light mb-0">Your cart is empty. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Continue shopping</a>.</div>
        @else
            <div class="row g-sm-4 g-3">
                <div class="col-xxl-9">
                    <div class="cart-table">
                        <div class="table-responsive-xl">
                            <form method="POST" action="{{ route('store.cart.update') }}">
                                @csrf
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>GST</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cartItems as $item)
                                            @php $product = $item['product']; @endphp
                                            <tr>
                                                <td>
                                                    <a href="{{ route('store.product', ['product' => $product->id]) }}">{{ $product->name }}</a>
                                                    @if($item['has_issue'])
                                                        <div class="text-danger small mt-1">Available stock: {{ number_format($item['available_stock'], 3) }}</div>
                                                    @endif
                                                </td>
                                                <td>Rs. {{ number_format($item['unit_price'], 2) }}</td>
                                                <td style="max-width: 140px;">
                                                    <input type="number" class="form-control" name="items[{{ $product->id }}]" value="{{ $item['quantity'] }}" min="0" step="0.001">
                                                </td>
                                                <td>Rs. {{ number_format($item['gst_amount'], 2) }}</td>
                                                <td>Rs. {{ number_format($item['line_total'], 2) }}</td>
                                                <td>
                                                    <button type="submit" formaction="{{ route('store.cart.remove', ['productId' => $product->id]) }}" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-animation">Update Cart</button>
                                    <button type="submit" formaction="{{ route('store.cart.clear') }}" class="btn btn-outline-secondary">Clear Cart</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3">
                    <div class="summery-box p-sticky">
                        <div class="summery-header"><h3>Order Summary</h3></div>
                        <ul class="summery-contain">
                            <li><h4>Items <span>{{ number_format((float) data_get($storeCart, 'count', 0), 3) }}</span></h4></li>
                            <li><h4>Subtotal <span>Rs. {{ number_format((float) data_get($storeCart, 'subtotal', 0), 2) }}</span></h4></li>
                            <li><h4>GST <span>Rs. {{ number_format((float) data_get($storeCart, 'gst_total', 0), 2) }}</span></h4></li>
                            <li><h4>Grand Total <span>Rs. {{ number_format((float) data_get($storeCart, 'grand_total', 0), 2) }}</span></h4></li>
                        </ul>
                        <div class="button-group cart-button">
                            <a href="{{ route('store.page', ['page' => 'checkout']) }}" class="btn btn-animation proceed-btn fw-bold">Proceed To Checkout</a>
                            <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="btn btn-light shopping-button text-dark">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
