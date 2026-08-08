<?php
    $sectionKey = $sectionKey ?? 'top-selling-items';
    $sectionTitle = $sectionTitle ?? 'Top Selling Items';
    $products = collect($products ?? collect())->filter()->take(8)->values();
    $productColumns = $products->chunk(2)->values();
    $dealProduct = $dealProduct ?? null;
    $audience = $storeAudience ?? 'customer';

    if (! isset($showDealTimer)) {
        $showDealTimer = $dealProduct
            && $dealProduct->is_offer_active
            && $dealProduct->offer_end_at
            && $dealProduct->offer_end_at->isFuture();
    }

    if ($dealProduct) {
        $dealImage = optional($dealProduct->images->first())->url ?: asset('fastkart-store/images/grocery/deal/big.png');
        $dealUrl = route('store.product', ['product' => $dealProduct->id]);
        $dealPrice = (float) ($audience === 'dealer' ? $dealProduct->dealer_price : $dealProduct->customer_price);
        $dealMrp = (float) $dealProduct->mrp;
        $dealStock = max(0, (float) $dealProduct->available_stock);
        $progressWidth = min(100, max(16, (int) round(min($dealStock, 50) * 2)));
    }
?>

<?php if($products->isNotEmpty() || $dealProduct): ?>
    <section class="product-section product-section-3" id="home-section-<?php echo e($sectionKey); ?>">
        <div class="container-fluid-lg">
            <div class="title">
                <h2><?php echo e($sectionTitle); ?></h2>
            </div>

            <div class="row g-sm-4 g-3 align-items-stretch">
                <?php if($dealProduct): ?>
                    <div class="col-xxl-4 col-lg-5 order-lg-2">
                        <div class="product-bg-image wow fadeInUp h-100">
                            <div class="product-title product-warning">
                                <h2>Special Offer</h2>
                            </div>

                            <div class="product-box-4 product-box-3 rounded-0 h-100">
                                <div class="deal-box">
                                    <div class="circle-box">
                                        <div class="shape-circle">
                                            <img src="<?php echo e(asset('fastkart-store/images/grocery/circle.svg')); ?>" class="blur-up lazyload" alt="">
                                            <div class="shape-text">
                                                <h6>Hot <br> Deal</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-image">
                                    <a href="<?php echo e($dealUrl); ?>">
                                        <img src="<?php echo e($dealImage); ?>" class="img-fluid product-image blur-up lazyload" alt="<?php echo e($dealProduct->name); ?>">
                                    </a>

                                    <ul class="option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="<?php echo e($dealUrl); ?>">
                                                <i class="iconly-Show icli"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="product-detail text-center">
                                    <ul class="rating justify-content-center">
                                        <li><i data-feather="star" class="fill"></i></li>
                                        <li><i data-feather="star" class="fill"></i></li>
                                        <li><i data-feather="star" class="fill"></i></li>
                                        <li><i data-feather="star" class="fill"></i></li>
                                        <li><i data-feather="star"></i></li>
                                    </ul>

                                    <a href="<?php echo e($dealUrl); ?>">
                                        <h3 class="name w-100 mx-auto text-center text-title"><?php echo e($dealProduct->homepage_title ?: $dealProduct->name); ?></h3>
                                    </a>

                                    <h3 class="price theme-color d-flex justify-content-center">
                                        Rs. <?php echo e(number_format($dealPrice, 2)); ?>

                                        <?php if($dealMrp > $dealPrice): ?>
                                            <del class="delete-price">Rs. <?php echo e(number_format($dealMrp, 2)); ?></del>
                                        <?php endif; ?>
                                    </h3>

                                    <div class="progress custom-progressbar">
                                        <div class="progress-bar" style="width: <?php echo e($progressWidth); ?>%" role="progressbar"></div>
                                    </div>

                                    <h5 class="text-content">
                                        Stock : <span class="text-dark"><?php echo e(rtrim(rtrim(number_format($dealStock, 3, '.', ''), '0'), '.') ?: '0'); ?> items</span>
                                        <span class="ms-auto text-content">Hurry up offer end in</span>
                                    </h5>

                                    <?php if($showDealTimer): ?>
                                        <div class="timer timer-2 ms-0 my-4 homepage-deal-timer" data-end-at="<?php echo e($dealProduct->offer_end_at->toIso8601String()); ?>">
                                            <ul class="d-flex justify-content-center">
                                                <li><div class="counter"><div class="days"><h6>0</h6></div></div></li>
                                                <li><div class="counter"><div class="hours"><h6>0</h6></div></div></li>
                                                <li><div class="counter"><div class="minutes"><h6>0</h6></div></div></li>
                                                <li><div class="counter"><div class="seconds"><h6>0</h6></div></div></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($dealStock > 0): ?>
                                        <form method="POST" action="<?php echo e(route('store.cart.add')); ?>" class="mt-3">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="product_id" value="<?php echo e($dealProduct->id); ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn theme-bg-color text-white">Add To Cart</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="<?php echo e($dealProduct ? 'col-xxl-8 col-lg-7 order-lg-1' : 'col-12'); ?>">
                    <div class="slider-5_2 img-slider">
                        <?php $__currentLoopData = $productColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $columnProducts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <?php $__currentLoopData = $columnProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php echo $__env->make('store.partials.product-card-4', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\top-selling-section.blade.php ENDPATH**/ ?>