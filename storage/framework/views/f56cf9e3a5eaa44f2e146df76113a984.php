<?php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $price = (float) $product->customer_price;
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
?>

<div>
    <div class="product-box-3 h-100 wow fadeInUp">
        <div class="product-header">
            <div class="product-image">
                <a href="<?php echo e($productUrl); ?>">
                    <img src="<?php echo e($imageUrl); ?>" class="img-fluid blur-up lazyload" alt="<?php echo e($product->name); ?>">
                </a>
                <ul class="product-option">
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                        <a href="<?php echo e($productUrl); ?>"><i data-feather="eye"></i></a>
                    </li>
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                        <a href="javascript:void(0)" class="notifi-wishlist"><i data-feather="heart"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="product-footer">
            <div class="product-detail">
                <span class="span-name"><?php echo e(data_get($product, 'category.name') ?: 'Product'); ?></span>
                <a href="<?php echo e($productUrl); ?>"><h5 class="name"><?php echo e($product->name); ?></h5></a>
                <p class="text-content mt-1 mb-2"><?php echo e(str($product->description ?: 'Quality farmer product')->limit(75)); ?></p>
                <h6 class="unit"><?php echo e($unitName); ?></h6>
                <h5 class="price">
                    <span class="theme-color">Rs. <?php echo e(number_format($price, 2)); ?></span>
                    <?php if($mrp > $price): ?>
                        <del>Rs. <?php echo e(number_format($mrp, 2)); ?></del>
                    <?php endif; ?>
                </h5>
                <?php if($discount > 0): ?>
                    <h6 class="theme-color"><?php echo e($discount); ?>% off</h6>
                <?php endif; ?>
                <div class="add-to-cart-box bg-white">
                    <button class="btn btn-add-cart addcart-button" onclick="location.href='<?php echo e($productUrl); ?>'">View Product</button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\product-grid-card.blade.php ENDPATH**/ ?>