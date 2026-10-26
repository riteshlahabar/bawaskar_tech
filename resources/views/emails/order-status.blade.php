@extends('emails.layout')

{{-- Wording plus a short reference block, so the reader can tell which order this is about. --}}

@section('content')
    @include('emails.partials.paragraphs')

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin-top:18px;font-size:13px;">
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;width:45%;">Order Number</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;"><strong>{{ $order->order_no }}</strong></td>
        </tr>
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;">Order Date</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;">{{ $order->created_at?->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;">Order Total</td>
            <td style="padding:8px 10px;border:1px solid #e2e6ed;">Rs {{ number_format((float) $order->grand_total, 2) }}</td>
        </tr>
    </table>
@endsection
