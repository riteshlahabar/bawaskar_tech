<?php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $price = (float) $product->customer_price;
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $categoryName = data_get($product, 'category.name') ?: 'Product';
?>

<div>
    <div class="product-box-4 wow fadeInUp">
        <div class="product-image product-image-2">
            <a href="<?php echo e($productUrl); ?>">
                <img src="<?php echo e($imageUrl); ?>" class="img-fluid blur-up lazyload" alt="<?php echo e($product->name); ?>">
            </a>

            <ul class="option">
                <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                    <a href="<?php echo e($productUrl); ?>">
                        <i class="iconly-Show icli"></i>
                    </a>
                </li>
                <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                    <a href="javascript:void(0)" class="notifi-wishlist">
                        <i class="iconly-Heart icli"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="product-detail">
            <ul class="rating">
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star" class="fill"></i></li>
                <li><i data-feather="star"></i></li>
            </ul>
            <a href="<?php echo e($productUrl); ?>">
                <h5 class="name text-title"><?php echo e($product->name); ?></h5>
            </a>
            <h5 class="sold text-content">
                <span class="theme-color price">Rs. <?php echo e(number_format($price, 2)); ?></span>
                <?php if($mrp > $price): ?>
                    <del>Rs. <?php echo e(number_format($mrp, 2)); ?></del>
                <?php endif; ?>
            </h5>
            <div class="price-qty">
                <h5 class="text-content"><?php echo e($categoryName); ?> / <?php echo e($unitName); ?></h5>
                <button class="add-button addcart-button btn buy-button text-light" onclick="location.href='<?php echo e($productUrl); ?>'">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <?php if($discount > 0): ?>
                <div class="label-tag mt-2"><span><?php echo e($discount); ?>% off</span></div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\product-card-4.blade.php ENDPATH**/ ?>