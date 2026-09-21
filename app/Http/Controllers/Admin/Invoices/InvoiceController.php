<?php

namespace App\Http\Controllers\Admin\Invoices;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends AdminModuleController
{
    protected string $moduleKey = 'invoices';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (empty($data['invoice_no'])) {
            $data['invoice_no'] = 'INV'.now()->format('ymdHis').random_int(100, 999);
        }

        if (empty($data['invoice_date'])) {
            $data['invoice_date'] = now()->toDateString();
        }

        if (! empty($data['order_id']) && (! isset($data['grand_total']) || $data['grand_total'] === '')) {
            $data['grand_total'] = Order::query()->whereKey($data['order_id'])->value('grand_total') ?? 0;
        }

        return $data;
    }

    public function sendToDispatch(int|string $id): RedirectResponse
    {
        $invoice = Invoice::query()->with('order')->findOrFail($id);
        $order = $invoice->order;

        abort_unless($order, 422, 'Sale Invoice is not linked to a Sale Order.');

        $dispatch = Dispatch::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'dispatch_no' => 'DSP'.now()->format('ymdHis').str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'status' => 'packing',
            ]
        );

        return redirect()->route('admin.dispatches.edit', $dispatch->getKey())->with('success', 'Sale Invoice sent to Dispatch.');
    }
}
