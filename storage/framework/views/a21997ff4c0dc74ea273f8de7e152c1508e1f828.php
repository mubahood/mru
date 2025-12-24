<div class="<?php echo e($viewClass['form-group'], false); ?> <?php echo !$errors->has($column) ?: 'has-error'; ?>">

    <label for="<?php echo e($id, false); ?>" class="<?php echo e($viewClass['label'], false); ?> control-label"><?php echo e($label, false); ?></label>

    <div class="<?php echo e($viewClass['field'], false); ?>" id="<?php echo e($id, false); ?>">

        <?php if($canCheckAll): ?>
            <span class="icheck">
            <label class="checkbox-inline">
                <input type="checkbox" class="<?php echo e($checkAllClass, false); ?>"/>&nbsp;<?php echo e(__('admin.all'), false); ?>

            </label>
            </span>
            <hr style="margin-top: 10px;margin-bottom: 0;">
        <?php endif; ?>

        <?php echo $__env->make('admin::form.error', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php if($groups): ?>

        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $options): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <p style="<?php echo e($canCheckAll ? 'margin: 15px 0 0 0;' : 'margin: 7px 0 0 0;', false); ?>padding-bottom: 5px;border-bottom: 1px solid #eee;display: inline-block;"><?php echo e($group, false); ?></p>

            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="checkbox icheck">

                <label>
                    <input type="checkbox" name="<?php echo e($name, false); ?>[]" value="<?php echo e($option, false); ?>" class="<?php echo e($class, false); ?>" <?php echo e(false !== array_search($option, array_filter(old($column, $value ?? []))) || ($value === null && in_array($option, $checked)) ?'checked':'', false); ?> <?php echo $attributes; ?> />&nbsp;<?php echo e($label, false); ?>&nbsp;&nbsp;
                </label>

            </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php else: ?>

        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php echo $inline ? '<span class="icheck">' : '<div class="checkbox icheck">'; ?>


                <label <?php if($inline): ?>class="checkbox-inline"<?php endif; ?>>
                    <input type="checkbox" name="<?php echo e($name, false); ?>[]" value="<?php echo e($option, false); ?>" class="<?php echo e($class, false); ?>" <?php echo e(false !== array_search($option, array_filter(old($column, $value ?? []))) || ($value === null && in_array($option, $checked)) ?'checked':'', false); ?> <?php echo $attributes; ?> />&nbsp;<?php echo e($label, false); ?>&nbsp;&nbsp;
                </label>

            <?php echo $inline ? '</span>' :  '</div>'; ?>


        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php endif; ?>

        <input type="hidden" name="<?php echo e($name, false); ?>[]">

        <?php echo $__env->make('admin::form.help-block', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </div>
</div>
<?php /**PATH /Applications/MAMP/htdocs/mru/vendor/encore/laravel-admin/src/../resources/views/form/checkbox.blade.php ENDPATH**/ ?>