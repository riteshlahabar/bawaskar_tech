<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends Model
{
    protected $fillable = ['salesman_id', 'salary_year', 'salary_month', 'basic_salary', 'allowances', 'bonus', 'incentives', 'commission', 'deductions', 'net_salary', 'status'];

    protected function casts(): array
    {
        return ['basic_salary' => 'decimal:2', 'allowances' => 'decimal:2', 'bonus' => 'decimal:2', 'incentives' => 'decimal:2', 'commission' => 'decimal:2', 'deductions' => 'decimal:2', 'net_salary' => 'decimal:2'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
