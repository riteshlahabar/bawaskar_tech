<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<div class="card admin-table-card" data-table-key="<?php echo e($module['key']); ?>">
    <div class="card-body pt-3">
        <div class="d-flex justify-content-end gap-2 mb-3">
            <?php if($module['key'] === 'salary'): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#salaryModal"><i class="iconoir-dollar-circle me-1"></i>Generate Salary</button>
            <?php endif; ?>
            <?php if($module['can_create'] ?? true): ?>
                <a href="<?php echo e(route($module['route'].'.create', request()->only(['type']))); ?>" class="btn btn-primary"><i class="iconoir-plus-circle me-1"></i>Add <?php echo e($module['singular']); ?></a>
            <?php endif; ?>
        </div>
        <?php echo $__env->make('admin.shared.table-toolbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <form id="bulkActionForm" method="POST" action="<?php echo e(route($module['route'].'.bulk-destroy')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <?php $__currentLoopData = request()->query(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="bulk-select-col"><input class="form-check-input admin-select-all" type="checkbox" title="Select all"></th>
                            <?php $__currentLoopData = $module['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th data-column-index="<?php echo e($index); ?>"><?php echo e($column['label']); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="bulk-select-col"><input class="form-check-input admin-row-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo e($record->getKey()); ?>"></td>
                                <?php $__currentLoopData = $module['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
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
                                    ?>
                                    <td data-column-index="<?php echo e($index); ?>">
                                        <?php if(($column['type'] ?? '') === 'image'): ?>
                                            <?php if($imageUrl): ?>
                                                <?php ($imageModalId = 'imagePreview'.$module['key'].$record->getKey().$index); ?>
                                                <button type="button" class="admin-image-thumb-btn" data-bs-toggle="modal" data-bs-target="#<?php echo e($imageModalId); ?>" title="Preview image">
                                                    <img src="<?php echo e($imageUrl); ?>" class="admin-image-thumb" alt="<?php echo e($column['label']); ?>">
                                                </button>
                                                <div class="modal fade admin-image-preview-modal" id="<?php echo e($imageModalId); ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"><?php echo e($column['label']); ?> Preview</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="<?php echo e($imageUrl); ?>" class="admin-image-preview" alt="<?php echo e($column['label']); ?> preview">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        <?php elseif(($column['type'] ?? '') === 'boolean'): ?>
                                            <span class="badge <?php echo e($value ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'); ?>"><?php echo e($value ? 'Active' : 'Inactive'); ?></span>
                                        <?php elseif(($column['type'] ?? '') === 'status'): ?>
                                            <span class="badge bg-<?php echo e(in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning')); ?>-subtle text-<?php echo e(in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning')); ?>"><?php echo e(str($value)->replace('_', ' ')->title()); ?></span>
                                        <?php elseif(($column['type'] ?? '') === 'money'): ?>
                                            Rs. <?php echo e(number_format((float) $value, 2)); ?>

                                        <?php elseif(($column['type'] ?? '') === 'date'): ?>
                                            <?php echo e($value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '-'); ?>

                                        <?php elseif(($column['type'] ?? '') === 'datetime'): ?>
                                            <?php echo e($value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '-'); ?>

                                        <?php else: ?>
                                            <?php echo e($value !== null && $value !== '' ? $value : '-'); ?>

                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="text-end">
                                    <div class="dropdown admin-row-action">
                                        <button class="btn btn-sm btn-outline-secondary admin-row-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" aria-label="Actions">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end admin-row-action-menu">
                                            <a class="dropdown-item" href="<?php echo e(route($module['route'].'.show', $record->getKey())); ?>"><i class="iconoir-eye"></i><span>View</span></a>
                                            <?php if($module['can_edit'] ?? true): ?>
                                                <a class="dropdown-item" href="<?php echo e(route($module['route'].'.edit', $record->getKey())); ?>"><i class="iconoir-edit-pencil"></i><span>Edit</span></a>
                                            <?php endif; ?>

                                            <?php if($module['key'] === 'dealers' && $record->status === 'pending_approval'): ?>
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="button" data-bs-toggle="modal" data-bs-target="#approveDealer<?php echo e($record->id); ?>"><i class="iconoir-check-circle"></i><span>Approve Dealer</span></button>
                                            <?php endif; ?>

                                            <?php if($module['key'] === 'orders'): ?>
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="button" data-bs-toggle="modal" data-bs-target="#orderStatus<?php echo e($record->id); ?>"><i class="iconoir-check-circle"></i><span>Update Status</span></button>
                                                <button class="dropdown-item text-info" type="submit" form="convertOrderToPi<?php echo e($record->id); ?>"><i class="iconoir-page"></i><span>Convert to PI</span></button>
                                                <a class="dropdown-item" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'order', 'id' => $record->getKey()])); ?>" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'order', 'id' => $record->getKey()])); ?>"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            <?php endif; ?>

                                            <?php if($module['key'] === 'proforma-invoices'): ?>
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-info" type="submit" form="convertPiToInvoice<?php echo e($record->id); ?>"><i class="iconoir-receipt"></i><span>Convert to Sale Invoice</span></button>
                                                <a class="dropdown-item" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'proforma', 'id' => $record->getKey()])); ?>" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'proforma', 'id' => $record->getKey()])); ?>"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            <?php endif; ?>

                                            <?php if($module['key'] === 'invoices'): ?>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'invoice', 'id' => $record->getKey()])); ?>" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'invoice', 'id' => $record->getKey()])); ?>"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            <?php endif; ?>

                                            <?php if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending'): ?>
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="submit" form="decisionForm<?php echo e($module['key']); ?><?php echo e($record->id); ?>" name="status" value="approved"><i class="iconoir-check-circle"></i><span>Approve</span></button>
                                                <button class="dropdown-item text-danger" type="submit" form="decisionForm<?php echo e($module['key']); ?><?php echo e($record->id); ?>" name="status" value="rejected"><i class="iconoir-xmark-circle"></i><span>Reject</span></button>
                                            <?php endif; ?>

                                            <?php if($module['can_delete'] ?? true): ?>
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-danger" type="button" onclick="if(confirm('Delete this record?')) document.getElementById('deleteForm<?php echo e($module['key']); ?><?php echo e($record->id); ?>').submit();"><i class="fa-solid fa-trash-can"></i><span>Delete</span></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="<?php echo e(count($module['columns']) + 2); ?>" class="text-center py-5 text-muted">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($module['can_delete'] ?? true): ?>
                <form id="deleteForm<?php echo e($module['key']); ?><?php echo e($record->id); ?>" method="POST" action="<?php echo e(route($module['route'].'.destroy', $record->getKey())); ?>" class="d-none"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?></form>
            <?php endif; ?>
            <?php if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending'): ?>
                <form id="decisionForm<?php echo e($module['key']); ?><?php echo e($record->id); ?>" method="POST" action="<?php echo e(route('admin.'.$module['key'].'.decision', $record->id)); ?>" class="d-none"><?php echo csrf_field(); ?></form>
            <?php endif; ?>
            <?php if($module['key'] === 'orders'): ?>
                <form id="convertOrderToPi<?php echo e($record->id); ?>" method="POST" action="<?php echo e(route('admin.orders.convert-to-proforma', $record->getKey())); ?>" class="d-none"><?php echo csrf_field(); ?></form>
            <?php endif; ?>
            <?php if($module['key'] === 'proforma-invoices'): ?>
                <form id="convertPiToInvoice<?php echo e($record->id); ?>" method="POST" action="<?php echo e(route('admin.proforma-invoices.convert-to-invoice', $record->getKey())); ?>" class="d-none"><?php echo csrf_field(); ?></form>
            <?php endif; ?>
            <?php if($module['key'] === 'dealers' && $record->status === 'pending_approval'): ?>
                <div class="modal fade" id="approveDealer<?php echo e($record->id); ?>"><div class="modal-dialog"><form class="modal-content" method="POST" action="<?php echo e(route('admin.dealers.approve', $record->id)); ?>"><?php echo csrf_field(); ?><div class="modal-header"><h5>Approve <?php echo e($record->name); ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Assign Salesman</label><select name="salesman_id" class="form-select" required><?php $__currentLoopData = \App\Models\User::where('role', 'salesman')->where('status', 'active')->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><label class="form-label mt-3">Credit Limit</label><input name="credit_limit" type="number" step="0.01" min="0" class="form-control" value="0"></div><div class="modal-footer"><button class="btn btn-success">Approve & Assign</button></div></form></div></div>
            <?php endif; ?>
            <?php if($module['key'] === 'orders'): ?>
                <div class="modal fade" id="orderStatus<?php echo e($record->id); ?>"><div class="modal-dialog"><form class="modal-content" method="POST" action="<?php echo e(route('admin.orders.change-status', $record->id)); ?>"><?php echo csrf_field(); ?><div class="modal-header"><h5>Update <?php echo e($record->order_no); ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><select name="status" class="form-select"><?php $__currentLoopData = ['salesman_review','admin_review','approved','packing','dispatched','delivered','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($st); ?>" <?php if($record->status === $st): echo 'selected'; endif; ?>><?php echo e(str($st)->replace('_', ' ')->title()); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="modal-footer"><button class="btn btn-primary">Update</button></div></form></div></div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="mt-3"><?php echo e($records->links()); ?></div>
    </div>
</div>

<?php if($module['key'] === 'salary'): ?>
    <div class="modal fade" id="salaryModal"><div class="modal-dialog"><form class="modal-content" method="POST" action="<?php echo e(route('admin.salary.generate')); ?>"><?php echo csrf_field(); ?><div class="modal-header"><h5>Generate Monthly Salary</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row"><div class="col-6"><label>Year</label><input class="form-control" type="number" name="salary_year" value="<?php echo e(now()->year); ?>" required></div><div class="col-6"><label>Month</label><select class="form-select" name="salary_month"><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($m); ?>" <?php if($m === now()->month): echo 'selected'; endif; ?>><?php echo e(DateTime::createFromFormat('!m', $m)->format('F')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div></div></div><div class="modal-footer"><button class="btn btn-success">Generate</button></div></form></div></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('admin-module-js/shared/table-toolbar.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/admin/shared/index.blade.php ENDPATH**/ ?>