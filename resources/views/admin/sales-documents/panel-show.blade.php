@php
    // Shared by Sale Orders, Proforma Invoices and Sale Invoices — the same
    // SalesDocumentDataContract that already builds the PDF/print payload, so
    // the in-panel view can never disagree with what gets printed.
    $documentType = match ($module['key']) {
        'orders' => 'order',
        'proforma-invoices' => 'proforma',
        'invoices' => 'invoice',
        default => 'order',
    };
    $doc = app(\App\Contracts\Sales\SalesDocumentDataContract::class)->forDocument($documentType, $record->getKey());
    $order = $doc['order'];
    $isDealer = $order->order_type === 'dealer';
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);
    $statusBadge = fn (?string $status): string => match (strtolower((string) $status)) {
        'active', 'approved', 'delivered', 'paid', 'accepted', 'converted', 'issued' => 'badge-light-success',
        'cancelled', 'rejected', 'failed' => 'badge-light-danger',
        'packing', 'dispatched', 'out_for_delivery', 'in_transit', 'sent' => 'badge-light-info',
        default => 'badge-light-warning',
    };
    $statusLabel = $module['key'] === 'proforma-invoices' && $doc['status'] === 'converted'
        ? 'Converted to Sale Invoice'
        : str((string) $doc['status'])->replace('_', ' ')->title();
    $money = fn ($value) => 'Rs. '.number_format((float) $value, 2);
@endphp

<div class="report-page sales-doc-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="report-hero-icon"><i data-feather="file-text"></i></span>
                    <div>
                        <span class="report-hero-section">{{ $doc['title'] }}</span>
                        <h4 class="report-hero-title">{{ $doc['number'] }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $statusBadge($doc['status']) }}">{{ $statusLabel }}</span>
                            @if($module['key'] === 'orders' && $order->proformaInvoices->isNotEmpty())
                                <span class="badge badge-light-info">Converted to PI</span>
                            @endif
                            <span><i data-feather="{{ $isDealer ? 'briefcase' : 'user' }}"></i>{{ $isDealer ? 'Dealer' : 'Customer' }}</span>
                            @if($doc['date'])<span><i data-feather="calendar"></i>{{ $doc['date']->format('d-m-Y') }}</span>@endif
                            @if($doc['validUntil'])<span><i data-feather="clock"></i>Valid until {{ $doc['validUntil']->format('d-m-Y') }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                    @if($module['key'] === 'orders' && $can['edit'])
                        @if($order->proformaInvoices->isNotEmpty())
                            <a class="btn btn-outline-secondary" href="{{ route('admin.proforma-invoices.show', $order->proformaInvoices->first()->getKey()) }}"><i data-feather="repeat"></i>View PI</a>
                        @else
                            <form method="POST" action="{{ route('admin.orders.convert-to-proforma', $record->getKey()) }}" class="d-inline">@csrf<button class="btn btn-outline-secondary" type="submit"><i data-feather="repeat"></i>Convert to PI</button></form>
                        @endif
                    @endif
                    @if($module['key'] === 'proforma-invoices' && $can['edit'])
                        @if($record->status === 'converted')
                            @if($order->invoice)<a class="btn btn-outline-secondary" href="{{ route('admin.invoices.show', $order->invoice->getKey()) }}"><i data-feather="repeat"></i>View Sale Invoice</a>@endif
                        @else
                            <form method="POST" action="{{ route('admin.proforma-invoices.convert-to-invoice', $record->getKey()) }}" class="d-inline">@csrf<button class="btn btn-outline-secondary" type="submit"><i data-feather="repeat"></i>Convert to Sale Invoice</button></form>
                        @endif
                    @endif
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sales-documents.print', ['document' => $documentType, 'id' => $record->getKey()]) }}" target="_blank"><i data-feather="printer"></i>Print A4</a>
                    <a class="btn btn-outline-danger" href="{{ route('admin.sales-documents.pdf', ['document' => $documentType, 'id' => $record->getKey()]) }}"><i data-feather="download"></i>PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach([
            ['title' => $isDealer ? 'Dealer' : 'Customer', 'icon' => $isDealer ? 'briefcase' : 'user', 'rows' => [
                'Name' => $doc['party'],
                'Mobile' => $doc['billing']['mobile'] ?: '-',
                'GSTIN' => $doc['billing']['gstin'] ?: '-',
                'Salesman' => $order->salesman?->name ?: '-',
            ]],
            ['title' => 'Billing Address', 'icon' => 'map-pin', 'rows' => [
                'Address' => $doc['billing']['address'] ?: '-',
                'Place of Supply' => $doc['placeOfSupply'] ?: '-',
            ]],
            ['title' => 'Shipping Address', 'icon' => 'truck', 'rows' => [
                'Name' => $doc['shipping']['name'] ?: '-',
                'Mobile' => $doc['shipping']['mobile'] ?: '-',
                'Address' => $doc['shipping']['address'] ?: '-',
            ]],
        ] as $panel)
            <div class="col-xl-4 col-md-6">
                <div class="card report-table-card people-info-card h-100 mb-0">
                    <div class="card-header border-0">
                        <div class="card-header-title d-flex align-items-center gap-2">
                            <span class="people-info-icon"><i data-feather="{{ $panel['icon'] }}"></i></span>
                            <h4>{{ $panel['title'] }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="people-info-list">
                            @foreach($panel['rows'] as $label => $value)
                                <li>
                                    <span class="people-info-label">{{ $label }}</span>
                                    <span class="people-info-value">{{ $value }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card report-table-card">
        <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="card-header-title"><h4>Items</h4></div>
            <span class="report-meta">{{ count($doc['items']) }} line{{ count($doc['items']) === 1 ? '' : 's' }}</span>
        </div>
        <div class="card-body pt-0">
            @if($doc['items'] === [])
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="inbox"></i></span>
                    <h5>No items</h5>
                    <p>This document has no line items.</p>
                </div>
            @else
                <div class="table-responsive report-table-wrap">
                    <table class="table align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>HSN</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">GST %</th>
                                <th class="text-end">GST Amt</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($doc['items'] as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-title">{{ $item['name'] }}</td>
                                    <td>{{ $item['hsn_code'] ?: '-' }}</td>
                                    <td class="text-end text-nowrap">{{ rtrim(rtrim(number_format((float) $item['quantity'], 3, '.', ''), '0'), '.') }}</td>
                                    <td>{{ $item['unit'] }}</td>
                                    <td class="text-end text-nowrap">{{ $money($item['unit_price']) }}</td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $item['gst_percent'], 2) }}%</td>
                                    <td class="text-end text-nowrap">{{ $money($item['gst_amount']) }}</td>
                                    <td class="text-end text-nowrap">{{ $money($item['line_total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-end">Subtotal</td>
                                <td class="text-end text-nowrap">{{ $money($doc['totals']['subtotal']) }}</td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">GST</td>
                                <td class="text-end text-nowrap">{{ $money($doc['totals']['gst_total']) }}</td>
                            </tr>
                            @if((float) $doc['totals']['discount_total'] > 0)
                                <tr>
                                    <td colspan="8" class="text-end">Discount</td>
                                    <td class="text-end text-nowrap">- {{ $money($doc['totals']['discount_total']) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="8" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end text-nowrap fw-bold">{{ $money($doc['totals']['grand_total']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p class="report-meta mt-2 mb-0">Amount in words: {{ $doc['taxSummary']['in_words'] }}</p>
            @endif
        </div>
    </div>
</div>
