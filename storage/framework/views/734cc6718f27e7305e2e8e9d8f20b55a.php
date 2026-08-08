<?php
    $productTypeLabels = data_get($storefrontNavigation ?? [], 'productTypeLabels', []);
    $productTypeTitle = $selectedProductType ? ($productTypeLabels[$selectedProductType] ?? str($selectedProductType)->replace(['_', '-'], ' ')->headline()->toString()) : null;
    $pageTitle = $selectedCategory?->name ?: ($productTypeTitle ?: ($searchQuery ? 'Search Products' : 'Shop Products'));
    $shopProducts = collect(method_exists($products, 'items') ? $products->items() : $products);
?>

    <!-- Breadcrumb Section Start -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2><?php echo e($pageTitle); ?></h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo e(route('store.home')); ?>"><i class="fa-solid fa-house"></i></a></li>
                                <li class="breadcrumb-item active"><?php echo e($pageTitle); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Shop Section Start -->
    <section class="section-b-space shop-section">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-custom-3">
                    <div class="left-box wow fadeInUp">
                        <div class="shop-left-sidebar">
                            <div class="back-button"><h3><i class="fa-solid fa-arrow-left"></i> Back</h3></div>
                            <div class="accordion custom-accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"><span>Categories</span></button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <ul class="category-list custom-padding custom-height">
                                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li>
                                                        <div class="form-check ps-0 m-0 category-list-box">
                                                            <a class="form-check-label w-100 d-flex justify-content-between" href="<?php echo e(route('store.category', ['category' => $category->slug])); ?>">
                                                                <span class="name"><?php echo e($category->name); ?></span>
                                                                <span class="number">(<?php echo e((int) ($category->products_count ?? 0)); ?>)</span>
                                                            </a>
                                                        </div>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-custom-">
                    <div class="show-button">
                        <div class="filter-button-group mt-0">
                            <div class="filter-button d-inline-block d-lg-none"><a><i class="fa-solid fa-filter"></i> Filter Menu</a></div>
                        </div>
                        <div class="top-filter-menu">
                            <div class="category-dropdown"><h5 class="text-content">Showing <?php echo e($shopProducts->count()); ?> products</h5></div>
                        </div>
                    </div>

                    <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-2 row-cols-md-3 row-cols-2 product-list-section">
                        <?php $__empty_1 = true; $__currentLoopData = $shopProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('store.partials.product-grid-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12"><div class="alert alert-light mb-0">No products found in this category.</div></div>
                        <?php endif; ?>
                    </div>

                    <?php if(method_exists($products, 'links')): ?>
                        <nav class="custom-pagination mt-4"><?php echo e($products->links()); ?></nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Shop Section End --><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\shop-products-main.blade.php ENDPATH**/ ?>