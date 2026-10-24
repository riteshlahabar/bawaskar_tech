<?php

namespace App\Models\Finance;

use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * The only authority on what a payment status means.
     *
     * Four writers set this column — a salesman collection writes `collected`,
     * the gateway writes `success` or `failed`, an attempt starts `pending` —
     * while admin can also set `paid`, `verified` or `refunded` by hand. Before
     * these constants existed each reader invented its own list, and two of
     * them were wrong: the dealer payment totals summed `completed`, which
     * nothing writes, and the outstanding calculation omitted `success`, so a
     * paid online order never reduced what the dealer owed. Add a new status
     * here and to the right list below — never inline a status string again.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_COLLECTED = 'collected';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_PAID = 'paid';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    /** Statuses where the money is actually in, so they reduce what is owed. */
    public const SETTLED_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_COLLECTED,
        self::STATUS_VERIFIED,
        self::STATUS_SUCCESS,
    ];

    /** Statuses still waiting on the money. */
    public const AWAITING_STATUSES = [self::STATUS_PENDING];

    /** Label map for the admin status dropdown and status badges. */
    public const STATUS_OPTIONS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_COLLECTED => 'Collected',
        self::STATUS_SUCCESS => 'Paid Online',
        self::STATUS_PAID => 'Paid',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_REFUNDED => 'Refunded',
    ];

    protected $fillable = ['payment_no', 'order_id', 'payer_id', 'collected_by', 'payment_mode', 'status', 'amount', 'transaction_ref', 'paid_at', 'gateway', 'gateway_payload', 'failure_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'gateway_payload' => 'array'];
    }

    /** @param  Builder<self>  $query */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->whereIn('status', self::SETTLED_STATUSES);
    }

    /** @param  Builder<self>  $query */
    public function scopeAwaiting(Builder $query): Builder
    {
        return $query->whereIn('status', self::AWAITING_STATUSES);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
