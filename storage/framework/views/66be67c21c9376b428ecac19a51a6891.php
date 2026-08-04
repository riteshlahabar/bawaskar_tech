<?php $__env->startSection('title','Email Templates'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
<?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-md-6 col-xl-4"><div class="card"><div class="card-body"><div class="d-flex align-items-center gap-3"><div class="bg-primary-subtle text-primary rounded p-3"><i data-feather="mail"></i></div><div><h5 class="mb-1"><?php echo e(str($template)->replace('-',' ')->title()); ?></h5><p class="text-muted mb-0">Fastkart responsive email layout</p></div></div><div class="mt-3"><a target="_blank" class="btn btn-primary" href="<?php echo e(route('admin.email-templates.show',$template)); ?>">Preview Template</a></div></div></div></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\email-templates\index.blade.php ENDPATH**/ ?>