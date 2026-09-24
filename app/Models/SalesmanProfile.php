<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanProfile extends Model
{
    public const EMPLOYMENT_STATUSES = [
        'active' => 'Active',
        'probation' => 'Probation',
        'notice_period' => 'Notice Period',
        'resigned' => 'Resigned',
        'exited' => 'Exited',
    ];

    /**
     * `department_id`, `designation_id` and `reporting_to` are deliberately
     * absent: those three were dropped from the Salesmen module on 2026-09-24
     * because the Phase 1 spec places the whole HRMS list under "HRMS Modules
     * (Admin Panel)" and never describes a reporting hierarchy. The columns
     * are left on the table, unread, rather than migrated away.
     */
    protected $fillable = [
        'user_id', 'employee_code',
        'joining_date', 'employment_status', 'confirmation_date', 'exit_date',
        'basic_salary', 'target_amount', 'territory',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'exit_date' => 'date',
            'basic_salary' => 'decimal:2',
            'target_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
