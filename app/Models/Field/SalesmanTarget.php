<?php

namespace App\Models\Field;

use App\Contracts\Sales\TargetAchievementContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanTarget extends Model
{
    protected $fillable = ['salesman_id', 'period_start', 'period_end', 'target_amount', 'commission_percent'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'target_amount' => 'decimal:2', 'commission_percent' => 'decimal:2'];
    }

    /**
     * Always computed live from delivered orders in the period (see
     * TargetAchievementService) — the stored column behind this attribute is
     * legacy and is intentionally never read, so the salesman app, the admin
     * list, the reports and the incentive calculation all show the same real
     * number instead of whatever was last typed in.
     */
    protected function achievedAmount(): Attribute
    {
        return Attribute::get(
            fn (): float => app(TargetAchievementContract::class)->achievedAmount(
                (int) $this->salesman_id,
                $this->period_start,
                $this->period_end,
            ),
        );
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
