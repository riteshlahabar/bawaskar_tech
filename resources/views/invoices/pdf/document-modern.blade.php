@php
    /**
     * "Modern" design - open layout in the Bawaskar brand teal, with the GST
     * summary full width at the bottom and the company's bank details printed
     * beside the signature.
     *
     * Bank details come from Company Profile. The block is dropped entirely when
     * none are filled in, rather than printing a box of blanks, so the footer
     * stays correct on a fresh install.
     *
     * Serves every document type (Sale Order, Proforma, Invoice) and both
     * channels - the payload already resolves dealer vs customer upstream.
     *
     * Table-only layout: dompdf has no flex, grid or shadow.
     */
    $money = static fn ($value): string => number_format((float) $value, 2);
    $qty = static fn ($value): string => rtrim(rtrim(number_format((float) $value, 3), '0'), '.');
    $pct = static fn ($value): string => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');

    $companyName = $company?->company_name ?: 'Dr. Bawasakar Technology';
    $issuedOn = $date ? \Illuminate\Support\Carbon::parse($date)->format('d-M-Y') : '-';
    $validTill = $validUntil ? \Illuminate\Support\Carbon::parse($validUntil)->format('d-M-Y') : null;
    $discount = (float) $totals['discount_total'];
    $intra = (bool) $taxSummary['intra_state'];

    $bank = $company?->hasBankDetails()
        ? array_filter([
            'Account Name' => $company->account_name ?: $companyName,
            'Bank' => $company->bank_name,
            'Branch' => $company->bank_branch,
            'Account No.' => $company->account_number,
            'IFSC' => $company->ifsc_code,
            'UPI' => $company->upi_id,
        ])
        : [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} {{ $number }}</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #16302b; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .c { text-align: center; }
        .r { text-align: right; }
        .mute { color: #6b8a83; }
        .sm { font-size: 8px; }
        .hd { font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 8px;
              letter-spacing: 1px; color: #0b7f69; padding-bottom: 3px; }

        /* header */
        .hdr td { vertical-align: top; padding: 0; }
        .co-name { font-family: "DejaVu Serif", serif; font-size: 17px; font-weight: bold;
                   color: #096d5a; letter-spacing: .2px; }
        .co-rule { border-top: 2.5px solid #0da487; width: 54px; margin: 4px 0 5px; }
        .co-line { line-height: 1.5; }
        .docpanel { border: 1px solid #0da487; }
        .docpanel .ttl { background: #0da487; color: #fff; font-family: "DejaVu Serif", serif;
                         font-weight: bold; font-size: 12px; letter-spacing: 2px;
                         text-align: center; padding: 5px; }
        .docpanel td.k { background: #f3faf8; font-weight: bold; padding: 3.5px 6px;
                         border-top: 1px solid #d8ece7; width: 44%; color: #0b7f69; }
        .docpanel td.v { padding: 3.5px 6px; border-top: 1px solid #d8ece7; }

        /* parties */
        .strip { margin-top: 12px; border-top: 2px solid #0da487; border-bottom: 1px solid #0da487; }
        .strip td { vertical-align: top; padding: 7px 9px; line-height: 1.5; }
        .strip td + td { border-left: 1px solid #d8ece7; }
        .strip .nm { font-weight: bold; font-size: 10px; color: #16302b; }

        /* items */
        .it { margin-top: 10px; }
        .it th { background: #0da487; color: #fff; font-family: "DejaVu Serif", serif;
                 font-size: 8px; letter-spacing: .8px; text-transform: uppercase;
                 padding: 6px 4px; border-left: 1px solid #45bba4; }
        .it th:first-child { border-left: 0; }
        .it td { padding: 6px 4px; border-bottom: 1px solid #edf7f4; vertical-align: top; }
        .it .nm { font-weight: bold; }
        .it tr.z td { background: #f7fcfa; }

        /* totals */
        .sum { border: 1px solid #0da487; }
        .sum td { padding: 5px 9px; border-bottom: 1px solid #edf7f4; }
        .sum td.k { color: #0b7f69; }
        .sum tr.g td { background: #0da487; color: #fff; font-size: 12px; font-weight: bold;
                       padding: 8px 9px; border-bottom: 0; }

        .words { margin-top: 10px; border: 1px solid #d8ece7; background: #f3faf8;
                 padding: 6px 9px; color: #09543f; }
        .words b { font-family: "DejaVu Serif", serif; }

        /* GST summary - now full width at the bottom */
        .tx { margin-top: 11px; border: 1px solid #0da487; }
        .tx th { background: #0da487; color: #fff; font-size: 8px; font-weight: bold;
                 padding: 5px 3px; border-left: 1px solid #45bba4; }
        .tx th.sub { background: #37b79f; font-size: 7.5px; padding: 3px; }
        .tx th:first-child { border-left: 0; }
        .tx td { padding: 4.5px 3px; border-left: 1px solid #e4f2ee; border-top: 1px solid #edf7f4; }
        .tx td:first-child { border-left: 0; }
        .tx tr.tot td { background: #d6f2ec; color: #09543f; font-weight: bold;
                        border-top: 1px solid #0da487; }

        /* bank + signature */
        .btm { margin-top: 11px; }
        .btm > tbody > tr > td { vertical-align: top; padding: 0; }
        .bank { border: 1px solid #0da487; }
        .bank .bh { background: #0da487; color: #fff; font-family: "DejaVu Serif", serif;
                    font-weight: bold; font-size: 8px; letter-spacing: 1px; padding: 4px 8px; }
        .bank td.k { background: #f3faf8; color: #0b7f69; font-weight: bold;
                     padding: 3.5px 8px; border-top: 1px solid #e4f2ee; width: 40%; }
        .bank td.v { padding: 3.5px 8px; border-top: 1px solid #e4f2ee; }
        .sg { border: 1px solid #0da487; text-align: center; padding: 7px 9px; }
        .sg .for { font-family: "DejaVu Serif", serif; font-weight: bold; color: #096d5a; }
        .sg .ln { border-top: 1px solid #8fc9bd; margin-top: 46px; padding-top: 3px;
                  font-size: 8px; color: #6b8a83; }
        .decl { font-size: 8px; line-height: 1.6; color: #4a6b64; }
    </style>
</head>
<body>

{{-- ── header ───────────────────────────────────────────── --}}
<table class="hdr">
    <tr>
        <td width="56%" style="padding-right: 14px;">
            <div class="co-name">{{ $companyName }}</div>
            <div class="co-rule"></div>
            @if($company?->address)<div class="co-line">{{ $company->address }}</div>@endif
            <div class="co-line sm mute">
                @if($company?->phone)Mobile {{ $company->phone }}@endif
                @if($company?->phone && $company?->email) &nbsp;|&nbsp; @endif
                @if($company?->email){{ $company->email }}@endif
            </div>
            @if($company?->gst_number)
                <div class="co-line"><strong style="color:#0b7f69;">GSTIN</strong> {{ $company->gst_number }}</div>
            @endif
        </td>
        <td width="44%">
            <table class="docpanel">
                <tr><td class="ttl" colspan="2">{{ strtoupper($title) }}</td></tr>
                <tr><td class="k">{{ $title }} No.</td><td class="v">{{ $number }}</td></tr>
                <tr><td class="k">Date</td><td class="v">{{ $issuedOn }}</td></tr>
                @if($validTill)<tr><td class="k">Valid Until</td><td class="v">{{ $validTill }}</td></tr>@endif
                @if($placeOfSupply)<tr><td class="k">Place of Supply</td><td class="v">{{ $placeOfSupply }}</td></tr>@endif
                <tr><td class="k">Reverse Charge</td><td class="v">No</td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- ── parties ──────────────────────────────────────────── --}}
<table class="strip">
    <tr>
        <td width="50%">
            <div class="hd">BILLED TO</div>
            <div class="nm">{{ $billing['name'] }}</div>
            @if($billing['gstin'] !== '')<div>GSTIN {{ $billing['gstin'] }}</div>@endif
            @if($billing['mobile'] !== '')<div class="sm mute">Mobile {{ $billing['mobile'] }}</div>@endif
            @if($billing['address'] !== '')<div class="sm mute">{{ $billing['address'] }}</div>@endif
        </td>
        <td width="50%">
            <div class="hd">SHIPPED TO</div>
            <div class="nm">{{ $shipping['name'] }}</div>
            @if($shipping['gstin'] !== '')<div>GSTIN {{ $shipping['gstin'] }}</div>@endif
            @if($shipping['mobile'] !== '')<div class="sm mute">Mobile {{ $shipping['mobile'] }}</div>@endif
            @if($shipping['address'] !== '')<div class="sm mute">{{ $shipping['address'] }}</div>@endif
        </td>
    </tr>
</table>

{{-- ── items ────────────────────────────────────────────── --}}
<table class="it">
    <thead>
        <tr>
            <th width="5%" class="c">Sr</th>
            <th width="34%" style="text-align: left;">Item Description</th>
            <th width="11%" class="c">HSN/SAC</th>
            <th width="9%" class="c">Qty</th>
            <th width="8%" class="c">Unit</th>
            <th width="12%" class="r">Rate</th>
            <th width="7%" class="c">GST%</th>
            <th width="14%" class="r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
            <tr @class(['z' => $index % 2 === 1])>
                <td class="c mute">{{ $index + 1 }}</td>
                <td class="nm">{{ $item['name'] }}</td>
                <td class="c mute">{{ $item['hsn_code'] ?: '-' }}</td>
                <td class="c">{{ $qty($item['quantity']) }}</td>
                <td class="c mute">{{ $item['unit'] }}</td>
                <td class="r">{{ $money($item['unit_price']) }}</td>
                <td class="c mute">{{ $pct($item['gst_percent']) }}</td>
                <td class="r nm">{{ $money($item['line_total']) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ── totals (right) ───────────────────────────────────── --}}
<table style="margin-top: 9px;">
    <tr>
        <td width="58%" style="padding: 0;"></td>
        <td width="42%" style="padding: 0;">
            <table class="sum">
                <tr><td class="k">Taxable Value</td><td class="r">{{ $money($taxSummary['taxable']) }}</td></tr>
                <tr>
                    <td class="k">{{ $intra ? 'CGST + SGST' : 'IGST' }}</td>
                    <td class="r">{{ $money($taxSummary['tax']) }}</td>
                </tr>
                @if($discount > 0)
                    <tr><td class="k">Discount</td><td class="r">- {{ $money($discount) }}</td></tr>
                @endif
                <tr class="g"><td>Grand Total</td><td class="r">{{ $money($totals['grand_total']) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="words"><b>Amount in words:</b> {{ $taxSummary['in_words'] }}</div>

{{-- ── GST summary, full width at the bottom ────────────── --}}
<table class="tx">
    <thead>
        <tr>
            <th rowspan="2" width="14%">Tax Rate</th>
            <th rowspan="2" width="20%">Taxable Value</th>
            @if($intra)
                <th colspan="2" width="24%">CGST</th>
                <th colspan="2" width="24%">SGST</th>
            @else
                <th colspan="2" width="48%">IGST</th>
            @endif
            <th rowspan="2" width="18%">Total Tax</th>
        </tr>
        <tr>
            <th class="sub">Rate</th><th class="sub">Amount</th>
            @if($intra)<th class="sub">Rate</th><th class="sub">Amount</th>@endif
        </tr>
    </thead>
    <tbody>
        @foreach($taxSummary['rows'] as $row)
            @php $half = $pct((float) $row['rate'] / 2); @endphp
            <tr>
                <td class="c">{{ $pct($row['rate']) }}%</td>
                <td class="r">{{ $money($row['taxable']) }}</td>
                @if($intra)
                    <td class="c">{{ $half }}%</td>
                    <td class="r">{{ $money($row['tax'] / 2) }}</td>
                    <td class="c">{{ $half }}%</td>
                    <td class="r">{{ $money($row['tax'] / 2) }}</td>
                @else
                    <td class="c">{{ $pct($row['rate']) }}%</td>
                    <td class="r">{{ $money($row['tax']) }}</td>
                @endif
                <td class="r">{{ $money($row['tax']) }}</td>
            </tr>
        @endforeach
        <tr class="tot">
            <td class="c">Total</td>
            <td class="r">{{ $money($taxSummary['taxable']) }}</td>
            @if($intra)
                <td class="c">-</td><td class="r">{{ $money($taxSummary['tax'] / 2) }}</td>
                <td class="c">-</td><td class="r">{{ $money($taxSummary['tax'] / 2) }}</td>
            @else
                <td class="c">-</td><td class="r">{{ $money($taxSummary['tax']) }}</td>
            @endif
            <td class="r">{{ $money($taxSummary['tax']) }}</td>
        </tr>
    </tbody>
</table>

{{-- ── bank details + declaration + signature ───────────── --}}
<table class="btm">
    <tr>
        @if($bank !== [])
            <td width="44%" style="padding-right: 10px;">
                <table class="bank">
                    <tr><td class="bh" colspan="2">BANK / ACCOUNT DETAILS</td></tr>
                    @foreach($bank as $k => $v)
                        <tr><td class="k">{{ $k }}</td><td class="v">{{ $v }}</td></tr>
                    @endforeach
                </table>
            </td>
        @endif
        <td width="{{ $bank === [] ? '66%' : '26%' }}" style="padding-right: 10px;">
            <div class="hd">DECLARATION</div>
            <div class="decl">
                We declare that this {{ strtolower($title) }} shows the actual price of the
                goods described and that all particulars are true and correct.
                @if($order->salesman?->name)<br><br>Salesman: {{ $order->salesman->name }}@endif
            </div>
        </td>
        <td width="30%" class="sg">
            <div class="for">For {{ $companyName }}</div>
            <div class="ln">Authorised Signatory</div>
        </td>
    </tr>
</table>

</body>
</html>
