@extends('admin.layouts.app')
@section('title', 'Dashboard ERP')
@section('content')
@php
    // The second strip of figures, given the same tile treatment as $stats so
    // the top of the dashboard reads as one band rather than two.
    $secondaryTiles = [
        ['label' => 'Today Sales', 'value' => 'Rs. '.number_format((float) $todaySales, 2), 'icon' => 'iconoir-receive-dollars', 'color' => 'success', 'trend' => 'Booked today'],
        ['label' => 'Month Sales', 'value' => 'Rs. '.number_format((float) $monthSales, 2), 'icon' => 'iconoir-wallet', 'color' => 'primary', 'trend' => 'This month'],
        ['label' => 'Pending Dealers', 'value' => number_format($pendingDealers->count()), 'icon' => 'iconoir-shop', 'color' => 'warning', 'trend' => 'Awaiting approval'],
        ['label' => 'Low Stock Alerts', 'value' => number_format($lowStock->count()), 'icon' => 'iconoir-box', 'color' => 'danger', 'trend' => 'At or below alert level'],
    ];
@endphp

{{-- The Fastkart dashboard tile (back-end/index.html): `main-tiles` on a
     `custome-N-bg` body, the figure beside a tinted icon box. The icon must be
     a FONT icon, not a feather SVG - `custome-N-bg .static-top-widget svg` is
     painted white by the template for the badge, which would have turned an
     SVG tile icon invisible against its own 10% tint. --}}
<div class="row">
    @foreach($stats as $index => $stat)
        <div class="col-sm-6 col-xxl-3 col-lg-6">
            <div class="main-tiles border-5 card-hover border-0 card o-hidden">
                <div class="custome-{{ ($index % 4) + 1 }}-bg b-r-4 card-body">
                    <div class="media align-items-center static-top-widget">
                        <div class="media-body p-0">
                            <span class="m-0">{{ $stat['label'] }}</span>
                            <h4 class="mb-0">{{ number_format((float) $stat['value']) }}
                                <span class="badge badge-light-{{ $stat['color'] }} grow">{{ $stat['trend'] }}</span>
                            </h4>
                        </div>
                        <div class="align-self-center text-center">
                            <i class="{{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach($secondaryTiles as $index => $tile)
        <div class="col-sm-6 col-xxl-3 col-lg-6">
            <div class="main-tiles border-5 card-hover border-0 card o-hidden">
                <div class="custome-{{ ($index % 4) + 1 }}-bg b-r-4 card-body">
                    <div class="media align-items-center static-top-widget">
                        <div class="media-body p-0">
                            <span class="m-0">{{ $tile['label'] }}</span>
                            <h4 class="mb-0">{{ $tile['value'] }}
                                <span class="badge badge-light-{{ $tile['color'] }} grow">{{ $tile['trend'] }}</span>
                            </h4>
                        </div>
                        <div class="align-self-center text-center">
                            <i class="{{ $tile['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h4 class="card-title">Recent Sale Orders</h4></div>
                    <div class="col-auto"><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table theme-table table-borderless table-hover align-middle mb-0 admin-detail-table">
                        <thead><tr><th>Order</th><th>Channel</th><th>Party</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->created_at->format('d-m-Y') }}</small></td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ strtoupper($order->order_type) }}</span></td>
                                    <td>{{ $order->dealer?->dealerProfile?->firm_name ?? $order->customer?->name ?? '-' }}</td>
                                    <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                    <td><span class="badge bg-{{ in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning' }}-subtle text-{{ in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning' }}">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No sale orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Dealer Sale Orders</h4></div>
                            <div class="col-auto"><a href="{{ route('admin.orders.index', ['type' => 'dealer']) }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table theme-table table-borderless table-hover align-middle mb-0 admin-detail-table">
                                <thead><tr><th>Order</th><th>Dealer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($pendingDealerOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->salesman?->name ?? '-' }}</small></td>
                                            <td>{{ $order->dealer?->dealerProfile?->firm_name ?? $order->dealer?->name ?? '-' }}</td>
                                            <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                            <td><span class="badge bg-warning-subtle text-warning">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending dealer sale orders.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Customer Sale Orders</h4></div>
                            <div class="col-auto"><a href="{{ route('admin.orders.index', ['type' => 'customer']) }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table theme-table table-borderless table-hover align-middle mb-0 admin-detail-table">
                                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($pendingCustomerOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->created_at->format('d-m-Y') }}</small></td>
                                            <td>{{ $order->customer?->name ?? '-' }}</td>
                                            <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                            <td><span class="badge bg-warning-subtle text-warning">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending customer sale orders.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">ERP Action Centre</h4></div>
            <div class="card-body">
                <a href="{{ route('admin.dealers.index', ['status' => 'pending_approval']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-shop me-2 text-primary"></i>Dealer approvals</span><strong>{{ $pendingDealers->count() }}</strong></a>
                <a href="{{ route('admin.orders.index', ['status' => 'admin_review']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-cart me-2 text-success"></i>Admin review orders</span><strong>{{ $stats[3]['value'] }}</strong></a>
                <a href="{{ route('admin.products.index') }}" class="d-flex justify-content-between py-3 text-body"><span><i class="iconoir-box me-2 text-info"></i>Products</span><strong>{{ $stats[2]['value'] }}</strong></a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title">Low Stock</h4></div>
            <div class="card-body pt-0">
                @forelse($lowStock as $stock)
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong>{{ $stock->product?->name }}</strong><small class="d-block text-muted">{{ $stock->warehouse?->name }} - {{ $stock->batch_no }}</small></div><span class="badge bg-danger-subtle text-danger align-self-center">{{ $stock->quantity }}</span></div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">No low-stock alerts.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection