<?php
    $categoryImages = [
        'fastkart-store/images/grocery/category/1.png',
        'fastkart-store/images/grocery/category/2.png',
        'fastkart-store/images/grocery/category/3.png',
        'fastkart-store/images/grocery/category/4.png',
        'fastkart-store/images/grocery/category/5.png',
        'fastkart-store/images/grocery/category/6.png',
        'fastkart-store/images/grocery/category/7.png',
        'fastkart-store/images/grocery/category/8.png',
    ];
?>

<?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $categoryUrl = route('store.category', ['category' => $category->slug]);
        $categoryImage = asset($categoryImages[$loop->index % count($categoryImages)]);
    ?>
    <div>
        <div class="category-box-list">
            <a href="<?php echo e($categoryUrl); ?>" class="category-name">
                <h4><?php echo e($category->name); ?></h4>
                <h6><?php echo e((int) ($category->products_count ?? 0)); ?> items</h6>
            </a>
            <div class="category-box-view">
                <a href="<?php echo e($categoryUrl); ?>">
                    <img src="<?php echo e($categoryImage); ?>" class="img-fluid blur-up lazyload" alt="<?php echo e($category->name); ?>">
                </a>
                <button onclick="location.href='<?php echo e($categoryUrl); ?>';" class="btn shop-button">
                    <span>Shop Now</span>
                    <i class="fas fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div>
        <div class="category-box-list">
            <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>" class="category-name">
                <h4>Products</h4>
                <h6>0 items</h6>
            </a>
            <div class="category-box-view">
                <a href="<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>">
                    <img src="<?php echo e(asset('fastkart-store/images/grocery/category/1.png')); ?>" class="img-fluid blur-up lazyload" alt="Products">
                </a>
                <button onclick="location.href='<?php echo e(route('store.page', ['page'=>'shop-left-sidebar'])); ?>';" class="btn shop-button">
                    <span>Shop Now</span>
                    <i class="fas fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/store/partials/category-slider.blade.php ENDPATH**/ ?>