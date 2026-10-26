@extends('emails.layout')

{{--
    The covering note for a Sale Invoice. The full GST document is the attached
    PDF - this body only has to tell the reader what arrived and what is owed,
    so it stays short and prints the same figures as the PDF totals block.
--}}

@section('content')
    <p style="margin:0 0 14px;">Dear {{ $party }},</p>

    <p style="margin:0 0 18px;">
        Please find attached your Sale Invoice <strong>{{ $number }}</strong>
        dated {{ $date?->format('d M Y') }}.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin-bottom:18px;">
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
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin-bottom:18px;font-size:13px;">
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

    <p style="margin:0;">Thank you for your business.</p>
@endsection
