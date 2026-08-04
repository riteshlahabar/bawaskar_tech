<?php $__currentLoopData = $homeProductSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $homeProductSection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $section = $homeProductSection['section'];
        $sectionProducts = collect($homeProductSection['products']);
    ?>
    <section class="product-section-3">
        <div class="container-fluid-lg">
            <div class="title">
                <h2><?php echo e($section->title); ?></h2>
                <?php if($section->subtitle): ?>
                    <span class="title-leaf"><span><?php echo e($section->subtitle); ?></span></span>
                <?php endif; ?>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-7_1 arrow-slider img-slider">
                        <?php $__currentLoopData = $sectionProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('store.partials.product-card-4', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\home-product-sections.blade.php ENDPATH**/ ?>