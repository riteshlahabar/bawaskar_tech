<?php

namespace App\Services\Finance;

use App\Contracts\Finance\DealerLedgerContract;
use App\Models\Finance\Payment;
use App\Models\Sales\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * The dealer account ledger, derived rather than stored.
 *
 * The `dealer_statements` table this used to read was never written to by any
 * code path, so both the app's Ledger screen and the dealer Statements screen
 * were always empty. Rather than add a writer and carry a second copy of the
 * money that can drift, the ledger is computed from the same two facts that
 * DealerOutstandingService already uses — invoices raised against the dealer's
 * own non-cancelled orders (debit) and settled payments (credit) — so the
 * closing balance here always equals `DealerProfile->outstanding_balance`.
 *
 * Rows are shaped exactly like the old DealerStatement model's columns, so the
 * three apps read the same keys they always did.
 */
final class DealerLedgerService implements DealerLedgerContract
{
    public function entries(int $dealerId): Collection
    {
        $events = $this->invoiceEvents($dealerId)
            ->concat($this->paymentEvents($dealerId))
            // Chronological so the running balance builds in the right order;
            // id breaks ties when an invoice and a payment share a timestamp.
            ->sortBy([['at', 'asc'], ['reference_id', 'asc']])
            ->values();

        $balance = 0.0;

        return $events
            ->map(static function (array $event) use (&$balance, $dealerId): array {
                $balance = round($balance + $event['debit'] - $event['credit'], 2);

                return [
                    'dealer_id' => $dealerId,
                    'entry_type' => $event['entry_type'],
                    'debit' => number_format($event['debit'], 2, '.', ''),
                    'credit' => number_format($event['credit'], 2, '.', ''),
                    'balance' => number_format($balance, 2, '.', ''),
                    'reference_type' => $event['reference_type'],
                    'reference_id' => $event['reference_id'],
                    'remarks' => $event['remarks'],
                    'created_at' => $event['at']?->toJSON(),
                ];
            })
            // Newest first, matching the `latest()` ordering both endpoints had.
            ->reverse()
            ->values();
    }

    public function paginate(int $dealerId, int $perPage, int $page): LengthAwarePaginator
    {
        $all = $this->entries($dealerId);

        return new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
        );
    }

    /** @return Collection<int, array<string, mixed>> */
    private function invoiceEvents(int $dealerId): Collection
    {
        return Invoice::query()
            ->whereHas('order', fn ($query) => $query
                ->where('dealer_id', $dealerId)
                ->where('status', '!=', 'cancelled'))
            ->with('order:id,order_no')
            ->get()
            ->map(static fn (Invoice $invoice): array => [
                'entry_type' => 'invoice',
                'debit' => (float) $invoice->grand_total,
                'credit' => 0.0,
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'remarks' => 'Invoice '.$invoice->invoice_no
                    .($invoice->order?->order_no ? ' for order '.$invoice->order->order_no : ''),
                'at' => $invoice->created_at ?? $invoice->invoice_date,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function paymentEvents(int $dealerId): Collection
    {
        return Payment::query()
            ->where('payer_id', $dealerId)
            ->settled()
            ->with('order:id,order_no')
            ->get()
            ->map(static fn (Payment $payment): array => [
                'entry_type' => 'payment',
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'remarks' => 'Payment '.$payment->payment_no.' ('.$payment->payment_mode.')'
                    .($payment->order?->order_no ? ' against order '.$payment->order->order_no : ''),
                'at' => $payment->paid_at ?? $payment->created_at,
            ]);
    }
}
