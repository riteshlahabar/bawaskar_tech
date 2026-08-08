<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body pt-3">
        <div class="d-flex justify-content-end gap-2 mb-3 flex-wrap">
            <a href="<?php echo e(route($module['route'].'.index', request()->only(['type','placement','section_key','row_title']))); ?>" class="btn btn-outline-secondary">Back</a>
            <?php if($module['can_edit'] ?? true): ?>
                <a href="<?php echo e(route($module['route'].'.edit', array_merge([$record->getKey()], request()->only(['type','placement','section_key','row_title'])))); ?>" class="btn btn-primary">Edit</a>
            <?php endif; ?>
            <?php if($module['key'] === 'orders'): ?>
                <form method="POST" action="<?php echo e(route('admin.orders.convert-to-proforma', $record->getKey())); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-info" type="submit">Convert to PI</button></form>
                <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'order', 'id' => $record->getKey()])); ?>" target="_blank">Print A4</a>
                <a class="btn btn-outline-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'order', 'id' => $record->getKey()])); ?>">PDF</a>
            <?php endif; ?>
            <?php if($module['key'] === 'proforma-invoices'): ?>
                <form method="POST" action="<?php echo e(route('admin.proforma-invoices.convert-to-invoice', $record->getKey())); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-info" type="submit">Convert to Sale Invoice</button></form>
                <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'proforma', 'id' => $record->getKey()])); ?>" target="_blank">Print A4</a>
                <a class="btn btn-outline-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'proforma', 'id' => $record->getKey()])); ?>">PDF</a>
            <?php endif; ?>
            <?php if($module['key'] === 'invoices'): ?>
                <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.sales-documents.print', ['document' => 'invoice', 'id' => $record->getKey()])); ?>" target="_blank">Print A4</a>
                <a class="btn btn-outline-danger" href="<?php echo e(route('admin.sales-documents.pdf', ['document' => 'invoice', 'id' => $record->getKey()])); ?>">PDF</a>
            <?php endif; ?>
        </div>

        <div class="row g-3">
            <?php $__currentLoopData = $module['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($value = data_get($record, $column['key'])); ?>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block"><?php echo e($column['label']); ?></small>
                        <strong>
                            <?php if(($column['type'] ?? '') === 'money'): ?>
                                Rs. <?php echo e(number_format((float) $value, 2)); ?>

                            <?php elseif(($column['type'] ?? '') === 'boolean'): ?>
                                <?php echo e($value ? 'Yes' : 'No'); ?>

                            <?php elseif(($column['type'] ?? '') === 'date'): ?>
                                <?php echo e($value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '-'); ?>

                            <?php elseif(($column['type'] ?? '') === 'datetime'): ?>
                                <?php echo e($value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '-'); ?>

                            <?php else: ?>
                                <?php echo e($value !== null && $value !== '' ? $value : '-'); ?>

                            <?php endif; ?>
                        </strong>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\shared\show.blade.php ENDPATH**/ ?>