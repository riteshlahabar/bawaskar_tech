@php
    /** Table-only layout: dompdf has no flex/grid support. */
    $money = static fn ($value): string => '₹'.number_format((float) $value, 2);
    $qty = static fn ($value): string => rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');

    // dompdf needs GD to rasterise PNG/JPG. Without it the whole render throws,
    // so the logo is dropped rather than risking a broken invoice.
    $logoFile = $company?->logo_path
        ? public_path($company->logo_path)
        : public_path('logo/logo.png');
    $logo = (extension_loaded('gd') && is_file($logoFile)) ? $logoFile : null;

    $companyName = $company?->company_name ?: 'Dr. Bawasakar Technology';
    $issuedOn = $date ? \Illuminate\Support\Carbon::parse($date)->format('d M Y') : '-';
    $statusLabel = str($status ?? '')->replace('_', ' ')->title();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} {{ $number }}</title>
    <style>
        @page { margin: 22px 26px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #222; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: top; padding: 0 0 10px 0; }
        .brand-name { font-size: 16px; font-weight: bold; color: #005D30; }
        .muted { color: #666; }
        .doc-title { font-size: 20px; font-weight: bold; color: #005D30; text-transform: uppercase; }
        .rule { border-bottom: 2px solid #005D30; height: 1px; font-size: 0; margin-bottom: 10px; }
        .panel { border: 1px solid #d9d9d9; padding: 8px 10px; }
        .panel-label { font-size: 8px; text-transform: uppercase; letter-spacing: .5px; color: #777; }
        .panel-value { font-size: 11px; font-weight: bold; padding-top: 2px; }
        .items th { background: #005D30; color: #fff; font-size: 9px; text-transform: uppercase; padding: 6px 5px; text-align: left; }
        .items td { border-bottom: 1px solid #e8e8e8; padding: 6px 5px; }
        .items tr.alt td { background: #f7faf8; }
        .r { text-align: right; }
        .c { text-align: center; }
        .totals td { padding: 4px 6px; }
        .totals .grand td { border-top: 1.5px solid #005D30; font-size: 12px; font-weight: bold; color: #005D30; padding-top: 6px; }
        .sign { height: 52px; }
        .sign-line { border-top: 1px solid #333; width: 150px; text-align: center; padding-top: 3px; font-size: 9px; }
        .foot { font-size: 8px; color: #888; padding-top: 14px; }
    </style>
</head>
<body>

<table class="head">
    <tr>
        <td style="width: 58%;">
            @if($logo)
                <img src="{{ $logo }}" alt="" height="34" style="margin-bottom:4px;">
            @endif
            <div class="brand-name">{{ $companyName }}</div>
            @if($company?->address)<div class="muted">{{ $company->address }}</div>@endif
            <div class="muted">
                @if($company?->phone){{ $company->phone }}@endif
                @if($company?->email) · {{ $company->email }}@endif
            </div>
            @if($company?->gst_number)<div class="muted">GSTIN: {{ $company->gst_number }}</div>@endif
        </td>
        <td style="width: 42%; text-align: right;">
            <div class="doc-title">{{ $title }}</div>
            <div style="padding-top:4px;"><strong>{{ $number }}</strong></div>
            <div class="muted">Date: {{ $issuedOn }}</div>
            @if($validUntil)
                <div class="muted">Valid until: {{ \Illuminate\Support\Carbon::parse($validUntil)->format('d M Y') }}</div>
            @endif
            @if($statusLabel !== '')<div class="muted">Status: {{ $statusLabel }}</div>@endif
        </td>
    </tr>
</table>

<div class="rule"></div>

<table style="margin-bottom: 12px;">
    <tr>
        <td style="width: 50%; padding-right: 6px;">
            <div class="panel">
                <div class="panel-label">Billed To</div>
                <div class="panel-value">{{ $party }}</div>
                @if($contact?->mobile)<div class="muted">{{ $contact->mobile }}</div>@endif
                @if($contact?->email)<div class="muted">{{ $contact->email }}</div>@endif
                @if($order->order_type === 'dealer' && $order->dealer?->dealerProfile?->gst_number)
                    <div class="muted">GSTIN: {{ $order->dealer->dealerProfile->gst_number }}</div>
                @endif
            </div>
        </td>
        <td style="width: 50%; padding-left: 6px;">
            <div class="panel">
                <div class="panel-label">Order Details</div>
                <div class="panel-value">{{ $order->order_no }}</div>
                <div class="muted">Channel: {{ str($order->order_type)->title() }}</div>
                @if($order->salesman?->name)<div class="muted">Salesman: {{ $order->salesman->name }}</div>@endif
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 41%;">Item</th>
            <th style="width: 12%;" class="c">HSN</th>
            <th style="width: 10%;" class="r">Qty</th>
            <th style="width: 12%;" class="r">Rate</th>
            <th style="width: 10%;" class="r">GST</th>
            <th style="width: 14%;" class="r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $index => $item)
            <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="c">{{ $item['hsn_code'] ?: '-' }}</td>
                <td class="r">{{ $qty($item['quantity']) }}</td>
                <td class="r">{{ $money($item['unit_price']) }}</td>
                <td class="r">
                    {{ $money($item['gst_amount']) }}
                    <div class="muted" style="font-size:8px;">{{ number_format((float) $item['gst_percent'], 2) }}%</div>
                </td>
                <td class="r">{{ $money($item['line_total']) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="c muted" style="padding:14px;">No items on this document.</td></tr>
        @endforelse
    </tbody>
</table>

<table style="margin-top: 12px;">
    <tr>
        <td style="width: 58%; vertical-align: bottom;">
            <div class="muted">This is a computer-generated document and does not require a physical signature.</div>
        </td>
        <td style="width: 42%;">
            <table class="totals">
                <tr><td>Subtotal</td><td class="r">{{ $money($totals['subtotal']) }}</td></tr>
                <tr><td>GST</td><td class="r">{{ $money($totals['gst_total']) }}</td></tr>
                @if((float) $totals['discount_total'] > 0)
                    <tr><td>Discount</td><td class="r">- {{ $money($totals['discount_total']) }}</td></tr>
                @endif
                <tr class="grand"><td>Grand Total</td><td class="r">{{ $money($totals['grand_total']) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table style="margin-top: 18px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%; text-align: right;">
            <div class="sign"></div>
            <table><tr><td class="r"><div class="sign-line">Authorised Signature</div></td></tr></table>
        </td>
    </tr>
</table>

<div class="foot">{{ $companyName }}@if($company?->website) · {{ $company->website }}@endif</div>

</body>
</html>
