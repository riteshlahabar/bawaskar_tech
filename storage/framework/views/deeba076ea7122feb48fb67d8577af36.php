<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<?php
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image', 'image_multiple'], true));
    $submenuQueryKeys = ['type', 'placement', 'section_key', 'row_title'];
    $fieldNames = collect($module['fields'] ?? [])->pluck('name')->filter()->values()->all();
    $optionAttributes = $optionAttributes ?? [];
?>
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">

            <div class="card-body pt-3">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e($record ? route($module['route'].'.update', array_merge([$record->getKey()], request()->only($submenuQueryKeys))) : route($module['route'].'.store', request()->only($submenuQueryKeys))); ?>" <?php if($hasUpload): ?> enctype="multipart/form-data" <?php endif; ?>>
                    <?php echo csrf_field(); ?>
                    <?php if($record): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                    <?php $__currentLoopData = request()->only($submenuQueryKeys); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queryKey => $queryValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(is_scalar($queryValue) && ! in_array($queryKey, $fieldNames, true)): ?>
                            <input type="hidden" name="<?php echo e($queryKey); ?>" value="<?php echo e($queryValue); ?>">
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="row g-3">
                        <?php $__currentLoopData = $module['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(($field['display_only'] ?? false) || (($field['create_only'] ?? false) && $record) || (($field['edit_only'] ?? false) && ! $record)) continue; ?>
                            <?php ($type = $field['type'] ?? 'text'); ?>
                            <?php ($visibilitySource = $field['visibility_field'] ?? null); ?>
                            <?php ($visibilitySectionTypes = array_values(array_filter((array) ($field['show_for_section_types'] ?? [])))); ?>
                            <?php ($visibilityLayoutTypes = array_values(array_filter((array) ($field['show_for_layout_types'] ?? [])))); ?>
                            <?php ($hasVisibility = filled($visibilitySource) && ($visibilitySectionTypes !== [] || $visibilityLayoutTypes !== [])); ?>

                            <?php if($type === 'section_heading'): ?>
                                <div class="col-12 <?php echo e($hasVisibility ? 'admin-conditional-field' : ''); ?>"
                                    <?php if($hasVisibility): ?>
                                        data-visibility-source="<?php echo e($visibilitySource); ?>"
                                        data-visibility-section-types="<?php echo e(implode(',', $visibilitySectionTypes)); ?>"
                                        data-visibility-layout-types="<?php echo e(implode(',', $visibilityLayoutTypes)); ?>"
                                        style="display:none;"
                                    <?php endif; ?>>
                                    <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;"><?php echo e($field['label']); ?></div>
                                </div>
                                <?php continue; ?>
                            <?php endif; ?>

                            <?php if(empty($field['name'])) continue; ?>
                            <?php ($name = $field['name'] ?? ''); ?>
                            <?php ($value = old($name, $formData[$name] ?? ($field['default'] ?? null))); ?>
                            <?php ($rulesList = array_map('strval', (array) ($field['rules'] ?? []))); ?>
                            <?php ($hasRequiredRule = collect($rulesList)->contains(fn ($rule) => str_starts_with($rule, 'required'))); ?>
                            <?php ($hasConditionalRequiredRule = collect($rulesList)->contains(fn ($rule) => str_starts_with($rule, 'required_with') || str_starts_with($rule, 'required_if') || str_starts_with($rule, 'required_without'))); ?>
                            <?php ($forcedRequiredIndicator = (bool) ($field['force_required_indicator'] ?? false)); ?>
                            <?php ($isRequired = $hasRequiredRule || $forcedRequiredIndicator); ?>
                            <?php ($requiredIndicator = '*'); ?>

                            <?php if($type === 'hidden'): ?>
                                <input type="hidden" name="<?php echo e($name); ?>" value="<?php echo e($value); ?>">
                                <?php continue; ?>
                            <?php endif; ?>

                            <?php ($lockedBySubmenu = in_array($module['key'] ?? '', ['storefront-banners', 'storefront-sections'], true) && in_array($name, ['placement', 'section_key'], true) && request()->filled($name)); ?>

                            <?php if($lockedBySubmenu): ?>
                                <input type="hidden" name="<?php echo e($name); ?>" value="<?php echo e($value); ?>">
                                <?php continue; ?>
                            <?php endif; ?>

                            <div class="<?php echo e($field['col'] ?? 'col-md-6'); ?> <?php echo e($hasVisibility ? 'admin-conditional-field' : ''); ?>"
                                <?php if($hasVisibility): ?>
                                    data-visibility-source="<?php echo e($visibilitySource); ?>"
                                    data-visibility-section-types="<?php echo e(implode(',', $visibilitySectionTypes)); ?>"
                                    data-visibility-layout-types="<?php echo e(implode(',', $visibilityLayoutTypes)); ?>"
                                    style="display:none;"
                                <?php endif; ?>>
                                <?php if($type === 'checkbox'): ?>
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" class="form-check-input" name="<?php echo e($name); ?>" value="1" id="<?php echo e($name); ?>" <?php if((bool) $value): echo 'checked'; endif; ?>>
                                        <label class="form-check-label" for="<?php echo e($name); ?>"><?php echo e($field['label']); ?></label>
                                        <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <?php if(! empty($field['help'])): ?><small class="text-muted d-block mt-1"><?php echo e($field['help']); ?></small><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <label class="form-label">
                                        <?php echo e($field['label']); ?>

                                        <?php if($isRequired): ?>
                                            <span class="text-danger" title="Required field"><?php echo e($requiredIndicator); ?></span>
                                        <?php endif; ?>
                                    </label>

                                    <?php if($type === 'select'): ?>
                                        <select class="form-select <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="<?php echo e($name); ?>" id="<?php echo e($name); ?>" data-option-attributes='<?php echo json_encode($optionAttributes[$name] ?? [], 15, 512) ?>' <?php if($isRequired): echo 'required'; endif; ?>>
                                            <option value="">Select <?php echo e($field['label']); ?></option>
                                            <?php $__currentLoopData = $options[$name] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php ($attrs = $optionAttributes[$name][$key] ?? []); ?>
                                                <option value="<?php echo e($key); ?>" <?php if((string) $value === (string) $key): echo 'selected'; endif; ?>
                                                    <?php $__currentLoopData = $attrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($attrValue !== null && $attrValue !== ''): ?>
                                                            data-<?php echo e(\Illuminate\Support\Str::kebab($attrName)); ?>="<?php echo e($attrValue); ?>"
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e($label); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    <?php elseif($type === 'textarea'): ?>
                                        <textarea rows="<?php echo e($field['rows'] ?? 4); ?>" class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="<?php echo e($name); ?>" <?php if($isRequired): echo 'required'; endif; ?>><?php echo e($value); ?></textarea>
                                    <?php elseif($type === 'image_multiple'): ?>
                                        <input class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="file" name="<?php echo e($name); ?>[]" accept="image/*" multiple <?php if($isRequired && ! $record): echo 'required'; endif; ?>>
                                        <?php if($record && method_exists($record, 'images') && $record->relationLoaded('images')): ?>
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                <?php $__currentLoopData = $record->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <a href="<?php echo e(asset($img->path)); ?>" target="_blank">
                                                        <img src="<?php echo e(asset($img->path)); ?>" style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                                                    </a>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif(in_array($type, ['file', 'image'], true)): ?>
                                        <input class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="file" name="<?php echo e($name); ?>" accept="<?php echo e($field['accept'] ?? ($type === 'image' ? 'image/*' : '')); ?>" <?php if($isRequired && ! $record): echo 'required'; endif; ?>>
                                        <?php if($value): ?>
                                            <small class="text-muted d-block mt-1">
                                                Current file: <a href="<?php echo e(asset($value)); ?>" target="_blank">View</a>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <input class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="<?php echo e($type); ?>" name="<?php echo e($name); ?>" value="<?php echo e($type === 'password' ? '' : $value); ?>" <?php if($isRequired): echo 'required'; endif; ?> step="<?php echo e($field['step'] ?? null); ?>" placeholder="<?php echo e($field['placeholder'] ?? ''); ?>">
                                    <?php endif; ?>

                                    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php if(! empty($field['help'])): ?><small class="text-muted"><?php echo e($field['help']); ?></small><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="<?php echo e(route($module['route'].'.index', request()->only(['type','placement','section_key','row_title']))); ?>">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i><?php echo e($record ? 'Update' : 'Save'); ?> <?php echo e($module['singular']); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function syncConditionalFields(form) {
        var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
        if (!conditionalBlocks.length) {
            return;
        }

        conditionalBlocks.forEach(function (block) {
            var sourceName = block.dataset.visibilitySource;
            var source = form.elements.namedItem(sourceName);
            if (!source || !('options' in source)) {
                return;
            }

            var selectedOption = source.options[source.selectedIndex] || null;
            var selectedValue = source.value || '';
            var optionAttributeMap = {};

            if (source.dataset.optionAttributes) {
                try {
                    optionAttributeMap = JSON.parse(source.dataset.optionAttributes);
                } catch (error) {
                    optionAttributeMap = {};
                }
            }

            var selectedOptionAttributes = selectedValue && optionAttributeMap[selectedValue] ? optionAttributeMap[selectedValue] : {};
            var sectionType = selectedOption ? (selectedOption.dataset.sectionType || '') : '';
            var layoutType = selectedOption ? (selectedOption.dataset.layoutType || '') : '';

            if (!sectionType && selectedOptionAttributes.section_type) {
                sectionType = selectedOptionAttributes.section_type;
            }

            if (!layoutType && selectedOptionAttributes.layout_type) {
                layoutType = selectedOptionAttributes.layout_type;
            }

            var allowedSectionTypes = (block.dataset.visibilitySectionTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var allowedLayoutTypes = (block.dataset.visibilityLayoutTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var isVisible = Boolean(selectedValue);

            if (isVisible && allowedSectionTypes.length) {
                isVisible = allowedSectionTypes.indexOf(sectionType) !== -1;
            }

            if (isVisible && allowedLayoutTypes.length) {
                isVisible = allowedLayoutTypes.indexOf(layoutType) !== -1;
            }

            block.style.display = isVisible ? '' : 'none';

            block.querySelectorAll('input, select, textarea').forEach(function (control) {
                if (!control.dataset.conditionalOriginalDisabled) {
                    control.dataset.conditionalOriginalDisabled = control.disabled ? '1' : '0';
                }

                control.disabled = isVisible ? control.dataset.conditionalOriginalDisabled === '1' : true;
            });
        });
    }

    function initConditionalAdminFields() {
        document.querySelectorAll('.admin-form-card form').forEach(function (form) {
            var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
            if (!conditionalBlocks.length) {
                return;
            }

            Array.from(new Set(Array.from(conditionalBlocks).map(function (block) {
                return block.dataset.visibilitySource;
            }))).forEach(function (sourceName) {
                var source = form.elements.namedItem(sourceName);
                if (source) {
                    source.addEventListener('change', function () {
                        syncConditionalFields(form);
                    });
                }
            });

            syncConditionalFields(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConditionalAdminFields);
    } else {
        initConditionalAdminFields();
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\shared\form.blade.php ENDPATH**/ ?>