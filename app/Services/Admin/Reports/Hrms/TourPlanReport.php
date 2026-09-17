<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\TourPlan;
use App\Services\Admin\Reports\Report;

final class TourPlanReport extends Report
{
    public function key(): string
    {
        return 'tour-plans';
    }

    public function title(): string
    {
        return 'Tour Plans';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Planned vs completed tour days per salesman.';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $groups = TourPlan::query()
            ->whereBetween('plan_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->selectRaw("salesman_id, COUNT(*) as plans,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->groupBy('salesman_id')
            ->get();

        $names = $this->userNames($groups->pluck('salesman_id'));

        $rows = $groups->map(fn (TourPlan $group): array => [
            'salesman' => $names[$group->salesman_id] ?? '#'.$group->salesman_id,
            'plans' => (int) $group->plans,
            'approved' => (int) $group->approved,
            'completed' => (int) $group->completed,
            'cancelled' => (int) $group->cancelled,
            'completion' => $this->percent((int) $group->completed, (int) $group->plans - (int) $group->cancelled),
        ])->sortBy('salesman')->values()->all();

        $plans = array_sum(array_column($rows, 'plans'));
        $completed = array_sum(array_column($rows, 'completed'));

        return new ReportResult(
            cards: [
                ['label' => 'Tour Plans', 'value' => $plans, 'type' => 'number'],
                ['label' => 'Completed', 'value' => $completed, 'type' => 'number'],
                ['label' => 'Cancelled', 'value' => array_sum(array_column($rows, 'cancelled')), 'type' => 'number'],
                ['label' => 'Completion Rate', 'value' => $this->percent($completed, $plans - array_sum(array_column($rows, 'cancelled'))), 'type' => 'percent'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'plans', 'label' => 'Planned', 'type' => 'number'],
                ['key' => 'approved', 'label' => 'Approved', 'type' => 'number'],
                ['key' => 'completed', 'label' => 'Completed', 'type' => 'number'],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'type' => 'number'],
                ['key' => 'completion', 'label' => 'Completion', 'type' => 'percent'],
            ],
            rows: $rows,
        );
    }
}
