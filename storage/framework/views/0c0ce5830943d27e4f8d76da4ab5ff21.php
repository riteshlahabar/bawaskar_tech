<?php
    $headerCartItems = collect(data_get($storeCart ?? [], 'items', collect()))->take(3);
    $headerCartCount = rtrim(rtrim(number_format((float) data_get($storeCart ?? [], 'count', 0), 3, '.', ''), '0'), '.');
    $headerCartCount = $headerCartCount !== '' ? $headerCartCount : '0';
    $headerCartTotal = (float) data_get($storeCart ?? [], 'grand_total', 0);
    $headerUserRole = $storeUser?->role === 'dealer' ? 'Dealer' : 'Customer';
?>

<ul class="right-side-menu">
    <li class="right-side">
        <div class="delivery-login-box">
            <div class="delivery-icon">
                <div class="search-box">
                    <i data-feather="search"></i>
                </div>
            </div>
        </div>
    </li>
    <li class="right-side">
        <a href="<?php echo e(route('store.page', ['page' => 'contact-us'])); ?>" class="delivery-login-box">
            <div class="delivery-icon">
                <i data-feather="phone-call"></i>
            </div>
            <div class="delivery-detail">
                <h6>24/7 Delivery</h6>
                <h5>+91 888 104 2340</h5>
            </div>
        </a>
    </li>
    <li class="right-side">
        <a href="<?php echo e(route('store.page', ['page' => 'wishlist'])); ?>" class="btn p-0 position-relative header-wishlist">
            <i data-feather="heart"></i>
        </a>
    </li>
    <li class="right-side">
        <div class="onhover-dropdown header-badge">
            <button type="button" class="btn p-0 position-relative header-wishlist">
                <i data-feather="shopping-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge"><?php echo e($headerCartCount); ?>

                    <span class="visually-hidden">cart items</span>
                </span>
            </button>

            <div class="onhover-div">
                <ul class="cart-list">
                    <?php $__empty_1 = true; $__currentLoopData = $headerCartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $product = $item['product'];
                            $productUrl = route('store.product', ['product' => $product->id]);
                            $imageUrl = optional($product->images->first())->url ?: asset('fastkart-store/images/vegetable/product/1.png');
                        ?>
                        <li class="product-box-contain">
                            <div class="drop-cart">
                                <a href="<?php echo e($productUrl); ?>" class="drop-image">
                                    <img src="<?php echo e($imageUrl); ?>" class="blur-up lazyload" alt="<?php echo e($product->name); ?>">
                                </a>

                                <div class="drop-contain">
                                    <a href="<?php echo e($productUrl); ?>">
                                        <h5><?php echo e($product->name); ?></h5>
                                    </a>
                                    <h6><span><?php echo e(rtrim(rtrim(number_format((float) $item['quantity'], 3, '.', ''), '0'), '.')); ?> x</span> Rs. <?php echo e(number_format((float) $item['unit_price'], 2)); ?></h6>
                                    <form method="POST" action="<?php echo e(route('store.cart.remove', ['productId' => $product->id])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="close-button close_button">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="product-box-contain">
                            <div class="drop-cart">
                                <div class="drop-contain">
                                    <h5>Your cart is empty.</h5>
                                    <h6>Add products to continue shopping.</h6>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="price-box">
                    <h5>Total :</h5>
                    <h4 class="theme-color fw-bold">Rs. <?php echo e(number_format($headerCartTotal, 2)); ?></h4>
                </div>

                <div class="button-group">
                    <a href="<?php echo e(route('store.page', ['page' => 'cart'])); ?>" class="btn btn-sm cart-button">View Cart</a>
                    <a href="<?php echo e(route('store.page', ['page' => 'checkout'])); ?>" class="btn btn-sm cart-button theme-bg-color text-white">Checkout</a>
                </div>
            </div>
        </div>
    </li>
    <li class="right-side onhover-dropdown">
        <div class="delivery-login-box">
            <div class="delivery-icon">
                <i data-feather="user"></i>
            </div>
            <div class="delivery-detail">
                <h6>Hello,</h6>
                <h5><?php echo e($storeUser?->name ?: 'My Account'); ?></h5>
            </div>
        </div>

        <div class="onhover-div onhover-div-login">
            <ul class="user-box-name">
                <?php if($storeUser): ?>
                    <li class="product-box-contain">
                        <a href="<?php echo e(route('store.page', ['page' => 'user-dashboard'])); ?>"><?php echo e($headerUserRole); ?> Dashboard</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="<?php echo e(route('store.page', ['page' => 'order-success'])); ?>">Recent Order</a>
                    </li>
                    <li class="product-box-contain">
                        <form method="POST" action="<?php echo e(route('store.auth.logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-link p-0 text-start text-decoration-none">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="product-box-contain">
                        <a href="<?php echo e(route('store.page', ['page' => 'login'])); ?>">Log In</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="<?php echo e(route('store.page', ['page' => 'sign-up'])); ?>">Register</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="<?php echo e(route('store.page', ['page' => 'forgot'])); ?>">Forgot Password</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
</ul>
<?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\store\partials\header-actions.blade.php ENDPATH**/ ?>