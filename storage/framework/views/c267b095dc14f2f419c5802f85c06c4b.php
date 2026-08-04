<?php
    $query = request()->query();
    $exportQuery = request()->except(['page']);
    $resetQuery = request()->only(['type']);
    $searchColumns = $module['search'] ?? [];
    $columnLabels = collect($module['columns'] ?? [])->keyBy('key');
    $hasTypeFilter = collect($module['filters'] ?? [])->contains(fn ($filter) => ($filter['name'] ?? '') === 'type');
?>
<div class="admin-table-toolbar mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
        <form class="d-flex flex-wrap align-items-end gap-2 flex-grow-1" method="GET">
            <?php if(request()->filled('type')): ?>
                <input type="hidden" name="type" value="<?php echo e(request('type')); ?>">
            <?php endif; ?>

            <div class="admin-toolbar-search">
                <label class="form-label small text-muted mb-1">Search</label>
                <input name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search <?php echo e(strtolower($pageTitle)); ?>...">
            </div>

            <div class="admin-toolbar-field">
                <label class="form-label small text-muted mb-1">Column</label>
                <select class="form-select" name="search_column">
                    <option value="">All Columns</option>
                    <?php $__currentLoopData = $searchColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($column); ?>" <?php if(request('search_column') === $column): echo 'selected'; endif; ?>><?php echo e($columnLabels[$column]['label'] ?? str($column)->replace('_', ' ')->replace('.', ' ')->title()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <?php if(!empty($module['status_options']) && !empty($module['status_column'])): ?>
                <div class="admin-toolbar-field">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        <?php $__currentLoopData = $module['status_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if(request('status') === (string) $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if($hasTypeFilter && ! request()->filled('type')): ?>
                <div class="admin-toolbar-field">
                    <label class="form-label small text-muted mb-1">Channel</label>
                    <select class="form-select" name="type">
                        <option value="">All Channels</option>
                        <option value="customer" <?php if(request('type') === 'customer'): echo 'selected'; endif; ?>>Customer</option>
                        <option value="dealer" <?php if(request('type') === 'dealer'): echo 'selected'; endif; ?>>Dealer</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="admin-toolbar-date">
                <label class="form-label small text-muted mb-1">From</label>
                <input class="form-control" type="date" name="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>

            <div class="admin-toolbar-date">
                <label class="form-label small text-muted mb-1">To</label>
                <input class="form-control" type="date" name="date_to" value="<?php echo e(request('date_to')); ?>">
            </div>

            <button class="btn btn-outline-primary" type="submit" title="Filter"><i class="iconoir-search"></i><span class="d-none d-lg-inline ms-1">Filter</span></button>
            <a href="<?php echo e(route($module['route'].'.index', $resetQuery)); ?>" class="btn btn-outline-secondary" title="Reset"><i class="iconoir-refresh"></i><span class="d-none d-lg-inline ms-1">Reset</span></a>
        </form>

        <div class="d-flex flex-wrap justify-content-end align-items-end gap-2 admin-toolbar-actions">
            <?php if(request()->filled('type')): ?>
                <span class="badge bg-primary-subtle text-primary align-self-center px-3 py-2"><?php echo e(str(request('type'))->title()); ?> View</span>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-outline-secondary admin-toolbar-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Columns" aria-label="Columns"><i class="iconoir-view-grid"></i></button>
                <div class="dropdown-menu dropdown-menu-end p-2 admin-column-menu">
                    <?php $__currentLoopData = $module['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input m-0 admin-column-toggle" type="checkbox" data-column-index="<?php echo e($index); ?>" checked>
                            <span><?php echo e($column['label']); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <a class="btn btn-outline-success admin-toolbar-icon" href="<?php echo e(route($module['route'].'.export', ['format' => 'excel'] + $exportQuery)); ?>" title="Export Excel" aria-label="Export Excel">
                <svg width="18" height="18" viewBox="0 0 48 48" role="img" aria-hidden="true" focusable="false">
                    <path fill="#185C37" d="M30 4h12c1.1 0 2 .9 2 2v36c0 1.1-.9 2-2 2H30V4Z"/>
                    <path fill="#21A366" d="M30 4H16c-1.1 0-2 .9-2 2v7l16 9V4Z"/>
                    <path fill="#107C41" d="M14 13h16v10H14V13Z"/>
                    <path fill="#33C481" d="M14 23h16v10H14V23Z"/>
                    <path fill="#107C41" d="M14 33v9c0 1.1.9 2 2 2h14V33H14Z"/>
                    <path fill="#0B6A3A" d="M4 11.8 20 9v30L4 36.2c-1-.2-1.8-1-1.8-2V13.8c0-1 .8-1.8 1.8-2Z"/>
                    <path fill="#FFFFFF" d="m8.7 18 3.2 5.8L15.4 18h3.2l-4.9 7.8 5.1 8.2h-3.3L12 27.8 8.4 34H5.2l5.1-8.1L5.5 18h3.2Z"/>
                    <path fill="#FFFFFF" opacity=".6" d="M31.8 10H40v4h-8.2v-4Zm0 7H40v4h-8.2v-4Zm0 7H40v4h-8.2v-4Zm0 7H40v4h-8.2v-4Z"/>
                </svg>
            </a>
            <a class="btn btn-outline-danger admin-toolbar-icon" href="<?php echo e(route($module['route'].'.export', ['format' => 'pdf'] + $exportQuery)); ?>" title="Export PDF" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></a>
            <button class="btn btn-outline-secondary admin-toolbar-icon" type="button" onclick="window.print()" title="Print" aria-label="Print"><i class="fa-solid fa-print"></i></button>

            <?php if($module['can_delete'] ?? true): ?>
                <button class="btn btn-outline-danger admin-toolbar-icon" type="submit" form="bulkActionForm" onclick="return confirm('Delete selected records?')" title="Delete Selected" aria-label="Delete Selected"><i class="fa-solid fa-trash-can"></i></button>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/admin/shared/table-toolbar.blade.php ENDPATH**/ ?>