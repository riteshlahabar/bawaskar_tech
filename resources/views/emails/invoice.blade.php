@extends('emails.layout')

{{--
    The covering note for a Sale Invoice. The wording above comes from
    Settings -> Email Templates; the summary and bank blocks below are built
    from the record, so admin can reword the mail but never change a figure.
    The full GST document is the attached PDF.
--}}

@section('content')
    @include('emails.partials.paragraphs')

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:18px 0;">
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;font-size:13px;width:45%;">Invoice Number</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;font-size:13px;"><strong>{{ $number }}</strong></td>
        </tr>
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;font-size:13px;">Invoice Date</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;font-size:13px;">{{ $date?->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;font-size:13px;">Sale Order</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;font-size:13px;">{{ $order->order_no }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;font-size:13px;">Items</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;font-size:13px;">{{ count($items) }}</td>
        </tr>
        <tr>
            <td style="padding:10px;background-color:#eafaf5;border:1px solid #bfe9dc;font-size:14px;"><strong>Total Payable</strong></td>
            <td style="padding:10px;border:1px solid #bfe9dc;font-size:15px;color:#0da487;"><strong>Rs {{ number_format((float) $totals['grand_total'], 2) }}</strong></td>
        </tr>
    </table>

    @if($company?->hasBankDetails())
        <div style="font-size:13px;font-weight:bold;margin-bottom:6px;">Payment Details</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;font-size:13px;">
            @foreach ([
                'Account Name' => $company->account_name,
                'Bank' => $company->bank_name,
                'Branch' => $company->bank_branch,
                'Account Number' => $company->account_number,
                'IFSC' => $company->ifsc_code,
                'UPI ID' => $company->upi_id,
            ] as $label => $value)
                @if(filled($value))
                    <tr>
                        <td style="padding:6px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;width:45%;">{{ $label }}</td>
                        <td style="padding:6px 10px;border:1px solid #e2e6ed;">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @endif
@endsection
