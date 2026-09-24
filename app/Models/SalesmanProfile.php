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
     * Five columns are deliberately absent, all dropped on 2026-09-24 with
     * their columns left on the table, unread, rather than migrated away:
     * `department_id`, `designation_id` and `reporting_to`, because the Phase 1
     * spec places the whole HRMS list under "HRMS Modules (Admin Panel)" and
     * never describes a reporting hierarchy; `territory`, because the LGD
     * location picker already records where a salesman works; and
     * `confirmation_date`, which nothing ever read or acted on.
     */
    protected $fillable = [
        'user_id', 'employee_code',
        'joining_date', 'employment_status', 'exit_date',
        'basic_salary', 'target_amount',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
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
