<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Sales\Order;
use App\Models\User;

interface StockReservationContract
{
    public function reserve(
        Order $order,
        array $lineItems,
        ?User $actor
    ): void;

    /**
     * Frees whatever this order still has reserved (a cancelled order never
     * ships). Safe to call more than once on the same order — it only
     * releases the amount not already released.
     */
    public function release(Order $order, ?int $actorId = null): void;
}
