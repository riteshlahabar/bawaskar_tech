@extends('emails.layout')

{{--
    Order acknowledgement. The lines and totals are read straight off the
    order, so a reworded template can never change what the buyer is quoted.
--}}

@section('content')
    @include('emails.partials.paragraphs')

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:18px 0;font-size:13px;">
        <tr>
            <th align="left" style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;">Item</th>
            <th align="right" style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;width:70px;">Qty</th>
            <th align="right" style="padding:8px 10px;background-color:#f7f9fc;border:1px solid #e2e6ed;width:110px;">Amount</th>
        </tr>
        @foreach($order->items as $item)
            <tr>
                <td style="padding:8px 10px;border:1px solid #e2e6ed;">
                    {{ $item->product?->name ?? 'Product' }}@if($item->variant_name) — {{ $item->variant_name }}@endif
                </td>
                <td align="right" style="padding:8px 10px;border:1px solid #e2e6ed;">{{ rtrim(rtrim(number_format((float) ($item->pack_quantity ?? $item->quantity), 3, '.', ''), '0'), '.') }}</td>
                <td align="right" style="padding:8px 10px;border:1px solid #e2e6ed;">Rs {{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" align="right" style="padding:10px;background-color:#eafaf5;border:1px solid #bfe9dc;font-size:14px;"><strong>Order Total</strong></td>
            <td align="right" style="padding:10px;border:1px solid #bfe9dc;font-size:15px;color:#0da487;"><strong>Rs {{ number_format((float) $order->grand_total, 2) }}</strong></td>
        </tr>
    </table>

    <div style="font-size:13px;color:#6b7280;">Order number {{ $order->order_no }} &nbsp;|&nbsp; placed {{ $order->created_at?->format('d M Y, g:i A') }}</div>
@endsection
