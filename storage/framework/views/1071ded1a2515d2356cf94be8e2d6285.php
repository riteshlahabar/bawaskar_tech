<?php
    $groups = config('admin.groups', []);
    $isItemActive = function (array $item): bool {
        if (empty($item['route'])) return false;
        $active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']);
        if (! $active) return false;
        foreach (($item['params'] ?? []) as $key => $value) if ((string) request($key) !== (string) $value) return false;
        return true;
    };
    $itemUrl = fn (array $item): string => route($item['route'], $item['params'] ?? []);
    $icons = [
        'dashboard-erp'=>'home','dashboard-hrms'=>'activity','dealers'=>'shopping-bag','customers'=>'users','salesmen'=>'user-check','couriers'=>'truck',
        'customer-sales'=>'user','dealer-sales'=>'briefcase','customer-orders'=>'shopping-cart','dealer-orders'=>'shopping-cart','customer-proforma-invoices'=>'file-text','dealer-proforma-invoices'=>'file-text','customer-invoices'=>'file','dealer-invoices'=>'file','customer-dispatches'=>'truck','dealer-dispatches'=>'truck','customer-returns'=>'rotate-ccw','dealer-returns'=>'rotate-ccw',
        'products'=>'box','product-variants'=>'layers','product-related-products'=>'link-2','product-types'=>'tag','categories'=>'list','brands'=>'award','units'=>'sliders','inventory'=>'package','warehouses'=>'home','batches'=>'calendar','homepage-settings'=>'layout',
        'storefront-banners'=>'image','storefront-sections'=>'grid','storefront-section-products'=>'shopping-cart','storefront-service-blocks'=>'truck','storefront-footer-links'=>'link',
        'sf-row-1-main-banner'=>'layout','sf-row-2-small-banners'=>'layout','sf-row-3-shop-categories'=>'layout',
        'sf-row-4-product-section'=>'layout','sf-row-4-heading'=>'type','sf-row-4-products'=>'shopping-cart',
        'sf-row-5-bank-offers'=>'layout',
        'sf-row-6-product-special'=>'layout','sf-row-6-heading'=>'type','sf-row-6-products'=>'shopping-cart',
        'sf-row-7-strip-banner'=>'layout',
        'sf-row-8-product-section'=>'layout','sf-row-8-heading'=>'type','sf-row-8-products'=>'shopping-cart',
        'sf-row-9-product-section'=>'layout','sf-row-9-heading'=>'type','sf-row-9-products'=>'shopping-cart',
        'sf-row-10-two-banners'=>'layout',
        'sf-row-11-product-section'=>'layout','sf-row-11-heading'=>'type','sf-row-11-products'=>'shopping-cart',
        'sf-row-12-product-section'=>'layout','sf-row-12-heading'=>'type','sf-row-12-products'=>'shopping-cart',
        'sf-row-13-delivery-banner'=>'layout',
        'sf-row-14-product-section'=>'layout','sf-row-14-heading'=>'type','sf-row-14-products'=>'shopping-cart',
        'sf-row-15-product-section'=>'layout','sf-row-15-heading'=>'type','sf-row-15-products'=>'shopping-cart',
        'sf-row-16-blog'=>'layout',
        'payments'=>'credit-card','collections'=>'dollar-sign','outstanding'=>'trending-up','internal-expenses'=>'clipboard','expense-categories'=>'list','expense-subcategories'=>'menu',
        'timesheet'=>'clock','attendance'=>'check-circle','leaves'=>'calendar','bulk-attendance'=>'grid','dealer-visits'=>'map-pin','tour-plans'=>'map','expenses'=>'dollar-sign','salary'=>'briefcase','targets'=>'target','assets'=>'monitor',
        'notifications'=>'bell','languages'=>'globe','translations'=>'type','support'=>'headphones','reports'=>'bar-chart-2','email-templates'=>'mail'
    ];
    $groupIcons = ['peopleMenu'=>'users','salesMenu'=>'shopping-cart','productInventoryMenu'=>'package','financeMenu'=>'credit-card','companyExpenseMenu'=>'clipboard','storefrontMenu'=>'globe','systemMenu'=>'settings'];
?>

<style>
    .sidebar-wrapper .sidebar-link,
    .sidebar-wrapper .admin-sidebar-submenu-link,
    .sidebar-wrapper .submenu-title,
    .sidebar-wrapper .sidebar-main-title h6 {
        font-weight: 700 !important;
    }

    .sidebar-wrapper .sidebar-link span,
    .sidebar-wrapper .admin-sidebar-submenu-link span,
    .sidebar-wrapper .submenu-title span {
        font-weight: 700 !important;
    }

    .sidebar-wrapper .sidebar-link svg,
    .sidebar-wrapper .admin-sidebar-submenu-link svg,
    .sidebar-wrapper .submenu-title svg,
    .sidebar-wrapper .admin-sidebar-submenu-icon,
    .sidebar-wrapper .fa-angle-right,
    .sidebar-wrapper .sub-arrow {
        stroke-width: 2.4px !important;
        font-weight: 700 !important;
    }
</style>

<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="bawaskar-sidebar-brand" style="display:flex;align-items:center;gap:8px;">
                <img src="<?php echo e(asset('logo/logo.png')); ?>" alt="Dr Bawasakar Technology" style="width:32px;height:32px;object-fit:contain;border-radius:6px;background:#fff;padding:2px;">
                <span style="font-size:14px;line-height:1.1;color:#fff;display:flex;flex-direction:column;justify-content:center;">
                    <strong style="display:block;white-space:nowrap;">Dr Bawasakar</strong>
                    <small style="display:block;font-size:11px;color:#fff;">Technology</small>
                </span>
            </a>
            <div class="back-btn"><i class="fa fa-angle-left"></i></div>
        </div>

        <div class="logo-icon-wrapper">
            <a href="<?php echo e(route('admin.dashboard')); ?>">
                <img src="<?php echo e(asset('logo/logo.png')); ?>" alt="Dr Bawasakar Technology" style="width:32px;height:32px;object-fit:contain;display:block;margin:12px auto;background:#fff;border-radius:6px;padding:2px;">
            </a>
        </div>

        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"></li>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $groupOpen = collect($group['items'] ?? [])->contains(function($item) use ($isItemActive){
                                return !empty($item['children']) ? collect($item['children'])->contains(fn($child)=>$isItemActive($child)) : $isItemActive($item);
                            });
                        ?>

                        <?php if(!isset($group['id'])): ?>
                            <?php if(strtolower($group['label'] ?? '') !== 'navigation'): ?>
                                <li class="sidebar-main-title <?php echo e(strtolower($group['label'] ?? '') === 'hrms' ? 'sidebar-main-title-compact' : ''); ?>"><div><h6><?php echo e($group['label']); ?></h6></div></li>
                            <?php endif; ?>

                            <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!empty($item['children'])): ?>
                                    <?php $open = collect($item['children'])->contains(fn($child)=>$isItemActive($child)); ?>
                                    <li class="sidebar-list">
                                        <a class="sidebar-link sidebar-title <?php echo e($open?'active':''); ?>" href="javascript:void(0)">
                                            <i data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><span><?php echo e($item['label']); ?></span><span style="margin-left:auto;color:#fff;font-size:18px;line-height:1;"><i class="fa fa-angle-right"></i></span>
                                        </a>
                                        <ul class="sidebar-submenu" style="display:<?php echo e($open?'block':'none'); ?>">
                                            <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="admin-sidebar-submenu-link <?php echo e($isItemActive($child)?'active':''); ?>" href="<?php echo e($itemUrl($child)); ?>"><?php if(!str_starts_with($child['key'] ?? '', 'sf-row-')): ?>
    <i class="admin-sidebar-submenu-icon" data-feather="<?php echo e($icons[$child['key']] ?? 'circle'); ?>"></i>
<?php endif; ?>
<span><?php echo e($child['label']); ?></span></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav <?php echo e($isItemActive($item)?'active':''); ?>" href="<?php echo e($itemUrl($item)); ?>"><i data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><span><?php echo e($item['label']); ?></span><?php if(isset($item['badge'])): ?><span class="badge badge-light-primary ms-auto"><?php echo e($item['badge']); ?></span><?php endif; ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <li class="sidebar-list">
                                <a class="sidebar-link sidebar-title <?php echo e($groupOpen?'active':''); ?>" href="javascript:void(0)">
                                    <i data-feather="<?php echo e($groupIcons[$group['id']] ?? 'folder'); ?>"></i><span><?php echo e($group['label']); ?></span><span style="margin-left:auto;color:#fff;font-size:18px;line-height:1;"><i class="fa fa-angle-right"></i></span>
                                </a>
                                <ul class="sidebar-submenu" style="display:<?php echo e($groupOpen?'block':'none'); ?>">
                                    <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!empty($item['children'])): ?>
                                            <?php $open = collect($item['children'])->contains(fn($child)=>$isItemActive($child)); ?>
                                            <li><a class="submenu-title admin-sidebar-submenu-link <?php echo e($open?'active':''); ?>" href="javascript:void(0)"><?php if(!str_starts_with($item['key'] ?? '', 'sf-row-')): ?>
    <?php if(($group['id'] ?? '') !== 'storefrontMenu'): ?><i class="admin-sidebar-submenu-icon" data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><?php endif; ?>
<?php endif; ?>
<span><?php echo e($item['label']); ?></span><span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a><ul class="nav-sub-childmenu submenu-content" style="display:<?php echo e($open?'block':'none'); ?>"><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="admin-sidebar-submenu-link <?php echo e($isItemActive($child)?'active':''); ?>" href="<?php echo e($itemUrl($child)); ?>"><?php if(!str_starts_with($child['key'] ?? '', 'sf-row-')): ?>
    <i class="admin-sidebar-submenu-icon" data-feather="<?php echo e($icons[$child['key']] ?? 'circle'); ?>"></i>
<?php endif; ?>
<span><?php echo e($child['label']); ?></span></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></li>
                                        <?php else: ?>
                                            <li><a class="admin-sidebar-submenu-link <?php echo e($isItemActive($item)?'active':''); ?>" href="<?php echo e($itemUrl($item)); ?>"><?php if(!str_starts_with($item['key'] ?? '', 'sf-row-')): ?>
    <?php if(($group['id'] ?? '') !== 'storefrontMenu'): ?><i class="admin-sidebar-submenu-icon" data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><?php endif; ?>
<?php endif; ?>
<span><?php echo e($item['label']); ?></span></a></li>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\partials\startbar.blade.php ENDPATH**/ ?>