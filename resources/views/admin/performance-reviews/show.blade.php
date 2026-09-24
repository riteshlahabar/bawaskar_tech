@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);

    // Scores are out of 100 (the form validates between:0,100), so they are
    // always printed with the denominator rather than as a bare decimal.
    $score = fn ($value): string => number_format((float) $value, 2).' / 100';

    // decimal:2 gives "82.00"; the bar only needs the number.
    $percent = fn ($value): float => max(0, min(100, (float) $value));

    $kpis = $record->kpis ?? [];

    $statusBadge = $record->status === 'published' ? 'badge-light-success' : 'badge-light-warning';
@endphp

<div class="report-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="report-hero-icon"><i data-feather="award"></i></span>
                    <div>
                        <span class="report-hero-section">Performance Review</span>
                        <h4 class="report-hero-title">{{ $record->salesman?->name ?? 'Salesman' }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $statusBadge }}">{{ str((string) $record->status)->replace('_', ' ')->title() }}</span>
                            <span><i data-feather="calendar"></i>{{ $record->period_start?->format('d M Y') ?? '-' }} &ndash; {{ $record->period_end?->format('d M Y') ?? '-' }}</span>
                            <span><i data-feather="star"></i>{{ $score($record->overall_rating) }} overall</span>
                            @if(count($kpis) > 0)
                                <span><i data-feather="target"></i>{{ count($kpis) }} KPI{{ count($kpis) === 1 ? '' : 's' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card report-table-card">
        <div class="card-body pt-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h5 class="report-section-title mb-2"><i data-feather="clipboard"></i>Review</h5>
                    <ul class="people-info-list">
                        <li>
                            <span class="people-info-label">Salesman</span>
                            <span class="people-info-value">{{ $record->salesman?->name ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Period From</span>
                            <span class="people-info-value">{{ $record->period_start?->format('d M Y') ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Period To</span>
                            <span class="people-info-value">{{ $record->period_end?->format('d M Y') ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Reviewed By</span>
                            <span class="people-info-value">{{ $record->reviewer?->name ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Recorded On</span>
                            <span class="people-info-value">{{ $record->created_at?->format('d M Y, g:i A') ?? '-' }}</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <h5 class="report-section-title mb-2"><i data-feather="bar-chart-2"></i>Scores</h5>
                    <ul class="people-info-list">
                        <li>
                            <span class="people-info-label">Sales Performance</span>
                            <span class="people-info-value">{{ $score($record->sales_score) }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Collection Performance</span>
                            <span class="people-info-value">{{ $score($record->collection_score) }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Dealer Visit Performance</span>
                            <span class="people-info-value">{{ $score($record->visit_score) }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Employee Rating</span>
                            <span class="people-info-value">{{ $score($record->overall_rating) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="report-section-title"><i data-feather="target"></i>KPIs</h5>
                <span class="report-meta">{{ count($kpis) }} recorded</span>
            </div>

            @if(count($kpis) === 0)
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="target"></i></span>
                    <h5>No KPIs recorded</h5>
                    <p>Add them on the Edit form to show them to the salesman in the app.</p>
                </div>
            @else
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>KPI</th>
                                <th class="text-end">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kpis as $index => $kpi)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $kpi['label'] ?? '-' }}</td>
                                    <td class="text-end">{{ $kpi['value'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(filled($record->remarks))
                <hr class="my-4">
                <h5 class="report-section-title mb-2"><i data-feather="message-square"></i>Remarks</h5>
                {{-- .people-info-value is right-aligned for the label/value
                     rows above, which reads wrong on a block of prose. --}}
                <p class="people-info-value" style="text-align:left">{{ $record->remarks }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
