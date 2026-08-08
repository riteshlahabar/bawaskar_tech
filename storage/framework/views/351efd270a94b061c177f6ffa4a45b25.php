<?php
    $navigation = $storefrontNavigation ?? [];
    $navCategories = collect(data_get($navigation, 'categories', collect()));
    $navProductTypes = collect(data_get($navigation, 'productTypes', collect()));
    $navFeaturedProducts = collect(data_get($navigation, 'featuredProducts', collect()));
    $navHomeSections = collect(data_get($homeContent ?? [], 'productSections', collect()))
        ->map(fn ($entry) => data_get($entry, 'section'))
        ->filter(fn ($section) => $section && $section->title)
        ->values();

    if ($navHomeSections->isEmpty()) {
        $navHomeSections = collect(data_get($homeContent ?? [], 'sections', collect()))
            ->values()
            ->filter(fn ($section) => $section && $section->title)
            ->values();
    }

    $shopUrl = route('store.page', ['page' => 'shop-left-sidebar']);
?>

<ul class="navbar-nav">
    <li class="nav-item dropdown">
        <a class="nav-link ps-0 dropdown-toggle" href="<?php echo e(route('store.home')); ?>" data-bs-toggle="dropdown">Home</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('store.home')); ?>">Home</a></li>
            <?php $__empty_1 = true; $__currentLoopData = $navHomeSections->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $homeSection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li>
                    <a class="dropdown-item" href="<?php echo e(route('store.home')); ?>#home-section-<?php echo e($homeSection->section_key); ?>">
                        <?php echo e($homeSection->title); ?>

                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li><a class="dropdown-item" href="<?php echo e(route('store.home')); ?>#products">Featured Products</a></li>
            <?php endif; ?>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="<?php echo e($shopUrl); ?>" data-bs-toggle="dropdown">Categories</a>
        <ul class="dropdown-menu">
            <?php $__empty_1 = true; $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $categoryUrl = $category->slug ? route('store.category', ['category' => $category->slug]) : $shopUrl;
                    $children = collect($category->children ?? [])->take(5);
                ?>
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="<?php echo e($categoryUrl); ?>">
                        <span><?php echo e($category->name); ?></span>
                        <small><?php echo e((int) ($category->products_count ?? 0)); ?></small>
                    </a>
                </li>
                <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a class="dropdown-item ps-4 d-flex justify-content-between" href="<?php echo e($childCategory->slug ? route('store.category', ['category' => $childCategory->slug]) : $shopUrl); ?>">
                            <span><?php echo e($childCategory->name); ?></span>
                            <small><?php echo e((int) ($childCategory->products_count ?? 0)); ?></small>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li><a class="dropdown-item" href="<?php echo e($shopUrl); ?>">All Categories</a></li>
            <?php endif; ?>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="<?php echo e($shopUrl); ?>" data-bs-toggle="dropdown">Products</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e($shopUrl); ?>">All Products</a></li>
            <?php $__currentLoopData = $navProductTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="<?php echo e(route('store.page', ['page' => 'shop-left-sidebar', 'product_type' => $productType['slug']])); ?>">
                        <span><?php echo e($productType['name']); ?></span>
                        <small><?php echo e((int) ($productType['products_count'] ?? 0)); ?></small>
                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </li>

    <li class="nav-item dropdown dropdown-mega">
        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown">Mega Menu</a>
        <div class="dropdown-menu dropdown-menu-2 dropdown-menu-left">
            <div class="row">
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Categories</h5>
                    <?php $__empty_1 = true; $__currentLoopData = $navCategories->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a class="dropdown-item" href="<?php echo e($category->slug ? route('store.category', ['category' => $category->slug]) : $shopUrl); ?>"><?php echo e($category->name); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <a class="dropdown-item" href="<?php echo e($shopUrl); ?>">All Categories</a>
                    <?php endif; ?>
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Product Types</h5>
                    <?php $__currentLoopData = $navProductTypes->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'shop-left-sidebar', 'product_type' => $productType['slug']])); ?>"><?php echo e($productType['name']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Featured Products</h5>
                    <?php $__empty_1 = true; $__currentLoopData = $navFeaturedProducts->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a class="dropdown-item" href="<?php echo e(route('store.product', ['product' => $product->getKey()])); ?>"><?php echo e($product->name); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <a class="dropdown-item" href="<?php echo e($shopUrl); ?>">All Products</a>
                    <?php endif; ?>
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header"><?php echo e($storeUser ? 'My Account' : 'Customer'); ?></h5>
                    <?php if($storeUser): ?>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'user-dashboard'])); ?>">Dashboard</a>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-success'])); ?>">Recent Order</a>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-tracking'])); ?>">Order Tracking</a>
                    <?php else: ?>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'login'])); ?>">Customer Login</a>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'sign-up'])); ?>">Customer Register</a>
                        <a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-tracking'])); ?>">Order Tracking</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="<?php echo e($storeUser ? route('store.page', ['page' => 'user-dashboard']) : route('store.page', ['page' => 'login'])); ?>" data-bs-toggle="dropdown"><?php echo e($storeUser?->role === 'dealer' ? 'Dealer Account' : 'Dealer'); ?></a>
        <ul class="dropdown-menu">
            <?php if($storeUser?->role === 'dealer'): ?>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'user-dashboard'])); ?>">Dealer Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-success'])); ?>">Recent Order</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-tracking'])); ?>">Track Order</a></li>
            <?php else: ?>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'login', 'role' => 'dealer'])); ?>">Dealer Login</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'sign-up', 'role' => 'dealer'])); ?>">Dealer Registration</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'order-tracking'])); ?>">Track Order</a></li>
            <?php endif; ?>
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="<?php echo e(route('store.page', ['page' => 'about-us'])); ?>" data-bs-toggle="dropdown">About Us</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'about-us'])); ?>">Company Profile</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('store.page', ['page' => 'faq'])); ?>">FAQ</a></li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link no-dropdown-arrow" href="<?php echo e(route('store.page', ['page' => 'contact-us'])); ?>">Contact Us</a>
    </li>
</ul><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\navbar.blade.php ENDPATH**/ ?>