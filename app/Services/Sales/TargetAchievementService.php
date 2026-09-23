<?php

namespace App\Services\Sales;

use App\Contracts\Sales\TargetAchievementContract;
use App\Models\Sales\Order;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Achievement = the value of this salesman's orders that reached `delivered`,
 * counted in the period the order was placed.
 *
 * Delivered, not invoiced: the user's decision — an order only counts once the
 * goods are actually with the dealer. The period is keyed on when the order was
 * placed rather than when it was delivered, so a September sale stays a
 * September sale even if it ships late; it simply does not count until it
 * arrives.
 */
final class TargetAchievementService implements TargetAchievementContract
{
    private const DELIVERED = 'delivered';

    public function achievedAmount(int $salesmanId, $periodStart, $periodEnd): float
    {
        if ($salesmanId <= 0 || $periodStart === null || $periodEnd === null) {
            return 0.0;
        }

        try {
            $start = CarbonImmutable::parse($periodStart)->startOfDay();
            $end = CarbonImmutable::parse($periodEnd)->endOfDay();
        } catch (Throwable) {
            // A malformed period must not take the whole screen down with it.
            return 0.0;
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        return round((float) Order::query()
            ->where('salesman_id', $salesmanId)
            ->where('status', self::DELIVERED)
            ->whereBetween('created_at', [$start, $end])
            ->sum('grand_total'), 2);
    }
}
