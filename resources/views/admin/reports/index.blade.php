@extends('admin.layouts.app')
@section('title', 'Reports Overview')
@section('content')
<div class="row">
    @foreach([['B2B Sales', $summary['b2b_sales']], ['B2C Sales', $summary['b2c_sales']], ['Collections', $summary['collections']], ['Approved Expenses', $summary['expenses']], ['Payroll', $summary['salary']]] as [$label, $value])
        <div class="col-md-6 col-xl">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">{{ $label }}</p>
                    <h4 class="mb-0">Rs. {{ number_format((float) $value, 2) }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>

@foreach($sections as $sectionTitle => $sectionReports)
    <div class="card">
        <div class="card-header"><h4 class="card-title">{{ $sectionTitle }}</h4></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($sectionReports as $item)
                    <div class="col-md-6 col-xl-4">
                        <a href="{{ route('admin.report.show', $item->key()) }}" class="d-block h-100 p-3 border rounded text-decoration-none">
                            <h6 class="mb-1 text-dark">{{ $item->title() }}</h6>
                            <small class="text-muted">{{ $item->description() }}</small>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
@endsection
