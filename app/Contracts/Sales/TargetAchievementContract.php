<?php

namespace App\Contracts\Sales;

/**
 * How much a salesman actually sold inside a target's period.
 *
 * Kept behind a contract so the figure has one definition: every screen,
 * report and incentive calculation asks this rather than reading the stored
 * `salesman_targets.achieved_amount` column, which was typed in by hand and
 * went stale the moment the next order landed.
 */
interface TargetAchievementContract
{
    /**
     * Delivered order value for this salesman between the two dates.
     *
     * @param  \DateTimeInterface|string|null  $periodStart
     * @param  \DateTimeInterface|string|null  $periodEnd
     */
    public function achievedAmount(int $salesmanId, $periodStart, $periodEnd): float;
}
