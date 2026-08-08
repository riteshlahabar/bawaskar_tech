<?php
    $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/grocery/product/fruits-vegetables/1.png');
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $categoryName = data_get($product, 'category.name') ?: 'Product';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
    $cardOuterClass = trim((string) ($cardOuterClass ?? ''));
?>

<div@if($cardOuterClass !== '') class="<?php echo e($cardOuterClass); ?>"<?php endif; ?>>
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
                <?php if($isOutOfStock): ?>
                    <button class="add-button addcart-button btn buy-button text-light" disabled>
                        <i class="fa-solid fa-ban"></i>
                    </button>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('store.cart.add')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="add-button addcart-button btn buy-button text-light">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php if($isOutOfStock): ?>
                <div class="label-tag mt-2"><span>Out of Stock</span></div>
            <?php elseif($isLowStock): ?>
                <div class="label-tag mt-2"><span><?php echo e($product->low_stock_text ?: 'Low Stock'); ?></span></div>
            <?php elseif($discount > 0): ?>
                <div class="label-tag mt-2"><span><?php echo e($discount); ?>% off</span></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\product-card-4.blade.php ENDPATH**/ ?>