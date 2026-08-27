<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\StockMovement;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createForCustomer(User $customer, array $items, ?string $notes = null, array $checkoutData = []): Order
    {
        return $this->createOrder('customer', $customer, null, null, $items, $notes, $checkoutData);
    }

    public function createForDealer(
    User $dealer,
    array $items,
    ?string $notes = null,
    array $checkoutData = []
): Order {
    $salesman =
        $dealer->dealerProfile
            ?->salesman;

    if (! $salesman) {
        throw ValidationException::withMessages([
            'dealer' =>
                'Dealer is not assigned to any salesman.',
        ]);
    }

    if (
        $salesman->role !==
            User::ROLE_SALESMAN ||
        $salesman->status !== 'active'
    ) {
        throw ValidationException::withMessages([
            'dealer' =>
                'Assigned salesman is not active. Please contact admin.',
        ]);
    }

    return $this->createOrder(
        'dealer',
        null,
        $dealer,
        $salesman,
        $items,
        $notes,
        $checkoutData
    );
}

    public function createBySalesman(User $salesman, User $dealer, array $items, ?string $notes = null, array $checkoutData = []): Order
    {
        if ((int) $dealer->dealerProfile?->salesman_id !== (int) $salesman->id) {
            throw ValidationException::withMessages(['dealer' => 'Dealer is not assigned to this salesman.']);
        }

        return $this->createOrder('dealer', null, $dealer, $salesman, $items, $notes, $checkoutData);
    }

    private function createOrder(
        string $type,
        ?User $customer,
        ?User $dealer,
        ?User $salesman,
        array $items,
        ?string $notes,
        array $checkoutData = []
    ): Order {
        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'At least one item is required.']);
        }

        return DB::transaction(function () use ($type, $customer, $dealer, $salesman, $items, $notes, $checkoutData): Order {
            $lineItems = $this->buildLineItems($type, $items);
            $actor = $salesman ?: $dealer ?: $customer;

            $order = Order::query()->create([
                'order_no' => $this->nextOrderNumber($type),
                'order_type' => $type,
                'customer_id' => $customer?->id,
                'dealer_id' => $dealer?->id,
                'salesman_id' => $salesman?->id,
                'status' => $type === 'dealer' ? 'salesman_review' : 'admin_review',
                'notes' => $notes,
                ...$this->mapCheckoutFields($checkoutData),
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

            $this->reserveStock($order, $lineItems, $actor);

            $order->forceFill([
                'subtotal' => $subtotal,
                'gst_total' => $gstTotal,
                'grand_total' => $subtotal + $gstTotal,
            ])->save();

            return $order->load('items.product', 'items.variant', 'dealer.dealerProfile', 'salesman', 'customer');
        });
    }

    private function buildLineItems(string $type, array $items): array
    {
        $requested = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $variantId = (int) ($item['variant_id'] ?? 0) ?: null;
            $quantity = (float) $item['quantity'];
            $key = $productId.':'.($variantId ?: 0);
            if (! isset($requested[$key])) {
                $requested[$key] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => 0.0,
                    'pack_quantity' => 0.0,
                    'units_per_case' => max(1, (float) ($item['units_per_case'] ?? 1)),
                ];
            }
            $requested[$key]['quantity'] += $quantity;
            $requested[$key]['pack_quantity'] += (float) ($item['pack_quantity'] ?? $quantity);
        }

        $lineItems = [];

        foreach ($requested as $requestLine) {
            $product = Product::query()->visibleFor($type)->findOrFail($requestLine['product_id']);
            $variant = $requestLine['variant_id']
                ? ProductVariant::query()->where('product_id', $product->id)->where('is_active', true)->findOrFail($requestLine['variant_id'])
                : null;
            $quantity = (float) $requestLine['quantity'];
            $this->ensureStockAvailable($product, $quantity, $variant);

            $unitPrice = $variant
                ? $variant->priceFor($type)
                : (float) ($type === 'dealer' ? $product->dealer_price : $product->customer_price);
            $priceTotal = round($quantity * $unitPrice, 2);
            $gstPercent = (float) $product->gst_percent;
            if ($variant) {
                $lineTotal = $priceTotal;
                $lineBase = $gstPercent > 0 ? round($lineTotal / (1 + ($gstPercent / 100)), 2) : $lineTotal;
                $gstAmount = round($lineTotal - $lineBase, 2);
            } else {
                $lineBase = $priceTotal;
                $gstAmount = round($lineBase * ($gstPercent / 100), 2);
                $lineTotal = round($lineBase + $gstAmount, 2);
            }

            $lineItems[] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'variant_name' => $variant?->display_name,
                'quantity' => $quantity,
                'pack_quantity' => (float) $requestLine['pack_quantity'],
                'units_per_case' => (float) $requestLine['units_per_case'],
                'unit_price' => $unitPrice,
                'gst_percent' => $product->gst_percent,
                'gst_amount' => $gstAmount,
                'line_total' => $lineTotal,
                'line_base' => $lineBase,
            ];
        }

        return $lineItems;
    }

    private function ensureStockAvailable(Product $product, float $requestedQuantity, ?ProductVariant $variant = null): void
    {
        if (! filter_var(env('ENFORCE_STOCK_ON_ORDER', true), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $available = (float) InventoryBatch::query()
            ->where('product_id', $product->id)
            ->when($variant, fn ($query) => $query->where('product_variant_id', $variant->id), fn ($query) => $query->whereNull('product_variant_id'))
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

    private function mapCheckoutFields(array $checkoutData): array
    {
        return [
            'contact_name' => $checkoutData['contact_name'] ?? null,
            'contact_mobile' => $checkoutData['contact_mobile'] ?? null,
            'address_type' => $checkoutData['address_type'] ?? null,
            'address_line1' => $checkoutData['address_line1'] ?? null,
            'address_line2' => $checkoutData['address_line2'] ?? null,
            'city' => $checkoutData['city'] ?? null,
            'state' => $checkoutData['state'] ?? null,
            'pincode' => $checkoutData['pincode'] ?? null,
            'payment_method' => $checkoutData['payment_method'] ?? null,
            'payment_status' => $checkoutData['payment_status'] ?? 'pending',
        ];
    }

    private function reserveStock(Order $order, array $lineItems, ?User $actor): void
    {
        if (! filter_var(env('ENFORCE_STOCK_ON_ORDER', true), FILTER_VALIDATE_BOOL)) {
            return;
        }

        foreach ($lineItems as $lineItem) {
            $remaining = (float) $lineItem['quantity'];

            $batches = InventoryBatch::query()
                ->where('product_id', $lineItem['product_id'])
                ->when(
                    $lineItem['product_variant_id'] ?? null,
                    fn ($query, $variantId) => $query->where('product_variant_id', $variantId),
                    fn ($query) => $query->whereNull('product_variant_id')
                )
                ->where(function ($query): void {
                    $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
                })
                ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0.0001) {
                    break;
                }

                $available = max(0, (float) $batch->quantity - (float) $batch->reserved_quantity);
                if ($available <= 0) {
                    continue;
                }

                $reservedQuantity = min($remaining, $available);

                $batch->forceFill([
                    'reserved_quantity' => round((float) $batch->reserved_quantity + $reservedQuantity, 3),
                ])->save();

                StockMovement::query()->create([
                    'inventory_batch_id' => $batch->id,
                    'product_variant_id' => $lineItem['product_variant_id'] ?? null,
                    'movement_type' => 'reserved',
                    'quantity' => $reservedQuantity,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'created_by' => $actor?->id,
                ]);

                $remaining = round($remaining - $reservedQuantity, 3);
            }

            if ($remaining > 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'Unable to reserve stock for one or more products. Please review current stock.',
                ]);
            }
        }
    }
}
