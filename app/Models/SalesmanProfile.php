<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanProfile extends Model
{
    protected $fillable = ['user_id', 'employee_code', 'joining_date', 'basic_salary', 'target_amount', 'territory'];

    protected function casts(): array
    {
        return ['joining_date' => 'date', 'basic_salary' => 'decimal:2', 'target_amount' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
