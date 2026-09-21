<?php

namespace App\Http\Controllers\Admin\Dispatches;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Sales\Invoice;
use Illuminate\Http\Request;

class DispatchController extends AdminModuleController
{
    protected string $moduleKey = 'dispatches';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (empty($data['dispatch_no'])) {
            $data['dispatch_no'] = 'DSP'.now()->format('ymdHis').random_int(100, 999);
        }

        return $data;
    }

    /**
     * The order_id dropdown needs the party name alongside the invoice number,
     * which the generic option builder can't produce (it only plucks one plain
     * column), so this one field is built by hand.
     */
    protected function formOptions(array $module): array
    {
        $options = parent::formOptions($module);

        $options['order_id'] = Invoice::query()
            ->with(['order.customer', 'order.dealer.dealerProfile'])
            ->orderBy('invoice_no')
            ->get()
            ->mapWithKeys(fn (Invoice $invoice) => [
                $invoice->order_id => $this->partyName($invoice).' - '.$invoice->invoice_no,
            ])
            ->all();

        return $options;
    }

    private function partyName(Invoice $invoice): string
    {
        $order = $invoice->order;

        if ($order === null) {
            return 'Unknown';
        }

        $name = $order->order_type === 'dealer'
            ? ($order->dealer?->dealerProfile?->firm_name ?: $order->dealer?->name)
            : $order->customer?->name;

        return $name ?: 'Walk-in Customer';
    }
}
