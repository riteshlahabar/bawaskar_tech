<?php

namespace App\Contracts\Finance;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DealerLedgerContract
{
    /**
     * Every ledger entry for one dealer, newest first, with a running balance.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function entries(int $dealerId): Collection;

    /**
     * The same entries as one page, so a screen can scroll them.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginate(int $dealerId, int $perPage, int $page): LengthAwarePaginator;
}
