<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends AdminModuleController
{
    protected string $moduleKey = 'orders';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);
        $type = (string) ($data['order_type'] ?? 'customer');

        if (empty($data['order_no'])) {
            $data['order_no'] = $this->nextOrderNumber($type);
        }

        if ($type === 'customer') {
            $data['dealer_id'] = null;
            $data['salesman_id'] = null;
        }

        if ($type === 'dealer') {
            $data['customer_id'] = null;

            if (empty($data['salesman_id']) && ! empty($data['dealer_id'])) {
                $dealer = User::query()->with('dealerProfile')->find($data['dealer_id']);
                $data['salesman_id'] = $dealer?->dealerProfile?->salesman_id;
            }
        }

        $subtotal = (float) ($data['subtotal'] ?? 0);
        $gstTotal = (float) ($data['gst_total'] ?? 0);
        $discount = (float) ($data['discount_total'] ?? 0);

        if (! isset($data['grand_total']) || $data['grand_total'] === '') {
            $data['grand_total'] = max(0, $subtotal + $gstTotal - $discount);
        }

        if (($data['status'] ?? null) === 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        return $data;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        return parent::persist($data, $record);
    }

    public function convertToProforma(int|string $id): RedirectResponse
    {
        $order = Order::query()->findOrFail($id);

        $proforma = ProformaInvoice::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'proforma_no' => 'PI'.now()->format('ymdHis').str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'proforma_date' => now()->toDateString(),
                'valid_until' => now()->addDays((int) config('admin.sales.proforma_valid_days', 15))->toDateString(),
                'subtotal' => $order->subtotal,
                'gst_total' => $order->gst_total,
                'discount_total' => $order->discount_total,
                'grand_total' => $order->grand_total,
                'status' => 'draft',
                'notes' => $order->notes,
            ]
        );

        return redirect()->route('admin.proforma-invoices.edit', $proforma->getKey())->with('success', 'Sale Order converted to Proforma Invoice.');
    }

    public function changeStatus(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['salesman_review','admin_review','approved','packing','dispatched','delivered','cancelled'])]]);
        $order = Order::with('items')->findOrFail($id);
        DB::transaction(function () use ($order, $data): void {
            $updates = ['status' => $data['status']];
            if ($data['status'] === 'approved') { $updates += ['approved_by' => auth()->id(), 'approved_at' => now()]; }
            $order->update($updates);
            if ($data['status'] === 'approved') {
                Invoice::firstOrCreate(['order_id' => $order->id], [
                    'invoice_no' => 'INV'.now()->format('ymdHis').str_pad((string)$order->id, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => now()->toDateString(), 'grand_total' => $order->grand_total,
                ]);
            }
        });
        return back()->with('success', 'Order status updated.');
    }

    private function nextOrderNumber(string $type): string
    {
        return ($type === 'dealer' ? 'DO' : 'CO').now()->format('ymdHis').random_int(100, 999);
    }
}