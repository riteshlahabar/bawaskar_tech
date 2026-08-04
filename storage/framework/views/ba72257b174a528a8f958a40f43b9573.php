<?php
    $languages = collect($storeLanguages ?? []);
    $currentLanguage = $currentStoreLanguage ?? $languages->first();
?>

<div class="language-box-2 dropdown d-xl-flex d-none">
    <button class="btn language-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-globe"></i>
        <span><?php echo e($currentLanguage?->name ?? 'English'); ?></span>
    </button>
    <ul class="dropdown-menu language-dropdown">
        <?php $__empty_1 = true; $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo e(($currentLanguage?->code === $language->code) ? 'active' : ''); ?>" href="<?php echo e(route('store.language', ['locale' => $language->code])); ?>">
                    <span><?php echo e($language->name); ?></span>
                    <?php if($language->native_name && $language->native_name !== $language->name): ?>
                        <small><?php echo e($language->native_name); ?></small>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li><span class="dropdown-item active">English</span></li>
        <?php endif; ?>
    </ul>
</div><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views/store/partials/language-selector.blade.php ENDPATH**/ ?>