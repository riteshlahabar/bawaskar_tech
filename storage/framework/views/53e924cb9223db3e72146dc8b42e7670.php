<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<div class="card admin-form-card">
    <div class="card-body pt-3">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <form class="row g-2 align-items-end mb-3" method="GET" action="<?php echo e(route('admin.attendance.bulk')); ?>">
            <div class="col-md-3">
                <label class="form-label">Attendance Date</label>
                <input class="form-control" type="date" name="attendance_date" value="<?php echo e($date); ?>">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-primary" type="submit"><i class="iconoir-search me-1"></i>Load</button>
            </div>
        </form>

        <form method="POST" action="<?php echo e(route('admin.attendance.bulk.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="attendance_date" value="<?php echo e($date); ?>">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="bulk-select-col">Mark</th>
                            <th>Employee</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Working Minutes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesmen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $salesman): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($row = $existing[$salesman->id] ?? null); ?>
                            <tr>
                                <td class="bulk-select-col">
                                    <input type="hidden" name="rows[<?php echo e($index); ?>][salesman_id]" value="<?php echo e($salesman->id); ?>">
                                    <input class="form-check-input" type="checkbox" name="rows[<?php echo e($index); ?>][mark]" value="1" checked>
                                </td>
                                <td><?php echo e($salesman->name); ?></td>
                                <td><?php echo e($salesman->mobile ?? '-'); ?></td>
                                <td style="min-width:150px">
                                    <select class="form-select" name="rows[<?php echo e($index); ?>][status]">
                                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($key); ?>" <?php if(($row->status ?? 'present') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td style="min-width:130px"><input class="form-control" type="time" name="rows[<?php echo e($index); ?>][check_in_time]" value="<?php echo e($row?->check_in_at?->format('H:i')); ?>"></td>
                                <td style="min-width:130px"><input class="form-control" type="time" name="rows[<?php echo e($index); ?>][check_out_time]" value="<?php echo e($row?->check_out_at?->format('H:i')); ?>"></td>
                                <td style="min-width:140px"><input class="form-control" type="number" min="0" max="1440" name="rows[<?php echo e($index); ?>][working_minutes]" value="<?php echo e($row->working_minutes ?? 0); ?>"></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No active salesmen found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.attendance.index')); ?>">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save Bulk Attendance</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\attendance\bulk.blade.php ENDPATH**/ ?>