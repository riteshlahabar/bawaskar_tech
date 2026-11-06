@extends('admin.layouts.app')
@section('title', 'Dashboard HRMS')
@section('content')
@php
    // Same tile treatment as the ERP dashboard, so the two match.
    $secondaryTiles = [
        ['label' => 'Issued Assets', 'value' => number_format((float) $issuedAssets), 'icon' => 'iconoir-box', 'color' => 'primary', 'trend' => 'With the field team'],
        ['label' => 'Month Payroll', 'value' => 'Rs. '.number_format((float) $monthlyPayroll, 2), 'icon' => 'iconoir-wallet', 'color' => 'success', 'trend' => 'This month'],
        ['label' => 'Target', 'value' => 'Rs. '.number_format((float) $targetTotal, 2), 'icon' => 'iconoir-route', 'color' => 'warning', 'trend' => 'Set for the month'],
        ['label' => 'Achieved', 'value' => 'Rs. '.number_format((float) $achievedTotal, 2), 'icon' => 'iconoir-check-circle', 'color' => 'info', 'trend' => 'Against target'],
    ];
@endphp

{{-- Fastkart's dashboard tile. A FONT icon, not a feather SVG: the template
     paints `custome-N-bg .static-top-widget svg` white, which would hide an
     SVG tile icon against its own 10% tint. --}}
<div class="row">
    @foreach($stats as $index => $stat)
        <div class="col-sm-6 col-xxl-3 col-lg-6">
            <div class="main-tiles border-5 card-hover border-0 card o-hidden">
                <div class="custome-{{ ($index % 4) + 1 }}-bg b-r-4 card-body">
                    <div class="media align-items-center static-top-widget">
                        <div class="media-body p-0">
                            <span class="m-0">{{ $stat['label'] }}</span>
                            <h4 class="mb-0">{{ number_format((float) $stat['value']) }}
                                <span class="badge badge-light-{{ $stat['color'] }} grow">{{ $stat['trend'] }}</span>
                            </h4>
                        </div>
                        <div class="align-self-center text-center">
                            <i class="{{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach($secondaryTiles as $index => $tile)
        <div class="col-sm-6 col-xxl-3 col-lg-6">
            <div class="main-tiles border-5 card-hover border-0 card o-hidden">
                <div class="custome-{{ ($index % 4) + 1 }}-bg b-r-4 card-body">
                    <div class="media align-items-center static-top-widget">
                        <div class="media-body p-0">
                            <span class="m-0">{{ $tile['label'] }}</span>
                            <h4 class="mb-0">{{ $tile['value'] }}
                                <span class="badge badge-light-{{ $tile['color'] }} grow">{{ $tile['trend'] }}</span>
                            </h4>
                        </div>
                        <div class="align-self-center text-center">
                            <i class="{{ $tile['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h4 class="card-title">Today Attendance</h4></div>
                    <div class="col-auto"><a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table theme-table table-borderless table-hover align-middle mb-0 admin-detail-table">
                        <thead><tr><th>Salesman</th><th>Check In</th><th>Check Out</th><th>Working Minutes</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($todayAttendance as $attendance)
                                <tr>
                                    <td>{{ $attendance->salesman?->name ?? '-' }}</td>
                                    <td>{{ $attendance->check_in_at?->format('h:i A') ?? '-' }}</td>
                                    <td>{{ $attendance->check_out_at?->format('h:i A') ?? '-' }}</td>
                                    <td>{{ $attendance->working_minutes }}</td>
                                    <td><span class="badge bg-{{ in_array($attendance->status, ['present', 'late']) ? 'success' : 'warning' }}-subtle text-{{ in_array($attendance->status, ['present', 'late']) ? 'success' : 'warning' }}">{{ str($attendance->status)->replace('_', ' ')->title() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No attendance marked today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Recent Dealer Visits</h4></div>
            <div class="card-body pt-0">
                @forelse($recentVisits as $visit)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <div><strong>{{ $visit->salesman?->name ?? '-' }}</strong><small class="d-block text-muted">{{ $visit->dealer?->dealerProfile?->firm_name ?? $visit->dealer?->name ?? '-' }} - {{ $visit->purpose ?? 'Visit' }}</small></div>
                        <span class="text-muted">{{ $visit->visited_at?->format('d-m-Y h:i A') }}</span>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">No dealer visits found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">HRMS Action Centre</h4></div>
            <div class="card-body">
                <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-calendar-minus me-2 text-warning"></i>Pending leave</span><strong>{{ $pendingLeaves->count() }}</strong></a>
                <a href="{{ route('admin.expenses.index', ['status' => 'pending']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-receive-dollars me-2 text-danger"></i>Pending expenses</span><strong>Rs. {{ number_format((float) $pendingExpenseAmount, 2) }}</strong></a>
                <a href="{{ route('admin.tour-plans.index') }}" class="d-flex justify-content-between py-3 text-body"><span><i class="iconoir-route me-2 text-primary"></i>Upcoming tours</span><strong>{{ $upcomingTours->count() }}</strong></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Pending Leaves</h4></div>
            <div class="card-body pt-0">
                @forelse($pendingLeaves as $leave)
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong>{{ $leave->salesman?->name ?? '-' }}</strong><small class="d-block text-muted">{{ str($leave->leave_type)->title() }} - {{ $leave->from_date?->format('d-m-Y') }}</small></div><span class="badge bg-warning-subtle text-warning align-self-center">Pending</span></div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">No pending leave.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Pending Expenses</h4></div>
            <div class="card-body pt-0">
                @forelse($pendingExpenses as $expense)
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong>{{ $expense->salesman?->name ?? '-' }}</strong><small class="d-block text-muted">{{ str($expense->expense_type)->title() }} - {{ $expense->expense_date?->format('d-m-Y') }}</small></div><span class="text-danger fw-semibold">Rs. {{ number_format((float) $expense->amount, 2) }}</span></div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">No pending expenses.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection