<?php

namespace App\Observers\Notifications;

use App\Models\Sales\Order;
use App\Models\User;

/**
 * Customer/dealer: order placed and every later status change.
 * Salesman: a dealer's order is waiting for their review.
 */
final class OrderNotificationObserver extends NotificationObserver
{
    public function created(Order $order): void
    {
        $this->notify($this->ownerId($order), 'order', 'placed', $this->replace($order), $this->data($order));

        // The order already carries its assigned salesman by the time it is
        // created (createForDealer/createBySalesman both fill it), so this
        // is the only signal available — a salesman notified about their own
        // order is a minor, harmless redundancy, not worth a schema change.
        if ($order->status === 'salesman_review' && $order->salesman_id && $order->dealer_id) {
            $this->attempt(function () use ($order): void {
                $dealer = User::query()->with('dealerProfile')->find($order->dealer_id);
                $name = $dealer?->dealerProfile?->firm_name ?: ($dealer?->name ?: 'A dealer');

                $this->notify((int) $order->salesman_id, 'order_review', 'salesman_review', $this->replace($order) + ['dealer' => $name], $this->data($order));
            });
        }
    }

    public function updated(Order $order): void
    {
        if ($this->statusChanged($order)) {
            $this->notify($this->ownerId($order), 'order', (string) $order->status, $this->replace($order), $this->data($order));
        }

        // The salesman's stock answer does not move the order, so it would
        // never reach the dealer through the status change above.
        if ($this->statusChanged($order, 'availability')) {
            $this->notify(
                $this->ownerId($order),
                'order_availability',
                (string) $order->availability,
                $this->replace($order) + ['available_on' => $this->dateTime($order->available_on)],
                $this->data($order),
            );
        }
    }

    private function ownerId(Order $order): ?int
    {
        $id = $order->dealer_id ?: $order->customer_id;

        return $id ? (int) $id : null;
    }

    /**
     * @return array<string, scalar|null>
     */
    private function replace(Order $order): array
    {
        return ['order_no' => $order->order_no, 'amount' => $this->money($order->grand_total)];
    }

    /**
     * @return array<string, scalar|null>
     */
    private function data(Order $order): array
    {
        return ['order_id' => $order->getKey(), 'order_no' => $order->order_no];
    }
}
