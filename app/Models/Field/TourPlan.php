<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class TourPlan extends Model
{
    protected $fillable = ['salesman_id', 'plan_date', 'route_name', 'dealer_ids', 'status'];

    protected function casts(): array
    {
        return ['plan_date' => 'date', 'dealer_ids' => 'array'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    /**
     * The dealer ids on this route, cleaned of the blanks an unticked
     * checkbox list can submit.
     *
     * @return array<int, int>
     */
    public function dealerIdList(): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) ($this->dealer_ids ?? [])),
        )));
    }

    /**
     * The dealer accounts on this route, in the order the admin ticked them.
     *
     * @return Collection<int, User>
     */
    public function dealers(): Collection
    {
        $ids = $this->dealerIdList();

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'mobile', 'city_village', 'district_name', 'state_name'])
            ->sortBy(fn (User $dealer): int => array_search($dealer->id, $ids, true) ?: 0)
            ->values();
    }

    /**
     * How many stops the route has — the admin list's "Dealers" column.
     */
    protected function dealerStops(): Attribute
    {
        return Attribute::get(fn (): int => count($this->dealerIdList()));
    }
}
