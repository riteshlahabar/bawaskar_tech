<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\Inventory\InventoryBatch;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createForCustomer(User $customer, array $items, ?string $notes = null): Order
    {
        return $this->createOrder('customer', $customer, null, null, $items, $notes);
    }

    public function createForDealer(User $dealer, array $items, ?string $notes = null): Order
    {
        $salesman = $dealer->dealerProfile?->salesman;

        if (! $salesman) {
            throw ValidationException::withMessages(['dealer' => 'Dealer is not assigned to any salesman.']);
        }

        return $this->createOrder('dealer', null, $dealer, $salesman, $items, $notes);
    }

    public function createBySalesman(User $salesman, User $dealer, array $items, ?string $notes = null): Order
    {
        if ((int) $dealer->dealerProfile?->salesman_id !== (int) $salesman->id) {
            throw ValidationException::withMessages(['dealer' => 'Dealer is not assigned to this salesman.']);
        }

        return $this->createOrder('dealer', null, $dealer, $salesman, $items, $notes);
    }

    private function createOrder(string $type, ?User $customer, ?User $dealer, ?User $salesman, array $items, ?string $notes): Order
    {
        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'At least one item is required.']);
        }

        return DB::transaction(function () use ($type, $customer, $dealer, $salesman, $items, $notes): Order {
            $lineItems = $this->buildLineItems($type, $items);

            $order = Order::query()->create([
                'order_no' => $this->nextOrderNumber($type),
                'order_type' => $type,
                'customer_id' => $customer?->id,
                'dealer_id' => $dealer?->id,
                'salesman_id' => $salesman?->id,
                'status' => $type === 'dealer' ? 'salesman_review' : 'admin_review',
                'notes' => $notes,
            ]);

            $subtotal = 0;
            $gstTotal = 0;

            foreach ($lineItems as $lineItem) {
                $orderItemData = $lineItem;
                unset($orderItemData['line_base']);

                $order->items()->create($orderItemData);
                $subtotal += (float) $lineItem['line_base'];
                $gstTotal += (float) $lineItem['gst_amount'];
            }

            $order->forceFill([
                'subtotal' => $subtotal,
                'gst_total' => $gstTotal,
                'grand_total' => $subtotal + $gstTotal,
            ])->save();

            return $order->load('items.product', 'dealer.dealerProfile', 'salesman', 'customer');
        });
    }

    private function buildLineItems(string $type, array $items): array
    {
        $requested = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (float) $item['quantity'];
            $requested[$productId] = ($requested[$productId] ?? 0) + $quantity;
        }

        $lineItems = [];

        foreach ($requested as $productId => $quantity) {
            $product = Product::query()->visibleFor($type)->findOrFail($productId);
            $this->ensureStockAvailable($product, $quantity);

            $unitPrice = (float) ($type === 'dealer' ? $product->dealer_price : $product->customer_price);
            $lineBase = round($quantity * $unitPrice, 2);
            $gstAmount = round($lineBase * ((float) $product->gst_percent / 100), 2);

            $lineItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'gst_percent' => $product->gst_percent,
                'gst_amount' => $gstAmount,
                'line_total' => $lineBase + $gstAmount,
                'line_base' => $lineBase,
            ];
        }

        return $lineItems;
    }

    private function ensureStockAvailable(Product $product, float $requestedQuantity): void
    {
        if (! filter_var(env('ENFORCE_STOCK_ON_ORDER', true), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $available = (float) InventoryBatch::query()
            ->where('product_id', $product->id)
            ->where(function ($query): void {
                $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
            })
            ->selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0) as available_quantity')
            ->value('available_quantity');

        if ($available + 0.0001 < $requestedQuantity) {
            throw ValidationException::withMessages([
                'items' => 'Insufficient stock for '.$product->name.'. Available quantity: '.number_format($available, 3),
            ]);
        }
    }

    private function nextOrderNumber(string $type): string
    {
        $prefix = $type === 'dealer' ? 'DO' : 'CO';

        return $prefix.now()->format('ymdHis').random_int(100, 999);
    }
}