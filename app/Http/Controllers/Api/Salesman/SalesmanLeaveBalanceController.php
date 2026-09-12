<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\LeaveApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Remaining leave balance per type for the current calendar year.
 *
 * Entitlements come from config so HR can change policy without a code change,
 * and only approved applications count against the balance.
 */
final class SalesmanLeaveBalanceController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $salesman = $this->salesman($request);
        $year = (int) $request->integer('year', (int) now()->year);

        $entitlements = config('hrms.leave_entitlements', [
            'casual' => 12,
            'sick' => 8,
            'earned' => 15,
            'unpaid' => 0,
        ]);

        $taken = LeaveApplication::query()
            ->where('salesman_id', $salesman->id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->get()
            ->groupBy('leave_type')
            ->map(fn ($group) => $group->sum(
                // Inclusive of both endpoints: a single-day leave is 1 day,
                // not 0.
                fn (LeaveApplication $leave): int => $leave->from_date->diffInDays($leave->to_date) + 1
            ));

        $balances = collect($entitlements)->map(fn (int $allowed, string $type): array => [
            'leave_type' => $type,
            'entitled' => $allowed,
            'taken' => (int) ($taken[$type] ?? 0),
            'balance' => max($allowed - (int) ($taken[$type] ?? 0), 0),
        ])->values();

        return $this->success([
            'year' => $year,
            'balances' => $balances,
            'pending_requests' => LeaveApplication::query()
                ->where('salesman_id', $salesman->id)
                ->where('status', 'pending')
                ->count(),
        ]);
    }
}
