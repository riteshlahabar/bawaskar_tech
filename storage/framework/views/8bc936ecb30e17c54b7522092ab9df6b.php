<div class="page-title">
    <div class="row">
        <div class="col-sm-6"><h3><?php echo e($pageTitle ?? trim($__env->yieldContent('title')) ?: 'Admin'); ?></h3></div>
        <div class="col-sm-6"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><i data-feather="home"></i></a></li><?php $__currentLoopData = ($breadcrumbs ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $breadcrumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li class="breadcrumb-item <?php echo e($loop->last ? 'active' : ''); ?>"><?php echo e($breadcrumb); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ol></div>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\partials\page-title.blade.php ENDPATH**/ ?>