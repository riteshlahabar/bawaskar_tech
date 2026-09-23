<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Field\AttendanceLog;
use App\Models\Hr\Holiday;
use App\Models\Hr\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Calendar-shaped HR data: the holiday list, the salesman's current shift and
 * their monthly attendance sheet.
 */
class SalesmanCalendarController extends ApiController
{
    public function holidays(Request $request): JsonResponse
    {
        if (($user = $this->requireUser($request, User::ROLE_SALESMAN)) instanceof JsonResponse) {
            return $user;
        }

        $year = (int) $request->integer('year', (int) now()->year);

        return $this->success([
            'year' => $year,
            'holidays' => Holiday::query()
                ->forYear($year)
                ->orderBy('holiday_date')
                ->get(),
        ]);
    }

    public function shift(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $assignment = ShiftAssignment::query()
            ->where('salesman_id', $user->id)
            ->current()
            ->with('shift')
            ->latest('effective_from')
            ->first();

        return $this->success([
            'assignment' => $assignment,
            'shift' => $assignment?->shift,
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        // A `from`+`to` pair (both `YYYY-MM-DD`) wins over `month`; either way
        // the window is bounded, so an unparseable value falls back to this
        // month rather than returning the whole history.
        [$from, $to] = $this->attendanceRange($request);

        $logs = AttendanceLog::query()
            ->where('salesman_id', $user->id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('attendance_date')
            ->get();

        return $this->success([
            'month' => $from->format('Y-m'),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'logs' => $logs,
            'summary' => [
                'present' => $logs->where('status', 'present')->count(),
                'half_day' => $logs->where('status', 'half_day')->count(),
                'absent' => $logs->where('status', 'absent')->count(),
                'working_minutes' => (int) $logs->sum('working_minutes'),
            ],
        ]);
    }

    /**
     * The window the attendance sheet should cover.
     *
     * `from`+`to` (both `YYYY-MM-DD`) gives an explicit range; otherwise
     * `month` (`YYYY-MM`) gives that whole month, defaulting to this one. A
     * reversed range is swapped rather than rejected, and the app never sends
     * only one half of a pair.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function attendanceRange(Request $request): array
    {
        $from = $this->parseDate($request->string('from')->toString());
        $to = $this->parseDate($request->string('to')->toString());

        if ($from !== null && $to !== null) {
            return $from->lessThanOrEqualTo($to) ? [$from, $to] : [$to, $from];
        }

        $month = $request->string('month', now()->format('Y-m'))->toString();
        [$year, $monthNumber] = array_pad(array_map('intval', explode('-', $month)), 2, 0);

        if ($year < 2000 || $monthNumber < 1 || $monthNumber > 12) {
            [$year, $monthNumber] = [(int) now()->year, (int) now()->month];
        }

        $start = Carbon::create($year, $monthNumber, 1)->startOfDay();

        return [$start, $start->copy()->endOfMonth()->startOfDay()];
    }

    /** A `YYYY-MM-DD` value, or null when it is missing or malformed. */
    private function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
