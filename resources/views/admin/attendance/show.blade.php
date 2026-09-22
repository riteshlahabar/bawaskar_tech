@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);

    $breaks = $record->breaks()->get();

    $checkIn = $record->check_in_at;
    $checkOut = $record->check_out_at;

    // Gross is the clock span; net is what payroll uses, so the two are shown
    // side by side with the break time that explains the difference.
    $grossMinutes = $checkIn && $checkOut ? (int) $checkIn->diffInMinutes($checkOut) : null;
    $breakMinutes = (int) ($record->break_minutes ?? 0);
    $netMinutes = (int) ($record->working_minutes ?? 0);

    $duration = function (?int $minutes): string {
        if ($minutes === null) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours > 0 ? $hours.'h '.$rest.'m' : $rest.'m';
    };

    $mapLink = fn ($lat, $lng): ?string => $lat && $lng
        ? 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng
        : null;

    $statusBadge = match (strtolower((string) $record->status)) {
        'present' => 'badge-light-success',
        'absent' => 'badge-light-danger',
        'leave' => 'badge-light-info',
        default => 'badge-light-warning',
    };
@endphp

<div class="report-page attendance-show-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="report-hero-icon"><i data-feather="clock"></i></span>
                    <div>
                        <span class="report-hero-section">Attendance Record</span>
                        <h4 class="report-hero-title">{{ $record->salesman?->name ?? 'Salesman' }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $statusBadge }}">{{ str((string) $record->status)->replace('_', ' ')->title() }}</span>
                            <span><i data-feather="calendar"></i>{{ $record->attendance_date?->format('d M Y') ?? '-' }}</span>
                            <span><i data-feather="watch"></i>{{ $duration($netMinutes) }} worked</span>
                            @if($breakMinutes > 0)
                                <span><i data-feather="coffee"></i>{{ $duration($breakMinutes) }} on break</span>
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
                    <h5 class="report-section-title mb-2"><i data-feather="user-check"></i>Attendance</h5>
                    <ul class="people-info-list">
                        <li>
                            <span class="people-info-label">Salesman</span>
                            <span class="people-info-value">{{ $record->salesman?->name ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Date</span>
                            <span class="people-info-value">{{ $record->attendance_date?->format('d M Y') ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Check In</span>
                            <span class="people-info-value">{{ $checkIn?->format('d M Y, g:i A') ?? 'Not checked in' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Check Out</span>
                            <span class="people-info-value">{{ $checkOut?->format('d M Y, g:i A') ?? 'Not checked out' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Gross Hours</span>
                            <span class="people-info-value">{{ $duration($grossMinutes) }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Break Time</span>
                            <span class="people-info-value">{{ $breakMinutes > 0 ? $duration($breakMinutes) : '-' }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Net Working Hours</span>
                            <span class="people-info-value">{{ $duration($netMinutes) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <h5 class="report-section-title mb-2"><i data-feather="map-pin"></i>GPS Location</h5>
                    <ul class="people-info-list">
                        <li>
                            <span class="people-info-label">Check In Location</span>
                            <span class="people-info-value">
                                @if($link = $mapLink($record->check_in_latitude, $record->check_in_longitude))
                                    <a href="{{ $link }}" target="_blank" rel="noopener">{{ $record->check_in_latitude }}, {{ $record->check_in_longitude }}</a>
                                @else
                                    No GPS captured
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="people-info-label">Check Out Location</span>
                            <span class="people-info-value">
                                @if($link = $mapLink($record->check_out_latitude, $record->check_out_longitude))
                                    <a href="{{ $link }}" target="_blank" rel="noopener">{{ $record->check_out_latitude }}, {{ $record->check_out_longitude }}</a>
                                @else
                                    No GPS captured
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="people-info-label">Total Breaks</span>
                            <span class="people-info-value">{{ $breaks->count() }}</span>
                        </li>
                        <li>
                            <span class="people-info-label">Recorded On</span>
                            <span class="people-info-value">{{ $record->created_at?->format('d M Y, g:i A') ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="report-section-title"><i data-feather="coffee"></i>Break &amp; Resume Records</h5>
                <span class="report-meta">{{ $breaks->count() }} break{{ $breaks->count() === 1 ? '' : 's' }} &middot; {{ $duration($breakMinutes) }} total</span>
            </div>

            @if($breaks->isEmpty())
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="coffee"></i></span>
                    <h5>No breaks recorded</h5>
                    <p>The salesman did not take a break on this day.</p>
                </div>
            @else
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Break At</th>
                                <th>Resume At</th>
                                <th class="text-end">Duration</th>
                                <th class="text-end">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($breaks as $index => $break)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $break->break_at?->format('g:i A') ?? '-' }}</td>
                                    <td>
                                        @if($break->resume_at)
                                            {{ $break->resume_at->format('g:i A') }}
                                        @else
                                            <span class="badge badge-light-warning">Still on break</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $break->resume_at ? $duration((int) $break->break_minutes) : '-' }}</td>
                                    <td class="text-end">
                                        @if($link = $mapLink($break->break_latitude, $break->break_longitude))
                                            <a href="{{ $link }}" target="_blank" rel="noopener">View on map</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total break time</td>
                                <td class="text-end">{{ $duration($breakMinutes) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
