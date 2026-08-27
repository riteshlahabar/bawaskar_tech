<?php

namespace App\Services;

use App\Models\Catalog\ProductVariant;
use Illuminate\Validation\ValidationException;

class OrderLineQuantityService
{
    /**
     * Convert user-facing order quantity to retail-pack stock quantity.
     *
     * Customer:
     * quantity 1 = one retail pack.
     *
     * Dealer:
     * quantity 1 = one case when a variant is selected.
     *
     * Storefront internally passes pack_quantity when quantity is
     * already converted to retail stock units. In that situation
     * quantity must not be converted again.
     */
    public function normalize(
        string $orderType,
        array $item,
        ?ProductVariant $variant
    ): array {
        $submittedQuantity = round(
            (float) ($item['quantity'] ?? 0),
            3
        );

        if ($submittedQuantity <= 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'Quantity must be greater than zero.',
            ]);
        }

        $unitsPerCase = $variant
            ? max(
                1.0,
                (float) $variant->units_per_case
            )
            : 1.0;

        /*
         * Internal storefront checkout already sends:
         *
         * quantity      = normalized retail pack quantity
         * pack_quantity = customer packs / dealer cases
         *
         * Never multiply this quantity again.
         */
        if (array_key_exists('pack_quantity', $item)) {
            $displayQuantity = round(
                (float) $item['pack_quantity'],
                3
            );

            if ($displayQuantity <= 0) {
                throw ValidationException::withMessages([
                    'items' =>
                        'Quantity must be greater than zero.',
                ]);
            }

            return [
                'quantity' =>
                    $submittedQuantity,

                'pack_quantity' =>
                    $displayQuantity,

                'units_per_case' =>
                    $unitsPerCase,
            ];
        }

        $displayQuantity =
            $submittedQuantity;

        $stockQuantity =
            $orderType === 'dealer'
            && $variant
                ? round(
                    $displayQuantity
                    * $unitsPerCase,
                    3
                )
                : $displayQuantity;

        return [
            'quantity' =>
                $stockQuantity,

            'pack_quantity' =>
                $displayQuantity,

            'units_per_case' =>
                $unitsPerCase,
        ];
    }
}