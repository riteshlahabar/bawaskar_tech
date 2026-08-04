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
        'products'=>'box','categories'=>'list','brands'=>'award','units'=>'sliders','inventory'=>'package','warehouses'=>'home','batches'=>'calendar',
        'payments'=>'credit-card','collections'=>'dollar-sign','outstanding'=>'trending-up','internal-expenses'=>'clipboard','expense-categories'=>'list','expense-subcategories'=>'menu',
        'timesheet'=>'clock','attendance'=>'check-circle','leaves'=>'calendar','bulk-attendance'=>'grid','dealer-visits'=>'map-pin','tour-plans'=>'map','expenses'=>'dollar-sign','salary'=>'briefcase','targets'=>'target','assets'=>'monitor',
        'notifications'=>'bell','translations'=>'globe','support'=>'headphones','reports'=>'bar-chart-2','email-templates'=>'mail'
    ];
?>
<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="bawaskar-sidebar-brand"><span class="brand-mark brand-mark-light">B</span><span>Bawaskar ERP</span></a>
            <div class="back-btn"><i class="fa fa-angle-left"></i></div><div class="toggle-sidebar"><i data-feather="menu" class="status_toggle middle sidebar-toggle"></i></div>
        </div>
        <div class="logo-icon-wrapper"><a href="<?php echo e(route('admin.dashboard')); ?>"><span class="brand-mark brand-mark-light">B</span></a></div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu"><ul class="sidebar-links" id="simple-bar"><li class="back-btn"></li>
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $groupOpen = collect($group['items'] ?? [])->contains(function($item) use ($isItemActive){
                            return !empty($item['children']) ? collect($item['children'])->contains(fn($child)=>$isItemActive($child)) : $isItemActive($item);
                        });
                    ?>
                    <?php if(!isset($group['id'])): ?>
                        <li class="sidebar-main-title"><div><h6><?php echo e($group['label']); ?></h6></div></li>
                        <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!empty($item['children'])): ?>
                                <?php $open=collect($item['children'])->contains(fn($child)=>$isItemActive($child)); ?>
                                <li class="sidebar-list"><a class="sidebar-link sidebar-title <?php echo e($open?'active':''); ?>" href="javascript:void(0)"><i data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><span><?php echo e($item['label']); ?></span></a>
                                    <ul class="sidebar-submenu" style="display:<?php echo e($open?'block':'none'); ?>"><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="<?php echo e($isItemActive($child)?'active':''); ?>" href="<?php echo e($itemUrl($child)); ?>"><?php echo e($child['label']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                                </li>
                            <?php else: ?>
                                <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav <?php echo e($isItemActive($item)?'active':''); ?>" href="<?php echo e($itemUrl($item)); ?>"><i data-feather="<?php echo e($icons[$item['key']] ?? 'circle'); ?>"></i><span><?php echo e($item['label']); ?></span><?php if(isset($item['badge'])): ?><span class="badge badge-light-primary ms-auto"><?php echo e($item['badge']); ?></span><?php endif; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <li class="sidebar-list"><a class="sidebar-link sidebar-title <?php echo e($groupOpen?'active':''); ?>" href="javascript:void(0)"><i data-feather="<?php echo e(['peopleMenu'=>'users','salesMenu'=>'shopping-cart','productInventoryMenu'=>'package','financeMenu'=>'credit-card','companyExpenseMenu'=>'clipboard','systemMenu'=>'settings'][$group['id']] ?? 'folder'); ?>"></i><span><?php echo e($group['label']); ?></span></a>
                            <ul class="sidebar-submenu" style="display:<?php echo e($groupOpen?'block':'none'); ?>">
                                <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!empty($item['children'])): ?>
                                        <?php $open=collect($item['children'])->contains(fn($child)=>$isItemActive($child)); ?>
                                        <li><a class="submenu-title <?php echo e($open?'active':''); ?>" href="javascript:void(0)"><?php echo e($item['label']); ?><span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a><ul class="nav-sub-childmenu submenu-content" style="display:<?php echo e($open?'block':'none'); ?>"><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="<?php echo e($isItemActive($child)?'active':''); ?>" href="<?php echo e($itemUrl($child)); ?>"><?php echo e($child['label']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></li>
                                    <?php else: ?>
                                        <li><a class="<?php echo e($isItemActive($item)?'active':''); ?>" href="<?php echo e($itemUrl($item)); ?>"><?php echo e($item['label']); ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul></div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/admin/partials/startbar.blade.php ENDPATH**/ ?>