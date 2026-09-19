<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\StockReservationContract;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\StockMovement;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class EloquentStockReservationService implements StockReservationContract
{
    public function reserve(
        Order $order,
        array $lineItems,
        ?User $actor
    ): void {
        if (! config('orders.enforce_stock', true)) {
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
                    $query->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                })
                ->orderByRaw(
                    'CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END'
                )
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0.0001) {
                    break;
                }

                $available = max(
                    0,
                    (float) $batch->quantity
                        - (float) $batch->reserved_quantity
                );

                if ($available <= 0) {
                    continue;
                }

                $reservedQuantity = min($remaining, $available);

                $batch->forceFill([
                    'reserved_quantity' => round(
                        (float) $batch->reserved_quantity + $reservedQuantity,
                        3
                    ),
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

    public function release(Order $order, ?int $actorId = null): void
    {
        if (! config('orders.enforce_stock', true)) {
            return;
        }

        $reserved = StockMovement::query()
            ->selectRaw('inventory_batch_id, product_variant_id, SUM(quantity) as qty')
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('movement_type', 'reserved')
            ->groupBy('inventory_batch_id', 'product_variant_id')
            ->get();

        $alreadyReleased = StockMovement::query()
            ->selectRaw('inventory_batch_id, SUM(quantity) as qty')
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('movement_type', 'released')
            ->groupBy('inventory_batch_id')
            ->pluck('qty', 'inventory_batch_id');

        foreach ($reserved as $row) {
            $toRelease = round((float) $row->qty - (float) ($alreadyReleased[$row->inventory_batch_id] ?? 0), 3);

            if ($toRelease <= 0.0001) {
                continue;
            }

            $batch = InventoryBatch::query()->lockForUpdate()->find($row->inventory_batch_id);

            if ($batch === null) {
                continue;
            }

            $batch->forceFill([
                'reserved_quantity' => max(0, round((float) $batch->reserved_quantity - $toRelease, 3)),
            ])->save();

            StockMovement::query()->create([
                'inventory_batch_id' => $batch->id,
                'product_variant_id' => $row->product_variant_id,
                'movement_type' => 'released',
                'quantity' => $toRelease,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'created_by' => $actorId,
            ]);
        }
    }
}
