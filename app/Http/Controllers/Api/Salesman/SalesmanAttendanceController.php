<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanAttendanceController extends SalesmanApiController
{
    public function checkIn(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);

        $attendance = AttendanceLog::query()->updateOrCreate(
            ['salesman_id' => $user->id, 'attendance_date' => today()],
            [
                'check_in_at' => now(),
                'check_in_latitude' => $validated['latitude'],
                'check_in_longitude' => $validated['longitude'],
                'status' => 'present',
            ]
        );

        return $this->success(['attendance' => $attendance], 'Checked in.');
    }

    public function checkOut(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);

        $attendance = $this->todayLog($user);

        if (! $attendance || ! $attendance->check_in_at) {
            return $this->fail('Check in is required before check out.');
        }

        // A salesman who checks out while still on break gets that break closed
        // at the same moment, so the day can never keep a break running forever.
        if ($open = $attendance->openBreak()) {
            $open->update(['resume_at' => now(), 'break_minutes' => Carbon::parse($open->break_at)->diffInMinutes(now())]);
        }

        $breakMinutes = (int) $attendance->breaks()->sum('break_minutes');
        $grossMinutes = (int) Carbon::parse($attendance->check_in_at)->diffInMinutes(now());

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'break_minutes' => $breakMinutes,
            'working_minutes' => max(0, $grossMinutes - $breakMinutes),
        ]);

        return $this->success(['attendance' => $attendance], 'Checked out.');
    }

    public function startBreak(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['nullable', 'numeric'], 'longitude' => ['nullable', 'numeric']]);

        $attendance = $this->todayLog($user);

        if (! $attendance || ! $attendance->check_in_at) {
            return $this->fail('Check in is required before taking a break.');
        }

        if ($attendance->check_out_at) {
            return $this->fail('The day is already checked out.');
        }

        if ($attendance->openBreak()) {
            return $this->fail('A break is already running.');
        }

        $break = $attendance->breaks()->create([
            'break_at' => now(),
            'break_latitude' => $validated['latitude'] ?? null,
            'break_longitude' => $validated['longitude'] ?? null,
        ]);

        return $this->success(['break' => $break], 'Break started.');
    }

    public function resumeBreak(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['nullable', 'numeric'], 'longitude' => ['nullable', 'numeric']]);

        $attendance = $this->todayLog($user);
        $break = $attendance?->openBreak();

        if (! $break) {
            return $this->fail('No break is running.');
        }

        $break->update([
            'resume_at' => now(),
            'resume_latitude' => $validated['latitude'] ?? null,
            'resume_longitude' => $validated['longitude'] ?? null,
            'break_minutes' => Carbon::parse($break->break_at)->diffInMinutes(now()),
        ]);

        $attendance->update(['break_minutes' => (int) $attendance->breaks()->sum('break_minutes')]);

        return $this->success(['break' => $break, 'break_minutes' => $attendance->break_minutes], 'Break ended.');
    }

    public function visits(Request $request): JsonResponse
    {
        $visits = DealerVisit::query()
            ->with('dealer.dealerProfile')
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['visits' => $visits]);
    }

    public function storeVisit(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $dealer = User::query()->findOrFail($validated['dealer_id']);

        if ((int) $dealer->dealerProfile?->salesman_id !== (int) $user->id) {
            return $this->fail('Dealer is not assigned to this salesman.', 403);
        }

        $visit = DealerVisit::query()->create($validated + ['salesman_id' => $user->id, 'visited_at' => now()]);

        return $this->success(['visit' => $visit], 'Dealer visit saved.', 201);
    }

    private function todayLog(User $user): ?AttendanceLog
    {
        return AttendanceLog::query()
            ->where('salesman_id', $user->id)
            ->where('attendance_date', today())
            ->first();
    }
}
