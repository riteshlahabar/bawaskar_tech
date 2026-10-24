@php
    /** Table-only layout: dompdf has no flex/grid support. Follows "Sales Order Template 01". */
    $money = static fn ($value): string => number_format((float) $value, 2);
    $qty = static fn ($value): string => number_format((float) $value, 2);

    $companyName = $company?->company_name ?: 'Dr. Bawasakar Technology';
    $issuedOn = $date ? \Illuminate\Support\Carbon::parse($date)->format('d-M-y') : '-';
    $fillerRows = max(0, 24 - count($items));
    $discount = (float) $totals['discount_total'];

    $details = array_filter([
        $title.' Number' => $number,
        $title.' Date' => $issuedOn,
        'Valid Until' => $validUntil ? \Illuminate\Support\Carbon::parse($validUntil)->format('d-M-y') : null,
        'Place of Supply' => $placeOfSupply ?: null,
        'Reverse Charge' => 'No',
    ]);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} {{ $number }}</title>
    <style>
        @page { margin: 26px 28px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9.5px; color: #111; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .sheet { border: 1px solid #555; }
        .box { border-bottom: 1px solid #555; }
        .serif { font-family: "DejaVu Serif", serif; }
        .title-bar { background: #d9d9d9; text-align: center; font-weight: bold; font-size: 12px; padding: 5px; letter-spacing: .3px; }
        .company { text-align: center; padding: 6px 8px 7px; line-height: 1.45; }
        .company-name { font-family: "DejaVu Serif", serif; font-size: 15px; font-weight: bold; }
        .details td { padding: 1.5px 8px; vertical-align: top; }
        .details .label { font-family: "DejaVu Serif", serif; font-weight: bold; width: 38%; }
        .party { width: 50%; vertical-align: top; padding: 6px 8px; line-height: 1.45; }
        .party-heading { font-family: "DejaVu Serif", serif; font-weight: bold; margin-bottom: 4px; }
        .items th { background: #fbd5b5; font-family: "DejaVu Serif", serif; font-weight: bold; padding: 7px 4px; border-left: 1px solid #555; border-bottom: 1px solid #555; }
        .items td { padding: 5px 4px; border-left: 1px solid #555; vertical-align: top; }
        .items th:first-child, .items td:first-child { border-left: 0; }
        .c { text-align: center; }
        .r { text-align: right; }
        .total-row td { background: #f8c79d; font-weight: bold; font-size: 11px; padding: 5px 6px; border-top: 1px solid #555; border-bottom: 1px solid #555; }
        .words { font-family: "DejaVu Serif", serif; font-weight: bold; padding: 4px 6px; }
        .tax-line { background: #d8e4bc; font-weight: bold; padding: 4px 6px; line-height: 1.5; }
        .foot td { vertical-align: top; padding: 8px; }
        .sign-box { border-left: 1px solid #555; height: 90px; }
        .taxtbl { border-top: 1px solid #555; }
        .taxtbl th { background: #d8e4bc; font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 9px; padding: 4px 3px; border-left: 1px solid #555; border-bottom: 1px solid #555; }
        .taxtbl th.sub { background: #e8f0d8; font-size: 8.5px; padding: 3px; }
        .taxtbl th:first-child { border-left: 0; }
        .taxtbl td { padding: 4px 3px; border-left: 1px solid #555; border-bottom: 1px solid #bbb; }
        .taxtbl td:first-child { border-left: 0; }
        .taxtbl .taxtotal td { background: #eef3e2; font-weight: bold; border-bottom: 1px solid #555; }
    </style>
</head>
<body>

<div class="sheet">
    <div class="box title-bar">{{ strtoupper($title) }}</div>

    <div class="box company">
        <div class="company-name">{{ $companyName }}</div>
        @if($company?->address)<div>{{ $company->address }}</div>@endif
        @if($company?->phone || $company?->email)
            <div>
                @if($company?->phone)<strong>Mobile:</strong> {{ $company->phone }}@endif
                @if($company?->phone && $company?->email) | @endif
                @if($company?->email)<strong>Email:</strong> {{ $company->email }}@endif
            </div>
        @endif
        @if($company?->gst_number)<div><strong>GSTIN</strong> - {{ $company->gst_number }}</div>@endif
    </div>

    <div class="box" style="padding: 5px 0;">
        <table class="details">
            @foreach($details as $label => $value)
                <tr><td class="label">{{ $label }}</td><td>: {{ $value }}</td></tr>
            @endforeach
        </table>
    </div>

    <table class="box">
        <tr>
            @foreach(['Billing Details' => $billing, 'Shipping Details' => $shipping] as $heading => $partyBlock)
                <td class="party" @if(! $loop->first) style="border-left: 1px solid #555;" @endif>
                    <div class="party-heading">{{ $heading }}</div>
                    <div class="serif"><strong>{{ $partyBlock['name'] }}</strong></div>
                    @if($partyBlock['gstin'] !== '' || $partyBlock['mobile'] !== '')
                        <div>
                            @if($partyBlock['gstin'] !== '')<strong>GSTIN:</strong> {{ $partyBlock['gstin'] }}@endif
                            @if($partyBlock['gstin'] !== '' && $partyBlock['mobile'] !== '') | @endif
                            @if($partyBlock['mobile'] !== '')<strong>Mobile:</strong> {{ $partyBlock['mobile'] }}@endif
                        </div>
                    @endif
                    @if($partyBlock['address'] !== '')
                        <div style="padding-top: 3px;"><strong>Address:</strong> {{ $partyBlock['address'] }}</div>
                    @endif
                </td>
            @endforeach
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">Sr.</th>
                <th style="width: 33%;">Item Description</th>
                <th style="width: 10%;">HSN/SAC</th>
                <th style="width: 9%;">Qty</th>
                <th style="width: 8%;">Unit</th>
                <th style="width: 12%;">List Price</th>
                <th style="width: 8%;">Tax %</th>
                <th style="width: 15%;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="c">{{ $item['hsn_code'] ?: '-' }}</td>
                    <td class="c">{{ $qty($item['quantity']) }}</td>
                    <td class="c">{{ $item['unit'] }}</td>
                    <td class="r">{{ $money($item['unit_price']) }}</td>
                    <td class="c">{{ number_format((float) $item['gst_percent'], 2) }}</td>
                    <td class="r">{{ $money($item['line_total']) }}</td>
                </tr>
            @endforeach
            @if($fillerRows > 0)
                {{-- Keeps the column lines running down the page like the printed template. --}}
                <tr>
                    @for($i = 0; $i < 8; $i++)<td style="height: {{ $fillerRows * 20 }}px;"></td>@endfor
                </tr>
            @endif
        </tbody>
    </table>

    <table>
        @if($discount > 0)
            <tr class="total-row">
                <td style="width: 70%; padding-left: 60px; background: #fde3cf;">Discount</td>
                <td class="r" style="background: #fde3cf;">- {{ $money($discount) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td style="width: 70%; padding-left: 60px;">Total</td>
            <td class="r">{{ $money($totals['grand_total']) }}</td>
        </tr>
    </table>

    <div class="box words">{{ $taxSummary['in_words'] }}</div>

    <table class="taxtbl">
        <thead>
            <tr>
                <th rowspan="2" style="width: 16%;">Tax Rate</th>
                <th rowspan="2" style="width: 20%;">Taxable Value</th>
                @if($taxSummary['intra_state'])
                    <th colspan="2" style="width: 24%;">CGST</th>
                    <th colspan="2" style="width: 24%;">SGST</th>
                @else
                    <th colspan="2" style="width: 48%;">IGST</th>
                @endif
                <th rowspan="2" style="width: 16%;">Total Tax</th>
            </tr>
            <tr>
                @if($taxSummary['intra_state'])
                    <th class="sub">Rate</th><th class="sub">Amount</th>
                    <th class="sub">Rate</th><th class="sub">Amount</th>
                @else
                    <th class="sub">Rate</th><th class="sub">Amount</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($taxSummary['rows'] as $row)
                @php $half = rtrim(rtrim(number_format((float) $row['rate'] / 2, 2), '0'), '.'); @endphp
                <tr>
                    <td class="c">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                    <td class="r">{{ $money($row['taxable']) }}</td>
                    @if($taxSummary['intra_state'])
                        <td class="c">{{ $half }}%</td>
                        <td class="r">{{ $money($row['tax'] / 2) }}</td>
                        <td class="c">{{ $half }}%</td>
                        <td class="r">{{ $money($row['tax'] / 2) }}</td>
                    @else
                        <td class="c">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                        <td class="r">{{ $money($row['tax']) }}</td>
                    @endif
                    <td class="r">{{ $money($row['tax']) }}</td>
                </tr>
            @endforeach
            <tr class="taxtotal">
                <td class="c">Total</td>
                <td class="r">{{ $money($taxSummary['taxable']) }}</td>
                @if($taxSummary['intra_state'])
                    <td class="c">&mdash;</td>
                    <td class="r">{{ $money($taxSummary['tax'] / 2) }}</td>
                    <td class="c">&mdash;</td>
                    <td class="r">{{ $money($taxSummary['tax'] / 2) }}</td>
                @else
                    <td class="c">&mdash;</td>
                    <td class="r">{{ $money($taxSummary['tax']) }}</td>
                @endif
                <td class="r">{{ $money($taxSummary['tax']) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="foot">
        <tr>
            <td style="width: 60%;">
                <div class="serif" style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">Declaration</div>
                <div>This is a computer-generated document.</div>
                @if($order->salesman?->name)<div style="padding-top: 3px;">Salesman: {{ $order->salesman->name }}</div>@endif
            </td>
            <td class="sign-box" style="width: 40%;">
                <div class="serif c" style="font-weight: bold;">For {{ $companyName }}</div>
                <div class="serif r" style="font-weight: bold; padding-top: 58px;">Signature</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
