@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->query();
    $exportQuery = request()->except(['page']);
    $resetQuery = request()->only(['type','placement','section_key','row_title']);
    $hasTypeFilter = collect($module['filters'] ?? [])->contains(fn ($filter) => ($filter['name'] ?? '') === 'type');

    // Configured filters that carry their own choices. The `type` channel
    // filter is rendered separately below because its options are fixed.
    $filterOptions = $filterOptions ?? [];
    $choiceFilters = collect($module['filters'] ?? [])
        ->filter(fn ($filter) => ($filter['name'] ?? '') !== 'type' && ! empty($filterOptions[$filter['name'] ?? '']));
@endphp
<div class="admin-table-toolbar mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
        <form class="d-flex flex-wrap align-items-end gap-2 flex-grow-1" method="GET">
            @foreach(request()->only(['type','placement','section_key','row_title']) as $hiddenKey => $hiddenValue)
                @if($hiddenValue !== null && $hiddenValue !== '')
                    <input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">
                @endif
            @endforeach

            <div class="admin-toolbar-search">
                <label class="form-label small text-muted mb-1">Search</label>
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search {{ strtolower($pageTitle) }}...">
            </div>

            @foreach($choiceFilters as $filter)
                @php($filterName = $filter['name'])
                @php($filterLabel = $filter['label'] ?? str($filterName)->replace('_id', '')->replace('_', ' ')->title())
                <div class="admin-toolbar-field">
                    <label class="form-label small text-muted mb-1">{{ $filterLabel }}</label>
                    <select class="form-select" name="{{ $filterName }}">
                        <option value="">All {{ $filterLabel }}</option>
                        @foreach($filterOptions[$filterName] as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) request($filterName) === (string) $optionValue)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            @if(!empty($module['status_options']) && !empty($module['status_column']))
                <div class="admin-toolbar-field">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach($module['status_options'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($hasTypeFilter && ! request()->filled('type'))
                <div class="admin-toolbar-field">
                    <label class="form-label small text-muted mb-1">Channel</label>
                    <select class="form-select" name="type">
                        <option value="">All Channels</option>
                        <option value="customer" @selected(request('type') === 'customer')>Customer</option>
                        <option value="dealer" @selected(request('type') === 'dealer')>Dealer</option>
                    </select>
                </div>
            @endif

            <div class="admin-toolbar-date">
                <label class="form-label small text-muted mb-1">From</label>
                <input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="admin-toolbar-date">
                <label class="form-label small text-muted mb-1">To</label>
                <input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}">
            </div>

            {{-- Every toolbar button carries its own label from md up, with a
                 purpose-specific Font Awesome icon rather than a bare glyph:
                 a funnel filters, an anticlockwise arrow resets. --}}
            <button class="btn btn-outline-primary" type="submit" title="Filter"><i class="fa-solid fa-filter"></i><span class="d-none d-md-inline ms-1">Filter</span></button>
            <a href="{{ route($module['route'].'.index', $resetQuery) }}" class="btn btn-outline-secondary" title="Reset"><i class="fa-solid fa-rotate-left"></i><span class="d-none d-md-inline ms-1">Reset</span></a>
        </form>

        <div class="d-flex flex-wrap justify-content-end align-items-end gap-2 admin-toolbar-actions">
            @if(request()->filled('type'))
                <span class="badge bg-primary-subtle text-primary align-self-center px-3 py-2">{{ str(request('type'))->title() }} View</span>
            @endif

            @if($can['create'])
            <form method="POST" action="{{ route('admin.common-import.store', ['module' => $module['key']]) }}" enctype="multipart/form-data" class="d-inline admin-import-form">
                @csrf
</form>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#importModal{{ $module['key'] }}" title="Import Excel/CSV" aria-label="Import Excel/CSV">
                <i class="fa-solid fa-file-import"></i><span class="d-none d-md-inline ms-1">Import</span>
            </button>

            <div class="modal fade" id="importModal{{ $module['key'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Import {{ $module['label'] ?? $pageTitle }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form method="POST" action="{{ route('admin.common-import.store', ['module' => $module['key']]) }}" enctype="multipart/form-data">
                            @csrf
                            @foreach(request()->only(['type','placement','section_key','row_title']) as $hiddenKey => $hiddenValue)
                                @if($hiddenValue !== null && $hiddenValue !== '')
                                    <input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">
                                @endif
                            @endforeach

                            <div class="modal-body">
                                <div class="alert alert-info small mb-3">
                                    Download sample file, fill data, then upload here. This import affects only current submenu: <strong>{{ $module['label'] ?? $pageTitle }}</strong>.
                                </div>

                                <a href="{{ route('admin.common-import.sample', ['module' => $module['key']]) }}" class="btn btn-outline-success w-100 mb-3">
                                    <i class="fa-solid fa-download me-1"></i> Download Sample Excel / CSV
                                </a>

                                <label class="form-label">Select Excel / CSV File</label>
                                <input type="file" name="import_file" accept=".csv,.txt,.xlsx" class="form-control" required>

                                <label class="form-label mt-3">Select Images ZIP File <span class="text-muted">(Optional)</span></label>
                                <input type="file" name="images_zip" accept=".zip" class="form-control">

                                <div class="form-text">
                                    Excel/CSV is required. Images ZIP is optional. If uploaded, images will be extracted automatically. Rows not included in file will not be changed.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-file-import me-1"></i> Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Columns" aria-label="Columns"><i class="fa-solid fa-table-columns"></i><span class="d-none d-md-inline ms-1">Columns</span></button>
                <div class="dropdown-menu dropdown-menu-end p-2 admin-column-menu">
                    @foreach($visibleColumns as $index => $column)
                        <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input m-0 admin-column-toggle" type="checkbox" data-column-index="{{ $index }}" checked>
                            <span>{{ $column['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <a class="btn btn-outline-success" href="{{ route($module['route'].'.export', ['format' => 'excel'] + $exportQuery) }}" title="Export Excel" aria-label="Export Excel">
                <i class="fa-solid fa-file-excel"></i><span class="d-none d-md-inline ms-1">Excel</span>
            </a>
            <a class="btn btn-outline-danger" href="{{ route($module['route'].'.export', ['format' => 'pdf'] + $exportQuery) }}" title="Export PDF" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i><span class="d-none d-md-inline ms-1">PDF</span></a>
            <button class="btn btn-outline-secondary" type="button" onclick="window.print()" title="Print" aria-label="Print"><i class="fa-solid fa-print"></i><span class="d-none d-md-inline ms-1">Print</span></button>

            @if($module['key'] === 'products' && $can['edit'])
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkCategoryModal" title="Change Category Of Selected" aria-label="Change Category Of Selected"><i class="fa-solid fa-folder-tree"></i><span class="d-none d-md-inline ms-1">Category</span></button>
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkSectionModal" title="Change Section Title Of Selected" aria-label="Change Section Title Of Selected"><i class="fa-solid fa-table-cells-large"></i><span class="d-none d-md-inline ms-1">Section</span></button>
            @endif

            @if(($module['can_delete'] ?? true) && $can['delete'])
                <button class="btn btn-outline-danger" type="submit" form="bulkActionForm" onclick="return confirm('Delete selected records?')" title="Delete Selected" aria-label="Delete Selected"><i class="fa-solid fa-trash-can"></i><span class="d-none d-md-inline ms-1">Delete</span></button>
            @endif
        </div>
    </div>
</div>