<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3"><?php echo e($record->title); ?></h5>
                <p class="mb-1"><strong>Type:</strong> <?php echo e(str($record->section_type)->replace('_', ' ')->title()); ?></p>
                <p class="mb-1"><strong>Layout:</strong> <?php echo e($record->layout_type ?: '-'); ?></p>
<p class="mb-1"><strong>Category:</strong> <?php echo e($record->category?->name ?: '-'); ?></p>
                <p class="mb-1"><strong>Product Limit:</strong> <?php echo e($record->product_limit); ?></p>
                <p class="mb-1"><strong>Item Limit:</strong> <?php echo e($record->item_limit ?: '-'); ?></p>
                <p class="mb-1"><strong>Image Note:</strong> <?php echo e($record->image_size_note ?: '-'); ?></p>
                <p class="mb-1"><strong>Sort Order:</strong> <?php echo e($record->sort_order); ?></p>
                <p class="mb-0"><strong>Status:</strong> <?php echo e($record->is_active ? 'Active' : 'Inactive'); ?></p>

                <div class="d-flex gap-2 mt-4">
                    <a class="btn btn-primary" href="<?php echo e(route('admin.homepage-settings.edit', $record->id)); ?>">Edit Section</a>
                    <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.homepage-settings.index')); ?>">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Section Content</h5>
                        <small class="text-muted">Products are assigned from Product Add/Edit using Homepage Section Title. This page only defines homepage row/design.</small>
                    </div>
                    <a class="btn btn-success" href="<?php echo e(route('admin.homepage-setting-items.create', ['section_id' => $record->id, 'row_title' => $record->title.' Content'])); ?>">
                        Add Content
                    </a>
                </div>

                <?php if(in_array($record->section_type, ['product_section', 'category_section', 'top_selling_section'], true)): ?>
                    <div class="alert alert-info">
                        <strong>No duplicate product adding required.</strong>
                        <?php if($record->section_type === 'product_section'): ?>
                            This row shows active products from selected category/source.
                        <?php elseif($record->section_type === 'top_selling_section'): ?>
                            This row uses products where <strong>Top Selling Product</strong> is enabled. Timer card uses product where <strong>Deal Timer Product</strong> is enabled.
                        <?php else: ?>
                            This row uses active categories.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Image/Icon</th>
                                <th>Title</th>
                                <th>Coupon</th>
                                <th>Sort</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php ($preview = $item->image_path ?: $item->logo_image_path); ?>
                                        <?php if($preview): ?>
                                            <img src="<?php echo e(asset($preview)); ?>" style="width:70px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" alt="">
                                        <?php else: ?>
                                            <?php echo e($item->icon_key ?: '-'); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($item->title ?: $item->highlight_text ?: '-'); ?></td>
                                    <td><?php echo e($item->coupon_code ?: '-'); ?></td>
                                    <td><?php echo e($item->sort_order); ?></td>
                                    <td><?php echo e($item->is_active ? 'Active' : 'Inactive'); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.homepage-setting-items.edit', $item->id)); ?>">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No content added yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\homepage-settings\show.blade.php ENDPATH**/ ?>