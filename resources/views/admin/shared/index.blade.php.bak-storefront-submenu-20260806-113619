@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="card admin-table-card" data-table-key="{{ $module['key'] }}">
    <div class="card-body pt-3">
        <div class="d-flex justify-content-end gap-2 mb-3">
            @if($module['key'] === 'salary')
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#salaryModal"><i class="iconoir-dollar-circle me-1"></i>Generate Salary</button>
            @endif
            @if($module['can_create'] ?? true)
                <a href="{{ route($module['route'].'.create', request()->only(['type','placement','section_key','row_title'])) }}" class="btn btn-primary"><i class="iconoir-plus-circle me-1"></i>Add {{ $module['singular'] }}</a>
            @endif
        </div>
        @include('admin.shared.table-toolbar')

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form id="bulkActionForm" method="POST" action="{{ route($module['route'].'.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            @foreach(request()->query() as $key => $value)
                @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="bulk-select-col"><input class="form-check-input admin-select-all" type="checkbox" title="Select all"></th>
                            @foreach($module['columns'] as $index => $column)
                                <th data-column-index="{{ $index }}">{{ $column['label'] }}</th>
                            @endforeach
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="bulk-select-col"><input class="form-check-input admin-row-checkbox" type="checkbox" name="selected_ids[]" value="{{ $record->getKey() }}"></td>
                                @foreach($module['columns'] as $index => $column)
                                    @php
                                        $value = data_get($record, $column['key']);
                                        if (($column['type'] ?? '') === 'image' && empty($value)) {
                                            foreach (($column['fallback_keys'] ?? []) as $fallbackKey) {
                                                $value = data_get($record, $fallbackKey);
                                                if (! empty($value)) break;
                                            }
                                        }
                                        $imageUrl = null;
                                        if (($column['type'] ?? '') === 'image' && ! empty($value)) {
                                            $imageUrl = str_starts_with((string) $value, 'http://') || str_starts_with((string) $value, 'https://') || str_starts_with((string) $value, '/')
                                                ? url((string) $value)
                                                : asset((string) $value);
                                        }
                                    @endphp
                                    <td data-column-index="{{ $index }}">
                                        @if(($column['type'] ?? '') === 'image')
                                            @if($imageUrl)
                                                @php($imageModalId = 'imagePreview'.$module['key'].$record->getKey().$index)
                                                <button type="button" class="admin-image-thumb-btn" data-bs-toggle="modal" data-bs-target="#{{ $imageModalId }}" title="Preview image">
                                                    <img src="{{ $imageUrl }}" class="admin-image-thumb" alt="{{ $column['label'] }}">
                                                </button>
                                                <div class="modal fade admin-image-preview-modal" id="{{ $imageModalId }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $column['label'] }} Preview</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ $imageUrl }}" class="admin-image-preview" alt="{{ $column['label'] }} preview">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @elseif(($column['type'] ?? '') === 'boolean')
                                            <span class="badge {{ $value ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $value ? 'Active' : 'Inactive' }}</span>
                                        @elseif(($column['type'] ?? '') === 'status')
                                            <span class="badge bg-{{ in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning') }}-subtle text-{{ in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning') }}">{{ str($value)->replace('_', ' ')->title() }}</span>
                                        @elseif(($column['type'] ?? '') === 'money')
                                            Rs. {{ number_format((float) $value, 2) }}
                                        @elseif(($column['type'] ?? '') === 'date')
                                            {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '-' }}
                                        @elseif(($column['type'] ?? '') === 'datetime')
                                            {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '-' }}
                                        @else
                                            {{ $value !== null && $value !== '' ? $value : '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <div class="dropdown admin-row-action">
                                        <button class="btn btn-sm btn-outline-secondary admin-row-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" aria-label="Actions">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end admin-row-action-menu">
                                            <a class="dropdown-item" href="{{ route($module['route'].'.show', $record->getKey()) }}"><i class="iconoir-eye"></i><span>View</span></a>
                                            @if($module['can_edit'] ?? true)
                                                <a class="dropdown-item" href="{{ route($module['route'].'.edit', $record->getKey()) }}"><i class="iconoir-edit-pencil"></i><span>Edit</span></a>
                                            @endif

                                            @if($module['key'] === 'dealers' && $record->status === 'pending_approval')
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="button" data-bs-toggle="modal" data-bs-target="#approveDealer{{ $record->id }}"><i class="iconoir-check-circle"></i><span>Approve Dealer</span></button>
                                            @endif

                                            @if($module['key'] === 'orders')
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="button" data-bs-toggle="modal" data-bs-target="#orderStatus{{ $record->id }}"><i class="iconoir-check-circle"></i><span>Update Status</span></button>
                                                <button class="dropdown-item text-info" type="submit" form="convertOrderToPi{{ $record->id }}"><i class="iconoir-page"></i><span>Convert to PI</span></button>
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'order', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'order', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if($module['key'] === 'proforma-invoices')
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-info" type="submit" form="convertPiToInvoice{{ $record->id }}"><i class="iconoir-receipt"></i><span>Convert to Sale Invoice</span></button>
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'proforma', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'proforma', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if($module['key'] === 'invoices')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'invoice', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'invoice', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending')
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="submit" form="decisionForm{{ $module['key'] }}{{ $record->id }}" name="status" value="approved"><i class="iconoir-check-circle"></i><span>Approve</span></button>
                                                <button class="dropdown-item text-danger" type="submit" form="decisionForm{{ $module['key'] }}{{ $record->id }}" name="status" value="rejected"><i class="iconoir-xmark-circle"></i><span>Reject</span></button>
                                            @endif

                                            @if($module['can_delete'] ?? true)
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-danger" type="button" onclick="if(confirm('Delete this record?')) document.getElementById('deleteForm{{ $module['key'] }}{{ $record->id }}').submit();"><i class="fa-solid fa-trash-can"></i><span>Delete</span></button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($module['columns']) + 2 }}" class="text-center py-5 text-muted">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach($records as $record)
            @if($module['can_delete'] ?? true)
                <form id="deleteForm{{ $module['key'] }}{{ $record->id }}" method="POST" action="{{ route($module['route'].'.destroy', $record->getKey()) }}" class="d-none">@csrf @method('DELETE')</form>
            @endif
            @if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending')
                <form id="decisionForm{{ $module['key'] }}{{ $record->id }}" method="POST" action="{{ route('admin.'.$module['key'].'.decision', $record->id) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'orders')
                <form id="convertOrderToPi{{ $record->id }}" method="POST" action="{{ route('admin.orders.convert-to-proforma', $record->getKey()) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'proforma-invoices')
                <form id="convertPiToInvoice{{ $record->id }}" method="POST" action="{{ route('admin.proforma-invoices.convert-to-invoice', $record->getKey()) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'dealers' && $record->status === 'pending_approval')
                <div class="modal fade" id="approveDealer{{ $record->id }}"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.dealers.approve', $record->id) }}">@csrf<div class="modal-header"><h5>Approve {{ $record->name }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Assign Salesman</label><select name="salesman_id" class="form-select" required>@foreach(\App\Models\User::where('role', 'salesman')->where('status', 'active')->orderBy('name')->get() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select><label class="form-label mt-3">Credit Limit</label><input name="credit_limit" type="number" step="0.01" min="0" class="form-control" value="0"></div><div class="modal-footer"><button class="btn btn-success">Approve & Assign</button></div></form></div></div>
            @endif
            @if($module['key'] === 'orders')
                <div class="modal fade" id="orderStatus{{ $record->id }}"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.change-status', $record->id) }}">@csrf<div class="modal-header"><h5>Update {{ $record->order_no }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select name="status" class="form-select">@foreach(['salesman_review','admin_review','approved','packing','dispatched','delivered','cancelled'] as $st)<option value="{{ $st }}" @selected($record->status === $st)>{{ str($st)->replace('_', ' ')->title() }}</option>@endforeach</select></div><div class="modal-footer"><button class="btn btn-primary">Update</button></div></form></div></div>
            @endif
        @endforeach

        <div class="mt-3">{{ $records->links() }}</div>
    </div>
</div>

@if($module['key'] === 'salary')
    <div class="modal fade" id="salaryModal"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.salary.generate') }}">@csrf<div class="modal-header"><h5>Generate Monthly Salary</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row"><div class="col-6"><label>Year</label><input class="form-control" type="number" name="salary_year" value="{{ now()->year }}" required></div><div class="col-6"><label>Month</label><select class="form-select" name="salary_month">@foreach(range(1, 12) as $m)<option value="{{ $m }}" @selected($m === now()->month)>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>@endforeach</select></div></div></div><div class="modal-footer"><button class="btn btn-success">Generate</button></div></form></div></div>
@endif
@endsection
@push('scripts')
    <script src="{{ asset('admin-module-js/shared/table-toolbar.js') }}"></script>
@endpush