<?php

namespace App\Models;

use App\Contracts\Finance\DealerOutstandingContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerProfile extends Model
{
    protected $fillable = ['user_id', 'salesman_id', 'dealer_code', 'firm_name', 'gst_number', 'credit_limit', 'approved_at', 'approved_by'];

    protected function casts(): array
    {
        return ['credit_limit' => 'decimal:2', 'approved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    /**
     * Always computed live from invoices minus recorded payments (see
     * DealerOutstandingService) — the stored column behind this attribute is
     * legacy and is intentionally never read, so every screen that shows
     * `outstanding_balance` on this model shows the same real number.
     */
    protected function outstandingBalance(): Attribute
    {
        return Attribute::get(
            fn (): float => app(DealerOutstandingContract::class)->outstandingBalance($this->user_id),
        );
    }
}
