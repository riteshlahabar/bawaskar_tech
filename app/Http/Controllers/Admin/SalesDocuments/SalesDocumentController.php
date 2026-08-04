<?php

namespace App\Http\Controllers\Admin\SalesDocuments;

use App\Http\Controllers\Controller;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use App\Support\Admin\SimplePdfExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SalesDocumentController extends Controller
{
    public function print(string $document, int|string $id): View
    {
        $template = max(1, min(3, (int) request()->integer('template', 1)));

        return view('admin.sales-documents.invoice-'.$template, $this->documentData($document, $id));
    }

    public function pdf(string $document, int|string $id): Response
    {
        $data = $this->documentData($document, $id);
        $rows = [];

        foreach ($data['items'] as $item) {
            $rows[] = [
                $item['name'],
                number_format((float) $item['quantity'], 3),
                number_format((float) $item['unit_price'], 2),
                number_format((float) $item['gst_amount'], 2),
                number_format((float) $item['line_total'], 2),
            ];
        }

        $rows[] = ['', '', '', 'Subtotal', number_format((float) $data['totals']['subtotal'], 2)];
        $rows[] = ['', '', '', 'GST', number_format((float) $data['totals']['gst_total'], 2)];
        $rows[] = ['', '', '', 'Discount', number_format((float) $data['totals']['discount_total'], 2)];
        $rows[] = ['', '', '', 'Grand Total', number_format((float) $data['totals']['grand_total'], 2)];

        $pdf = SimplePdfExporter::table($data['title'].' - '.$data['number'], ['Product', 'Qty', 'Rate', 'GST', 'Total'], $rows);
        $filename = Str::slug($data['title'].'-'.$data['number']).'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function documentData(string $document, int|string $id): array
    {
        [$title, $record, $order, $number, $date, $validUntil, $status] = match ($document) {
            'order' => $this->orderData($id),
            'proforma' => $this->proformaData($id),
            'invoice' => $this->invoiceData($id),
            default => abort(404),
        };

        abort_unless($order, 404, 'Linked Sale Order not found.');

        $party = $order->order_type === 'dealer'
            ? ($order->dealer?->dealerProfile?->firm_name ?: $order->dealer?->name)
            : $order->customer?->name;

        $contact = $order->order_type === 'dealer' ? $order->dealer : $order->customer;
        $items = $order->items->map(fn ($item): array => [
            'name' => $item->product?->name ?? 'Product',
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'gst_percent' => $item->gst_percent,
            'gst_amount' => $item->gst_amount,
            'line_total' => $item->line_total,
        ])->all();

        return [
            'title' => $title,
            'record' => $record,
            'order' => $order,
            'number' => $number,
            'date' => $date,
            'validUntil' => $validUntil,
            'status' => $status,
            'party' => $party ?: 'Walk-in Customer',
            'contact' => $contact,
            'items' => $items,
            'totals' => [
                'subtotal' => data_get($record, 'subtotal', $order->subtotal),
                'gst_total' => data_get($record, 'gst_total', $order->gst_total),
                'discount_total' => data_get($record, 'discount_total', $order->discount_total),
                'grand_total' => data_get($record, 'grand_total', $order->grand_total),
            ],
        ];
    }

    private function orderData(int|string $id): array
    {
        $order = Order::query()->with(['items.product', 'customer', 'dealer.dealerProfile', 'salesman'])->findOrFail($id);

        return ['Sale Order', $order, $order, $order->order_no, $order->created_at, null, $order->status];
    }

    private function proformaData(int|string $id): array
    {
        $proforma = ProformaInvoice::query()->with(['order.items.product', 'order.customer', 'order.dealer.dealerProfile', 'order.salesman'])->findOrFail($id);

        return ['Proforma Invoice', $proforma, $proforma->order, $proforma->proforma_no, $proforma->proforma_date, $proforma->valid_until, $proforma->status];
    }

    private function invoiceData(int|string $id): array
    {
        $invoice = Invoice::query()->with(['order.items.product', 'order.customer', 'order.dealer.dealerProfile', 'order.salesman'])->findOrFail($id);

        return ['Sale Invoice', $invoice, $invoice->order, $invoice->invoice_no, $invoice->invoice_date, null, 'issued'];
    }
}