<?php $__env->startSection('title', 'Dashboard HRMS'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-<?php echo e($stat['color']); ?>-subtle text-<?php echo e($stat['color']); ?> thumb-xl rounded-circle d-flex align-items-center justify-content-center">
                            <i class="<?php echo e($stat['icon']); ?> fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1"><?php echo e($stat['label']); ?></p>
                            <h3 class="mb-1"><?php echo e(number_format((float) $stat['value'])); ?></h3>
                            <small class="text-muted"><?php echo e($stat['trend']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row">
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Issued Assets</p><h4 class="mb-0"><?php echo e(number_format((float) $issuedAssets)); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Month Payroll</p><h4 class="mb-0">Rs. <?php echo e(number_format((float) $monthlyPayroll, 2)); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Target</p><h4 class="mb-0">Rs. <?php echo e(number_format((float) $targetTotal, 2)); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Achieved</p><h4 class="mb-0">Rs. <?php echo e(number_format((float) $achievedTotal, 2)); ?></h4></div></div></div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h4 class="card-title">Today Attendance</h4></div>
                    <div class="col-auto"><a href="<?php echo e(route('admin.attendance.index')); ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Salesman</th><th>Check In</th><th>Check Out</th><th>Working Minutes</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $todayAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($attendance->salesman?->name ?? '-'); ?></td>
                                    <td><?php echo e($attendance->check_in_at?->format('h:i A') ?? '-'); ?></td>
                                    <td><?php echo e($attendance->check_out_at?->format('h:i A') ?? '-'); ?></td>
                                    <td><?php echo e($attendance->working_minutes); ?></td>
                                    <td><span class="badge bg-<?php echo e(in_array($attendance->status, ['present', 'late']) ? 'success' : 'warning'); ?>-subtle text-<?php echo e(in_array($attendance->status, ['present', 'late']) ? 'success' : 'warning'); ?>"><?php echo e(str($attendance->status)->replace('_', ' ')->title()); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No attendance marked today.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Recent Dealer Visits</h4></div>
            <div class="card-body pt-0">
                <?php $__empty_1 = true; $__currentLoopData = $recentVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <div><strong><?php echo e($visit->salesman?->name ?? '-'); ?></strong><small class="d-block text-muted"><?php echo e($visit->dealer?->dealerProfile?->firm_name ?? $visit->dealer?->name ?? '-'); ?> - <?php echo e($visit->purpose ?? 'Visit'); ?></small></div>
                        <span class="text-muted"><?php echo e($visit->visited_at?->format('d-m-Y h:i A')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center py-3 mb-0">No dealer visits found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">HRMS Action Centre</h4></div>
            <div class="card-body">
                <a href="<?php echo e(route('admin.leaves.index', ['status' => 'pending'])); ?>" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-calendar-minus me-2 text-warning"></i>Pending leave</span><strong><?php echo e($pendingLeaves->count()); ?></strong></a>
                <a href="<?php echo e(route('admin.expenses.index', ['status' => 'pending'])); ?>" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-receive-dollars me-2 text-danger"></i>Pending expenses</span><strong>Rs. <?php echo e(number_format((float) $pendingExpenseAmount, 2)); ?></strong></a>
                <a href="<?php echo e(route('admin.tour-plans.index')); ?>" class="d-flex justify-content-between py-3 text-body"><span><i class="iconoir-route me-2 text-primary"></i>Upcoming tours</span><strong><?php echo e($upcomingTours->count()); ?></strong></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Pending Leaves</h4></div>
            <div class="card-body pt-0">
                <?php $__empty_1 = true; $__currentLoopData = $pendingLeaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong><?php echo e($leave->salesman?->name ?? '-'); ?></strong><small class="d-block text-muted"><?php echo e(str($leave->leave_type)->title()); ?> - <?php echo e($leave->from_date?->format('d-m-Y')); ?></small></div><span class="badge bg-warning-subtle text-warning align-self-center">Pending</span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center py-3 mb-0">No pending leave.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="card-title">Pending Expenses</h4></div>
            <div class="card-body pt-0">
                <?php $__empty_1 = true; $__currentLoopData = $pendingExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong><?php echo e($expense->salesman?->name ?? '-'); ?></strong><small class="d-block text-muted"><?php echo e(str($expense->expense_type)->title()); ?> - <?php echo e($expense->expense_date?->format('d-m-Y')); ?></small></div><span class="text-danger fw-semibold">Rs. <?php echo e(number_format((float) $expense->amount, 2)); ?></span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center py-3 mb-0">No pending expenses.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\dashboard\hrms.blade.php ENDPATH**/ ?>