<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Contracts\Finance\DealerOutstandingContract;
use App\Models\DealerProfile;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanTarget;
use App\Models\Finance\Payment;
use App\Models\Hr\Task;
use App\Models\Sales\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanDashboardController extends SalesmanApiController
{
    public function __construct(private readonly DealerOutstandingContract $outstandingCalculator) {}

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $todayAttendance = AttendanceLog::query()
            ->where('salesman_id', $user->id)
            ->where('attendance_date', today())
            ->first();

        $dealerIds = DealerProfile::query()->where('salesman_id', $user->id)->pluck('user_id');

        $totalOutstanding = $dealerIds->sum(fn (int $dealerId): float => $this->outstandingCalculator->outstandingBalance($dealerId));

        $currentTarget = SalesmanTarget::query()
            ->where('salesman_id', $user->id)
            ->where('period_start', '<=', today())
            ->where('period_end', '>=', today())
            ->latest('period_start')
            ->first(['target_amount', 'achieved_amount']);

        $currentSlip = SalarySlip::query()
            ->where('salesman_id', $user->id)
            ->where('status', '!=', 'draft')
            ->where('salary_year', now()->year)
            ->where('salary_month', now()->month)
            ->first(['incentives', 'commission']);

        return $this->success([
            'assigned_dealers' => $dealerIds->count(),
            'pending_orders' => Order::query()->where('salesman_id', $user->id)->where('status', 'salesman_review')->count(),
            'today_collections' => Payment::query()->where('collected_by', $user->id)->whereDate('created_at', today())->sum('amount'),
            'total_outstanding' => $totalOutstanding,
            'today_visits' => DealerVisit::query()->where('salesman_id', $user->id)->whereDate('visited_at', today())->count(),
            'month_target' => $currentTarget?->target_amount,
            'month_achieved' => $currentTarget?->achieved_amount,
            'month_incentive' => $currentSlip ? (float) $currentSlip->incentives + (float) $currentSlip->commission : null,
            'pending_tasks' => Task::query()->where('assigned_to', $user->id)->whereIn('status', ['pending', 'in_progress'])->count(),
            'profile' => $user->load('salesmanProfile'),
            'today_attendance' => $todayAttendance ? [
                'check_in_at' => $todayAttendance->check_in_at,
                'check_in_latitude' => $todayAttendance->check_in_latitude,
                'check_in_longitude' => $todayAttendance->check_in_longitude,
                'check_out_at' => $todayAttendance->check_out_at,
                // `on_break` is what restores the app's Break/Resume button after
                // a restart — an unfinished break row means the break is running.
                'on_break' => (bool) $todayAttendance->openBreak(),
                'break_minutes' => (int) $todayAttendance->breaks()->sum('break_minutes'),
            ] : null,
        ]);
    }

    public function dealers(Request $request): JsonResponse
    {
        $dealers = DealerProfile::query()
            ->with('user.addresses')
            ->where('salesman_id', $this->salesman($request)->id)
            ->paginate($request->integer('per_page', 20));

        return $this->success(['dealers' => $dealers]);
    }
}
