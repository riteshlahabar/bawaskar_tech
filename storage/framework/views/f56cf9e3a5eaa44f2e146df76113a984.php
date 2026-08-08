<?php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
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
                <?php if($isOutOfStock): ?>
                    <h6 class="theme-color">Out of Stock</h6>
                <?php elseif($isLowStock): ?>
                    <h6 class="theme-color"><?php echo e($product->low_stock_text ?: 'Low Stock'); ?></h6>
                <?php elseif($discount > 0): ?>
                    <h6 class="theme-color"><?php echo e($discount); ?>% off</h6>
                <?php endif; ?>
                <div class="add-to-cart-box bg-white">
                    <?php if($isOutOfStock): ?>
                        <button class="btn btn-add-cart addcart-button" disabled>Out of Stock</button>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('store.cart.add')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-add-cart addcart-button">Add To Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\product-grid-card.blade.php ENDPATH**/ ?>