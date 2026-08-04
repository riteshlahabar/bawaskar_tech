<?php $__env->startSection('title', $pageTitle); ?>
<?php $__env->startSection('content'); ?>
<?php
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image'], true));
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

                <form method="POST" action="<?php echo e($record ? route($module['route'].'.update', $record->getKey()) : route($module['route'].'.store')); ?>" <?php if($hasUpload): ?> enctype="multipart/form-data" <?php endif; ?>>
                    <?php echo csrf_field(); ?>
                    <?php if($record): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                    <div class="row g-3">
                        <?php $__currentLoopData = $module['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($field['display_only'] ?? false) continue; ?>
                            <?php ($name = $field['name']); ?>
                            <?php ($type = $field['type'] ?? 'text'); ?>
                            <?php ($value = old($name, $formData[$name] ?? ($field['default'] ?? null))); ?>

                            <div class="<?php echo e($field['col'] ?? 'col-md-6'); ?>">
                                <?php if($type === 'checkbox'): ?>
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" class="form-check-input" name="<?php echo e($name); ?>" value="1" id="<?php echo e($name); ?>" <?php if((bool) $value): echo 'checked'; endif; ?>>
                                        <label class="form-check-label" for="<?php echo e($name); ?>"><?php echo e($field['label']); ?></label>
                                    </div>
                                <?php else: ?>
                                    <label class="form-label">
                                        <?php echo e($field['label']); ?>

                                        <?php if(str_contains(implode('|', array_map('strval', (array) ($field['rules'] ?? []))), 'required')): ?>
                                            <span class="text-danger">*</span>
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
unset($__errorArgs, $__bag); ?>" name="<?php echo e($name); ?>" <?php if($field['required'] ?? false): echo 'required'; endif; ?>>
                                            <option value="">Select <?php echo e($field['label']); ?></option>
                                            <?php $__currentLoopData = $options[$name] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>" <?php if((string) $value === (string) $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
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
unset($__errorArgs, $__bag); ?>" name="<?php echo e($name); ?>"><?php echo e($value); ?></textarea>
                                    <?php elseif(in_array($type, ['file', 'image'], true)): ?>
                                        <input class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="file" name="<?php echo e($name); ?>" accept="<?php echo e($field['accept'] ?? ($type === 'image' ? 'image/*' : '')); ?>">
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
unset($__errorArgs, $__bag); ?>" type="<?php echo e($type); ?>" name="<?php echo e($name); ?>" value="<?php echo e($type === 'password' ? '' : $value); ?>" step="<?php echo e($field['step'] ?? null); ?>" placeholder="<?php echo e($field['placeholder'] ?? ''); ?>">
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
                        <a class="btn btn-outline-secondary" href="<?php echo e(route($module['route'].'.index')); ?>">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i><?php echo e($record ? 'Update' : 'Save'); ?> <?php echo e($module['singular']); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\All Project flutter laravel reactjs python\Bawaskar Technology\bawaskar_erp\resources\views\admin\shared\form.blade.php ENDPATH**/ ?>