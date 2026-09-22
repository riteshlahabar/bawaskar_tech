<?php

namespace App\Services\Finance;

use App\Contracts\Finance\DealerOutstandingContract;
use App\Models\Finance\Payment;
use App\Models\Sales\Invoice;

final class DealerOutstandingService implements DealerOutstandingContract
{
    /** @var list<string> payment statuses that actually reduce what's owed */
    private const COLLECTED_STATUSES = ['paid', 'collected', 'verified'];

    public function outstandingBalance(int $dealerId): float
    {
        $billed = (float) Invoice::query()
            ->whereHas('order', fn ($query) => $query
                ->where('dealer_id', $dealerId)
                ->where('status', '!=', 'cancelled'))
            ->sum('grand_total');

        $collected = (float) Payment::query()
            ->where('payer_id', $dealerId)
            ->whereIn('status', self::COLLECTED_STATUSES)
            ->sum('amount');

        return round(max($billed - $collected, 0), 2);
    }
}
