<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Medicine ERP, eCommerce and Salesman HRMS">
    <title><?php echo $__env->yieldContent('title', $pageTitle ?? 'Admin'); ?> | <?php echo e(config('admin.brand.name')); ?></title>
    <link rel="icon" href="<?php echo e(asset('fastkart-admin/images/favicon.png')); ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/ratio.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/vendors/scrollbar.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/vendors/animate.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/vendors/bootstrap.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/vendors/slick.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fastkart-admin/css/bawaskar-fastkart.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php echo $__env->make('admin.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="page-body-wrapper">
        <?php echo $__env->make('admin.partials.startbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="page-body">
            <div class="container-fluid">
                <?php echo $__env->make('admin.partials.page-title', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                <?php if(isset($errors) && $errors->any()): ?>
                    <div class="alert alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<script src="<?php echo e(asset('fastkart-admin/js/jquery-3.6.0.min.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/icons/feather-icon/feather.min.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/icons/feather-icon/feather-icon.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/scrollbar/simplebar.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/scrollbar/custom.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/config.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/sidebar-menu.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/sidebareffect.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/tooltip-init.js')); ?>"></script>
<script src="<?php echo e(asset('fastkart-admin/js/script.js')); ?>"></script>
<script src="<?php echo e(asset('admin-module-js/shared/table-toolbar.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>