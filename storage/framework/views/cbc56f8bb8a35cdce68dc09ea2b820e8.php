<?php
    $detailImages = $product->images->isNotEmpty() ? $product->images : collect([(object) ['url' => asset('fastkart-store/images/product/category/1.jpg')]]);
    $audience = $storeAudience ?? 'customer';
    $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) $product->mrp;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs';
    $availableStock = (float) $product->available_stock;
    $lowStockAlert = (float) optional($product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
    $activeVariants = collect($product->variants ?? collect())->where('is_active', true)->values();
?>

<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row"><div class="col-12"><div class="breadcrumb-contain">
            <h2><?php echo e($product->name); ?></h2>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('store.home')); ?>"><i class="fa-solid fa-house"></i></a></li>
                <?php if($product->category): ?><li class="breadcrumb-item"><a href="<?php echo e(route('store.category', ['category' => $product->category->slug])); ?>"><?php echo e($product->category->name); ?></a></li><?php endif; ?>
                <li class="breadcrumb-item active"><?php echo e($product->name); ?></li>
            </ol></nav>
        </div></div></div>
    </div>
</section>

<section class="product-section">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-xxl-9 col-xl-8 col-lg-7 wow fadeInUp">
                <div class="row g-4">
                    <div class="col-xl-6 wow fadeInUp">
                        <div class="product-left-box">
                            <div class="row g-2">
                                <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
                                    <div class="product-main-2 no-arrow">
                                        <?php $__currentLoopData = $detailImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div><div class="slider-image"><img src="<?php echo e($image->url); ?>" class="img-fluid image_zoom_cls-<?php echo e($loop->index); ?> blur-up lazyload" alt="<?php echo e($product->name); ?>"></div></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                                    <div class="left-slider-image-2 left-slider no-arrow slick-top">
                                        <?php $__currentLoopData = $detailImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div><div class="sidebar-image"><img src="<?php echo e($image->url); ?>" class="img-fluid blur-up lazyload" alt="<?php echo e($product->name); ?>"></div></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="right-box-contain">
                            <?php if($discount > 0): ?><h6 class="offer-top"><?php echo e($discount); ?>% Off</h6><?php endif; ?>
                            <h2 class="name"><?php echo e($product->name); ?></h2>
                            <div class="price-rating">
                                <h3 class="theme-color price">Rs. <?php echo e(number_format($price, 2)); ?> <?php if($mrp > $price): ?><del class="text-content">Rs. <?php echo e(number_format($mrp, 2)); ?></del><?php endif; ?></h3>
                            </div>
                            <div class="product-contain"><p><?php echo e($product->description ?: 'Quality product for farmer requirements.'); ?></p></div>
                            <div class="pickup-box">
                                <div class="product-info"><ul class="product-info-list product-info-list-2">
                                    <li>SKU : <a href="javascript:void(0)"><?php echo e($product->sku); ?></a></li>
                                    <li>Category : <a href="javascript:void(0)"><?php echo e(data_get($product, 'category.name') ?: 'Product'); ?></a></li>
                                    <li>Brand : <a href="javascript:void(0)"><?php echo e(data_get($product, 'brand.name') ?: 'Bawaskar'); ?></a></li>
                                    <li>Unit : <a href="javascript:void(0)"><?php echo e($unitName); ?></a></li>
                                    <li>Available Stock : <a href="javascript:void(0)"><?php echo e(number_format($availableStock, 2)); ?></a></li>
                                    <li>Status : <a href="javascript:void(0)"><?php echo e($isOutOfStock ? 'Out of Stock' : ($isLowStock ? ($product->low_stock_text ?: 'Low Stock') : 'In Stock')); ?></a></li>
                                </ul></div>
                            </div>

                            <?php if($activeVariants->isNotEmpty()): ?>
                                <div class="product-package mb-3">
                                    <div class="product-title"><h4>Available Options</h4></div>
                                    <ul class="select-package">
                                        <?php $__currentLoopData = $activeVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>
                                                <a href="javascript:void(0)" class="<?php echo e($variant->is_default ? 'active' : ''); ?>">
                                                    <?php echo e($variant->group_name); ?>: <?php echo e($variant->value); ?>

                                                    <?php if((float) $variant->price_difference !== 0.0): ?>
                                                        (<?php echo e((float) $variant->price_difference > 0 ? '+' : ''); ?>Rs. <?php echo e(number_format((float) $variant->price_difference, 2)); ?>)
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="note-box product-package">
                                <?php if($isOutOfStock): ?>
                                    <button class="btn btn-md bg-dark cart-button text-white w-100" disabled>Out of Stock</button>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('store.cart.add')); ?>" class="row g-2 align-items-center">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                                        <div class="col-sm-4">
                                            <input type="number" class="form-control" name="quantity" value="1" min="0.001" step="0.001">
                                        </div>
                                        <div class="col-sm-8">
                                            <button type="submit" class="btn btn-md bg-dark cart-button text-white w-100">Add To Cart</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if($product->additional_info || $product->care_instructions || $product->manufacturer_details || $product->seller_name || $product->detail_banner_image): ?>
    <section class="product-list-section section-b-space">
        <div class="container-fluid-lg">
            <?php if($product->detail_banner_image): ?>
                <a href="<?php echo e($product->detail_banner_url ?: 'javascript:void(0)'); ?>" class="d-block mb-4">
                    <img src="<?php echo e(asset($product->detail_banner_image)); ?>" class="img-fluid blur-up lazyload" alt="<?php echo e($product->name); ?>">
                </a>
            <?php endif; ?>
            <div class="row g-4">
                <?php if($product->additional_info): ?>
                    <div class="col-md-6"><h4>Additional Info</h4><p class="text-content"><?php echo e($product->additional_info); ?></p></div>
                <?php endif; ?>
                <?php if($product->care_instructions): ?>
                    <div class="col-md-6"><h4>Care Instructions</h4><p class="text-content"><?php echo e($product->care_instructions); ?></p></div>
                <?php endif; ?>
                <?php if($product->manufacturer_details): ?>
                    <div class="col-md-6"><h4><?php echo e($product->manufacturer_title ?: 'Manufacturer Details'); ?></h4><p class="text-content"><?php echo e($product->manufacturer_details); ?></p></div>
                <?php endif; ?>
                <?php if($product->seller_name): ?>
                    <div class="col-md-6"><h4>Seller Information</h4><p class="text-content mb-1"><?php echo e($product->seller_name); ?></p><?php if($product->seller_contact): ?><p class="text-content mb-1"><?php echo e($product->seller_contact); ?></p><?php endif; ?> <?php if($product->seller_address): ?><p class="text-content mb-0"><?php echo e($product->seller_address); ?></p><?php endif; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if(collect($relatedProducts)->isNotEmpty()): ?>
    <section class="product-list-section section-b-space">
        <div class="container-fluid-lg">
            <div class="title"><h2>Related Products</h2></div>
            <div class="slider-6_1 product-wrapper">
                <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('store.partials.product-card-4', ['product' => $relatedProduct], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\product-detail-main.blade.php ENDPATH**/ ?>