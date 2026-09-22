<?php

namespace App\Models\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One break/resume pair inside a day's attendance log.
 *
 * A salesman can break more than once a day, so these are rows rather than a
 * pair of columns on the log. A row with a null `resume_at` is the break that
 * is still running — that is what the app restores its button state from.
 */
class AttendanceBreak extends Model
{
    protected $fillable = ['attendance_log_id', 'break_at', 'resume_at', 'break_latitude', 'break_longitude', 'resume_latitude', 'resume_longitude', 'break_minutes'];

    protected function casts(): array
    {
        return ['break_at' => 'datetime', 'resume_at' => 'datetime'];
    }

    public function attendanceLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class);
    }
}
