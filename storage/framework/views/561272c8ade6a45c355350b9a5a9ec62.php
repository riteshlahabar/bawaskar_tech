<?php $__env->startSection('title', 'Dashboard ERP'); ?>
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
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Today Sales</p><h4 class="mb-0">Rs. <?php echo e(number_format((float) $todaySales, 2)); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Month Sales</p><h4 class="mb-0">Rs. <?php echo e(number_format((float) $monthSales, 2)); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Pending Dealers</p><h4 class="mb-0"><?php echo e($pendingDealers->count()); ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Low Stock Alerts</p><h4 class="mb-0"><?php echo e($lowStock->count()); ?></h4></div></div></div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h4 class="card-title">Recent Sale Orders</h4></div>
                    <div class="col-auto"><a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Order</th><th>Channel</th><th>Party</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="fw-semibold"><?php echo e($order->order_no); ?></a><small class="d-block text-muted"><?php echo e($order->created_at->format('d-m-Y')); ?></small></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?php echo e(strtoupper($order->order_type)); ?></span></td>
                                    <td><?php echo e($order->dealer?->dealerProfile?->firm_name ?? $order->customer?->name ?? '-'); ?></td>
                                    <td>Rs. <?php echo e(number_format((float) $order->grand_total, 2)); ?></td>
                                    <td><span class="badge bg-<?php echo e(in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning'); ?>-subtle text-<?php echo e(in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning'); ?>"><?php echo e(str($order->status)->replace('_', ' ')->title()); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No sale orders yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Dealer Sale Orders</h4></div>
                            <div class="col-auto"><a href="<?php echo e(route('admin.orders.index', ['type' => 'dealer'])); ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Order</th><th>Dealer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $pendingDealerOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="fw-semibold"><?php echo e($order->order_no); ?></a><small class="d-block text-muted"><?php echo e($order->salesman?->name ?? '-'); ?></small></td>
                                            <td><?php echo e($order->dealer?->dealerProfile?->firm_name ?? $order->dealer?->name ?? '-'); ?></td>
                                            <td>Rs. <?php echo e(number_format((float) $order->grand_total, 2)); ?></td>
                                            <td><span class="badge bg-warning-subtle text-warning"><?php echo e(str($order->status)->replace('_', ' ')->title()); ?></span></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending dealer sale orders.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Customer Sale Orders</h4></div>
                            <div class="col-auto"><a href="<?php echo e(route('admin.orders.index', ['type' => 'customer'])); ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $pendingCustomerOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="fw-semibold"><?php echo e($order->order_no); ?></a><small class="d-block text-muted"><?php echo e($order->created_at->format('d-m-Y')); ?></small></td>
                                            <td><?php echo e($order->customer?->name ?? '-'); ?></td>
                                            <td>Rs. <?php echo e(number_format((float) $order->grand_total, 2)); ?></td>
                                            <td><span class="badge bg-warning-subtle text-warning"><?php echo e(str($order->status)->replace('_', ' ')->title()); ?></span></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending customer sale orders.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">ERP Action Centre</h4></div>
            <div class="card-body">
                <a href="<?php echo e(route('admin.dealers.index', ['status' => 'pending_approval'])); ?>" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-shop me-2 text-primary"></i>Dealer approvals</span><strong><?php echo e($pendingDealers->count()); ?></strong></a>
                <a href="<?php echo e(route('admin.orders.index', ['status' => 'admin_review'])); ?>" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-cart me-2 text-success"></i>Admin review orders</span><strong><?php echo e($stats[3]['value']); ?></strong></a>
                <a href="<?php echo e(route('admin.products.index')); ?>" class="d-flex justify-content-between py-3 text-body"><span><i class="iconoir-box me-2 text-info"></i>Products</span><strong><?php echo e($stats[2]['value']); ?></strong></a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title">Low Stock</h4></div>
            <div class="card-body pt-0">
                <?php $__empty_1 = true; $__currentLoopData = $lowStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong><?php echo e($stock->product?->name); ?></strong><small class="d-block text-muted"><?php echo e($stock->warehouse?->name); ?> - <?php echo e($stock->batch_no); ?></small></div><span class="badge bg-danger-subtle text-danger align-self-center"><?php echo e($stock->quantity); ?></span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center py-3 mb-0">No low-stock alerts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\dashboard\erp.blade.php ENDPATH**/ ?>