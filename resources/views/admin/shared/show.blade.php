@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php($can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true])
@if(in_array($module['key'], ['orders', 'proforma-invoices', 'invoices'], true))
    @include('admin.sales-documents.panel-show')
@else
<div class="card">
    <div class="card-body pt-3">
        <div class="d-flex justify-content-end gap-2 mb-3 flex-wrap">
            <a href="{{ route($module['route'].'.index', request()->only(['type','placement','section_key','row_title'])) }}" class="btn btn-outline-secondary">Back</a>
            @if(($module['can_edit'] ?? true) && $can['edit'])
                <a href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], request()->only(['type','placement','section_key','row_title']))) }}" class="btn btn-primary">Edit</a>
            @endif
        </div>

        <div class="row g-3">
            @foreach($module['columns'] as $column)
                @php($value = data_get($record, $column['key']))
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">{{ $column['label'] }}</small>
                        <strong>
                            @if(($column['type'] ?? '') === 'money')
                                Rs. {{ number_format((float) $value, 2) }}
                            @elseif(($column['type'] ?? '') === 'boolean')
                                {{ $value ? 'Yes' : 'No' }}
                            @elseif(($column['type'] ?? '') === 'date')
                                {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '-' }}
                            @elseif(($column['type'] ?? '') === 'datetime')
                                {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '-' }}
                            @else
                                {{ $value !== null && $value !== '' ? $value : '-' }}
                            @endif
                        </strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection