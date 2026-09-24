<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanAsset extends Model
{
    /**
     * The three things a salesman may report about an asset they hold.
     *
     * `return_request` is not one of the statuses: it only stamps
     * `return_requested_at`, because marking an asset physically returned
     * stays the admin's word.
     */
    public const ISSUE_LOST = 'lost';

    public const ISSUE_DAMAGED = 'damaged';

    public const ISSUE_RETURN_REQUEST = 'return_request';

    public const REPORTABLE_ISSUES = [self::ISSUE_LOST, self::ISSUE_DAMAGED, self::ISSUE_RETURN_REQUEST];

    protected $fillable = ['salesman_id', 'asset_type', 'asset_name', 'serial_no', 'issued_on', 'returned_on', 'return_requested_at', 'condition', 'salesman_remarks', 'status'];

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'returned_on' => 'date', 'return_requested_at' => 'datetime'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
