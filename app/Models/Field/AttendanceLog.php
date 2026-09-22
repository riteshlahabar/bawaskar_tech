<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceLog extends Model
{
    protected $fillable = ['salesman_id', 'attendance_date', 'check_in_at', 'check_out_at', 'check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude', 'working_minutes', 'break_minutes', 'status'];

    protected function casts(): array
    {
        return ['attendance_date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class)->orderBy('break_at');
    }

    /**
     * The break that is still running, if the salesman has not resumed yet.
     */
    public function openBreak(): ?AttendanceBreak
    {
        return $this->breaks()->whereNull('resume_at')->latest('break_at')->first();
    }
}
