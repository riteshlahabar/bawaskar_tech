@extends('admin.layouts.app')
@section('title', $report->title())
@section('content')
@php
    $query = $filters->toQuery();
    $selectFilters = [
        'channel' => ['name' => 'channel', 'label' => 'Channel', 'all' => 'All channels'],
        'period' => ['name' => 'period', 'label' => 'Group By', 'all' => null],
        'salesman' => ['name' => 'salesman_id', 'label' => 'Salesman', 'all' => 'All salesmen'],
        'dealer' => ['name' => 'dealer_id', 'label' => 'Dealer', 'all' => 'All dealers'],
        'warehouse' => ['name' => 'warehouse_id', 'label' => 'Warehouse', 'all' => 'All warehouses'],
        'expiry_days' => ['name' => 'expiry_days', 'label' => 'Expiry', 'all' => null],
    ];
@endphp

<div class="card admin-table-card">
    <div class="card-body pt-3">
        @if($report->description())
            <p class="text-muted mb-3">{{ $report->description() }}</p>
        @endif

        <div class="admin-table-toolbar mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                <form class="d-flex flex-wrap align-items-end gap-2 flex-grow-1" method="GET" action="{{ route('admin.report.show', $report->key()) }}">
                    @if(in_array('date', $report->filters(), true))
                        <div class="admin-toolbar-field">
                            <label class="form-label small text-muted mb-1">From</label>
                            <input type="date" name="from" value="{{ $query['from'] }}" class="form-control">
                        </div>
                        <div class="admin-toolbar-field">
                            <label class="form-label small text-muted mb-1">To</label>
                            <input type="date" name="to" value="{{ $query['to'] }}" class="form-control">
                        </div>
                    @endif

                    @foreach($report->filters() as $filterKey)
                        @continue(! isset($selectFilters[$filterKey]))
                        @php($select = $selectFilters[$filterKey])
                        <div class="admin-toolbar-field">
                            <label class="form-label small text-muted mb-1">{{ $select['label'] }}</label>
                            <select class="form-select" name="{{ $select['name'] }}">
                                @if($select['all'])<option value="">{{ $select['all'] }}</option>@endif
                                @foreach($filterOptions[$select['name']] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) ($query[$select['name']] ?? '') === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="iconoir-filter me-1"></i>Filter</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.report.show', $report->key()) }}">Reset</a>
                    </div>
                </form>

                <div class="d-flex gap-2">
                    <a class="btn btn-outline-success" href="{{ route('admin.report.export', array_merge(['report' => $report->key(), 'format' => 'excel'], $query)) }}"><i class="fa fa-file-excel me-1"></i>Excel</a>
                    <a class="btn btn-outline-danger" href="{{ route('admin.report.export', array_merge(['report' => $report->key(), 'format' => 'pdf'], $query)) }}"><i class="fa fa-file-pdf me-1"></i>PDF</a>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($result->cards as $card)
                <div class="col-md-6 col-xl">
                    <div class="card border mb-3">
                        <div class="card-body py-3">
                            <p class="text-muted mb-1">{{ $card['label'] }}</p>
                            <h4 class="mb-0">{{ $formatter->format($card['value'], $card['type'] ?? 'text') }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($result->note)
            <div class="alert alert-light border small mb-3">{{ $result->note }}</div>
        @endif

        <div class="table-responsive admin-table-responsive">
            <table class="table table-hover align-middle mb-0 admin-data-table">
                <thead class="table-light">
                    <tr>
                        @foreach($result->columns as $column)
                            <th @class(['text-end' => in_array($column['type'] ?? 'text', ['money', 'number', 'percent'], true)])>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($result->rows as $row)
                        <tr>
                            @foreach($result->columns as $column)
                                @php($type = $column['type'] ?? 'text')
                                <td @class(['text-end text-nowrap' => in_array($type, ['money', 'number', 'percent'], true)])>{{ $formatter->format($row[$column['key']] ?? null, $type) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($result->columns) }}" class="text-center text-muted py-4">No records for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
