<?php

namespace App\Contracts\Finance;

/**
 * SRP: turning a dealer's invoices and recorded payments into the one number
 * "how much do they currently owe" — so the home dashboard and the
 * dedicated Outstanding screen can never drift apart by reading two
 * different sources.
 */
interface DealerOutstandingContract
{
    /**
     * Total invoiced amount for the dealer's non-cancelled orders, minus
     * everything recorded against them as paid/collected/verified. Never
     * negative — an advance payment shows as headroom, not a credit balance.
     */
    public function outstandingBalance(int $dealerId): float;
}
