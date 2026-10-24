<?php

namespace App\Services\Finance;

use App\Contracts\Finance\DealerOutstandingContract;
use App\Models\Finance\Payment;
use App\Models\Sales\Invoice;

final class DealerOutstandingService implements DealerOutstandingContract
{
    public function outstandingBalance(int $dealerId): float
    {
        $billed = (float) Invoice::query()
            ->whereHas('order', fn ($query) => $query
                ->where('dealer_id', $dealerId)
                ->where('status', '!=', 'cancelled'))
            ->sum('grand_total');

        // Payment::SETTLED_STATUSES is the authority. The list used to live
        // here privately and left out the gateway's own `success`, so a paid
        // online order never reduced what the dealer owed.
        $collected = (float) Payment::query()
            ->where('payer_id', $dealerId)
            ->settled()
            ->sum('amount');

        return round(max($billed - $collected, 0), 2);
    }
}
