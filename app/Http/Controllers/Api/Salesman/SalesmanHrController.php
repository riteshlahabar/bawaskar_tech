<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\LeaveApplication;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\TourPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SalesmanHrController extends SalesmanApiController
{
    public function leaves(Request $request): JsonResponse
    {
        $leaves = LeaveApplication::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['leaves' => $leaves]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:40'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $leave = LeaveApplication::query()->create($validated + ['salesman_id' => $user->id]);

        return $this->success(['leave' => $leave], 'Leave application submitted.', 201);
    }

    public function assets(Request $request): JsonResponse
    {
        $assets = SalesmanAsset::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->get();

        return $this->success(['assets' => $assets]);
    }

    public function tourPlans(Request $request): JsonResponse
    {
        $plans = TourPlan::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->orderByDesc('plan_date')
            ->orderByDesc('id')
            ->paginate(20);

        // `dealer_ids` is only a list of ids, so the app could count the stops
        // but never name them. Each plan carries its dealers resolved.
        $plans->getCollection()->each(function (TourPlan $plan): void {
            $plan->setAttribute('dealers', $plan->dealers());
        });

        return $this->success(['tour_plans' => $plans]);
    }

    public function storeTourPlan(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'plan_date' => ['required', 'date'],
            'route_name' => ['required', 'string', 'max:255'],
            'dealer_ids' => ['nullable', 'array'],
            'dealer_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', User::ROLE_DEALER)],
        ]);

        // A plan the salesman writes themselves still waits for the admin —
        // `approved` is the admin's word, never the app's.
        $plan = TourPlan::query()->create($validated + [
            'salesman_id' => $user->id,
            'status' => 'planned',
        ]);

        return $this->success(['tour_plan' => $plan], 'Tour plan saved.', 201);
    }

    /**
     * Closes a route the salesman has finished walking.
     *
     * Only their own plan, and only one that has not already been settled —
     * a completed or cancelled plan is final.
     */
    public function completeTourPlan(Request $request, TourPlan $tourPlan): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $tourPlan->salesman_id !== (int) $user->id) {
            return $this->fail('This tour plan belongs to another salesman.', 403);
        }

        if (! in_array($tourPlan->status, ['planned', 'approved'], true)) {
            return $this->fail('This tour plan is already '.$tourPlan->status.'.', 422);
        }

        $tourPlan->update(['status' => 'completed']);

        return $this->success(['tour_plan' => $tourPlan], 'Tour plan marked completed.');
    }
}
